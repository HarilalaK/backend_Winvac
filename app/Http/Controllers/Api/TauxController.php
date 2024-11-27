<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Taux;
use Illuminate\Http\Request;

class TauxController extends Controller
{
    public function index()
    {
        return response()->json(Taux::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'annee' => 'required|unique:taux,annee',
            'secretariat' => 'required|numeric|min:0',
            'surveillance_securite' => 'required|numeric|min:0',
            'correction_max_copies' => 'required|numeric|min:0',
            'correction_surplus_bep' => 'required|numeric|min:0',
            'correction_surplus_autre' => 'required|numeric|min:0',
            'forfaitaire_pdo_vpdo' => 'required|numeric|min:0',
            'forfaitaire_cdc_cdca' => 'required|numeric|min:0'
        ]);

        $taux = Taux::create($request->all());
        return response()->json($taux, 201);
    }

    public function show(Taux $taux)
    {
        return response()->json($taux);
    }

    public function update(Request $request, Taux $taux)
    {
        $request->validate([
            'annee' => 'sometimes|required|unique:taux,annee,' . $taux->id,
            'secretariat' => 'sometimes|required|numeric|min:0',
            'surveillance_securite' => 'sometimes|required|numeric|min:0',
            'correction_max_copies' => 'sometimes|required|numeric|min:0',
            'correction_surplus_bep' => 'sometimes|required|numeric|min:0',
            'correction_surplus_autre' => 'sometimes|required|numeric|min:0',
            'forfaitaire_pdo_vpdo' => 'sometimes|required|numeric|min:0',
            'forfaitaire_cdc_cdca' => 'sometimes|required|numeric|min:0'
        ]);

        $taux->update($request->all());
        return response()->json($taux);
    }

    public function destroy(Taux $taux)
    {
        $taux->delete();
        return response()->json(null, 204);
    }
} 