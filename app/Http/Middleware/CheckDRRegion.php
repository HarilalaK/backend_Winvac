<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckDRRegion
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Cette restriction ne s'applique qu'aux Directeurs Régionaux
        if (!$user || $user->statut !== 'DR') {
            return $next($request);
        }

        // Si le DR n'a pas de région assignée, il ne peut rien voir
        if (!$user->region_id) {
            return response()->json([
                'success' => false,
                'message' => 'Aucune région assignée à ce Directeur Régional. Veuillez contacter l\'administrateur.',
                'user_role' => $user->statut
            ], 403);
        }

        $routeUri = $request->route()->uri();

        // Appliquer les restrictions selon les routes
        if ($this->needsGeographicRestriction($routeUri)) {
            $restrictionResult = $this->applyGeographicRestriction($request, $user, $routeUri);
            if ($restrictionResult !== null) {
                return $restrictionResult;
            }
        }

        // Ajouter les informations de l'utilisateur à la requête
        $request->merge(['current_user' => $user]);

        return $next($request);
    }

    /**
     * Détermine si la route nécessite une restriction géographique
     */
    private function needsGeographicRestriction($routeUri)
    {
        $restrictedRoutes = [
            'api/agents',
            'api/agents/{id}',
            'api/agents/{id}/details',
            'api/centres',
            'api/centres/{id}',
            'api/centres/{centreId}/agents',
            'api/agents/filter',
            'api/agents/decompte'
        ];

        foreach ($restrictedRoutes as $route) {
            if (str_contains($routeUri, str_replace(['{id}', '{centreId}'], '', $route))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Applique la restriction géographique
     */
    private function applyGeographicRestriction($request, $user, $routeUri)
    {
        // Pour les routes de filtrage, ajouter automatiquement la région du DR
        if ($request->isMethod('POST') && str_contains($routeUri, 'filter')) {
            $request->merge(['region_id' => $user->region_id]);
            return null;
        }

        // Si le DR essaie d'accéder à une ressource spécifique
        if ($request->has('id') || $request->has('centre_id')) {
            $resourceId = $request->get('id') ?: $request->get('centre_id');
            
            if (!$this->resourceBelongsToDRRegion($resourceId, $user->region_id, $routeUri)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cette ressource ne se trouve pas dans votre région',
                    'user_region' => $user->region_id,
                    'user_role' => $user->statut,
                    'resource_id' => $resourceId
                ], 403);
            }
        }

        // Pour les listes, ajouter un filtre automatique sur la région
        if ($request->isMethod('GET') && !str_contains($routeUri, 'filter')) {
            $request->merge(['region_id' => $user->region_id]);
        }

        return null;
    }

    /**
     * Vérifie si une ressource appartient à la région du DR
     */
    private function resourceBelongsToDRRegion($resourceId, $userRegionId, $routeUri)
    {
        try {
            if (str_contains($routeUri, 'agents')) {
                $agent = \App\Models\Agent::with(['centre.region'])->find($resourceId);
                return $agent && $agent->centre && $agent->centre->region_id === $userRegionId;
            }
            
            if (str_contains($routeUri, 'centres')) {
                $centre = \App\Models\Centre::find($resourceId);
                return $centre && $centre->region_id === $userRegionId;
            }
        } catch (\Exception $e) {
            // En cas d'erreur, on refuse l'accès par sécurité
            return false;
        }

        return false;
    }
}
