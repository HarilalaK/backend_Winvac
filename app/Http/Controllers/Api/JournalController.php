<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;

class JournalController extends Controller
{
    // Récupérer tous les journaux
    public function index()
    {
        return Journal::all();
    }

    // Créer un journal
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date_op' => 'required|date',
            'operateur' => 'required|string|max:255',
            'operations' => 'required|string',
            'cin' => 'required|string|max:20',
            'nom_prenom' => 'required|string|max:255',
            'autres' => 'nullable|string',
        ]);

        return Journal::create($validated);
    }

    // Afficher un journal spécifique
    public function show($id)
    {
        return Journal::findOrFail($id);
    }

    // Mettre à jour un journal
    public function update(Request $request, $id)
    {
        $journal = Journal::findOrFail($id);

        $validated = $request->validate([
            'date_op' => 'sometimes|date',
            'operateur' => 'sometimes|string|max:255',
            'operations' => 'sometimes|string',
            'cin' => 'sometimes|string|max:20',
            'nom_prenom' => 'sometimes|string|max:255',
            'autres' => 'nullable|string',
        ]);

        $journal->update($validated);

        return $journal;
    }

    // Supprimer un journal
    public function destroy($id)
    {
        $journal = Journal::findOrFail($id);
        $journal->delete();

        return response()->json(['message' => 'Journal supprimé avec succès']);
    }
}
