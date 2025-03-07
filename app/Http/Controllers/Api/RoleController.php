<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;

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

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Création d'un nouveau rôle : {$role->nom}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'role' => $role->toArray(),
                'action' => 'création'
            ])
        ]);

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

        $oldData = $role->toArray();
        $role->update($request->all());

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Modification du rôle : {$role->nom}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'ancien' => $oldData,
                'nouveau' => $role->toArray(),
                'action' => 'modification'
            ])
        ]);

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

        $roleData = $role->toArray();
        $role->delete();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Suppression du rôle : {$roleData['nom']}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'role' => $roleData,
                'action' => 'suppression'
            ])
        ]);

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