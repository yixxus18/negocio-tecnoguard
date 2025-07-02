<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class CheckRole
{
    /**
     * Maneja una solicitud entrante.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  mixed ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$rol)
    {
        $data = $request->user();
        $user = $data->data;

        if (!$user) {
            return response()->json([
                'error' => 'unauthenticated',
                'message' => 'No autenticado',
                'data' => null,
                'status' => false
            ], 401);
        }
       
        if ($user['role_id'] != $rol[0]) {
            return response()->json([
                'error' => 'unauthorized',
                'message' => 'No autorizado',
                'data' => null,
                'status' => false
            ], 403);
        }

        return $next($request);
    }
}
