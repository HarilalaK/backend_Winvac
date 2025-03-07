<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TauxRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;

class TauxRoleController extends Controller
{
    /**
     * Afficher tous les taux par rôle
     */
    public function index()
    {
        return TauxRole::all();
    }

    /**
     * Enregistrer un nouveau taux pour un rôle
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role' => ['required', Rule::in(['PDO', 'VPDO', 'CDC', 'CDCA', 'Secretaire', 'SecOrg', 'Surveillance', 'Securite', 'Correcteur'])],
            'taux_forfaitaire' => 'nullable|numeric|min:0',
            'taux_journalier' => 'nullable|numeric|min:0',
            'taux_base_correcteur' => 'nullable|numeric|min:0',
            'taux_surplus_bep' => 'nullable|numeric|min:0',
            'taux_surplus_autres' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Vérifier si le rôle existe déjà
        if (TauxRole::where('role', $request->role)->exists()) {
            return response()->json(['message' => 'Ce rôle existe déjà'], 409);
        }

        // Validation des taux selon le rôle
        if (in_array($request->role, ['PDO', 'VPDO', 'CDC', 'CDCA'])) {
            if (!$request->taux_forfaitaire) {
                return response()->json(['message' => 'Le taux forfaitaire est requis pour ce rôle'], 422);
            }
        } elseif (in_array($request->role, ['Secretaire', 'SecOrg', 'Surveillance', 'Securite'])) {
            if (!$request->taux_journalier) {
                return response()->json(['message' => 'Le taux journalier est requis pour ce rôle'], 422);
            }
        } elseif ($request->role === 'Correcteur') {
            if (!$request->taux_base_correcteur || !$request->taux_surplus_bep || !$request->taux_surplus_autres) {
                return response()->json(['message' => 'Tous les taux de correction sont requis'], 422);
            }
        }

        $tauxRole = TauxRole::create($request->all());

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Création d'un nouveau taux pour le rôle : {$tauxRole->role}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'taux_role' => $tauxRole->toArray(),
                'action' => 'création'
            ])
        ]);

        return response()->json($tauxRole, 201);
    }

    /**
     * Afficher un taux spécifique
     */
    public function show($id)
    {
        $tauxRole = TauxRole::find($id);
        if (!$tauxRole) {
            return response()->json(['message' => 'Taux non trouvé'], 404);
        }
        return response()->json($tauxRole);
    }

    /**
     * Mettre à jour un taux existant
     */
    public function update(Request $request, $id)
    {
        $tauxRole = TauxRole::find($id);
        if (!$tauxRole) {
            return response()->json(['message' => 'Taux non trouvé'], 404);
        }

        $validator = Validator::make($request->all(), [
            'taux_forfaitaire' => 'nullable|numeric|min:0',
            'taux_journalier' => 'nullable|numeric|min:0',
            'taux_base_correcteur' => 'nullable|numeric|min:0',
            'taux_surplus_bep' => 'nullable|numeric|min:0',
            'taux_surplus_autres' => 'nullable|numeric|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validation des taux selon le rôle
        if (in_array($tauxRole->role, ['PDO', 'VPDO', 'CDC', 'CDCA'])) {
            if (isset($request->taux_forfaitaire) && $request->taux_forfaitaire <= 0) {
                return response()->json(['message' => 'Le taux forfaitaire doit être supérieur à 0'], 422);
            }
        } elseif (in_array($tauxRole->role, ['Secretaire', 'SecOrg', 'Surveillance', 'Securite'])) {
            if (isset($request->taux_journalier) && $request->taux_journalier <= 0) {
                return response()->json(['message' => 'Le taux journalier doit être supérieur à 0'], 422);
            }
        } elseif ($tauxRole->role === 'Correcteur') {
            if ((isset($request->taux_base_correcteur) && $request->taux_base_correcteur <= 0) ||
                (isset($request->taux_surplus_bep) && $request->taux_surplus_bep <= 0) ||
                (isset($request->taux_surplus_autres) && $request->taux_surplus_autres <= 0)) {
                return response()->json(['message' => 'Les taux de correction doivent être supérieurs à 0'], 422);
            }
        }

        $oldData = $tauxRole->toArray();
        $request->merge(['updated_by' => Auth::user()->nom_prenom]);
        $tauxRole->update($request->all());

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Modification du taux pour le rôle : {$tauxRole->role}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'ancien' => $oldData,
                'nouveau' => $tauxRole->toArray(),
                'modifications' => array_diff_assoc($tauxRole->toArray(), $oldData),
                'action' => 'modification'
            ])
        ]);

        return response()->json($tauxRole);
    }

    /**
     * Supprimer un taux
     */
    public function destroy($id)
    {
        $tauxRole = TauxRole::find($id);
        if (!$tauxRole) {
            return response()->json(['message' => 'Taux non trouvé'], 404);
        }

        $tauxRoleData = $tauxRole->toArray();
        $tauxRole->delete();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Suppression du taux pour le rôle : {$tauxRoleData['role']}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'taux_role' => $tauxRoleData,
                'action' => 'suppression'
            ])
        ]);

        return response()->json(['message' => 'Taux supprimé avec succès']);
    }

    /**
     * Récupérer le taux par rôle
     */
    public function getTauxByRole($role)
    {
        $tauxRole = TauxRole::where('role', $role)->first();
        if (!$tauxRole) {
            return response()->json(['message' => 'Taux non trouvé pour ce rôle'], 404);
        }
        return response()->json($tauxRole);
    }
}
