<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

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
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado'], 401);
        }

        // Convierte los roles a enteros para comparar con role_id
        $roleIds = array_map('intval', $roles);

        if (!in_array($user->role_id, $roleIds)) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        return $next($request);
    }
}
