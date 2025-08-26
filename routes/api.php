<?php

use App\Http\Controllers\Api\JournalController;
use App\Http\Controllers\Api\MatiereController;
use App\Http\Controllers\Api\PostController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AgentController;
use App\Http\Controllers\Api\ProvinceController;
use App\Http\Controllers\Api\RegionController;
use App\Http\Controllers\Api\CentreController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\TauxRoleController;
use App\Http\Controllers\Api\DashboardController;
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

// Routes publiques
Route::post('/login', [UserController::class, 'login']);

// Routes pour la gestion des lieux (accessibles avec authentification)
Route::middleware('auth:sanctum')->group(function () {
    // Hiérarchie des lieux
    Route::get('/locations/hierarchy', [ProvinceController::class, 'hierarchy']);

    // Routes des provinces
    Route::get('/provinces', [ProvinceController::class, 'index']);
    Route::get('/provinces/{province}', [ProvinceController::class, 'show']);
    Route::get('/provinces/{province}/regions', [ProvinceController::class, 'regions']);

    // Routes des régions
    Route::get('/regions/{region}/centres', [RegionController::class, 'centres']);

    // Routes des rôles (accessibles à tous les utilisateurs authentifiés)
    Route::get('/roles', [RoleController::class, 'index']);
    Route::get('/roles/{role}', [RoleController::class, 'show']);

    // Routes protégées par le rôle Admin
    Route::middleware(['check.status:Admin'])->group(function() {
        Route::post('/provinces', [ProvinceController::class, 'store']);
        Route::put('/provinces/{province}', [ProvinceController::class, 'update']);
        Route::delete('/provinces/{province}', [ProvinceController::class, 'destroy']);

        // Routes pour la gestion des taux par rôle
        Route::apiResource('taux-roles', TauxRoleController::class);
        Route::get('taux-roles/role/{role}', [TauxRoleController::class, 'getTauxByRole']);

        // Autres routes Admin existantes...
        Route::get('/users', [UserController::class, 'getAllUsers']);
        Route::get('/users/{id}', [UserController::class, 'getUserById']);
        Route::post('/register', [UserController::class, 'register']);
        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);
        Route::get('/journal', [JournalController::class, 'index']);
        Route::post('/post', [PostController::class, 'addPost']);
        Route::apiResource('regions', RegionController::class);
        Route::apiResource('centres', CentreController::class);
        // Seules les opérations de modification des rôles sont protégées
        Route::post('/roles', [RoleController::class, 'store']);
        Route::put('/roles/{role}', [RoleController::class, 'update']);
        Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
        Route::get('roles/{role}/agents', [RoleController::class, 'agents']);
        Route::apiResource('agents', AgentController::class);

    });

    // Routes Admin,DR existantes...
    Route::middleware(['check.status:Admin,DR'])->group(function() {
        Route::patch('/post/{id}', [PostController::class, 'editPost']);
        Route::delete('/post/{id}', [PostController::class, 'deletePost']);
        Route::post('/matiere', [MatiereController::class, 'creerMatiere']);
        Route::apiResource('matieres', MatiereController::class);
        Route::patch('/users/{id}',[UserController::class,'updateUser']);
    });

    // Routes Admin,DR,Operateur existantes...
    Route::middleware(['check.status:Admin,DR,Operateur'])->group(function() {
        Route::get('/post/{id}', [PostController::class, 'getPostbyId']);
        Route::get('/user', [UserController::class,'getProfile']);
        Route::patch('/user/password', [UserController::class, 'updatePassword']);
        Route::get('/agents', [AgentController::class, 'index']);
        Route::get('/agents/{id}', [AgentController::class, 'show']);
        Route::get('/agents/role/{role}', [AgentController::class, 'getAgentsByRole']);
        Route::get('/agents/{id}/details', [AgentController::class, 'getAgentDetails']);
        Route::get('/centres/{centreId}/agents', [AgentController::class, 'getAgentsByCentre']);
        Route::post('/agents/filter', [AgentController::class, 'filter']);
        Route::get('/agents/decompte', [AgentController::class, 'getDecompte']);
    });

    // Routes Operateur,Admin existantes...
    Route::middleware(['check.status:Operateur,Admin'])->group(function() {
        Route::post('/agents', [AgentController::class, 'store']);
        Route::put('/agents/{id}', [AgentController::class, 'update']);
        Route::delete('/agents/{id}', [AgentController::class, 'destroy']);
    });

    // Routes pour le journal
    Route::prefix('journal')->group(function () {
        Route::get('/', [JournalController::class, 'index']);
        Route::get('/recent', [JournalController::class, 'getRecentActivities']);
        Route::get('/user/{userId}', [JournalController::class, 'getActivitiesByUser']);
    });

    // Routes pour le dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('/stats', [DashboardController::class, 'getStats']);
        Route::get('/activities', [DashboardController::class, 'getActivities']);
    });

});
