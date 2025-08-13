<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Guardia\CrearTokenReq;
use App\Models\LogToken;
use App\Models\TokenAcceso;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Log;

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
        $exists = TokenAcceso::where('valor', $code)->where('usos', 1)->first();
        if ($exists) {
            do {
                $code = random_int(100000, 999999);
                $exists = TokenAcceso::where('valor', $code)->where('usos', 1)->first();
            } while ($exists != null);
        }
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

    public function usarToken(Request $request)
    {
        $data = $request->validate([
            'token' => 'required|integer|min:100000|max:999999',
            'cerrada_id' => 'required|integer|exists:cerradas,id'
        ]);
        $token = TokenAcceso::where('valor', $data['token'])
            ->where('usos', 1)->first();
        if($token) $token->load('usuario.familyGroup.cerrada');

        if (
            !$token ||
            !$token->usuario ||
            !$token->usuario->familyGroup ||
            !$token->usuario->familyGroup->cerrada ||
            $token->usuario->familyGroup->cerrada->id != $data['cerrada_id']
        ) {
            LogToken::create([
                'token' => $data['token'],
                'used_at' => Carbon::now('America/Monterrey')->addHours(5)->format('Y-m-d h:i:s'),
                'created_by' => "N/A",
                'nombre' => "N/A",
                'was_valid' => false,
                'cerrada' => "N/A"
            ]);
            return response()->json([
                'message' => 'Se registro intento fallido!',
                'status' => false
            ], 400);
        }

        $token->update([
            'usos' => 0
        ]);
        LogToken::create([
            'token' => $data['token'],
            'used_at' => Carbon::now('America/Monterrey')->addHours(5)->format('Y-m-d h:i:s'),
            'created_by' => ['name' => $token->usuario->name, 'id' => $token->usuario->id],
            'nombre' => $token->nombre,
            'was_valid' => true,
            'cerrada' => [
                'name' => $token->usuario->familyGroup->cerrada->group_name,
                'id' => $token->usuario->familyGroup->cerrada->id
            ]
        ]);

        return response()->json([
            "message" => 'Acceso autorizado!',
            "status" => true
        ]);

    }


}
