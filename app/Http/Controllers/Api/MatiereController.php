<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use Illuminate\Http\Request;

class MatiereController extends Controller
{
    public function index()
    {
        return Matiere::orderBy('num_matiere')->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'num_matiere' => 'required|integer|unique:matieres',
            'code' => 'required|string|unique:matieres',
            'designation' => 'required|string',
            'BEP' => 'boolean',
            'CAP' => 'boolean',
            'CFA' => 'boolean',
            'ConcoursLTP' => 'boolean',
            'ConcoursCFP' => 'boolean'
        ]);

        return Matiere::create($validated);
    }

    public function show(Matiere $matiere)
    {
        return $matiere;
    }

    public function update(Request $request, Matiere $matiere)
    {
        $validated = $request->validate([
            'num_matiere' => 'sometimes|required|integer|unique:matieres,num_matiere,' . $matiere->id,
            'code' => 'sometimes|required|string|unique:matieres,code,' . $matiere->id,
            'designation' => 'sometimes|required|string',
            'BEP' => 'boolean',
            'CAP' => 'boolean',
            'CFA' => 'boolean',
            'ConcoursLTP' => 'boolean',
            'ConcoursCFP' => 'boolean'
        ]);

        $matiere->update($validated);
        return $matiere;
    }

    public function destroy(Matiere $matiere)
    {
        $matiere->delete();
        return response()->noContent();
    }
}
