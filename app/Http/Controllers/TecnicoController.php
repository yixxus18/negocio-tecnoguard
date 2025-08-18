<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\CatalogoDispositivo;
use App\Models\Cerrada;
use App\Models\TipoServicio;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;
use Log;
class TecnicoController extends Controller
{
    /**
     * Mostrar todos los dispositivos con su técnico y cerrada relacionados.
     */
    public function index()
    {
        $catalogos = CatalogoDispositivo::with(['tecnico', 'cerrada'])->get();

        return response()->json([
            'success' => true,
            'data'    => $catalogos
        ], 200);
    }
//     public function crearconfiguracioninicialiot(Request $request, ?int $identificador = null)
// {
//     // Si viene un identificador en la ruta, valida que exista y regresa ese mismo id
//     if (!is_null($identificador)) {
//         $exists = CatalogoDispositivo::whereKey($identificador)->exists();
//         if (! $exists) {
//             return response()->json([
//                 'success' => false,
//                 'message' => 'El identificador no existe en catalogo_dispositivos.',
//             ], 404);
//         }

//         return response()->json([
//             'success' => true,
//             'message' => 'Identificador válido. No se creó un nuevo registro.',
//             'data'    => ['id' => $identificador],
//         ], 200);
//     }
//     try {
//         $catalogo = CatalogoDispositivo::create([
//             'cerrada_id'            => 1,
//             'tecnico_id'            => 32,
//             'archivo_configuracion' => 'config.json',
//             'bitacora_id'           => 1,
//         ]);

//         return response()->json([
//             'success' => true,
//             'message' => 'Registro inicial creado correctamente.',
//             'data'    => ['id' => $catalogo->id],
//         ], 201);
//     } catch (\Throwable $e) {
//         Log::info($e);
//         return response()->json([
//             'success' => false,
//             'message' => 'No se pudo crear el registro inicial.',
//         ], 500);
//     }
// }


public function crearconfiguracioninicialiot(Request $request, ?int $identificador = null)
{
    try {
        // Valores desde config/broadcast.php (que lee .env)
        $pusherKey = config('broadcast.ws.pusher_app_key');
        $wsHost    = config('broadcast.ws.host');
        $wsPort    = (int) config('broadcast.ws.port');
        $dominiows =config('broadcast.ws.dominiows');

        // Extras opcionales desde el request (con defaults)
        $channelName = (string) $request->input('chanel_name', 'puerta');     // (sic) chanel_name
        $eventName   = (string) $request->input('event_name', 'AbrirPuerta');

        // 1) Si viene identificador, devolver ese catálogo con relaciones
        if (!is_null($identificador)) {
            $catalogo = CatalogoDispositivo::with(['detalles', 'cerrada'])->find($identificador);

            if (!$catalogo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El identificador no existe en catalogo_dispositivos.',
                ], 404);
            }

            $payload = [
                'cerrada_id'            => $catalogo->cerrada_id,
                'tecnico_id'            => $catalogo->tecnico_id,
                'archivo_configuracion' => $catalogo->archivo_configuracion,
                'updated_at'            => optional($catalogo->updated_at)->toJSON(),
                'created_at'            => optional($catalogo->created_at)->toJSON(),

                // Websocket
                'pusher_app_key' => $pusherKey,
                'websocket_host' => $wsHost,
                'websocket_port' => $wsPort,
                'dominiows'=>$dominiows,

                // Extras
                'chanel_name' => $channelName,
                'ssid'        => (string) ($catalogo->ssid ?? ''),
                'password'    => (string) ($catalogo->password ?? ''),
                'event_name'  => $eventName,

                'id' => $catalogo->id,

                'detalles' => $catalogo->detalles->map(function ($d) {
                    return [
                        'id'                 => $d->id,
                        'created_at'         => optional($d->created_at)->toJSON(),
                        'updated_at'         => optional($d->updated_at)->toJSON(),
                        'uid'                => $d->uid,
                        'catalogo_id'        => $d->catalogo_id,
                        'pin'                => $d->getAttribute('pin'), // por si existe la columna
                        'nombre_dispositivo' => $d->nombre_dispositivo,
                    ];
                })->values()->all(),

                'cerrada' => $catalogo->cerrada ? [
                    'id'                     => $catalogo->cerrada->id,
                    'group_name'             => $catalogo->cerrada->group_name,
                    'description'            => $catalogo->cerrada->description,
                    'configuration_pay_date' => $catalogo->cerrada->configuration_pay_date,
                    'guard_id'               => $catalogo->cerrada->guard_id,
                    'jefe_cerrada_id'        => $catalogo->cerrada->jefe_cerrada_id,
                    'created_at'             => optional($catalogo->cerrada->created_at)->toJSON(),
                    'updated_at'             => optional($catalogo->cerrada->updated_at)->toJSON(),
                ] : null,
            ];

            return response()->json($payload, 200);
        }

        // 2) Sin identificador: crear registro en blanco (todos null)
        $catalogo = CatalogoDispositivo::create([
            'cerrada_id'            => null,
            'tecnico_id'            => null,
            'archivo_configuracion' => null,
            'bitacora_id'           => null,
            'ssid'                  => null,
            'password'              => null,
        ]);

        // Cargar relaciones para homogeneidad del payload
        $catalogo->load(['detalles', 'cerrada']);

        $payload = [
            'cerrada_id'            => $catalogo->cerrada_id,
            'tecnico_id'            => $catalogo->tecnico_id,
            'archivo_configuracion' => $catalogo->archivo_configuracion,
            'updated_at'            => optional($catalogo->updated_at)->toJSON(),
            'created_at'            => optional($catalogo->created_at)->toJSON(),

            // Websocket
            'pusher_app_key' => $pusherKey,
            'websocket_host' => $wsHost,
            'websocket_port' => $wsPort,
            'dominiows'=>$dominiows,

            // Extras
            'chanel_name' => $channelName,
            'ssid'        => (string) ($catalogo->ssid ?? ''),
            'password'    => (string) ($catalogo->password ?? ''),
            'event_name'  => $eventName,

            'id' => $catalogo->id,

            'detalles' => $catalogo->detalles->map(function ($d) {
                return [
                    'id'                 => $d->id,
                    'created_at'         => optional($d->created_at)->toJSON(),
                    'updated_at'         => optional($d->updated_at)->toJSON(),
                    'uid'                => $d->uid,
                    'catalogo_id'        => $d->catalogo_id,
                    'pin'                => $d->getAttribute('pin'),
                    'nombre_dispositivo' => $d->nombre_dispositivo,
                ];
            })->values()->all(),

            'cerrada' => $catalogo->cerrada ? [
                'id'                     => $catalogo->cerrada->id,
                'group_name'             => $catalogo->cerrada->group_name,
                'description'            => $catalogo->cerrada->description,
                'configuration_pay_date' => $catalogo->cerrada->configuration_pay_date,
                'guard_id'               => $catalogo->cerrada->guard_id,
                'jefe_cerrada_id'        => $catalogo->cerrada->jefe_cerrada_id,
                'created_at'             => optional($catalogo->cerrada->created_at)->toJSON(),
                'updated_at'             => optional($catalogo->cerrada->updated_at)->toJSON(),
            ] : null,
        ];

        return response()->json($payload, 201);

    } catch (\Throwable $e) {
        Log::error('crearconfiguracioninicialiot: '.$e->getMessage());
        return response()->json([
            'success' => false,
            'message' => 'No se pudo crear/obtener el registro.',
        ], 500);
    }
}

    /**
     * Agregar un nuevo dispositivo.
     */
    public function store(Request $request)
    {
        
        $validated = $request->validate([
            'Identificador'         => 'required|exists:cerradas,id',
            'tecnico_id'            => 'required|exists:users,id',
            'archivo_configuracion' => 'required|string|max:512',
            'detalles'              => 'required|array|min:1',
            'detalles.*.uid'        => 'required|string|max:64',
            'detalles.*.nombre_dispositivo' => 'required|string|max:255',
        ], [
            'Identificador.required'         => 'El identificador (cerrada) es obligatorio.',
            'Identificador.exists'           => 'La cerrada seleccionada no existe.',
            'tecnico_id.required'            => 'El técnico es obligatorio.',
            'tecnico_id.exists'              => 'El técnico seleccionado no existe.',
            'archivo_configuracion.required' => 'El archivo de configuración es obligatorio.',
            'archivo_configuracion.max'      => 'El archivo de configuración no puede exceder de :max caracteres.',
            'detalles.required'              => 'Debe especificar al menos un detalle de dispositivo.',
            'detalles.array'                 => 'Los detalles deben enviarse como un arreglo.',
            'detalles.min'                   => 'Debe contener al menos un detalle.',
            'detalles.*.uid.required'        => 'El UID es obligatorio en cada detalle.',
            'detalles.*.uid.max'             => 'El UID no puede exceder de :max caracteres.',
            'detalles.*.nombre_dispositivo.required' => 'El nombre del dispositivo es obligatorio en cada detalle.',
            'detalles.*.nombre_dispositivo.max'      => 'El nombre del dispositivo no puede exceder de :max caracteres.',
        ]);

        $catalogo = CatalogoDispositivo::create([
            'cerrada_id'            => $validated['Identificador'],
            'tecnico_id'            => $validated['tecnico_id'],
            'archivo_configuracion' => $validated['archivo_configuracion'],
        ]);

        foreach ($validated['detalles'] as $detalle) {
            $catalogo->detalles()->create([
                'uid'                => $detalle['uid'],
                'nombre_dispositivo' => $detalle['nombre_dispositivo'],
            ]);
        }

        $catalogo->load(['detalles', 'cerrada']);

        $groupSlug = Str::slug($catalogo->cerrada->group_name, '_');

        $files  = Storage::disk('s3')->files('firmware');
        $maxVer = 0;
        foreach ($files as $file) {
            if (preg_match("/^{$groupSlug}v(\d+)\.json$/", basename($file), $m)) {
                $maxVer = max($maxVer, (int)$m[1]);
            }
        }

        $version  = $maxVer + 1;
        $fileName = "{$groupSlug}v{$version}.json";

        $jsonContent = json_encode($catalogo->toArray(), JSON_PRETTY_PRINT);

        Storage::disk('s3')->put("firmware/{$fileName}", $jsonContent);

        $baseUrl = config('filesystems.disks.s3.url');
        $fileUrl = rtrim($baseUrl, '/') . '/firmware/' . $fileName;

        return response()->json([
            'success'      => true,
            'message'      => 'Catálogo y detalles creados, JSON subido correctamente.',
            'catalogo'     => $catalogo,
            'version'      => "v{$version}",
            'archivo_json' => $fileName,
            'url_archivo'  => $fileUrl,
        ], 201);
    }


    public function actualizarCatalogo(Request $request, int $catalogo_id)
{
    // Validación
    $validated = $request->validate([
        'bitacora_id'          => ['required', 'integer', 'exists:bitacora,id'],
        'tecnico_id'           => ['required', 'integer', 'exists:users,id'],
        'ssid'                 => ['required', 'string', 'max:512'],
        'password'             => ['required', 'string', 'max:512'],
        'archivo_configuracion'=>['required','string','max:64'],
        'detalles'             => ['required', 'array', 'min:1'],
        'detalles.*.uid'       => ['required', 'string', 'max:64'],
        'detalles.*.nombre_dispositivo' => ['required', 'string', 'max:255'],
        'detalles.*.pin'       => ['required', 'integer'],
        // Si llega detalles.*.catalogo_id lo ignoramos; no lo validamos ni usamos
    ], [
        'bitacora_id.required' => 'La bitácora es obligatoria.',
        'bitacora_id.exists'   => 'La bitácora indicada no existe.',
        'archivo_configuracion.required'=>'el nombre es requerido',
        'archivo_configuracion.string'=>'el nombre debe ser string',
        'archivo_configuracion.max'=>'Los caracteres maximos deben ser 64',
        'tecnico_id.exists'    => 'El técnico indicado no existe.',
        'detalles.required'    => 'Debes enviar al menos un detalle.',
        'detalles.array'       => 'Los detalles deben enviarse como arreglo.',
        'detalles.min'         => 'Debes enviar al menos un detalle.',
        'detalles.*.uid.required'  => 'Cada detalle debe incluir un UID.',
        'detalles.*.uid.max'       => 'El UID no puede exceder 64 caracteres.',
        'detalles.*.nombre_dispositivo.required' => 'Cada detalle debe incluir nombre_dispositivo.',
        'detalles.*.nombre_dispositivo.max'      => 'El nombre del dispositivo no puede exceder 255 caracteres.',
        'detalles.*.pin.integer'  => 'El pin debe ser un número entero.',
        'ssid.max'                => 'El SSID no puede exceder 512 caracteres.',
        'password.max'            => 'El password no puede exceder 512 caracteres.',
    ]);

    try {
        // Buscar catálogo
        $catalogo = CatalogoDispositivo::with('detalles')->find($catalogo_id);
        if (! $catalogo) {
            return response()->json([
                'success' => false,
                'message' => 'El catálogo indicado no existe.',
            ], 404);
        }

        // Obtener cerrada_id desde la bitácora proporcionada
        $bitacora = Bitacora::select('id', 'cerrada_id')->find($validated['bitacora_id']);
        if (! $bitacora) {
            // Por si pasó la validación pero no se encuentra (raro)
            return response()->json([
                'success' => false,
                'message' => 'La bitácora indicada no existe.',
            ], 404);
        }

        DB::transaction(function () use ($catalogo, $validated, $bitacora) {
            // Actualizar campos del catálogo
            $catalogo->update([
                'bitacora_id' => $validated['bitacora_id'],
                'cerrada_id'  => $bitacora->cerrada_id,                 // tomado de la bitácora
                'tecnico_id'  => $validated['tecnico_id'] ?? $catalogo->tecnico_id,
                'ssid'        => $validated['ssid']     ?? null,
                'password'    => $validated['password'] ?? null,
                'archivo_configuracion'=>$validated['archivo_configuracion']??null,
            ]);

            // Reemplazar detalles: borrar y crear de nuevo
            $catalogo->detalles()->delete();

            $detallesToCreate = collect($validated['detalles'])->map(function ($d) use ($catalogo) {
                return [
                    'uid'                => $d['uid'],
                    'catalogo_id'        => $catalogo->id, // ignoramos el que pudiera venir
                    'nombre_dispositivo' => $d['nombre_dispositivo'] ?? null,
                    'pin'                => $d['pin'] ?? null,
                ];
            })->all();

            if (!empty($detallesToCreate)) {
                $catalogo->detalles()->createMany($detallesToCreate);
            }
        });

        // Recargar con relaciones para la respuesta
        $catalogo->load(['detalles', 'cerrada', 'tecnico:id,name,email']);

        return response()->json([
            'success' => true,
            'message' => 'Catálogo actualizado correctamente.',
            'data'    => $catalogo,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('actualizarCatalogo: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'No fue posible actualizar el catálogo.',
        ], 500);
    }
}



   public function downloadConfig(Request $request)
    {
        // 1) Validar que recibimos una URL válida
        $request->validate([
            'url' => 'required|url',
        ], [
            'url.required' => 'La URL del archivo es obligatoria.',
            'url.url'      => 'La URL proporcionada no es válida.',
        ]);

        $url     = $request->input('url');
        $baseUrl = rtrim(config('filesystems.disks.s3.url'), '/') . '/';

        // 2) Verificar que la URL pertenezca al bucket
        if (!Str::startsWith($url, $baseUrl)) {
            return response()->json([
                'success' => false,
                'message' => 'La URL no pertenece al bucket configurado.'
            ], 400);
        }

        // 3) Hacer la petición HTTP para obtener el JSON
        $response = Http::get($url);

        if (! $response->ok()) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo descargar el archivo desde el bucket.'
            ], 404);
        }

        // 4) Devolver el cuerpo como descarga
        return response($response->body(), 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="config.json"',
        ]);
    }

    
    /**
     * Actualizar un dispositivo existente.
     */
    public function update(Request $request, $id)
    {
        $catalogo = CatalogoDispositivo::findOrFail($id);

        $validated = $request->validate([
            'cerrada_id'             => 'required|exists:cerradas,id',
            'tecnico_id'             => 'required|exists:users,id',
            'uid'                    => 'required|string|max:64',
            'archivo_configuracion'  => 'required|string|max:512',
            'nombre_dispositivo'     => 'required|string|max:255',
        ], [
            'cerrada_id.required'            => 'La cerrada es obligatoria.',
            'cerrada_id.exists'              => 'La cerrada seleccionada no existe.',
            'tecnico_id.required'            => 'El técnico es obligatorio.',
            'tecnico_id.exists'              => 'El técnico seleccionado no existe.',
            'uid.required'                   => 'El UID es obligatorio.',
            'uid.string'                     => 'El UID debe ser un texto.',
            'uid.max'                        => 'El UID no puede exceder de :max caracteres.',
            'archivo_configuracion.required' => 'El archivo de configuración es obligatorio.',
            'archivo_configuracion.string'   => 'El archivo de configuración debe ser un texto.',
            'archivo_configuracion.max'      => 'El archivo de configuración no puede exceder de :max caracteres.',
            'nombre_dispositivo.required'    => 'El nombre del dispositivo es obligatorio.',
            'nombre_dispositivo.string'      => 'El nombre del dispositivo debe ser un texto.',
            'nombre_dispositivo.max'         => 'El nombre del dispositivo no puede exceder de :max caracteres.',
        ]);

        $catalogo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo actualizado correctamente.',
            'data'    => $catalogo->load(['tecnico', 'cerrada'])
        ], 200);
    }

    /**
     * Eliminar (soft delete) un dispositivo.
     */
    public function destroy($id)
    {
        $catalogo = CatalogoDispositivo::findOrFail($id);
        $catalogo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Dispositivo eliminado correctamente.'
        ], 200);
    }
public function crearActividadcontecnico(Request $request)
{
    $validated = $request->validate([
        'tecnico_id'        => 'required|integer',
        'cerrada_id'        => 'required|integer',
        'tiposervicio_id'   => 'required|integer',
        'fecha_programada'  => 'required|date',
        'fecha_asignacion'  => 'required|date',
        'status'            => 'nullable|string|in:,Asignado',
        'Prioridad'         => 'nullable|string|in:Urgente,Importante,Sin prioridad',
        'descripcion'       => 'nullable|string|max:500',
    ], [
        'tecnico_id.required'       => 'El técnico es obligatorio.',
        'tecnico_id.integer'        => 'El técnico debe ser un número.',
        'cerrada_id.required'       => 'La cerrada es obligatoria.',
        'cerrada_id.integer'        => 'La cerrada debe ser un número.',
        'tiposervicio_id.required'  => 'El tipo de servicio es obligatorio.',
        'tiposervicio_id.integer'   => 'El tipo de servicio debe ser un número.',
        'fecha_asignacion.date'     => 'La fecha de asignacion debe ser una fecha válida.',
        'fecha_programada.date'     => 'La fecha programada debe ser una fecha válida.',
        'status.in'                 => 'El estado debe ser: Asignado, En Proceso, Concluido o No concluido.',
        'Prioridad.in'              => 'La prioridad debe ser: Urgente, Importante o Sin prioridad.',
        'descripcion.string'        => 'La descripción debe ser un texto.',
        'descripcion.max'           => 'La descripción no puede exceder de :max caracteres.',
    ]);

    // Checar si ya existe una actividad para esa cerrada que NO esté Concluida
    $yaExiste = Bitacora::where('cerrada_id', $validated['cerrada_id'])
        ->where(function ($q) {
            $q->whereNull('status')      // por si hay registros sin status
              ->orWhere('status', '!=', 'Concluido');
        })
        ->exists();

    if ($yaExiste) {
        return response()->json([
            'success' => false,
            'message' => 'ya existe una actividad para esta cerrada',
            'data'    => null,
        ], 500);
    }

    $actividad = Bitacora::create([
        'tecnico_id'        => $validated['tecnico_id'],
        'cerrada_id'        => $validated['cerrada_id'],
        'tiposervicio_id'   => $validated['tiposervicio_id'],
        'fecha_asignacion'  => $validated['fecha_asignacion'],
        'fecha_programada'  => $validated['fecha_programada'],
        'status'            => $validated['status']      ?? 'Asignado',
        'prioridad'         => $validated['Prioridad']   ?? 'Sin prioridad',
        'descripcion'       => $validated['descripcion'] ?? null,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Actividad creada correctamente.',
        'data'    => $actividad,
    ], 201);
}


public function catalogosDelTecnico(Request $request)
{
    try {
        $user = $request->user();

        // 1) Bitácoras del técnico autenticado (con cerrada y servicio)
        $bitacoras = Bitacora::with(['cerrada', 'servicio'])
            ->where('tecnico_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // 2) Catálogos vinculados a esas bitácoras (con detalles, cerrada y técnico->name)
        $catalogosPorBitacora = collect();
        if ($bitacoras->isNotEmpty()) {
            $bitacoraIds = $bitacoras->pluck('id');

            $catalogosVinculados = CatalogoDispositivo::with([
                    'detalles',
                    'cerrada',
                    'tecnico:id,name' // para traer el nombre del técnico
                ])
                ->whereIn('bitacora_id', $bitacoraIds)
                ->orderByDesc('created_at')
                ->get();

            $catalogosPorBitacora = $catalogosVinculados->groupBy('bitacora_id');
        }

        // 3) Inyectar la relación "catalogo_dispositivos" a cada bitácora
        $bitacoras->transform(function ($b) use ($catalogosPorBitacora) {
            $b->setRelation('catalogo_dispositivos', $catalogosPorBitacora->get($b->id, collect()));
            return $b;
        });

        // 4) Catálogos donde el técnico sea el actual (míos) con nombre del técnico
        $catalogoDispositivosMios = CatalogoDispositivo::with([
                'detalles',
                'cerrada',
                'tecnico:id,name'
            ])
            ->where('tecnico_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        // 5) Catálogos libres (tecnico_id null) con nombre del técnico (será null)
        $catalogoDispositivosLibres = CatalogoDispositivo::with([
                'detalles',
                'cerrada',
                'tecnico:id,name'
            ])
            ->whereNull('tecnico_id')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Catálogos del técnico obtenidos correctamente.',
            'data'    => [
                'bitacoras'                    => $bitacoras,
                'catalogo_dispositivos_mios'   => $catalogoDispositivosMios,
                'catalogo_dispositivos_libres' => $catalogoDispositivosLibres,
            ],
        ], 200);

    } catch (\Throwable $e) {
        Log::error('catalogosDelTecnico: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'No fue posible obtener los catálogos del técnico.',
        ], 500);
    }
}


public function newactivityfromtecnichian()
{
    $cerradas  = Cerrada::all();
    $servicios = TipoServicio::all();

    return response()->json([
        'success' => true,
        'message' => 'Registros cargados correctamente.',
        'data'    => [
            'cerradas'  => $cerradas,
            'servicios' => $servicios,
        ],
    ], 200);
}
public function DashboardTecnico(Request $request)
{
    try {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'data'    => null,
            ], 401);
        }

        // Zona horaria base para el dashboard
        $tz       = 'America/Monterrey';
        $ahoraTz  = now($tz);
        // Últimos 7 días hacia atrás, incluyendo hoy
        $inicioTz = $ahoraTz->copy()->subDays(7)->startOfDay(); // hace 7 días a las 00:00 (Monterrey)
        $finTz    = $ahoraTz->copy()->endOfDay();               // hoy a las 23:59:59 (Monterrey)

        // Convertir a UTC para consultar en BD (común si las columnas se guardan en UTC)
        $inicioUtc = $inicioTz->copy()->timezone('UTC');
        $finUtc    = $finTz->copy()->timezone('UTC');

        // Traer TODAS las bitácoras del técnico en el rango, con cerrada y servicio
        $bitacoras = Bitacora::with([
                'cerrada:id,group_name,description',
                'servicio:id,nombreServicio',
            ])
            ->where('tecnico_id', $user->id)
            ->whereBetween('created_at', [$inicioUtc, $finUtc])
            ->orderByDesc('created_at')
            ->get();

        // Desglose por estatus (exact match)
        $desglose = [
            'Asignado'     => $bitacoras->where('status', 'Asignado')->count(),
            'En Proceso'   => $bitacoras->where('status', 'En Proceso')->count(),
            'Concluido'    => $bitacoras->where('status', 'Concluido')->count(),
            'No concluido' => $bitacoras->where('status', 'No concluido')->count(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Dashboard del técnico.',
            'data'    => [
                'timezone' => $tz,
                'rango'    => [
                    'inicio_local' => $inicioTz->toDateTimeString(),
                    'fin_local'    => $finTz->toDateTimeString(),
                    'inicio_utc'   => $inicioUtc->toDateTimeString(),
                    'fin_utc'      => $finUtc->toDateTimeString(),
                ],
                'total'     => $bitacoras->count(),
                'desglose'  => $desglose,
                'bitacoras' => $bitacoras, // objeto completo con cerrada y servicio
            ],
        ], 200);

    } catch (\Throwable $e) {
        Log::error('DashboardTecnico: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'No fue posible obtener el dashboard del técnico.',
        ], 500);
    }
}



public function ConcluirActividad(Request $request, int $bitacora_id)
{
    try {
        $bitacora = Bitacora::find($bitacora_id);

        if (! $bitacora) {
            return response()->json([
                'success' => false,
                'message' => 'La bitácora indicada no existe.',
            ], 404);
        }
        if($bitacora->status == 'No concluido' ||$bitacora->status == 'Concluido'  )
        {
            return response()->json([
                'success' => false,
                'message' => 'La bitácora indicada ya se ha cerrado.',
            ], 404);
        }

        $bitacora->update([
            'status' => 'Concluido',
        ]);

        // Si quieres devolver con relaciones:
        $bitacora->load(['cerrada', 'servicio']);

        return response()->json([
            'success' => true,
            'message' => 'La actividad fue marcada como Concluido.',
            'data'    => $bitacora,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('ConcluirActividad: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'No fue posible concluir la actividad.',
        ], 500);
    }
}

public function NoConcluidoActividad(Request $request, int $bitacora_id)
{
   
    $validated = $request->validate([
        'comentario' => ['required', 'string', 'max:256'],
    ], [
        'comentario.required' => 'El comentario es obligatorio.',
        'comentario.string'   => 'El comentario debe ser texto.',
        'comentario.max'      => 'El comentario no puede exceder de 256 caracteres.',
    ]);

    try {
        $bitacora = Bitacora::find($bitacora_id);

        if (! $bitacora) {
            return response()->json([
                'success' => false,
                'message' => 'La bitácora indicada no existe.',
            ], 404);
        }
        

        $bitacora->update([
            'status'     => 'No concluido',
            'comentario' => $validated['comentario'],
        ]);

        // Si quieres devolver con relaciones:
        $bitacora->load(['cerrada', 'servicio']);

        return response()->json([
            'success' => true,
            'message' => 'La actividad fue marcada como No concluido y se actualizó el comentario.',
            'data'    => $bitacora,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('NoConcluidoActividad: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json([
            'success' => false,
            'message' => 'No fue posible marcar la actividad como No concluido.',
        ], 500);
    }
}


}
