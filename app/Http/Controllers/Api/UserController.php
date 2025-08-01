<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function register(RegisterUserRequest $request)
    {
        try {
            $user = new User();
            $user->im = $request->im;
            $user->cin = $request->cin;
            $user->nom_prenom = $request->nom_prenom;
            $user->date_cin = $request->date_cin;
            $user->lieu_cin = $request->lieu_cin;
            $user->attribution = $request->attribution;
            $user->sexe = $request->sexe;
            $user->date_entree = $request->date_entree;
            $user->statut = $request->statut;
            $user->ref_st = $request->ref_st;
            $user->contact = $request->contact;
            $user->password = $request->password; // Hachage du mot de passe
            $user->photo = $request->photo;

            // Gestion de la photo
            if ($request->hasFile('photo')) {
                $photoPath = $request->file('photo')->store('photos', 'public');
                $user->photo = $photoPath;
            }

            $user->save();

            // Journalisation
            Journal::create([
                'date_op' => now(),
                'operateur' => Auth::check() ? Auth::user()->nom_prenom : 'System',
                'operations' => "Création d'un nouvel utilisateur : {$user->nom_prenom} (IM: {$user->im})",
                'cin' => Auth::check() ? Auth::user()->cin : 'System',
                'nom_prenom' => Auth::check() ? Auth::user()->nom_prenom : 'System',
                'autres' => json_encode([
                    'utilisateur' => [
                        'im' => $user->im,
                        'cin' => $user->cin,
                        'nom_prenom' => $user->nom_prenom,
                        'statut' => $user->statut,
                        'attribution' => $user->attribution,
                        'contact' => $user->contact,
                        'sexe' => $user->sexe,
                        'date_entree' => $user->date_entree
                    ],
                    'action' => 'création'
                ])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Utilisateur ajouté avec succès',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de l\'ajout de l'utilisateur",
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function login(LoginUserRequest $request) {
        try {
            $credentials = $request->only(['password']);
            $login = $request->input('im') ?? $request->input('nom_prenom');

            if (auth()->attempt(['password' => $request->password, 'im' => $login]) ||
                auth()->attempt(['password' => $request->password, 'nom_prenom' => $login])) {

                $user = auth()->user();
                $token = $user->createToken('la_cle_hyper_hyper_long_de_token_de_winvac')->plainTextToken;

                // Journalisation de la connexion
                Journal::create([
                    'date_op' => now(),
                    'operateur' => $user->nom_prenom,
                    'operations' => "Connexion de l'utilisateur : {$user->nom_prenom} (IM: {$user->im})",
                    'cin' => $user->cin,
                    'nom_prenom' => $user->nom_prenom,
                    'autres' => json_encode([
                        'utilisateur' => [
                            'im' => $user->im,
                            'nom_prenom' => $user->nom_prenom,
                            'attribution' => $user->attribution
                        ],
                        'methode_connexion' => $request->has('im') ? 'IM' : 'NOM_PRENOM',
                        'action' => 'connexion'
                    ])
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Utilisateur connecté',
                    'token' => $token
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => "Utilisateur n'existe pas"
            ], 403);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "Erreur lors de la connexion d'utilisateur",
                'error' => $e->getMessage()
            ], 500);
        }
    }

//prendre tous les utilisateurs
    public function getAllUsers()
{
    try {
        $users = User::all();

        // Ajouter l'URL de la photo pour chaque utilisateur
        foreach ($users as $user) {
            $user->photo_url = $user->photo ? url('storage/' . $user->photo) : null;
        }

        return response()->json([
            'success' => true,
            'data' => $users
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Erreur lors de la récupération des utilisateurs",
            'error' => $e->getMessage()
        ], 500);
    }
}

// Prendre par les utilisateurs par id
public function getUserById($id)
{
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur non trouvé"
            ], 404);
        }

        // Ajouter l'URL de la photo
        $user->photo_url = $user->photo ? url('storage/' . $user->photo) : null;

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Erreur lors de la récupération de l'utilisateur",
            'error' => $e->getMessage()
        ], 500);
    }
}


//mis a jour utilisateur
public function updateUser(Request $request, $id)
{
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur non trouvé"
            ], 404);
        }

        $oldData = $user->toArray();

        $user->im = $request->im ?? $user->im;
        $user->cin = $request->cin ?? $user->cin;
        $user->nom_prenom = $request->nom_prenom ?? $user->nom_prenom;
        $user->date_cin = $request->date_cin ?? $user->date_cin;
        $user->lieu_cin = $request->lieu_cin ?? $user->lieu_cin;
        $user->attribution = $request->attribution ?? $user->attribution;
        $user->sexe = $request->sexe ?? $user->sexe;
        $user->date_entree = $request->date_entree ?? $user->date_entree;
        $user->statut = $request->statut ?? $user->statut;
        $user->ref_st = $request->ref_st ?? $user->ref_st;
        $user->contact = $request->contact ?? $user->contact;
        $user->photo = $request->photo ?? $user->photo;

        if ($request->hasFile('photo')) {
                // Supprimer l'ancienne photo si nécessaire
                if ($user->photo) {
                    Storage::disk('public')->delete($user->photo);
                }
                $photoPath = $request->file('photo')->store('photos', 'public');
                $user->photo = $photoPath;
            }

        $user->save();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Modification de l'utilisateur : {$user->nom_prenom} (IM: {$user->im})",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'modifications' => array_diff_assoc($user->toArray(), $oldData),
                'ancien' => $oldData,
                'nouveau' => $user->toArray(),
                'action' => 'modification'
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur modifié avec succès',
            'data' => $user
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Erreur lors de la modification de l'utilisateur",
            'error' => $e->getMessage()
        ], 500);
    }
}


//Mis a jour mot de passe
public function updatePassword(Request $request)
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur non connecté"
            ], 401);
        }

        $oldPassword = $request->input('old_password');
        $newPassword = $request->input('new_password');
        $confirmNewPassword = $request->input('confirm_new_password');

        if (!Hash::check($oldPassword, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => "Ancien mot de passe incorrect"
            ], 400);
        }

        if ($newPassword !== $confirmNewPassword) {
            return response()->json([
                'success' => false,
                'message' => "Les mots de passe ne correspondent pas"
            ], 400);
        }

        $user->password = bcrypt($newPassword);
        $user->save();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => $user->nom_prenom,
            'operations' => "Modification du mot de passe de l'utilisateur : {$user->nom_prenom}",
            'cin' => $user->cin,
            'nom_prenom' => $user->nom_prenom,
            'autres' => json_encode([
                'utilisateur' => [
                    'im' => $user->im,
                    'nom_prenom' => $user->nom_prenom
                ],
                'action' => 'modification_mot_de_passe'
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Mot de passe modifié avec succès',
            'data' => $user
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Erreur lors de la modification du mot de passe",
            'error' => $e->getMessage()
        ], 500);
    }
}

//Supprimer utilisateur
public function deleteUser(Request $request, $id)
{
    try {
        $user = User::find($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur non trouvé"
            ], 404);
        }

        $userData = $user->toArray();
        $user->delete();

        // Journalisation
        Journal::create([
            'date_op' => now(),
            'operateur' => Auth::user()->nom_prenom,
            'operations' => "Suppression de l'utilisateur : {$userData['nom_prenom']} (IM: {$userData['im']})",
            'cin' => Auth::user()->cin,
            'nom_prenom' => Auth::user()->nom_prenom,
            'autres' => json_encode([
                'utilisateur' => $userData,
                'action' => 'suppression'
            ])
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Utilisateur supprimé avec succès'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Erreur lors de la suppression de l'utilisateur",
            'error' => $e->getMessage()
        ], 500);
    }
}


//Profile
public function getProfile()
{
    try {
        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => "Utilisateur non connecté"
            ], 401);
        }

        // Ajouter l'URL de la photo
        $user->photo_url = $user->photo ? url('storage/' . $user->photo) : null;

        return response()->json([
            'success' => true,
            'data' => $user
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => "Erreur lors de la récupération du profil",
            'error' => $e->getMessage()
        ], 500);
    }
}
}
