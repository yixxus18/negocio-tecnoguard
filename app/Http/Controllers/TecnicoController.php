<?php

namespace App\Http\Controllers;

use App\Models\Bitacora;
use App\Models\CatalogoDispositivo;
use App\Models\Cerrada;
use App\Models\TipoServicio;
use DB;
use Illuminate\Http\Request;
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
        // Valores de websocket desde config/broadcast.php (que a su vez lee .env)
        $pusherKey = config('broadcast.ws.pusher_app_key');
        $wsHost    = config('broadcast.ws.host');
        $wsPort    = (int) config('broadcast.ws.port');

        // Nombre de canal y evento (pueden venir del request; defaults si no)
        $channelName = (string) $request->input('chanel_name', 'Puerta');
        $eventName   = (string) $request->input('event_name', 'AbrirPuerta');

        // 1) Si viene identificador en la ruta: buscar el catálogo y responderlo
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

                // Extras de config
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
                        'pin'                => $d->pin ?? null, // si existe columna pin
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

        // 2) Sin identificador en ruta: crear registro (asumiendo columnas NULLables)
        //    Si en tu DB aún son NOT NULL, debes ajustar migraciones o validar aquí.
        $catalogo = CatalogoDispositivo::create([
            'cerrada_id'            => $request->input('cerrada_id'),                 // o null
            'tecnico_id'            => $request->input('tecnico_id'),                 // o null
            'archivo_configuracion' => $request->input('archivo_configuracion', 'config_v1.0.0'),
            'bitacora_id'           => $request->input('bitacora_id'),                // o null
            'ssid'                  => $request->input('ssid'),                       // o null
            'password'              => $request->input('password'),                   // o null
        ]);

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

            // Extras de config
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
                    'pin'                => $d->pin ?? null, // si existe columna pin
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
}
