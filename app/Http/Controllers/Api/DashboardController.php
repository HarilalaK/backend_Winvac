<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Agent;
use App\Models\Centre;
use App\Models\Region;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function getStats()
    {
        try {
            // Récupérer les données avec les relations nécessaires
            $users = User::all();
            $agents = Agent::all();
            $centres = Centre::whereNull('deleted_at')->get();
            $regions = Region::all();

            // Calculer les statistiques des centres par type d'examen
            $centreStats = [
                'total' => $centres->count(),
                'details' => [
                    'BEP' => $centres->where('type_examen', 'BEP')->count(),
                    'CAP' => $centres->where('type_examen', 'CAP')->count(),
                    'CFA' => $centres->where('type_examen', 'CFA')->count(),
                    'ConcoursLTP' => $centres->where('type_examen', 'ConcoursLTP')->count(),
                    'ConcoursCFP' => $centres->where('type_examen', 'ConcoursCFP')->count()
                ]
            ];

            // Calculer le nombre total de candidats et de salles
            $totalCandidats = $centres->sum('nombre_candidats');
            $totalSalles = $centres->sum('nombre_salles');

            return response()->json([
                'users' => [
                    'total' => $users->count(),
                    'variation' => $this->calculateVariation($users),
                    'details' => 'Utilisateurs actifs'
                ],
                'agents' => [
                    'total' => $agents->count(),
                    'variation' => $this->calculateVariation($agents),
                    'details' => 'Agents enregistrés'
                ],
                'centres' => [
                    'total' => $centreStats['total'],
                    'variation' => $this->calculateVariation($centres),
                    'details' => "Centres d'examen actifs",
                    'stats' => $centreStats['details'],
                    'candidats' => $totalCandidats,
                    'salles' => $totalSalles
                ],
                'regions' => [
                    'total' => $regions->count(),
                    'variation' => $this->calculateVariation($regions),
                    'details' => 'Régions enregistrées'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des statistiques',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateVariation($data)
    {
        try {
            if ($data->isEmpty()) {
                return 0;
            }

            $now = Carbon::now();
            $oneMonthAgo = $now->copy()->subMonth();

            // Compter les éléments du dernier mois
            $recentCount = $data->filter(function ($item) use ($oneMonthAgo) {
                return Carbon::parse($item->created_at)->isAfter($oneMonthAgo);
            })->count();

            // Calculer la variation en pourcentage
            $totalCount = $data->count();
            if ($totalCount === 0) {
                return 0;
            }

            $variation = (($recentCount / $totalCount) * 100) - 100;
            return round($variation);
        } catch (\Exception $e) {
            \Log::error('Erreur dans le calcul de la variation: ' . $e->getMessage());
            return 0;
        }
    }

    public function getActivities()
    {
        try {
            $activities = Journal::orderBy('date_op', 'desc')
                ->take(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $activities
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Erreur lors de la récupération des activités',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 