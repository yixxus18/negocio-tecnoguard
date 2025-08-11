<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\MembershipDetail;
use Carbon\Carbon;
use Log;


class AdminController extends Controller
{
    /**
     * Crear una nueva cerrada
     */




    public function crearTecnico(Request $request): JsonResponse
{
    Log::info($request);
    // 1) Validación
    $validator = Validator::make($request->all(), [
        'name'     => ['required', 'string', 'max:255'],
        'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8'],
        // 👇 exactamente 10 dígitos y único en users.phone
        'phone'    => ['required', 'digits:10', 'unique:users,phone'],
    ], [
        'name.required'       => 'El nombre es obligatorio.',
        'name.string'         => 'El nombre debe ser una cadena de texto.',
        'name.max'            => 'El nombre no puede exceder los 255 caracteres.',
        'email.required'      => 'El correo electrónico es obligatorio.',
        'email.email'         => 'El formato del correo no es válido.',
        'email.max'           => 'El correo no puede exceder los 255 caracteres.',
        'email.unique'        => 'El correo ya está registrado.',
        'password.required'   => 'La contraseña es obligatoria.',
        'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
        'phone.required'      => 'El teléfono es obligatorio.',
        'phone.digits'        => 'El teléfono debe tener exactamente 10 dígitos.',
        'phone.unique'        => 'El teléfono ya está registrado.',
    ]);


    if ($validator->fails()) {
        return response()->json([
            'error'   => 'validation_failed',
            'message' => 'Los datos proporcionados no son válidos.',
            'data'    => ['errors' => $validator->errors()],
            'status'  => false,
        ], 422);
    }

    // 2) Crear técnico (rol 5), desactivar 2FA, activar usuario, hash de contraseña
    try {
        $user = User::create([
            'name'               => $request->input('name'),
            'email'              => $request->input('email'),
            'phone'=>$request->input('phone'),
            'password'           => Hash::make($request->input('password')),
            'role_id'            => 6,
            'two_factor_enabled' => false,
            'is_active'          => true,
            'email_verified_at'  => now(),
        ]);

        return response()->json([
            'message' => 'Técnico creado exitosamente.',
            'data'    => [
                'id'      => $user->id,
                'name'    => $user->name,
                'email'   => $user->email,
                'role_id' => $user->role_id,
                'phone'=>$user->phone
            ],
            'status'  => true,
        ], 201);
    } catch (\Throwable $e) {
        Log::info($e);
        return response()->json([
            'error'   => 'server_error',
            'message' => 'No se pudo crear el técnico.',
            'status'  => false,
        ], 500);
    }
}

public function ObtenerTecnico(): JsonResponse
{
    $tecnicos = User::where('role_id', 6)
        ->with(['bitacora.cerrada','bitacora.servicio']) // carga la cerrada de cada registro de bitácora
        ->get();

    return response()->json([
        'data'    => $tecnicos,
        'message' => 'Técnicos obtenidos correctamente',
        'status'  => true,
    ], 200);
}

// public function bloqueartecnico
// {

// }

public function cambiarresponsableactividad(Request $request): JsonResponse
{

    $validated = $request->validate([
        'bitacora_id' => 'required|integer',
        'tecnico_id'  => 'required|integer',
        'comentario'  => 'required|string|max:500',
    ], [
        'bitacora_id.required' => 'El identificador de la actividad es obligatorio.',
        'bitacora_id.integer'  => 'El identificador de la actividad debe ser un número.',
        'tecnico_id.required'  => 'El técnico es obligatorio.',
        'tecnico_id.integer'   => 'El técnico debe ser un número.',
         'comentario.required'    => 'El comentario debe ser un texto.',
        'comentario.string'    => 'El comentario debe ser un texto.',
        'comentario.max'       => 'El comentario no puede exceder de :max caracteres.',
    ]);

    $actividad = Bitacora::find($validated['bitacora_id']);
    if (! $actividad) {
        return response()->json([
            'success' => false,
            'message' => 'Actividad no encontrada.',
        ], 404);
    }

    $actividad->tecnico_id = $validated['tecnico_id'];
    if (array_key_exists('comentario', $validated)) {
        $actividad->comentario = $validated['comentario'];
    }
    $actividad->save();
    return response()->json([
        'success' => true,
        'message' => 'Responsable actualizado correctamente.',
        'data'    => $actividad,
    ], 200);
}

public function ObtenerBitacora(Request $request): JsonResponse
{
    // Validación de fechas
    $validated = $request->validate([
        'date_initial' => 'required|date',
        'date_final'   => 'required|date|after_or_equal:date_initial',
    ], [
        'date_initial.required' => 'La fecha inicial es obligatoria.',
        'date_initial.date'     => 'La fecha inicial no es válida.',
        'date_final.required'   => 'La fecha final es obligatoria.',
        'date_final.date'       => 'La fecha final no es válida.',
        'date_final.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
    ]);

    $start = Carbon::parse($validated['date_initial'])->startOfDay();
    $end   = Carbon::parse($validated['date_final'])->endOfDay();

    $bitacoras = Bitacora::with('tenico','servicio','cerrada')
        ->whereBetween('created_at', [$start, $end])
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json([
        'success' => true,
        'message' => 'Bitácoras obtenidas correctamente.',
        'data'    => $bitacoras,
    ], 200);
}


    public function crearCerrada(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para crear cerrada
        return response()->json([
            'message' => 'Cerrada creada exitosamente',
            'data' => []
        ], 201);
    }

    /**
     * Obtener todas las cerradas
     */
    public function obtenerTodasCerradas(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para obtener todas las cerradas
        return response()->json([
            'message' => 'Cerradas obtenidas exitosamente',
            'data' => []
        ]);
    }

    /**
     * Actualizar una cerrada específica
     */
    public function actualizarCerrada(Request $request, $id_cerrada): JsonResponse
    {
        // TODO: Implementar lógica para actualizar cerrada
        return response()->json([
            'message' => 'Cerrada actualizada exitosamente',
            'data' => ['id' => $id_cerrada]
        ]);
    }

    /**
     * Asignar jefe a una cerrada
     */
    public function asignarJefeCerrada(Request $request, $id_cerrada): JsonResponse
    {
        // TODO: Implementar lógica para asignar jefe a cerrada
        return response()->json([
            'message' => 'Jefe asignado exitosamente',
            'data' => ['id_cerrada' => $id_cerrada]
        ]);
    }

    /**
     * Crear usuario administrativo
     */
  public function crearUsuarioAdministrativo(Request $request): JsonResponse
{
    
    $validator = Validator::make($request->all(), [
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|max:255|unique:users,email',
        'phone'    => 'required|string|size:10|unique:users,phone',
        'role_id'  => 'required|integer|exists:roles,id',
        'password' => [
            'required',
            'string',
            'min:8',
            'regex:/[A-Z]/',    
            'regex:/\d/',       
            'regex:/[#$%]/',   
        ],
    ], [
        'name.required'     => 'El nombre es obligatorio.',
        'name.string'       => 'El nombre debe ser una cadena de texto.',
        'name.max'          => 'El nombre no puede exceder los 255 caracteres.',
        'email.required'    => 'El correo electrónico es obligatorio.',
        'email.email'       => 'El formato del correo no es válido.',
        'email.max'         => 'El correo no puede exceder los 255 caracteres.',
        'email.unique'      => 'El correo ya está registrado.',
        'phone.required'    => 'El teléfono es obligatorio.',
        'phone.string'      => 'El teléfono debe ser una cadena de texto.',
        'phone.size'        => 'El teléfono debe tener exactamente 10 dígitos.',
        'phone.unique'      => 'El teléfono ya está registrado.',
        'role_id.required'  => 'El role_id es obligatorio.',
        'role_id.integer'   => 'El role_id debe ser un número entero.',
        'role_id.exists'    => 'El role_id especificado no existe.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
        'password.regex'    => 'La contraseña debe contener al menos una mayúscula, un número y un carácter especial (#, $, %).',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error'   => 'validation_failed',
            'message' => 'Los datos proporcionados no son válidos.',
            'data'    => ['errors' => $validator->errors()],
            'status'  => false,
        ], 422);
    }

    
    $user = User::create([
        'name'              => $request->input('name'),
        'email'             => $request->input('email'),
        'phone'             => $request->input('phone'),
        'role_id'           => $request->input('role_id'),
        'password'          => Hash::make($request->input('password')),
        'is_active'         => true,
        'email_verified_at' => now(),
    ]);

    
    return response()->json([
        'message' => 'Usuario administrativo creado exitosamente.',
        'data'    => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'phone'    => $user->phone,
            'role_id'  => $user->role_id,
        ],
        'status'  => true,
    ], 201);
}


public function getEarningsByCerrada(Request $request): JsonResponse
{
    // 1) Validar fechas
    $validator = Validator::make($request->all(), [
        'start_date' => ['required', 'date'],
        'end_date'   => ['required', 'date', 'after_or_equal:start_date'],
    ], [
        'start_date.required'     => 'La fecha de inicio es obligatoria.',
        'start_date.date'         => 'La fecha de inicio no es válida.',
        'end_date.required'       => 'La fecha de fin es obligatoria.',
        'end_date.date'           => 'La fecha de fin no es válida.',
        'end_date.after_or_equal' => 'La fecha de fin debe ser igual o posterior a la de inicio.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error'   => 'validation_failed',
            'message' => 'Rango de fechas inválido.',
            'data'    => ['errors' => $validator->errors()],
            'status'  => false,
        ], 422);
    }

    // 2) Límites del día completo
    $start = Carbon::parse($request->input('start_date'))->startOfDay();
    $end   = Carbon::parse($request->input('end_date'))->endOfDay();

    // 3) Traer detalles con relaciones y filtrar los que carezcan de cerrada
    $details = MembershipDetail::with('membership.familyGroups.cerrada')
        ->whereBetween('date_pay', [$start, $end])
        ->get()
        ->filter(fn ($d) =>
            $d->membership
            && $d->membership->familyGroups
            && $d->membership->familyGroups->cerrada
        );

    // 4) Agrupar por cerrada y calcular métricas
    $stats = $details
        ->groupBy(fn ($d) => $d->membership->familyGroups->cerrada->id)
        ->map(function ($group, $cerradaId) {
            $cerrada = $group->first()->membership->familyGroups->cerrada;

            // Sólo suma de validado
            $montogenerado = $group->where('estatus', 'validado')->sum('amount');

            // Contadores por estatus
            $validados  = $group->where('estatus', 'validado')->count();
            // Considera "rechazado" y/o "revision" como rechazados
            $rechazados = $group->filter(fn ($d) => in_array($d->estatus, ['rechazado', 'revision']))->count();
            $pendientes = $group->where('estatus', 'pendiente')->count();

            return [
                'id_cerrada'     => (int) $cerradaId,
                'nombre_cerrada' => $cerrada->group_name,
                'montogenerado'  => (float) $montogenerado,
                'validados'      => (int) $validados,
                'rechazados'     => (int) $rechazados,
                'pendientes'     => (int) $pendientes,
            ];
        })
        ->values(); // reindexar

    // 5) Respuesta
    return response()->json([
        'message' => 'Ganancias por cerrada obtenidas correctamente.',
        'data'    => $stats,
        'status'  => true,
    ], 200);
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

    /**
     * Obtener detalles de un usuario
     */
    public function obtenerDetallesUsuario(Request $request, $id): JsonResponse
    {
        // TODO: Implementar lógica para obtener detalles de usuario
        return response()->json([
            'message' => 'Detalles de usuario obtenidos exitosamente',
            'data' => ['id' => $id]
        ]);
    }

    /**
     * Listar configuraciones de pagos
     */
    public function listarConfiguracionesPagos(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para listar configuraciones de pagos
        return response()->json([
            'message' => 'Configuraciones de pagos obtenidas exitosamente',
            'data' => []
        ]);
    }

    /**
     * Crear configuración de pagos
     */
    public function crearConfiguracionPagos(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para crear configuración de pagos
        return response()->json([
            'message' => 'Configuración de pagos creada exitosamente',
            'data' => []
        ], 201);
    }

    /**
     * Obtener detalle de configuración de pagos
     */
    public function obtenerDetalleConfiguracion(Request $request, $id): JsonResponse
    {
        // TODO: Implementar lógica para obtener detalle de configuración
        return response()->json([
            'message' => 'Detalle de configuración obtenido exitosamente',
            'data' => ['id' => $id]
        ]);
    }

    /**
     * Actualizar configuración de pagos
     */
    public function actualizarConfiguracionPagos(Request $request, $id): JsonResponse
    {
        // TODO: Implementar lógica para actualizar configuración de pagos
        return response()->json([
            'message' => 'Configuración de pagos actualizada exitosamente',
            'data' => ['id' => $id]
        ]);
    }
}
