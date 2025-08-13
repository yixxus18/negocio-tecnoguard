<?php

namespace App\Http\Controllers;

use App\Http\Requests\Guardia\CrearTokenReq;
use App\Models\LogToken;
use App\Models\TokenAcceso;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GuardiaController extends Controller
{
    /**
     * Obtener logs de acceso
     */
    public function obtenerLogsAcceso(Request $request, ?string $date = null): JsonResponse
    {
        $guardia = $request->user();
        $guardia->load('cerradasAsGuard');
        $log_tokens = ($date != null) ?
            LogToken::where("used_at", '>=', Carbon::parse($date)->format('Y-m-d h-m-s'))
                ->where('cerrada.id', $guardia->cerradasAsGuard->id)->get() :
            LogToken::where('cerrada.id', $guardia->cerradasAsGuard->id)->get();

        return response()->json([
            'message' => 'Logs de acceso obtenidos exitosamente',
            'data' => $log_tokens,
            'status' => true,
        ]);
    }

    /**
     * Obtener tokens activos
     */
    public function obtenerTokensActivos(?string $tipo = null): JsonResponse
    {
        $tokens = ($tipo != null) ?
            TokenAcceso::where('tipo_token', $tipo)->where('usos', 1)->get() :
            TokenAcceso::where('usos', 1)->get();
        $tokens->load('usuario');
        return response()->json([
            'message' => 'Tokens activos obtenidos exitosamente',
            'data' => $tokens,
            'status' => true
        ]);
    }

    /**
     * Crear token de servicio
     */
    public function crearTokenServicio(CrearTokenReq $request): JsonResponse
    {
        $guardia = $request->user();
        $data = $request->validated();
        if (!array_key_exists('tipo_token', $data) || !$data['tipo_token']) {
            $data['tipo_token'] = 'servicio';
        }
        $code = random_int(100000, 999999);
        $data['fecha_expiracion'] = Carbon::now('America/Monterrey')->addHours(5)->format('Y-m-d h:i:s');
        $data['usuario_id'] = $guardia->id;
        $data['usos'] = 1;
        $data['valor'] = $code;
        $token = TokenAcceso::create($data);
        return response()->json([
            'message' => 'Aceeso creado correctamente!',
            'data' => $token,
            'status' => true
        ]);
    }
}
