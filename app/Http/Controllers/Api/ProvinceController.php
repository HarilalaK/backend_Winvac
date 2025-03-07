<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Province;
use Illuminate\Http\Request;
use App\Models\Journal;
use Illuminate\Support\Facades\Auth;

class ProvinceController extends Controller
{
    /**
     * Récupérer toute la hiérarchie des lieux
     */
    public function hierarchy()
    {
        try {
            $provinces = Province::with(['regions' => function ($query) {
                $query->orderBy('nom');
            }, 'regions.centres' => function ($query) {
                $query->orderBy('nom');
            }])->orderBy('nom')->get();

            $formattedData = $provinces->map(function ($province) {
                return [
                    'id' => $province->id,
                    'nom' => $province->nom,
                    'regions' => $province->regions->map(function ($region) {
                        return [
                            'id' => $region->id,
                            'nom' => $region->nom,
                            'province_id' => $region->province_id,
                            'centres' => $region->centres->map(function ($centre) {
                                return [
                                    'id' => $centre->id,
                                    'nom' => $centre->nom,
                                    'type' => $centre->type,
                                    'region_id' => $centre->region_id
                                ];
                            })
                        ];
                    })
                ];
            });

            return response()->json($formattedData);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la hiérarchie',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des provinces
     */
    public function index()
    {
        try {
            $provinces = Province::orderBy('nom')->get();
            return response()->json($provinces);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des provinces',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Détails d'une province
     */
    public function show(Province $province)
    {
        try {
            $province->load(['regions.centres']);
            return response()->json($province);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération de la province',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Créer une nouvelle province
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'nom' => 'required|string|unique:provinces,nom'
            ]);

            $province = Province::create($request->all());

            // Journalisation
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::user()->nom_prenom,
                'operations' => "Création d'une nouvelle province : {$province->nom}",
                'cin' => Auth::user()->cin,
                'nom_prenom' => Auth::user()->nom_prenom,
                'autres' => json_encode([
                    'province' => $province->toArray(),
                    'action' => 'création'
                ])
            ]);

            return response()->json($province, 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la création de la province',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mettre à jour une province
     */
    public function update(Request $request, Province $province)
    {
        try {
            $request->validate([
                'nom' => 'required|string|unique:provinces,nom,' . $province->id
            ]);

            $oldData = $province->toArray();
            $province->update($request->all());

            // Journalisation
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::user()->nom_prenom,
                'operations' => "Modification de la province : {$province->nom}",
                'cin' => Auth::user()->cin,
                'nom_prenom' => Auth::user()->nom_prenom,
                'autres' => json_encode([
                    'ancien' => $oldData,
                    'nouveau' => $province->toArray(),
                    'action' => 'modification'
                ])
            ]);

            return response()->json($province);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la mise à jour de la province',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une province
     */
    public function destroy(Province $province)
    {
        try {
            $provinceData = $province->toArray();
            
            if ($province->regions()->exists()) {
                return response()->json([
                    'message' => 'Impossible de supprimer cette province car elle contient des régions'
                ], 422);
            }

            $province->delete();

            // Journalisation
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::user()->nom_prenom,
                'operations' => "Suppression de la province : {$provinceData['nom']}",
                'cin' => Auth::user()->cin,
                'nom_prenom' => Auth::user()->nom_prenom,
                'autres' => json_encode([
                    'province' => $provinceData,
                    'action' => 'suppression'
                ])
            ]);

            return response()->json(null, 204);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la suppression de la province',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Récupérer les régions d'une province
     */
    public function regions(Province $province)
    {
        try {
            $regions = $province->regions()->with('centres')->orderBy('nom')->get();
            return response()->json($regions);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erreur lors de la récupération des régions',
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 