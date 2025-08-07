<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardia\CrearTokenReq;
use App\Models\TokenAcceso;
use Carbon\Carbon;
use Illuminate\Http\Request;

class TokensController extends Controller
{
    /**
     * Generar token de acceso
     */
    public function generarTokenAcceso(CrearTokenReq $request)
    {
        $familiar = $request->user();
        $data = $request->validated();
        if (!array_key_exists('tipo_token', $data) || !$data['tipo_token']) {
            $data['tipo_token'] = 'visita';
        }
        $code = random_int(100000, 999999);
        $data['fecha_expiracion'] = Carbon::now('America/Monterrey')->addHours(5)->format('Y-m-d h:i:s');
        $data['usuario_id'] = $familiar->id;
        $data['usos'] = 1;
        $data['valor'] = $code;
        $token = TokenAcceso::create($data);
        return response()->json([
            'message' => 'Aceeso creado correctamente!',
            'data' => $token,
            'status' => true
        ]);
    }

    public function obtenerTokens(Request $request)
    {
        $familiar = $request->user();
        $tokens = TokenAcceso::where('usuario_id', $familiar->id)
            ->limit(15)->orderBy('created_at', 'desc')->get();
        return response()->json([
            'message' => 'Accesos obtenidos!',
            'data' => $tokens,
            'status' => true
        ]);
    }
}
