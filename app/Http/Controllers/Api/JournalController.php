<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Carbon\Carbon;

class JournalController extends Controller
{
    // Récupérer tous les journaux
    public function index(Request $request)
    {
        try {
            $query = Journal::query();

            // Filtrage par date
            if ($request->has('start_date') && $request->has('end_date')) {
                $query->whereBetween('date_op', [
                    Carbon::parse($request->start_date)->startOfDay(),
                    Carbon::parse($request->end_date)->endOfDay()
                ]);
            }

            // Filtrage par opérateur
            if ($request->has('operateur')) {
                $query->where('operateur', 'like', '%' . $request->operateur . '%');
            }

            // Filtrage par type d'opération
            if ($request->has('operation')) {
                $query->where('operations', 'like', '%' . $request->operation . '%');
            }

            // Tri par date décroissante
            $query->orderBy('date_op', 'desc');

            $journal = $query->get();

            return response()->json($journal);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération du journal',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getRecentActivities()
    {
        try {
            $recentActivities = Journal::orderBy('date_op', 'desc')
                ->take(10)
                ->get();

            return response()->json($recentActivities);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des activités récentes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getActivitiesByUser($userId)
    {
        try {
            $activities = Journal::where('cin', $userId)
                ->orderBy('date_op', 'desc')
                ->get();

            return response()->json($activities);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des activités de l\'utilisateur',
                'error' => $e->getMessage()
            ], 500);
        }
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
