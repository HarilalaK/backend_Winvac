<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function show(Role $role)
    {
        return response()->json($role);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|unique:roles,nom',
            'prix' => 'required|numeric|min:0',
            'requiert_jours_travaille' => 'required|boolean',
            'requiert_jours_surveillance' => 'required|boolean',
            'requiert_copies' => 'required|boolean',
            'requiert_matiere' => 'required|boolean'
        ]);

        $role = Role::create($request->all());
        return response()->json($role, 201);
    }

    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nom' => 'sometimes|required|string|unique:roles,nom,' . $role->id,
            'prix' => 'sometimes|required|numeric|min:0',
            'requiert_jours_travaille' => 'sometimes|required|boolean',
            'requiert_jours_surveillance' => 'sometimes|required|boolean',
            'requiert_copies' => 'sometimes|required|boolean',
            'requiert_matiere' => 'sometimes|required|boolean'
        ]);

        $role->update($request->all());
        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        // Vérifier si le rôle est utilisé par des agents
        if ($role->agents()->exists()) {
            return response()->json([
                'message' => 'Impossible de supprimer ce rôle car il est utilisé par des agents'
            ], 422);
        }

        $role->delete();
        return response()->json(null, 204);
    }

    // Méthode pour obtenir les agents par rôle
    public function agents(Role $role)
    {
        $agents = $role->agents()->with(['centre.region.province'])->get()->map(function ($agent) {
            return [
                'id' => $agent->id,
                'nom' => $agent->nom,
                'prenom' => $agent->prenom,
                'centre' => [
                    'nom' => $agent->centre->nom,
                    'region' => [
                        'nom' => $agent->centre->region->nom,
                        'province' => [
                            'nom' => $agent->centre->region->province->nom
                        ]
                    ]
                ],
                'montant' => $agent->montant
            ];
        });

        return response()->json([
            'role' => [
                'id' => $role->id,
                'nom' => $role->nom,
                'prix' => $role->prix
            ],
            'agents' => $agents
        ]);
    }
} 