<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TauxRole;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Controller TauxRoleController - Gestion des taux par rôle
 * 
 * Ce controller gère toutes les opérations CRUD sur les taux des rôles.
 * Seuls les administrateurs peuvent modifier les taux, les autres rôles
 * ne peuvent que les consulter.
 */
class TauxRoleController extends Controller
{
    /**
     * Afficher tous les taux par rôle
     * 
     * @return \Illuminate\Http\JsonResponse
     * @security Tous les utilisateurs authentifiés peuvent voir les taux
     */
    public function index()
    {
        try {
            // Récupérer uniquement les taux actifs, triés par rôle
            $tauxRoles = TauxRole::active()
                ->orderBy('role')
                ->get()
                ->map(function ($taux) {
                    return [
                        'id' => $taux->id,
                        'role' => $taux->role,
                        'type' => $this->getRoleType($taux->role, $taux),
                        'taux_forfaitaire' => $taux->taux_forfaitaire,
                        'taux_journalier' => $taux->taux_journalier,
                        'taux_base_correcteur' => $taux->taux_base_correcteur,
                        'taux_surplus_bep' => $taux->taux_surplus_bep,
                        'taux_surplus_autres' => $taux->taux_surplus_autres,
                        'is_active' => $taux->is_active,
                        'can_be_deleted' => !$taux->isUsedByAgents(),
                        'created_by' => $taux->created_by,
                        'updated_by' => $taux->updated_by,
                        'created_at' => $taux->created_at,
                        'updated_at' => $taux->updated_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $tauxRoles,
                'message' => 'Liste des taux récupérée avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des taux: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des taux'
            ], 500);
        }
    }

    /**
     * Déterminer le type de taux selon le rôle
     * 
     * @param string $role
     * @param object $taux
     * @return string
     */
    private function getRoleType($role, $taux = null)
    {
        if (in_array($role, ['PDO', 'VPDO', 'CDC', 'CDCA'])) {
            return 'forfaitaire';
        } elseif (in_array($role, ['Secretaire', 'SecOrg', 'Surveillance', 'Securite'])) {
            return 'journalier';
        } elseif ($role === 'Correcteur') {
            return 'correcteur';
        }
        
        // Pour les rôles personnalisés, déterminer le type selon les champs disponibles
        if ($taux) {
            if ($taux->taux_forfaitaire) {
                return 'forfaitaire';
            } elseif ($taux->taux_journalier) {
                return 'journalier';
            } elseif ($taux->taux_base_correcteur) {
                return 'correcteur';
            }
        }
        
        return 'custom'; // Type par défaut pour les rôles personnalisés
    }

    /**
     * Enregistrer un nouveau taux pour un rôle
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @security Seuls les Admin peuvent créer des taux
     */
    public function store(Request $request)
    {
        try {
            // Validation des données d'entrée avec messages personnalisés
            $validator = Validator::make($request->all(), [
                'role' => [
                    'required', 
                    'string', 
                    'max:255',
                    'unique:taux_roles,role'
                ],
                'taux_forfaitaire' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_journalier' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_base_correcteur' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_surplus_bep' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_surplus_autres' => 'nullable|numeric|min:0|max:999999999.99'
            ], [
                'role.required' => 'Le rôle est obligatoire',
                'role.string' => 'Le rôle doit être une chaîne de caractères',
                'role.max' => 'Le nom du rôle ne peut pas dépasser 255 caractères',
                'role.unique' => 'Ce rôle existe déjà dans le système',
                'taux_forfaitaire.numeric' => 'Le taux forfaitaire doit être un nombre',
                'taux_forfaitaire.min' => 'Le taux forfaitaire ne peut pas être négatif',
                'taux_journalier.numeric' => 'Le taux journalier doit être un nombre',
                'taux_journalier.min' => 'Le taux journalier ne peut pas être négatif',
                'taux_base_correcteur.numeric' => 'Le taux de base correcteur doit être un nombre',
                'taux_base_correcteur.min' => 'Le taux de base correcteur ne peut pas être négatif',
                'taux_surplus_bep.numeric' => 'Le taux surplus BEP doit être un nombre',
                'taux_surplus_bep.min' => 'Le taux surplus BEP ne peut pas être négatif',
                'taux_surplus_autres.numeric' => 'Le taux surplus autres doit être un nombre',
                'taux_surplus_autres.min' => 'Le taux surplus autres ne peut pas être négatif'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation des données',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validation spécifique selon le type de rôle
            $validationError = $this->validateRoleSpecificTaux($request);
            if ($validationError) {
                return response()->json([
                    'success' => false,
                    'message' => $validationError
                ], 422);
            }

            // Création du taux de rôle avec transaction pour la sécurité
            DB::beginTransaction();
            
            $tauxRole = TauxRole::create($request->all());
            
            // Journalisation de l'opération
            $this->logOperation('création', "Création d'un nouveau taux pour le rôle : {$tauxRole->role}", [
                'taux_role' => $tauxRole->fresh()->toArray()
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Taux créé avec succès',
                'data' => $tauxRole->fresh()
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la création du taux: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création du taux',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Valider les taux spécifiques selon le rôle
     * 
     * @param Request $request
     * @return string|null Message d'erreur ou null si valide
     */
    private function validateRoleSpecificTaux($request)
    {
        $role = $request->role;
        
        // Rôles forfaitaires
        if (in_array($role, ['PDO', 'VPDO', 'CDC', 'CDCA'])) {
            if (!$request->has('taux_forfaitaire') || $request->taux_forfaitaire <= 0) {
                return 'Le taux forfaitaire est requis et doit être supérieur à 0 pour ce rôle';
            }
        }
        
        // Rôles journaliers
        elseif (in_array($role, ['Secretaire', 'SecOrg', 'Surveillance', 'Securite'])) {
            if (!$request->has('taux_journalier') || $request->taux_journalier <= 0) {
                return 'Le taux journalier est requis et doit être supérieur à 0 pour ce rôle';
            }
        }
        
        // Rôle correcteur
        elseif ($role === 'Correcteur') {
            $required = ['taux_base_correcteur', 'taux_surplus_bep', 'taux_surplus_autres'];
            foreach ($required as $field) {
                if (!$request->has($field) || $request->$field <= 0) {
                    return 'Tous les taux de correction sont requis et doivent être supérieurs à 0';
                }
            }
        }
        
        return null; // Validation réussie
    }

    /**
     * Journaliser une opération sur les taux
     * 
     * @param string $action
     * @param string $operation
     * @param array $details
     */
    private function logOperation($action, $operation, $details = [])
    {
        try {
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::user()->nom_prenom,
                'operations' => $operation,
                'cin' => Auth::user()->cin,
                'nom_prenom' => Auth::user()->nom_prenom,
                'autres' => json_encode(array_merge($details, [
                    'action' => $action,
                    'user_id' => Auth::id(),
                    'ip_address' => request()->ip()
                ]))
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la journalisation: ' . $e->getMessage());
        }
    }

    /**
     * Afficher un taux spécifique
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @security Tous les utilisateurs authentifiés peuvent voir un taux spécifique
     */
    public function show($id)
    {
        try {
            $tauxRole = TauxRole::withTrashed()->find($id);
            
            if (!$tauxRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taux non trouvé'
                ], 404);
            }

            // Formater la réponse avec des informations supplémentaires
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tauxRole->id,
                    'role' => $tauxRole->role,
                    'type' => $this->getRoleType($tauxRole->role),
                    'taux_forfaitaire' => $tauxRole->taux_forfaitaire,
                    'taux_journalier' => $tauxRole->taux_journalier,
                    'taux_base_correcteur' => $tauxRole->taux_base_correcteur,
                    'taux_surplus_bep' => $tauxRole->taux_surplus_bep,
                    'taux_surplus_autres' => $tauxRole->taux_surplus_autres,
                    'is_active' => $tauxRole->is_active,
                    'is_deleted' => !is_null($tauxRole->deleted_at),
                    'can_be_deleted' => !$tauxRole->isUsedByAgents(),
                    'created_by' => $tauxRole->created_by,
                    'updated_by' => $tauxRole->updated_by,
                    'created_at' => $tauxRole->created_at,
                    'updated_at' => $tauxRole->updated_at,
                    'deleted_at' => $tauxRole->deleted_at
                ],
                'message' => 'Taux récupéré avec succès'
            ]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération du taux: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du taux'
            ], 500);
        }
    }

    /**
     * Mettre à jour un taux existant
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @security Seuls les Admin peuvent modifier des taux
     */
    public function update(Request $request, $id)
    {
        try {
            $tauxRole = TauxRole::find($id);
            
            if (!$tauxRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taux non trouvé'
                ], 404);
            }

            // Validation des données d'entrée
            $validator = Validator::make($request->all(), [
                'taux_forfaitaire' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_journalier' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_base_correcteur' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_surplus_bep' => 'nullable|numeric|min:0|max:999999999.99',
                'taux_surplus_autres' => 'nullable|numeric|min:0|max:999999999.99',
                'is_active' => 'boolean'
            ], [
                'taux_forfaitaire.numeric' => 'Le taux forfaitaire doit être un nombre',
                'taux_forfaitaire.min' => 'Le taux forfaitaire ne peut pas être négatif',
                'taux_journalier.numeric' => 'Le taux journalier doit être un nombre',
                'taux_journalier.min' => 'Le taux journalier ne peut pas être négatif',
                'taux_base_correcteur.numeric' => 'Le taux de base correcteur doit être un nombre',
                'taux_base_correcteur.min' => 'Le taux de base correcteur ne peut pas être négatif',
                'taux_surplus_bep.numeric' => 'Le taux surplus BEP doit être un nombre',
                'taux_surplus_bep.min' => 'Le taux surplus BEP ne peut pas être négatif',
                'taux_surplus_autres.numeric' => 'Le taux surplus autres doit être un nombre',
                'taux_surplus_autres.min' => 'Le taux surplus autres ne peut pas être négatif',
                'is_active.boolean' => 'Le statut actif doit être un booléen'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Erreur de validation des données',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Validation spécifique selon le rôle
            $validationError = $this->validateUpdateRoleSpecificTaux($request, $tauxRole);
            if ($validationError) {
                return response()->json([
                    'success' => false,
                    'message' => $validationError
                ], 422);
            }

            // Mise à jour avec transaction
            DB::beginTransaction();
            
            $oldData = $tauxRole->toArray();
            $tauxRole->update($request->all());
            
            // Journalisation détaillée
            $this->logOperation('modification', "Modification du taux pour le rôle : {$tauxRole->role}", [
                'ancien' => $oldData,
                'nouveau' => $tauxRole->fresh()->toArray(),
                'modifications' => array_diff_assoc($tauxRole->fresh()->toArray(), $oldData)
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Taux mis à jour avec succès',
                'data' => $tauxRole->fresh()
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la mise à jour du taux: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la mise à jour du taux',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Valider les taux spécifiques lors de la mise à jour
     * 
     * @param Request $request
     * @param TauxRole $tauxRole
     * @return string|null Message d'erreur ou null si valide
     */
    private function validateUpdateRoleSpecificTaux($request, $tauxRole)
    {
        $role = $tauxRole->role;
        
        // Rôles forfaitaires
        if (in_array($role, ['PDO', 'VPDO', 'CDC', 'CDCA'])) {
            if ($request->has('taux_forfaitaire') && $request->taux_forfaitaire <= 0) {
                return 'Le taux forfaitaire doit être supérieur à 0 pour ce rôle';
            }
        }
        
        // Rôles journaliers
        elseif (in_array($role, ['Secretaire', 'SecOrg', 'Surveillance', 'Securite'])) {
            if ($request->has('taux_journalier') && $request->taux_journalier <= 0) {
                return 'Le taux journalier doit être supérieur à 0 pour ce rôle';
            }
        }
        
        // Rôle correcteur
        elseif ($role === 'Correcteur') {
            $fields = ['taux_base_correcteur', 'taux_surplus_bep', 'taux_surplus_autres'];
            foreach ($fields as $field) {
                if ($request->has($field) && $request->$field <= 0) {
                    return 'Les taux de correction doivent être supérieurs à 0';
                }
            }
        }
        
        return null; // Validation réussie
    }

    /**
     * Supprimer un taux
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @security Seuls les Admin peuvent supprimer des taux
     */
    public function destroy($id)
    {
        try {
            $tauxRole = TauxRole::find($id);
            
            if (!$tauxRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taux non trouvé'
                ], 404);
            }

            // Vérifier si le taux est utilisé par des agents
            if ($tauxRole->isUsedByAgents()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Impossible de supprimer ce taux car il est utilisé par des agents. Vous pouvez le désactiver à la place.',
                    'suggestion' => 'Utilisez la méthode PUT pour désactiver le taux (is_active: false)'
                ], 422);
            }

            // Suppression avec transaction
            DB::beginTransaction();
            
            $tauxRoleData = $tauxRole->toArray();
            $tauxRole->delete();
            
            // Journalisation
            $this->logOperation('suppression', "Suppression du taux pour le rôle : {$tauxRoleData['role']}", [
                'taux_role' => $tauxRoleData
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Taux supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Erreur lors de la suppression du taux: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la suppression du taux',
                'error' => config('app.debug') ? $e->getMessage() : 'Erreur interne du serveur'
            ], 500);
        }
    }

    /**
     * Récupérer le taux par rôle
     * 
     * @param string $role
     * @return \Illuminate\Http\JsonResponse
     * @security Tous les utilisateurs authentifiés peuvent récupérer un taux par rôle
     */
    public function getTauxByRole($role)
    {
        try {
            // Valider que le rôle est valide
            $validRoles = ['PDO', 'VPDO', 'CDC', 'CDCA', 'Secretaire', 'SecOrg', 'Surveillance', 'Securite', 'Correcteur'];
            
            if (!in_array($role, $validRoles)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Rôle non valide',
                    'valid_roles' => $validRoles
                ], 400);
            }

            $tauxRole = TauxRole::active()->where('role', $role)->first();
            
            if (!$tauxRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun taux trouvé pour ce rôle',
                    'role' => $role
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tauxRole->id,
                    'role' => $tauxRole->role,
                    'type' => $this->getRoleType($tauxRole->role),
                    'taux_forfaitaire' => $tauxRole->taux_forfaitaire,
                    'taux_journalier' => $tauxRole->taux_journalier,
                    'taux_base_correcteur' => $tauxRole->taux_base_correcteur,
                    'taux_surplus_bep' => $tauxRole->taux_surplus_bep,
                    'taux_surplus_autres' => $tauxRole->taux_surplus_autres,
                    'is_active' => $tauxRole->is_active,
                    'updated_at' => $tauxRole->updated_at
                ],
                'message' => 'Taux récupéré avec succès'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération du taux par rôle: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du taux'
            ], 500);
        }
    }

    /**
     * Désactiver un taux (alternative à la suppression)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @security Seuls les Admin peuvent désactiver des taux
     */
    public function deactivate($id)
    {
        try {
            $tauxRole = TauxRole::find($id);
            
            if (!$tauxRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taux non trouvé'
                ], 404);
            }

            if (!$tauxRole->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce taux est déjà désactivé'
                ], 422);
            }

            // Utiliser la méthode du modèle pour la désactivation
            $tauxRole->deactivate();
            
            // Journalisation
            $this->logOperation('désactivation', "Désactivation du taux pour le rôle : {$tauxRole->role}", [
                'taux_role' => $tauxRole->fresh()->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Taux désactivé avec succès',
                'data' => $tauxRole->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la désactivation du taux: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'suggestion' => 'Vérifiez que ce taux n\'est pas utilisé par des agents actifs'
            ], 422);
        }
    }

    /**
     * Réactiver un taux
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     * @security Seuls les Admin peuvent réactiver des taux
     */
    public function reactivate($id)
    {
        try {
            $tauxRole = TauxRole::find($id);
            
            if (!$tauxRole) {
                return response()->json([
                    'success' => false,
                    'message' => 'Taux non trouvé'
                ], 404);
            }

            if ($tauxRole->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce taux est déjà actif'
                ], 422);
            }

            $tauxRole->update(['is_active' => true]);
            
            // Journalisation
            $this->logOperation('réactivation', "Réactivation du taux pour le rôle : {$tauxRole->role}", [
                'taux_role' => $tauxRole->fresh()->toArray()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Taux réactivé avec succès',
                'data' => $tauxRole->fresh()
            ]);

        } catch (\Exception $e) {
            \Log::error('Erreur lors de la réactivation du taux: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la réactivation du taux'
            ], 500);
        }
    }
}
