<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Matiere;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $matiere = Matiere::create($validated);

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Création d'une nouvelle matière : {$matiere->designation} (Code: {$matiere->code})",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'num_matiere' => $matiere->num_matiere,
                'code' => $matiere->code,
                'designation' => $matiere->designation,
                'action' => 'création',
                'examens' => [
                    'BEP' => $matiere->BEP,
                    'CAP' => $matiere->CAP,
                    'CFA' => $matiere->CFA,
                    'ConcoursLTP' => $matiere->ConcoursLTP,
                    'ConcoursCFP' => $matiere->ConcoursCFP
                ]
            ])
        ]);

        return $matiere;
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

        $oldData = $matiere->toArray();
        $matiere->update($validated);

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Modification de la matière : {$matiere->designation} (Code: {$matiere->code})",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'num_matiere' => $matiere->num_matiere,
                'code' => $matiere->code,
                'designation' => $matiere->designation,
                'modifications' => array_diff_assoc($matiere->toArray(), $oldData),
                'ancien' => $oldData,
                'nouveau' => $matiere->toArray(),
                'action' => 'modification'
            ])
        ]);

        return $matiere;
    }

    public function destroy(Matiere $matiere)
    {
        $matiereData = $matiere->toArray();
        $matiere->delete();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Suppression de la matière : {$matiereData['designation']} (Code: {$matiereData['code']})",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'num_matiere' => $matiereData['num_matiere'],
                'code' => $matiereData['code'],
                'designation' => $matiereData['designation'],
                'examens' => [
                    'BEP' => $matiereData['BEP'],
                    'CAP' => $matiereData['CAP'],
                    'CFA' => $matiereData['CFA'],
                    'ConcoursLTP' => $matiereData['ConcoursLTP'],
                    'ConcoursCFP' => $matiereData['ConcoursCFP']
                ],
                'action' => 'suppression'
            ])
        ]);

        return response()->noContent();
    }
}
