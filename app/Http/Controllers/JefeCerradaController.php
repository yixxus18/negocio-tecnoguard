<?php

namespace App\Http\Controllers;

use App\Http\Requests\JefeCerrada\AsignarGuardiaReq;
use App\Http\Requests\JefeCerrada\CrearConfigReq;
use App\Http\Requests\JefeCerrada\CrearPagoReq;
use App\Models\TokenAcceso;
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


    /**obtener todos los guardias de mi cerrada */
    // public function obtenerallguardiasdemicerrada(Request $request)
    // {

    // }
    /**
     * Obtener familias de la cerrada
     */



    public function cambioguardia(Request $request, int $guardiaId): JsonResponse
{
   
    $v = Validator::make($request->all(), [
        'cerrada_id' => 'required|integer|exists:cerradas,id',
    ], [
        'cerrada_id.required' => 'El campo cerrada_id es obligatorio.',
        'cerrada_id.integer'  => 'El campo cerrada_id debe ser un número entero.',
        'cerrada_id.exists'   => 'La cerrada indicada no existe.',
    ]);

    if ($v->fails()) {
        return response()->json([
            'message' => 'Los datos proporcionados no son válidos.',
            'data'    => ['errors' => $v->errors()],
            'status'  => false,
        ], 422);
    }

    $guardia = User::find($guardiaId);
    if (! $guardia) {
        return response()->json([
            'message' => 'No se encontró al usuario.',
            'data'    => null,
            'status'  => false,
        ], 404);
    }

    $cerrada = Cerrada::find($request->input('cerrada_id')); 


    $yaAsignado = Cerrada::where('guard_id', $guardiaId)
        ->where('id', '!=', $cerrada->id)
        ->exists();

    if ($yaAsignado) {
        return response()->json([
            'message' => 'El guardia ya está asignado a otra cerrada.',
            'data'    => null,
            'status'  => false,
        ], 409);
    }

    $cerrada->update(['guard_id' => $guardia->id]);

    return response()->json([
        'message' => 'Guardia asignado/cambiado correctamente.',
        'data'    => $cerrada->load('assignedGuard'),
        'status'  => true,
    ], 200);
}

    public function obtenerguardiaslibres(Request $request): JsonResponse
{
    try {
      
        $sub = Cerrada::query()
            ->select('guard_id')
            ->whereNotNull('guard_id');

        $guards = User::query()
            ->where('role_id', 3)
            ->whereNotIn('id', $sub)   
            ->get(['id', 'name', 'email', 'phone']);

        if ($guards->isEmpty()) {
            return response()->json([
                'message' => 'No hay guardias libres disponibles.',
                'data'    => [],
                'status'  => false,
            ], 404);
        }
        $data = $guards->map(fn ($g) => [
            'id'      => $g->id,
            'nombre'  => $g->name,
            'email'   => $g->email,
            'phone'   => $g->phone,
            'ocupado' => false,
        ])->values();

        return response()->json([
            'message' => 'Guardias libres obtenidos correctamente.',
            'data'    => $data,
            'status'  => true,
        ], 200);
    } catch (\Throwable $e) {
        \Log::error('obtenerguardiaslibres error: '.$e->getMessage());
        return response()->json([
            'message' => 'Ocurrió un error al obtener guardias libres.',
            'data'    => null,
            'status'  => false,
        ], 500);
    }
}

public function obtenerusuariosmicerrada(Request $request): JsonResponse
{
    $jefe = $request->user();

    // Usuarios que pertenecen a familias cuyas cerradas son del jefe autenticado.
    // Cargamos con Eloquent las relaciones para NO usar joins manuales.
    $usuarios = User::with([
            'familyGroup.cerrada:id,group_name' // para obtener nombre de la cerrada
        ])
        ->whereHas('familyGroup.cerrada', function ($q) use ($jefe) {
            $q->where('jefe_cerrada_id', $jefe->id);
        })
        ->get(['id','name','email','phone','family_id','role_id']);

    if ($usuarios->isEmpty()) {
        return response()->json([
            'message' => 'No tiene una cerrada asignada o no hay usuarios en sus cerradas.',
            'data'    => [],
            'status'  => false,
        ], 404);
    }

    $data = $usuarios->map(function (User $u) {
        $fg       = $u->familyGroup;          // FamilyGroup (puede ser null si no tiene)
        $cerrada  = $fg?->cerrada;            // Cerrada relacionada
        $rolLabel = $u->role_id === 4
            ? 'Jefe de familia'
            : ($u->role_id === 5 ? 'Familiar' : 'Otro');

        return [
            'id'             => $u->id,
            'name'           => $u->name,
            'email'          => $u->email,
            'phone'          => $u->phone,
            'family_id'      => $u->family_id,
            'role_id'        => $u->role_id,
            'rol'            => $rolLabel,
            'cerrada_id'     => $fg?->cerrada_id,
            'cerrada_nombre' => $cerrada?->group_name,
        ];
    })->values();

    return response()->json([
        'message' => 'Usuarios de tus cerradas obtenidos correctamente.',
        'data'    => $data,
        'status'  => true,
    ], 200);
}



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


public function dashboardjefecerrada(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado.',
            'data'    => null,
        ], 401);
    }

    $tz         = 'America/Monterrey';
    $endLocal   = now($tz);
    $startLocal = $endLocal->copy()->subDays(7)->startOfDay();

    // Comparar contra created_at (UTC en BD normalmente)
    $startUtc = $startLocal->clone()->setTimezone('UTC');
    $endUtc   = $endLocal->clone()->setTimezone('UTC');

    $cerradas = Cerrada::select('id', 'group_name')
        ->where('jefe_cerrada_id', $user->id)
        ->orderBy('group_name')
        ->get();

    if ($cerradas->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'No administras ninguna cerrada.',
            'data'    => [
                'total_cerradas' => 0,
                'rango' => [
                    'timezone'    => $tz,
                    'start_local' => $startLocal->toDateTimeString(),
                    'end_local'   => $endLocal->toDateTimeString(),
                    'start_utc'   => $startUtc->toDateTimeString(),
                    'end_utc'     => $endUtc->toDateTimeString(),
                ],
                'cerradas' => [],
            ],
        ], 200);
    }

    $salida = [];

    foreach ($cerradas as $cerrada) {
        $familyGroupIds = FamilyGroup::where('cerrada_id', $cerrada->id)->pluck('id');

        $membershipIds = FamilyGroup::where('cerrada_id', $cerrada->id)
            ->whereNotNull('membership_id')
            ->pluck('membership_id');

        $baseDetails = MembershipDetail::whereIn('membership_id', $membershipIds)
            ->whereBetween('created_at', [$startUtc, $endUtc]);

        $details = (clone $baseDetails)
            ->orderByDesc('created_at')
            ->get([
                'id',
                'membership_id',
                'amount',
                'date_pay',
                'date_finalization',
                'ticket',
                'estatus',
                'created_at',
                'updated_at',
            ]);

        $estatusCounts = (clone $baseDetails)
            ->select('estatus', \DB::raw('COUNT(*) AS c'))
            ->groupBy('estatus')
            ->pluck('c', 'estatus');

        $countRevision  = (int) ($estatusCounts['revision']  ?? 0);
        $countValidado  = (int) ($estatusCounts['validado']  ?? 0);
        $countRechazado = (int) ($estatusCounts['rechazado'] ?? 0);

        // ✅ AQUÍ ESTABA EL PROBLEMA: sumar en membership_details, no en memberships
        $totalGanado = (float) MembershipDetail::whereIn('membership_id', $membershipIds)
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->where('estatus', 'validado')
            ->sum('amount');

        $userIdsEnCerrada = User::whereIn('family_id', $familyGroupIds)->pluck('id');

        $tokensGenerados = TokenAcceso::whereIn('usuario_id', $userIdsEnCerrada)
            ->whereBetween('created_at', [$startUtc, $endUtc])
            ->count();

        $salida[] = [
            'cerrada' => [
                'id'         => $cerrada->id,
                'group_name' => $cerrada->group_name,
            ],
            'rango' => [
                'timezone'    => $tz,
                'start_local' => $startLocal->toDateTimeString(),
                'end_local'   => $endLocal->toDateTimeString(),
                'start_utc'   => $startUtc->toDateTimeString(),
                'end_utc'     => $endUtc->toDateTimeString(),
            ],
            'membership_details' => [
                'count'        => $details->count(),
                'revision'     => $countRevision,
                'validado'     => $countValidado,
                'rechazado'    => $countRechazado,
                'total_ganado' => $totalGanado,
                'items'        => $details,
            ],
            'tokens_acceso' => [
                'usuarios_en_cerrada' => $userIdsEnCerrada->count(),
                'generados'           => $tokensGenerados,
            ],
        ];
    }

    return response()->json([
        'success' => true,
        'message' => 'Dashboard de jefe de cerrada generado correctamente.',
        'data'    => [
            'total_cerradas' => $cerradas->count(),
            'rango' => [
                'timezone'    => $tz,
                'start_local' => $startLocal->toDateTimeString(),
                'end_local'   => $endLocal->toDateTimeString(),
                'start_utc'   => $startUtc->toDateTimeString(),
                'end_utc'     => $endUtc->toDateTimeString(),
            ],
            'cerradas' => $salida,
        ],
    ], 200);
}


}
