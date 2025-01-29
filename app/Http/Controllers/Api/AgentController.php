<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use App\Models\PdoDetail;
use App\Models\VpdoDetail;
use App\Models\CdcDetail;
use App\Models\CdcaDetail;
use App\Models\SecretaireDetail;
use App\Models\SecOrgDetail;
use App\Models\SurveillanceDetail;
use App\Models\SecuriteDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with(['centre'])->get();
        return response()->json([
            'status' => 'success',
            'data' => $agents
        ]);
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validation commune pour tous les agents
            $validatedData = $request->validate([
                'nom' => 'required|string|max:255',
                'prenom' => 'required|string|max:255',
                'centre_id' => 'required|exists:centres,id',
                'role' => ['required', Rule::in(['PDO', 'VPDO', 'CDC', 'CDCA', 'Secretaire', 'SecOrg', 'Surveillance', 'Securite'])],
            ]);

            // Créer l'agent
            $agent = Agent::create($validatedData);

            // Traitement spécifique selon le rôle
            switch ($request->role) {
                case 'PDO':
                case 'VPDO':
                case 'CDC':
                case 'CDCA':
                case 'Secretaire':
                case 'SecOrg':
                case 'Securite':
                    $this->validateStandardRole($request);
                    $this->createStandardDetail($agent, $request);
                    break;

                case 'Surveillance':
                    $this->validateSurveillanceRole($request);
                    $this->createSurveillanceDetail($agent, $request);
                    break;
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Agent créé avec succès',
                'data' => $agent->load('centre')
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
            $agent = Agent::with(['centre'])->findOrFail($id);
            $details = $this->getAgentDetailsByRole($agent);

            return response()->json([
                'status' => 'success',
                'data' => [
                    'agent' => $agent,
                    'details' => $details
                ]
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

            // Validation commune
            $validatedData = $request->validate([
                'nom' => 'sometimes|string|max:255',
                'prenom' => 'sometimes|string|max:255',
                'centre_id' => 'sometimes|exists:centres,id',
            ]);

            $agent->update($validatedData);

            // Mise à jour des détails selon le rôle
            if ($request->has('jours_travaille') || $request->has('taux_journalier')) {
                $this->updateAgentDetails($agent, $request);
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Agent mis à jour avec succès',
                'data' => $agent->load('centre')
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

    // Méthodes utilitaires privées
    private function validateStandardRole(Request $request)
    {
        return $request->validate([
            'jours_travaille' => 'required|integer|min:1',
            'taux_journalier' => 'required|numeric|min:0'
        ]);
    }

    private function validateSurveillanceRole(Request $request)
    {
        return $request->validate([
            'jours_surveillance' => 'required|integer|min:0',
            'jours_encours' => 'required|integer|min:0',
            'jours_ensalles' => 'required|integer|min:0',
            'taux_par_jour' => 'required|numeric|min:0'
        ]);
    }

    private function calculateTaux($base)
    {
        $tauxBrut = $base;
        $irsa = $this->calculateIRSA($tauxBrut);
        $tauxNet = $tauxBrut - $irsa;

        return [
            'taux_brut' => $tauxBrut,
            'irsa' => $irsa,
            'taux_net' => $tauxNet
        ];
    }

    private function calculateIRSA($tauxBrut)
    {
        return $tauxBrut * 0.2; // 20% IRSA
    }

    private function createStandardDetail($agent, $request)
    {
        $taux = $this->calculateTaux($request->jours_travaille * $request->taux_journalier);
        $detailData = [
            'agent_id' => $agent->id,
            'jours_travaille' => $request->jours_travaille,
            'taux_journalier' => $request->taux_journalier,
            'taux_brut' => $taux['taux_brut'],
            'irsa' => $taux['irsa'],
            'taux_net' => $taux['taux_net']
        ];

        switch ($agent->role) {
            case 'PDO':
                PdoDetail::create($detailData);
                break;
            case 'VPDO':
                VpdoDetail::create($detailData);
                break;
            case 'CDC':
                CdcDetail::create($detailData);
                break;
            case 'CDCA':
                CdcaDetail::create($detailData);
                break;
            case 'Secretaire':
                SecretaireDetail::create($detailData);
                break;
            case 'SecOrg':
                SecOrgDetail::create($detailData);
                break;
            case 'Securite':
                SecuriteDetail::create($detailData);
                break;
        }
    }

    private function createSurveillanceDetail($agent, $request)
    {
        $totalJours = $request->jours_surveillance + $request->jours_encours + $request->jours_ensalles;
        $taux = $this->calculateTaux($totalJours * $request->taux_par_jour);

        SurveillanceDetail::create([
            'agent_id' => $agent->id,
            'jours_surveillance' => $request->jours_surveillance,
            'jours_encours' => $request->jours_encours,
            'jours_ensalles' => $request->jours_ensalles,
            'taux_par_jour' => $request->taux_par_jour,
            'taux_brut' => $taux['taux_brut'],
            'irsa' => $taux['irsa'],
            'taux_net' => $taux['taux_net']
        ]);
    }

    private function getAgentDetailsByRole($agent)
    {
        switch ($agent->role) {
            case 'PDO':
                return $agent->pdoDetail;
            case 'VPDO':
                return $agent->vpdoDetail;
            case 'CDC':
                return $agent->cdcDetail;
            case 'CDCA':
                return $agent->cdcaDetail;
            case 'Secretaire':
                return $agent->secretaireDetail;
            case 'SecOrg':
                return $agent->secOrgDetail;
            case 'Surveillance':
                return $agent->surveillanceDetail;
            case 'Securite':
                return $agent->securiteDetail;
            default:
                return null;
        }
    }

    // Méthodes supplémentaires pour les fonctionnalités spécifiques
    public function getAgentsByRole($role)
    {
        $agents = Agent::where('role', $role)
            ->with(['centre'])
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $agents
        ]);
    }

    public function getAgentsByCentre($centreId)
    {
        $agents = Agent::where('centre_id', $centreId)
            ->with(['centre'])
            ->get();
            
        return response()->json([
            'status' => 'success',
            'data' => $agents
        ]);
    }
}