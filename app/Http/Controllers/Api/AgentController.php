<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with(['centre.region.province'])->get()->map(function ($agent) {
            return [
                'id' => $agent->id,
                'annee' => $agent->annee,
                'situation' => $agent->situation,
                'role' => $agent->role,
                'jours_travaille' => $agent->jours_travaille,
                'im' => $agent->im,
                'cin' => $agent->cin,
                'nom' => $agent->nom,
                'prenom' => $agent->prenom,
                'sexe' => $agent->sexe,
                'lieu_cin' => $agent->lieu_cin,
                'date_cin' => $agent->date_cin,
                'matiere' => $agent->matiere,
                'nombre_copie' => $agent->nombre_copie,
                'jours_surveillance' => $agent->jours_surveillance,
                'jours_encours' => $agent->jours_encours,
                'jours_ensalles' => $agent->jours_ensalles,
                'centre' => [
                    'id' => $agent->centre->id,
                    'nom' => $agent->centre->nom,
                    'type' => $agent->centre->type,
                    'region' => [
                        'id' => $agent->centre->region->id,
                        'nom' => $agent->centre->region->nom,
                        'province' => [
                            'id' => $agent->centre->region->province->id,
                            'nom' => $agent->centre->region->province->nom
                        ]
                    ]
                ],
                'created_at' => $agent->created_at,
                'updated_at' => $agent->updated_at
            ];
        });

        return response()->json($agents);
    }

    public function show(Agent $agent)
    {
        $agent->load(['centre.region.province']);
        
        return response()->json([
            'id' => $agent->id,
            'annee' => $agent->annee,
            'situation' => $agent->situation,
            'role' => $agent->role,
            'jours_travaille' => $agent->jours_travaille,
            'im' => $agent->im,
            'cin' => $agent->cin,
            'nom' => $agent->nom,
            'prenom' => $agent->prenom,
            'sexe' => $agent->sexe,
            'lieu_cin' => $agent->lieu_cin,
            'date_cin' => $agent->date_cin,
            'matiere' => $agent->matiere,
            'nombre_copie' => $agent->nombre_copie,
            'jours_surveillance' => $agent->jours_surveillance,
            'jours_encours' => $agent->jours_encours,
            'jours_ensalles' => $agent->jours_ensalles,
            'centre' => [
                'id' => $agent->centre->id,
                'nom' => $agent->centre->nom,
                'type' => $agent->centre->type,
                'region' => [
                    'id' => $agent->centre->region->id,
                    'nom' => $agent->centre->region->nom,
                    'province' => [
                        'id' => $agent->centre->region->province->id,
                        'nom' => $agent->centre->region->province->nom
                    ]
                ]
            ],
            'created_at' => $agent->created_at,
            'updated_at' => $agent->updated_at
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'annee' => 'required',
            'centre_id' => 'required|exists:centres,id',
            'situation' => 'required',
            'role' => 'required|in:president,presidentAdjoint,SecOrg,CDC,CDCA,secretaire,Surveillance,correction,Securite',
            'cin' => 'required',
            'nom' => 'required',
            'prenom' => 'required',
            'sexe' => 'required|in:M,F',
            'lieu_cin' => 'required',
            'date_cin' => 'required|date'
        ]);

        $agent = Agent::create($request->all());
        return response()->json($agent->load(['centre.region.province']), 201);
    }

    public function update(Request $request, Agent $agent)
    {
        $request->validate([
            'annee' => 'sometimes|required',
            'centre_id' => 'sometimes|required|exists:centres,id',
            'situation' => 'sometimes|required',
            'role' => 'sometimes|required|in:president,presidentAdjoint,SecOrg,CDC,CDCA,secretaire,Surveillance,correction,Securite',
            'cin' => 'sometimes|required',
            'nom' => 'sometimes|required',
            'prenom' => 'sometimes|required',
            'sexe' => 'sometimes|required|in:M,F',
            'lieu_cin' => 'sometimes|required',
            'date_cin' => 'sometimes|required|date'
        ]);

        $agent->update($request->all());
        return response()->json($agent->load(['centre.region.province']));
    }

    public function destroy(Agent $agent)
    {
        $agent->delete();
        return response()->json(null, 204);
    }
}
