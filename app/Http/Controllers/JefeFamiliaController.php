<?php

namespace App\Http\Controllers;

use App\Http\Requests\JefeFamilia\AddMiembroReq;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Log;

class JefeFamiliaController extends Controller
{
    /**
     * Generar token de acceso
     */
    public function generarTokenAcceso(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para generar token de acceso
        return response()->json([
            'message' => 'Token de acceso generado exitosamente',
            'data' => []
        ], 201);
    }

    /**
     * Agregar miembro de familia
     */
    public function agregarMiembroFamilia(AddMiembroReq $request): JsonResponse
    {
        $data = $request->validated();
        $jefe_familia = $request->user();
        $miembro = User::where('email', $data['email'])->firstOrFail();
        if (!$miembro) {
            return response()->json([
                'message' => 'El miembro no fue encontrado!',
                'status' => true
            ], 404);
        }
        $miembro->update(['family_id' => $jefe_familia->family_id, 'is_active' => true]);
        return response()->json([
            'message' => 'Miembro de familia agregado exitosamente',
            'data' => $miembro,
            'status' => true
        ]);
    }

    /**
     * Obtener miembros de familia
     */
    public function obtenerMiembrosFamilia(Request $request): JsonResponse
    {
        $jefe_familia = $request->user();
        $miembros = User::where('family_id', $jefe_familia->family_id)
            ->whereNot('id', $jefe_familia->id)->get();
        return response()->json([
            'message' => 'Miembros de familia obtenidos exitosamente',
            'data' => $miembros,
            'status' => true
        ]);
    }

    /**
     * Eliminar miembro de familia
     */
    public function eliminarMiembroFamilia(Request $request, $member_id): JsonResponse
    {
        $jefe_familia = $request->user();
        $miembro = User::find($member_id);
        if ($jefe_familia->family_id != $miembro->family_id) {
            return response()->json([
                'message' => 'El miembro no pertenece a su familia!',
                'status' => false
            ], 422);
        }
        $miembro->update([
            'family_id' => null,
            'is_active' => false
        ]);
        return response()->json([
            'message' => 'Miembro de familia eliminado exitosamente',
            'data' => $miembro,
            'status' => true
        ]);
    }

    /**
     * Consultar saldo y estado
     */
    public function consultarSaldoEstado(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para consultar saldo y estado
        return response()->json([
            'message' => 'Saldo y estado consultados exitosamente',
            'data' => []
        ]);
    }

    /**
     * Obtener historial de membresía
     */
    public function obtenerHistorialMembresia(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para obtener historial de membresía
        return response()->json([
            'message' => 'Historial de membresía obtenido exitosamente',
            'data' => []
        ]);
    }
}
