<?php

namespace App\Http\Controllers;

use App\Http\Requests\JefeCerrada\AsignarGuardiaReq;
use App\Http\Requests\JefeCerrada\CrearConfigReq;
use App\Models\Cerrada;
use App\Models\ConfigurationPayDate;
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

    public function obtenerConfigPago(Request $request, int $configId): JsonResponse
    {
        $jefe_cerrada = $request->user();
        $config = ConfigurationPayDate::find($configId);
        $cerrada = Cerrada::where('jefe_cerrada_id', $jefe_cerrada->id)->where('configuration_pay_date', $configId)->first();
        if (!$cerrada || !$config) {
            return response()->json([
                'message' => 'Error: TG-RES-001, La configuración no pertenece a la cerrada del Jefe de Cerrada o la configuración no existe',
                'status' => false
            ], 404);
        }
        return response()->json([
            'message' => 'Configuración obtenida exitosamente!',
            'data' => $config,
            'status' => true
        ]);
    }

    public function crearConfigPago(Request $request)
    {
        $jefe_cerrada = $request->user();
        $cerrada = Cerrada::where('jefe_cerrada_id', $jefe_cerrada->id)
            ->whereNull('configuration_pay_date')->first();
        if (!$cerrada) {
            return response()->json([
                'message' => 'Error: TG-CONF-001, La cerrada ya cuenta con una configuración activa',
                'status' => false
            ], 401);
        }
        $validator = validator($request->all(), [
            'nombre_configuracion' => 'required|string|min:5|max:127',
            'fecha_corte' => 'required|integer|min:1|max:31',
            'pay' => 'required|integer|min:100|max:9999',
            'tiempo_prorroga' => 'required|integer|min:1|max:3'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors(),
                'status' => false
            ], 422);
        }

        $data = $validator->validated();

        $config = ConfigurationPayDate::create([
            'nombre_configuracion' => $data['nombre_configuracion'],
            'Fecha_Corte' => $data['fecha_corte'],
            'pay' => $data['pay'],
            'tiempo_prorroga' => $data['tiempo_prorroga'],
        ]);

        $cerrada->update([
            'configuration_pay_date' => $config->id
        ]);
        return response()->json([
            'message' => 'La configuracion se creo y se asigno a la cerrada correctamente!',
            'data' => $cerrada->load('configurationPayDate')->load('assignedGuard'),
            'status' => true
        ], 201);

    }
}
