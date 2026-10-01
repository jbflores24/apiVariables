<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Permite el acceso solo si el usuario autenticado tiene alguno de los roles indicados.
     * Uso: ->middleware('role:Administrador,Técnico')
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();
        if (!$user || !$user->hasRole(...$roles)) {
            return ApiResponse::error('No autorizado', 403);
        }
        return $next($request);
    }
}
