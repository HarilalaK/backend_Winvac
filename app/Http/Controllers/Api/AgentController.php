<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

// Cette classe gère les demandes liées aux agents
class AgentController extends Controller
{
    // Cette méthode récupère tous les agents
    public function index()
    {
        $agents = Agent::all();
        return response()->json(['agents' => $agents]);
    }

    // Cette méthode crée un nouvel agent
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'annee' => 'required|digits:4', // Année doit être un nombre de 4 chiffres
            'province' => 'required|string', // Province doit être une chaîne de caractères
            'region' => 'required|string', // Région doit être une chaîne de caractères
            'centre' => 'required|string', // Centre doit être une chaîne de caractères
            'situation' => 'required|string', // Situation doit être une chaîne de caractères
            'role' => 'required|string', // Rôle doit être une chaîne de caractères
            'jours_travaille' => 'required|integer', // Jours travaillés doit être un entier
            'im' => 'nullable|string', // IM peut être une chaîne de caractères ou null
            'cin' => 'required|string|unique:agents', // CIN doit être unique et une chaîne de caractères
            'nom' => 'required|string', // Nom doit être une chaîne de caractères
            'prenom' => 'required|string', // Prénom doit être une chaîne de caractères
            'sexe' => 'required|in:M,F', // Sexe doit être M ou F
            'lieu_cin' => 'required|string', // Lieu du CIN doit être une chaîne de caractères
            'date_cin' => 'required|date', // Date du CIN doit être une date
            'matiere' => 'nullable|string', // Matière peut être une chaîne de caractères ou null
            'nombre_copie' => 'nullable|integer', // Nombre de copies peut être un entier ou null
            'jours_surveillance' => 'nullable|integer', // Jours de surveillance peuvent être un entier ou null
            'jours_encours' => 'nullable|integer', // Jours en cours peuvent être un entier ou null
            'jours_ensalles' => 'nullable|integer', // Jours en salles peuvent être un entier ou null
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agent = Agent::create($request->all());
        return response()->json(['agent' => $agent, 'message' => 'Agent créé avec succès'], 201);
    }

    // Cette méthode affiche un agent spécifique
    public function show($id)
    {
        $agent = Agent::findOrFail($id);
        return response()->json(['agent' => $agent]);
    }

    // Cette méthode met à jour un agent existant
    public function update(Request $request, $id)
    {
        $agent = Agent::findOrFail($id);
        
        $validator = Validator::make($request->all(), [
            'annee' => 'required|digits:4', // Année doit être un nombre de 4 chiffres
            'province' => 'required|string', // Province doit être une chaîne de caractères
            'region' => 'required|string', // Région doit être une chaîne de caractères
            'centre' => 'required|string', // Centre doit être une chaîne de caractères
            'situation' => 'required|string', // Situation doit être une chaîne de caractères
            'role' => 'required|string', // Rôle doit être une chaîne de caractères
            'jours_travaille' => 'required|integer', // Jours travaillés doit être un entier
            'im' => 'nullable|string', // IM peut être une chaîne de caractères ou null
            'cin' => 'required|string|unique:agents,cin,' . $id, // CIN doit être unique sauf pour l'agent courant
            'nom' => 'required|string', // Nom doit être une chaîne de caractères
            'prenom' => 'required|string', // Prénom doit être une chaîne de caractères
            'sexe' => 'required|in:M,F', // Sexe doit être M ou F
            'lieu_cin' => 'required|string', // Lieu du CIN doit être une chaîne de caractères
            'date_cin' => 'required|date', // Date du CIN doit être une date
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $agent->update($request->all());
        return response()->json(['agent' => $agent, 'message' => 'Agent mis à jour avec succès']);
    }

    // Cette méthode supprime un agent
    public function destroy($id)
    {
        $agent = Agent::findOrFail($id);
        $agent->delete();
        return response()->json(['message' => 'Agent supprimé avec succès']);
    }
}
