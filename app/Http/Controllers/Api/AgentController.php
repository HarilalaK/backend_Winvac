<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('centre.region.province')->get();
        $agents->each(function ($agent) {
            if (empty($agent->taux_brut)) {
                $tauxBrut = $agent->calculerTauxBrut();
                $irsa = $agent->calculerIRSA($tauxBrut);
                $tauxNet = $agent->calculerTauxNet($tauxBrut, $irsa);

                $agent->taux_brut = $tauxBrut;
                $agent->irsa = $irsa;
                $agent->taux_net = $tauxNet;
                $agent->save();
            }
        });
        return response()->json($agents);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'annee' => 'required|integer',
            'centre_id' => 'required|exists:centres,id',
            'situation' => 'required|string',
            'role' => ['required', Rule::in(['PDO', 'VPDO', 'CDC', 'CDCA', 'secretaire', 'secOrg', 'surveillance', 'securite', 'correcteur'])],
            'jours_travaille' => 'nullable|integer',
            'im' => 'nullable|string',
            'cin' => 'required|string',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'sexe' => ['required', Rule::in(['M', 'F'])],
            'lieu_cin' => 'required|string',
            'date_cin' => 'required|date',
            'matiere' => 'nullable|string',
            'nombre_copie' => 'nullable|integer',
            'jours_surveillance' => 'nullable|integer',
            'jours_encours' => 'nullable|integer',
            'jours_ensalles' => 'nullable|integer'
        ]);

        $agent = Agent::create($validatedData);
        return response()->json($agent->load('centre.region.province'), 201);
    }

    public function show($id)
    {
        $agent = Agent::with('centre.region.province')->findOrFail($id);
        if (empty($agent->taux_brut)) {
            $tauxBrut = $agent->calculerTauxBrut();
            $irsa = $agent->calculerIRSA($tauxBrut);
            $tauxNet = $agent->calculerTauxNet($tauxBrut, $irsa);

            $agent->taux_brut = $tauxBrut;
            $agent->irsa = $irsa;
            $agent->taux_net = $tauxNet;
            $agent->save();
        }
        return response()->json($agent);
    }

    public function update(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);

        $validatedData = $request->validate([
            'annee' => 'sometimes|required|integer',
            'centre_id' => 'sometimes|required|exists:centres,id',
            'situation' => 'sometimes|required|string',
            'role' => ['sometimes', 'required', Rule::in(['PDO', 'VPDO', 'CDC', 'CDCA', 'secretaire', 'secOrg', 'surveillance', 'securite', 'correcteur'])],
            'jours_travaille' => 'nullable|integer',
            'im' => 'nullable|string',
            'cin' => 'sometimes|required|string',
            'nom' => 'sometimes|required|string',
            'prenom' => 'sometimes|required|string',
            'sexe' => ['sometimes', 'required', Rule::in(['M', 'F'])],
            'lieu_cin' => 'sometimes|required|string',
            'date_cin' => 'sometimes|required|date',
            'matiere' => 'nullable|string',
            'nombre_copie' => 'nullable|integer',
            'jours_surveillance' => 'nullable|integer',
            'jours_encours' => 'nullable|integer',
            'jours_ensalles' => 'nullable|integer'
        ]);

        $agent->update($validatedData);
        return response()->json($agent->load('centre.region.province'));
    }

    public function destroy($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->delete();
        return response()->json(null, 204);
    }
}
