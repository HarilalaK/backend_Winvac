<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\AgentDetail;
use App\Models\TauxRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            // Validation et création des détails selon le rôle
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
                    // Récupérer les taux correspondants
                    $tauxRole = TauxRole::where('role', $agent->role)->first();
                    if ($tauxRole) {
                        $detailsData = array_merge($detailsData, $this->calculateMontants($agent->role, $detailsData, $tauxRole));
                    }
                    $agent->detail()->create($detailsData);
                }
            }

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

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Agent mis à jour avec succès',
                'data' => $agent->load(['centre', 'detail'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la mise à jour',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $agent = Agent::findOrFail($id);
            $agent->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Agent supprimé avec succès'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la suppression',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function filter(Request $request)
    {
        try {
            $query = Agent::with(['centre.region.province', 'detail']);

            // Filtre par type d'examen
            if ($request->has('type_examen') && $request->type_examen !== '') {
                $query->where('typeExamen', $request->type_examen);
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
            if ($request->has('centre') && $request->centre !== '') {
                $query->where('centre_id', $request->centre);
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

    private function calculateIRSA($montant)
    {
        // IRSA est de 2%
        return $montant * 0.02;
    }
}
