<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Http\Request;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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

        // Todas las respuestas de error de la API usan el formato de ApiResponse
        $this->renderable(function (Throwable $e, Request $request) {
            if (!$request->is('api/*')) {
                return null;
            }
            return $this->respuestaApi($e);
        });
    }

    private function respuestaApi(Throwable $e)
    {
        if ($e instanceof ValidationException) {
            return ApiResponse::error($e->getMessage(), 422, $e->errors());
        }
        if ($e instanceof AuthenticationException) {
            return ApiResponse::error('No autenticado', 401);
        }
        if ($e instanceof AuthorizationException) {
            return ApiResponse::error('No autorizado', 403);
        }
        if ($e instanceof ModelNotFoundException) {
            return ApiResponse::error('No Encontrado', 404);
        }
        if ($e instanceof HttpExceptionInterface) {
            $status = $e->getStatusCode();
            $mensajes = [
                404 => 'Ruta no encontrada',
                405 => 'Método no permitido',
                429 => 'Demasiados intentos, espera un momento',
            ];
            return ApiResponse::error($mensajes[$status] ?? ($e->getMessage() ?: 'Error'), $status);
        }
        return ApiResponse::error(config('app.debug') ? $e->getMessage() : 'Error del servidor', 500);
    }
}
