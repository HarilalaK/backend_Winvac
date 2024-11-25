<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matiere; // Assurez-vous que le modèle Matiere existe
use Illuminate\Http\Request;

class MatiereController extends Controller
{
    // Récupérer toutes les matières
    public function recupererMatieres()
    {
        $matieres = Matiere::all();
        return response()->json($matieres);
    }

    public function creerMatiere(Request $request)
    {
        $request->validate([
            'Nmat' => 'required|integer',
            'code' => 'required|integer|unique:matieres',
            'designation' => 'required|string|max:255',
            'BEP' => 'boolean',
            'CFA' => 'boolean',
            'CAP' => 'boolean',
            'ConcoursLTP' => 'boolean',
            'ConcoursCFP' => 'boolean',
            'Observations' => 'nullable|string',
        ]);

        $matiere = Matiere::create($request->all());

        return response()->json($matiere, 201);
    }


    // Récupérer une matière par son ID
    public function recupererMatiere($id)
    {
        $matiere = Matiere::findOrFail($id);
        return response()->json($matiere);
    }

    // Mettre à jour une matière
    public function mettreAJourMatiere(Request $request, $id)
    {
        $request->validate([
            'Nmat' => 'sometimes|required|string|max:255',
            'code' => 'sometimes|required|string|max:255',
            'designation' => 'sometimes|required|string|max:255',
            'BEP' => 'sometimes|boolean',
            'CFA' => 'sometimes|boolean',
            'CAP' => 'sometimes|boolean',
            'ConcoursLTP' => 'sometimes|boolean',
            'ConcoursCFP' => 'sometimes|boolean',
            'Observations' => 'nullable|string',
        ]);

        $matiere = Matiere::findOrFail($id);
        $matiere->update($request->all());

        return response()->json($matiere);
    }

    // Supprimer une matière
    public function supprimerMatiere($id)
    {
        $matiere = Matiere::findOrFail($id);
        $matiere->delete();

        return response()->json(null, 204);
    }
}
