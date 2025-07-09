<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            // Model not found
            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json([
                    'error' => 'ModelNotFoundException',
                    'message' => 'Recurso no encontrado',
                    'data' => null,
                    'status' => false
                ], 404);
            }
            // No autenticado
            if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json([
                    'error' => 'AuthenticationException',
                    'message' => 'No autenticado',
                    'data' => null,
                    'status' => false
                ], 401);
            }
            // Validación
            if ($exception instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'error' => 'ValidationException',
                    'message' => $exception->getMessage(),
                    'data' => $exception->errors(),
                    'status' => false
                ], 422);
            }
            // Otros errores
            $status = ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException)
                ? $exception->getStatusCode()
                : 500;
            $error = class_basename($exception);
            $message = $exception->getMessage() ?: 'Error interno del servidor';
            $response = [
                'error' => $error,
                'message' => $message,
                'data' => null,
                'status' => false
            ];
            // Si está en modo debug, agregar detalles de la excepción
            if (config('app.debug')) {
                $response['exception'] = $error;
                $response['trace'] = $exception->getTrace();
            }
            return response()->json($response, $status);
        }
        return parent::render($request, $exception);
    }
}
