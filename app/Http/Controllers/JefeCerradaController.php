<?php

namespace App\Http\Controllers;

use App\Http\Requests\JefeCerrada\AsignarGuardiaReq;
use App\Models\Cerrada;
use App\Models\FamilyGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use User;

class JefeCerradaController extends Controller
{
    /**
     * Obtener familias de la cerrada
     */
    public function obtenerFamiliasCerrada(Request $request): JsonResponse
    {
        $jefe_cerrada = $request->user();
        $cerrada = Cerrada::where('jefe_cerrada_id', $jefe_cerrada->id)->first();
        $familias = FamilyGroup::where('cerrada_id', $cerrada->id)->get()->load('users');
        return response()->json([
            'message' => 'Lista de familias de la cerrada obtenida exitosamente',
            'data' => $familias,
            'status' => true
        ]);
    }

    /**
     * Asignar guardia a la cerrada
     */
    public function asignarGuardiaCerrada(AsignarGuardiaReq $request, int $cerradaId): JsonResponse
    {
        $data = $request->validated();
        $cerrada = Cerrada::find($cerradaId);
        if (!$cerrada) {
            return response()->json([
                'message' => 'La cerrada no existe!',
                'status' => false
            ], 404);
        }
        $cerrada->update([
            'guard_id' => $data['guardia_id'],
        ]);
        return response()->json([
            'message' => 'Guardia asignado a la cerrada exitosamente.',
            'data' => $cerrada->load('assignedGuard'),
            'status' => true
        ]);
    }

    public function desasignarGuardiaCerrada(Request $request, int $userId): JsonResponse
    {
        $guardia = User::find($userId);
        if (!$guardia) {
            return response()->json([
                'message' => 'Error: TG-RES-002, El guardia no existe!',
                'status' => false
            ], 404);
        }
        $jefe_cerrada = $request->user();
        $cerrada = Cerrada::where('jefe_cerrada_id', $jefe_cerrada->id)->first();
        $cerrada->update([
            'guard_id' => null
        ]);
        return response()->json([
            'message' => 'Guardia desasignado de la cerrada exitosamente.',
            'data' => $cerrada,
            'status' => true
        ]);
    }

    /**
     * Obtener guardias de la cerrada
     */
    public function obtenerGuardiasCerrada(Request $request): JsonResponse
    {
        $jefe_cerrada = $request->user();
        $cerrada = Cerrada::where('jefe_cerrada_id', $jefe_cerrada->id)->first()->load('assignedGuard');
        return response()->json([
            'message' => 'Guardias de la cerrada obtenidos exitosamente',
            'data' => $cerrada->flatMap->users,
            'status' => true
        ]);
    }

    /**
     * Procesar pago de familia
     */
    public function procesarPagoFamilia(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para procesar pago de familia
        return response()->json([
            'message' => 'Pago procesado exitosamente',
            'data' => []
        ]);
    }
}
