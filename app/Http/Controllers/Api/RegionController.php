<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;

class RegionController extends Controller
{
    public function index()
    {
        return response()->json(Region::with('province')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required',
            'province_id' => 'required|exists:provinces,id',
            'nom' => 'unique:regions,nom,NULL,id,province_id,' . $request->province_id
        ]);

        $region = Region::create($request->all());

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Création d'une nouvelle région : {$region->nom} (Province: {$region->province->nom})",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'region' => $region->toArray(),
                'province' => $region->province->toArray(),
                'action' => 'création'
            ])
        ]);

        return response()->json($region->load('province'), 201);
    }

    public function show(Region $region)
    {
        return response()->json($region->load(['province', 'centres']));
    }

    public function update(Request $request, Region $region)
    {
        $request->validate([
            'nom' => 'sometimes|required',
            'province_id' => 'sometimes|required|exists:provinces,id',
            'nom' => 'unique:regions,nom,' . $region->id . ',id,province_id,' . ($request->province_id ?? $region->province_id)
        ]);

        $oldData = $region->toArray();
        $region->update($request->all());

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Modification de la région : {$region->nom}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'ancien' => $oldData,
                'nouveau' => $region->toArray(),
                'action' => 'modification'
            ])
        ]);

        return response()->json($region->load('province'));
    }

    public function destroy(Region $region)
    {
        $regionData = $region->toArray();
        $region->delete();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Suppression de la région : {$regionData['nom']}",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'region' => $regionData,
                'province' => $region->province->toArray(),
                'action' => 'suppression'
            ])
        ]);

        return response()->json(null, 204);
    }

    /**
     * Récupérer tous les centres d'une région
     */
    public function centres(Region $region)
    {
        return response()->json($region->centres);
    }
} 