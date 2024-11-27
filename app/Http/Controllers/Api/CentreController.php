<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Centre;
use Illuminate\Http\Request;

class CentreController extends Controller
{
    public function index()
    {
        $centres = Centre::with(['region.province'])->get()->map(function ($centre) {
            return [
                'id' => $centre->id,
                'nom' => $centre->nom,
                'type' => $centre->type,
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
            ];
        });

        return response()->json($centres);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'type' => 'required|in:examen,etablissement',
            'region_id' => 'required|exists:regions,id',
            'nom' => 'unique:centres,nom,NULL,id,region_id,' . $request->region_id
        ]);

        $centre = Centre::create($request->all());
        return response()->json($centre->load(['region.province']), 201);
    }

    public function show(Centre $centre)
    {
        $centre->load(['region.province']);
        
        return response()->json([
            'id' => $centre->id,
            'nom' => $centre->nom,
            'type' => $centre->type,
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

    public function update(Request $request, Centre $centre)
    {
        $request->validate([
            'nom' => 'sometimes|required',
            'type' => 'sometimes|required|in:examen,etablissement',
            'region_id' => 'sometimes|required|exists:regions,id',
            'nom' => 'unique:centres,nom,' . $centre->id . ',id,region_id,' . ($request->region_id ?? $centre->region_id)
        ]);

        $centre->update($request->all());
        return response()->json($centre->load(['region.province']));
    }

    public function destroy(Centre $centre)
    {
        $centre->delete();
        return response()->json(null, 204);
    }
} 