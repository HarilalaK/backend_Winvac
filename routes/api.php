<?php

use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\MatiereController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AgentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Post
Route::get('/post', [PostController::class, 'getPostAll']);
Route::get('/post/{id}', [PostController::class, 'getPostbyId']);
Route::patch('/post/{id}', [PostController::class, 'editPost']);
Route::delete('/post/{id}', [PostController::class, 'deletePost']);
Route::get('/users/all', [UserController::class, 'getAllUsers']);
//utilisateur

Route::post('/login', [UserController::class, 'login']);


Route::middleware('auth:sanctum')->group(function(){

    Route::middleware(['check.status:Admin'])->group(function() {
        Route::get('/users/{id}', [UserController::class, 'getUserById']);
        Route::post('/post', [PostController::class, 'addPost']);
        Route::post('/register', [UserController::class, 'register']);
        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
        Route::get('/journal', [JournalController::class, 'index']);

        //agents
        Route::apiResource('agents', AgentController::class);
    });

    Route::middleware(['check.status:Admin,DR'])->group(function() {
        Route::patch('/post/{id}', [PostController::class, 'editPost']);
        Route::delete('/post/{id}', [PostController::class, 'deletePost']);

        //Matiere
        Route::post('/matiere', [MatiereController::class, 'creerMatiere']); // Ajouter une matière
        Route::delete('/matiere/{id}', [MatiereController::class, 'supprimerMatiere']); // Supprimer une matière
        Route::patch('/matiere/{id}', [MatiereController::class, 'mettreAJourMatiere']); // Mettre à jour une matière
        Route::get('/matieres', [MatiereController::class, 'recupererMatieres']); // Voir toutes les matières
        Route::get('/matiere/{id}', [MatiereController::class, 'recupererMatiere']);

        // Routes pour les agents
        
    });

    Route::middleware(['check.status:Admin,DR,Operateur'])->group(function() {
        Route::get('/post/{id}', [PostController::class, 'getPostbyId']);
        Route::get('/user', [UserController::class,'getProfile']);
        Route::patch('/users/{id}',[UserController::class,'updateUser']);
        Route::patch('/user/password', [UserController::class, 'updatePassword']);


        //agents
        Route::get('/agents', [AgentController::class, 'index']);
        Route::get('/agent/{id}', [AgentController::class, 'show']);
    });

    Route::middleware(['check.status:Operateur'])->group(function() {
        //ajout agent
        Route::post('/agents', [AgentController::class, 'store']);

    });
    Route::middleware(['check.status:DR'])->group(function() {
        
        
    });
});
