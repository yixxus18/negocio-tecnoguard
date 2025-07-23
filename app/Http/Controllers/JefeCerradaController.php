<?php

namespace App\Http\Controllers;

use App\Models\Cerrada;
use App\Models\FamilyGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

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
    public function asignarGuardiaCerrada(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para asignar guardia a la cerrada
        return response()->json([
            'message' => 'Guardia asignado exitosamente',
            'data' => []
        ]);
    }

    /**
     * Obtener guardias de la cerrada
     */
    public function obtenerGuardiasCerrada(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para obtener guardias de la cerrada
        return response()->json([
            'message' => 'Guardias de la cerrada obtenidos exitosamente',
            'data' => []
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
