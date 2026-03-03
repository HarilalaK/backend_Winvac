<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentDetail;
use App\Models\TauxRole;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with(['centre', 'detail'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $agents
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validation des données de base de l'agent
            $validatedAgentData = $request->validate([
                'agent.annee' => 'required|integer',
                'agent.centre_id' => 'required|exists:centres,id',
                'agent.situation' => ['required', Rule::in(['permanant', 'non permanant'])],
                'agent.role' => ['required', Rule::in(['PDO', 'VPDO', 'CDC', 'CDCA', 'Secretaire', 'SecOrg', 'Surveillance', 'Securite', 'Correcteur'])],
                'agent.typeExamen' => ['required', Rule::in(['BEP', 'CFA', 'CAP', 'ConcoursLTP', 'ConcoursCFP'])],
                'agent.im' => 'nullable|string',
                'agent.cin' => 'required|string|unique:agents,cin',
                'agent.nom' => 'required|string',
                'agent.prenom' => 'required|string',
                'agent.sexe' => ['required', Rule::in(['M', 'F'])],
                'agent.lieu_cin' => 'required|string',
                'agent.date_cin' => 'required|date',
            ]);

            // Créer l'agent
            $agent = Agent::create($validatedAgentData['agent']);

            // Pour les rôles fixes, créer automatiquement les détails
            if (in_array($agent->role, ['PDO', 'VPDO', 'CDC', 'CDCA'])) {
                $this->handleAgentDetails($agent, $request);
            }
            // Pour les autres rôles, traiter les détails s'ils sont fournis
            elseif ($request->has('details')) {
                $detailsData = [];
                
                switch ($agent->role) {
                    case 'Secretaire':
                    case 'SecOrg':
                    case 'Securite':
                        $validatedDetails = $request->validate([
                            'details.jours_travaille' => 'required|integer|min:1'
                        ]);
                        $detailsData = $validatedDetails['details'];
                        break;

                    case 'Surveillance':
                        $validatedDetails = $request->validate([
                            'details.jours_surveillance' => 'required|integer|min:0',
                            'details.jours_encours' => 'required|integer|min:0',
                            'details.jours_ensalles' => 'required|integer|min:0'
                        ]);
                        $detailsData = $validatedDetails['details'];
                        break;

                    case 'Correcteur':
                        $validatedDetails = $request->validate([
                            'details.matiere' => 'required|string',
                            'details.nombre_copie' => 'required|integer|min:0'
                        ]);
                        $detailsData = $validatedDetails['details'];
                        break;
                }

                if (!empty($detailsData)) {
                    // Récupérer les taux correspondants
                    $tauxRole = TauxRole::where('role', $agent->role)->first();
                    if ($tauxRole) {
                        $detailsData = array_merge($detailsData, $this->calculateMontants($agent->role, $detailsData, $tauxRole));
                    }
                    $agent->detail()->create($detailsData);
                }
            }

            // Journalisation après la création de l'agent et ses détails
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::user()->nom_prenom,
                'operations' => "Ajout d'un agent : {$agent->nom} {$agent->prenom} (CIN: {$agent->cin})",
                'cin' => Auth::user()->cin,
                'nom_prenom' => Auth::user()->nom_prenom,
                'autres' => json_encode([
                    'agent' => [
                        'im' => $agent->im,
                        'cin' => $agent->cin,
                        'nom' => $agent->nom,
                        'prenom' => $agent->prenom,
                        'role' => $agent->role,
                        'situation' => $agent->situation,
                        'centre' => $agent->centre->nom ?? null,
                        'type_examen' => $agent->typeExamen
                    ],
                    'details' => $agent->detail ? $agent->detail->toArray() : null,
                    'action' => 'création'
                ])
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Agent créé avec succès',
                'data' => $agent->load(['centre', 'detail'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la création de l\'agent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $agent = Agent::with(['centre', 'detail'])->findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $agent
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Agent non trouvé',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $agent = Agent::findOrFail($id);
            $oldData = $agent->toArray();
            $oldDetails = $agent->detail ? $agent->detail->toArray() : null;

            // Validation des données de base de l'agent
            if ($request->has('agent')) {
                $validatedAgentData = $request->validate([
                    'agent.annee' => 'sometimes|integer',
                    'agent.centre_id' => 'sometimes|exists:centres,id',
                    'agent.situation' => ['sometimes', Rule::in(['permanant', 'non permanant'])],
                    'agent.role' => ['sometimes', Rule::in(['PDO', 'VPDO', 'CDC', 'CDCA', 'Secretaire', 'SecOrg', 'Surveillance', 'Securite', 'Correcteur'])],
                    'agent.typeExamen' => ['sometimes', Rule::in(['BEP', 'CFA', 'CAP', 'ConcoursLTP', 'ConcoursCFP'])],
                    'agent.im' => 'nullable|string',
                    'agent.cin' => 'sometimes|string|unique:agents,cin,' . $id,
                    'agent.nom' => 'sometimes|string',
                    'agent.prenom' => 'sometimes|string',
                    'agent.sexe' => ['sometimes', Rule::in(['M', 'F'])],
                    'agent.lieu_cin' => 'sometimes|string',
                    'agent.date_cin' => 'sometimes|date',
                ]);

                $agent->update($validatedAgentData['agent']);
            }

            // Mise à jour des détails
            if ($request->has('details')) {
                $detailsData = [];
                
                switch ($agent->role) {
                    case 'Secretaire':
                    case 'SecOrg':
                    case 'Securite':
                        $validatedDetails = $request->validate([
                            'details.jours_travaille' => 'required|integer|min:1'
                        ]);
                        $detailsData = $validatedDetails['details'];
                        break;

                    case 'Surveillance':
                        $validatedDetails = $request->validate([
                            'details.jours_surveillance' => 'required|integer|min:0',
                            'details.jours_encours' => 'required|integer|min:0',
                            'details.jours_ensalles' => 'required|integer|min:0'
                        ]);
                        $detailsData = $validatedDetails['details'];
                        break;

                    case 'Correcteur':
                        $validatedDetails = $request->validate([
                            'details.matiere' => 'required|string',
                            'details.nombre_copie' => 'required|integer|min:0'
                        ]);
                        $detailsData = $validatedDetails['details'];
                        break;
                }

                if (!empty($detailsData)) {
                    $tauxRole = TauxRole::where('role', $agent->role)->first();
                    if ($tauxRole) {
                        $detailsData = array_merge($detailsData, $this->calculateMontants($agent->role, $detailsData, $tauxRole));
                    }
                    
                    if ($agent->detail) {
                        $agent->detail->update($detailsData);
                    } else {
                        $agent->detail()->create($detailsData);
                    }
                }
            }

            // Journalisation après la mise à jour
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::user()->nom_prenom,
                'operations' => "Modification de l'agent : {$agent->nom} {$agent->prenom} (CIN: {$agent->cin})",
                'cin' => Auth::user()->cin,
                'nom_prenom' => Auth::user()->nom_prenom,
                'autres' => json_encode([
                    'modifications' => array_diff_assoc($agent->toArray(), $oldData),
                    'ancien' => [
                        'agent' => $oldData,
                        'details' => $oldDetails
                    ],
                    'nouveau' => [
                        'agent' => $agent->toArray(),
                        'details' => $agent->detail ? $agent->detail->toArray() : null
                    ],
                    'action' => 'modification'
                ])
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Agent modifié avec succès',
                'data' => $agent->load(['centre', 'detail'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la modification de l\'agent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $agent = Agent::findOrFail($id);
            $agentData = $agent->toArray();
            
            $agent->delete();

            // Journalisation
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::user()->nom_prenom,
                'operations' => "Suppression de l'agent : {$agentData['nom']} {$agentData['prenom']} (CIN: {$agentData['cin']})",
                'cin' => Auth::user()->cin,
                'nom_prenom' => Auth::user()->nom_prenom,
                'autres' => json_encode([
                    'agent' => $agentData,
                    'details' => $agent->detail ? $agent->detail->toArray() : null,
                    'action' => 'suppression'
                ])
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Agent supprimé avec succès'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression de l\'agent',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function filter(Request $request)
    {
        try {
            $query = Agent::with(['centre.region.province', 'detail']);

            // Filtre par type d'examen
            if ($request->filled('typeExamen')) {
                $query->where('typeExamen', $request->typeExamen);
            }

            // Filtre par session/année
            if ($request->has('session') && $request->session !== '') {
                $query->where('annee', $request->session);
            }

            // Filtre par province
            if ($request->has('province') && $request->province !== '') {
                $query->whereHas('centre.region.province', function($q) use ($request) {
                    $q->where('id', $request->province);
                });
            }

            // Filtre par région
            if ($request->has('region') && $request->region !== '') {
                $query->whereHas('centre.region', function($q) use ($request) {
                    $q->where('id', $request->region);
                });
            }

            // Filtre par centre
            if ($request->filled('centre_id')) {
                $query->where('centre_id', $request->centre_id);
            }

            // Filtre par rôle
            if ($request->filled('role')) {
                $query->where('role', $request->role);
            }

            // Filtre par situation
            if ($request->has('situation') && $request->situation !== '') {
                $query->where('situation', $request->situation);
            }

            $agents = $query->get();

            // Calculer les totaux
            $totals = [
                'taux_brut_total' => $agents->sum(function($agent) {
                    return $agent->detail ? $agent->detail->taux_brut : 0;
                }),
                'irsa_total' => $agents->sum(function($agent) {
                    return $agent->detail ? $agent->detail->irsa : 0;
                }),
                'taux_net_total' => $agents->sum(function($agent) {
                    return $agent->detail ? $agent->detail->taux_net : 0;
                }),
                'nombre_agents' => $agents->count()
            ];

            return response()->json([
                'status' => 'success',
                'data' => $agents,
                'totals' => $totals,
                'message' => 'Filtrage effectué avec succès'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors du filtrage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getDecompte(Request $request)
    {
        try {
            $query = Agent::with(['centre', 'detail']);

            if ($request->filled('typeExamen')) {
                $query->where('typeExamen', $request->typeExamen);
            }
            
            if ($request->filled('session')) {
                $query->where('annee', $request->session);
            }
            
            if ($request->filled('centre_id')) {
                $query->where('centre_id', $request->centre_id);
            }

            $agents = $query->get();

            // Calculer les totaux depuis les détails
            $totals = [
                'taux_brut' => $agents->sum(function($agent) {
                    return $agent->detail ? $agent->detail->taux_brut : 0;
                }),
                'irsa' => $agents->sum(function($agent) {
                    return $agent->detail ? $agent->detail->irsa : 0;
                }),
                'net_a_payer' => $agents->sum(function($agent) {
                    return $agent->detail ? $agent->detail->taux_net : 0;
                })
            ];

            return response()->json([
                'status' => 'success',
                'data' => [
                    'data' => $agents,
                    'totals' => $totals
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateMontants($role, $details, $tauxRole)
    {
        $taux_brut = 0;

        switch ($role) {
            case 'PDO':
            case 'VPDO':
            case 'CDC':
            case 'CDCA':
                $taux_brut = $tauxRole->taux_forfaitaire;
                break;

            case 'Secretaire':
            case 'SecOrg':
            case 'Securite':
                $taux_brut = $tauxRole->taux_journalier * $details['jours_travaille'];
                break;

            case 'Surveillance':
                $total_jours = $details['jours_surveillance'] + $details['jours_encours'] + $details['jours_ensalles'];
                $taux_brut = $tauxRole->taux_journalier * $total_jours;
                break;

            case 'Correcteur':
                if ($details['nombre_copie'] <= 100) {
                    $taux_brut = $tauxRole->taux_base_correcteur;
                } else {
                    $surplus = $details['nombre_copie'] - 100;
                    $taux_surplus = $tauxRole->taux_surplus_bep;
                    $taux_brut = $tauxRole->taux_base_correcteur + ($surplus * $taux_surplus);
                }
                break;
        }

        // Calcul de l'IRSA (à adapter selon vos règles)
        $irsa = $this->calculateIRSA($taux_brut);
        $taux_net = $taux_brut - $irsa;

        return [
            'taux_brut' => $taux_brut,
            'irsa' => $irsa,
            'taux_net' => $taux_net
        ];
    }

    /**
     * Gérer les détails financiers d'un agent en utilisant le système de taux dynamique
     * 
     * @param Agent $agent
     * @param Request $request
     * @return bool
     * @security Cette méthode utilise le système TauxRole pour calculer les montants
     */
    private function handleAgentDetails($agent, $request)
    {
        try {
            // Récupérer le taux actif pour ce rôle
            $tauxRole = TauxRole::active()->where('role', $agent->role)->first();
            
            if (!$tauxRole) {
                \Log::error("Aucun taux trouvé pour le rôle: {$agent->role}");
                throw new \Exception("Aucun taux configuré pour le rôle: {$agent->role}");
            }
            
            $detailsData = [];

            // Préparer les paramètres selon le rôle pour le calcul
            $params = $this->prepareParamsForRole($agent->role, $request);
            
            // Utiliser la méthode de calcul du modèle TauxRole
            $tauxBrut = $tauxRole->calculateTauxBrut($params);
            
            // Construire les données de détails selon le rôle
            $detailsData = $this->buildDetailsData($agent->role, $request, $tauxRole, $tauxBrut);
            
            // Calcul de l'IRSA (20%) et du taux net
            $detailsData['irsa'] = $this->calculateIRSA($tauxBrut);
            $detailsData['taux_net'] = $tauxBrut - $detailsData['irsa'];

            // Créer ou mettre à jour les détails de l'agent
            if ($agent->detail) {
                $agent->detail->update($detailsData);
                \Log::info("Détails de l'agent {$agent->id} mis à jour avec succès");
            } else {
                $agent->detail()->create($detailsData);
                \Log::info("Détails de l'agent {$agent->id} créés avec succès");
            }

            return true;
        } catch (\Exception $e) {
            \Log::error("Erreur lors du traitement des détails de l'agent {$agent->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Préparer les paramètres pour le calcul selon le rôle
     * 
     * @param string $role
     * @param Request $request
     * @return array
     */
    private function prepareParamsForRole($role, $request)
    {
        switch ($role) {
            case 'PDO':
            case 'VPDO':
            case 'CDC':
            case 'CDCA':
                return []; // Les rôles forfaitaires n'ont pas besoin de paramètres
                
            case 'Secretaire':
            case 'SecOrg':
            case 'Securite':
                return [
                    'jours_travaille' => $request->jours_travaille ?? 1
                ];
                
            case 'Surveillance':
                return [
                    'jours_surveillance' => $request->jours_surveillance ?? 0,
                    'jours_encours' => $request->jours_encours ?? 0,
                    'jours_ensalles' => $request->jours_ensalles ?? 0
                ];
                
            case 'Correcteur':
                return [
                    'nombre_copie' => $request->nombre_copie ?? 0,
                    'type_examen' => $request->type_examen ?? 'Autres'
                ];
                
            default:
                return [];
        }
    }

    /**
     * Construire les données de détails selon le rôle
     * 
     * @param string $role
     * @param Request $request
     * @param TauxRole $tauxRole
     * @param float $tauxBrut
     * @return array
     */
    private function buildDetailsData($role, $request, $tauxRole, $tauxBrut)
    {
        $baseData = [
            'taux_brut' => $tauxBrut
        ];

        switch ($role) {
            case 'PDO':
            case 'VPDO':
            case 'CDC':
            case 'CDCA':
                return array_merge($baseData, [
                    'taux_forfaitaire' => $tauxRole->taux_forfaitaire
                ]);
                
            case 'Secretaire':
            case 'SecOrg':
            case 'Securite':
                return array_merge($baseData, [
                    'jours_travaille' => $request->jours_travaille ?? 1,
                    'taux_journalier' => $tauxRole->taux_journalier
                ]);
                
            case 'Surveillance':
                return array_merge($baseData, [
                    'jours_surveillance' => $request->jours_surveillance ?? 0,
                    'jours_encours' => $request->jours_encours ?? 0,
                    'jours_ensalles' => $request->jours_ensalles ?? 0,
                    'taux_par_jour' => $tauxRole->taux_journalier
                ]);
                
            case 'Correcteur':
                return array_merge($baseData, [
                    'matiere' => $request->matiere,
                    'nombre_copie' => $request->nombre_copie ?? 0,
                    'taux_base_correcteur' => $tauxRole->taux_base_correcteur,
                    'taux_surplus_bep' => $tauxRole->taux_surplus_bep,
                    'taux_surplus_autres' => $tauxRole->taux_surplus_autres
                ]);
                
            default:
                return $baseData;
        }
    }

    /**
     * Calculer l'IRSA (20% du taux brut)
     * 
     * @param float $montant
     * @return float
     */
    private function calculateIRSA($montant)
    {
        return $montant * 0.20; // IRSA fixé à 20%
    }
}
