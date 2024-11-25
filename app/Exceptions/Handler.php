<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
{
    // Vérifie si l'exception est liée à un problème de connexion à la base de données
    if ($exception instanceof QueryException) {
        return response()->json([
            'error' => 'Problème de connexion à la base de données. Veuillez réessayer plus tard.'
        ], 500);
    }

    // Continue le rendu par défaut pour les autres exceptions
    return parent::render($request, $exception);
}

protected function unauthenticated($request, AuthenticationException $exception)
{
    return $request->expectsJson()
                ? response()->json(['message' => 'Veuillez vous connecter pour accéder à cette ressource.'], 401)
                : redirect()->guest(route('login'));
}
}
