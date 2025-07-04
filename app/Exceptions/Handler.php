<?php

namespace App\Exceptions;

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

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $exception)
    {
        if ($request->expectsJson()) {
            $status = ($exception instanceof \Symfony\Component\HttpKernel\Exception\HttpException)
                ? $exception->getStatusCode()
                : 500;
            $error = $exception->getMessage() ?: 'Error interno del servidor';
            return response()->json([
                'error' => class_basename($exception),
                'message' => $error,
                'data' => null,
                'status' => false
            ], $status);
        }
        return parent::render($request, $exception);
    }
}
