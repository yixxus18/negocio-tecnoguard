<?php

namespace App\Http\Controllers;

use App\Http\Requests\JefeCerrada\AsignarGuardiaReq;
use App\Http\Requests\JefeCerrada\CrearConfigReq;
use App\Http\Requests\JefeCerrada\CrearPagoReq;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\JefeCerrada\UpdConfigReq;
use App\Models\Cerrada;
use App\Models\ConfigurationPayDate;
use App\Models\FamilyGroup;
use App\Models\Membership;
use App\Models\MembershipDetail;
use App\Services\FileUploadService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Log;

class JefeCerradaController extends Controller
{
    /**
     * Obtener familias de la cerrada
     */
    public function obtenerFamiliasCerrada(Request $request): JsonResponse
    {
        $jefe_cerrada = $request->user();
        $cerrada = Cerrada::where('jefe_cerrada_id', $jefe_cerrada->id)->first();
        if (!$cerrada) {
            return response()->json([
                'message' => 'No tiene una cerrada asignada!',
                'status' => false
            ], 404);
        }
        $familias = FamilyGroup::where('cerrada_id', $cerrada->id)->get()->load('users');
        Log::info($familias);
        return response()->json([
            
            'message' => 'Lista de familias de la cerrada obtenida exitosamente',
            'data' => $familias,
            'status' => true
        ]);
    }

     public function obtenerpagosdemicerrada(Request $request): JsonResponse
    {
        // validar fechas
        $v = Validator::make($request->all(), [
            'initial_date' => 'required|date_format:Y-m-d',
            'final_date'   => 'required|date_format:Y-m-d|after_or_equal:initial_date',
        ], [
            'initial_date.required' => 'La fecha inicial es obligatoria.',
            'initial_date.date_format' => 'La fecha inicial debe tener formato AAAA-MM-DD.',
            'final_date.required' => 'La fecha final es obligatoria.',
            'final_date.date_format' => 'La fecha final debe tener formato AAAA-MM-DD.',
            'final_date.after_or_equal' => 'La fecha final debe ser igual o posterior a la inicial.',
        ]);

        if ($v->fails()) {
            return response()->json([
                'error'   => 'validation_failed',
                'message' => 'Los datos proporcionados no son válidos.',
                'data'    => ['errors' => $v->errors()],
                'status'  => false,
            ], 422);
        }

        try {
            $user = $request->user(); 
            
            if ($user->role_id === 2) {
                $cerrada = Cerrada::where('jefe_cerrada_id', $user->id)->first();
            } elseif ($user->role_id === 3) {
                $cerrada = Cerrada::where('guard_id', $user->id)->first();
            } else {
                return response()->json([
                    'error'   => 'forbidden',
                    'message' => 'No tienes permiso para ver los pagos de esta cerrada.',
                    'data'    => null,
                    'status'  => false,
                ], 403);
            }

            if (! $cerrada) {
                return response()->json([
                    'error'   => 'forbidden',
                    'message' => 'No perteneces a ninguna cerrada válida.',
                    'data'    => null,
                    'status'  => false,
                ], 403);
            }

            
            $membershipIds = FamilyGroup::where('cerrada_id', $cerrada->id)
                ->pluck('membership_id');

            
            $details = MembershipDetail::whereIn('membership_id', $membershipIds)
                ->whereBetween('date_pay', [
                    $request->input('initial_date'),
                    $request->input('final_date'),
                ])
                ->get();

            return response()->json([
                'message' => 'Pagos de la cerrada obtenidos correctamente.',
                'data'    => $details,
                'status'  => true,
            ], 200);

        } catch (\Exception $e) {
            Log::error('Error obtenerpagosdemicerrada: ' . $e->getMessage());
            return response()->json([
                'error'   => 'internal_server_error',
                'message' => 'Ocurrió un error inesperado al obtener los pagos.',
                'data'    => null,
                'status'  => false,
            ], 500);
        }
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
        if (!$cerrada) {
            return response()->json([
                'message' => 'No tiene una cerrada asignada!',
                'status' => false
            ], 404);
        }
        return response()->json([
            'message' => 'Guardias de la cerrada obtenidos exitosamente',
            'data' => $cerrada,
            'status' => true
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

    public function updateConfigPago(UpdConfigReq $request, int $configId)
    {
        $jefe_cerrada = $request->user();
        $cerrada = Cerrada::where('jefe_cerrada_id', $jefe_cerrada->id)->first();

        $config = ConfigurationPayDate::find($configId);
        if (!$config || $config->id != $cerrada->configuration_pay_date) {
            return response()->json([
                'message' => 'Error: TG-RES-001, La configuración no existe o no pertenece a la cerrada asignada al jefe de cerrada',
                'status' => false
            ]);
        }
        $data = $request->validated();
        $config->update([
            'nombre_configuracion' => $data['nombre_configuracion'],
            'Fecha_Corte' => $data['fecha_corte'],
            'pay' => $data['pay'],
            'tiempo_prorroga' => $data['tiempo_prorroga'],
        ]);
        return response()->json([
            'message' => 'La configuracion se actualizo y se asigno a la cerrada correctamente!',
            'data' => $cerrada->load('configurationPayDate')->load('assignedGuard'),
            'status' => true
        ]);
    }

   public function obtenerGuardiasDisponibles(): JsonResponse
    {
       
        $guardias = User::where('role_id', 3)
            ->with('cerradasAsGuard:id,group_name,guard_id')
            ->get(['id', 'name']);

        if ($guardias->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron guardias',
                'data'    => [],
                'status'  => false,
            ], 404);
        }

        $resultado = $guardias->map(function ($guard) {
            $tieneCerrada  = $guard->cerradasAsGuard->isNotEmpty();
            $nombreCerrada = $guard->cerradasAsGuard->first()->group_name ?? null;

            return [
                'id'             => $guard->id,
                'nombre'         => $guard->name,
                'nombre_cerrada' => $nombreCerrada,
                'ocupado'        => $tieneCerrada,
            ];
        });

        return response()->json([
            'message' => 'Guardias obtenidos correctamente',
            'data'    => $resultado,
            'status'  => true,
        ],200);
    }
}
