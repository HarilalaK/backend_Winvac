<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserStatus
{
    public function handle(Request $request, Closure $next, ...$statuts)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'Non autorisé'
            ], 401);
        }

        if (!in_array(auth()->user()->statut, $statuts)) {
            return response()->json([
                'success' => false,
                'message' => 'Vous n\'avez pas les permissions nécessaires'
            ], 403);
        }

        return $next($request);
    }
}