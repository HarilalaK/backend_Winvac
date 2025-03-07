<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;

class CentreController extends Controller
{
    public function index()
    {
        return Centre::with('region.province')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom' => 'required|string',
            'region_id' => 'required|exists:regions,id',
            'nombre_salles' => 'nullable|integer',
            'nombre_candidats' => 'nullable|integer',
            'numero_centre' => 'nullable|string',
            'type_examen' => ['nullable', Rule::in(['BEP', 'CFA', 'CAP', 'ConcoursLTP', 'ConcoursCFP'])],
            'session' => 'nullable|integer'
        ]);

        $centre = Centre::create($validated);

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Création d'un nouveau centre : {$centre->nom} (Région: {$centre->region->nom})",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'centre' => $centre->toArray(),
                'region' => $centre->region->toArray(),
                'action' => 'création'
            ])
        ]);

        return response()->json($centre, 201);
    }

    public function update(Request $request, Centre $centre)
    {
        $validated = $request->validate([
            'nom' => 'sometimes|required|string',
            'region_id' => 'sometimes|required|exists:regions,id',
            'nombre_salles' => 'nullable|integer',
            'nombre_candidats' => 'nullable|integer',
            'numero_centre' => 'nullable|string',
            'type_examen' => ['nullable', Rule::in(['BEP', 'CFA', 'CAP', 'ConcoursLTP', 'ConcoursCFP'])],
            'session' => 'nullable|integer'
        ]);

        $oldData = $centre->toArray();
        $centre->update($validated);

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Modification du centre : {$centre->nom}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'ancien' => $oldData,
                'nouveau' => $centre->toArray(),
                'region' => $centre->region->toArray(),
                'action' => 'modification'
            ])
        ]);

        return response()->json($centre);
    }

    public function show(Centre $centre)
    {
        $centre->load(['region.province']);
        
        return response()->json([
            'id' => $centre->id,
            'nom' => $centre->nom,
            'region' => [
                'id' => $centre->region->id,
                'nom' => $centre->region->nom,
                'province' => [
                    'id' => $centre->region->province->id,
                    'nom' => $centre->region->province->nom
                ]
            ],
            'created_at' => $centre->created_at,
            'updated_at' => $centre->updated_at
        ]);
    }

    public function destroy(Centre $centre)
    {
        $centreData = $centre->toArray();
        $centre->delete();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Suppression du centre : {$centreData['nom']}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'centre' => $centreData,
                'region' => $centre->region->toArray(),
                'action' => 'suppression'
            ])
        ]);

        return response()->json(null, 204);
    }
}