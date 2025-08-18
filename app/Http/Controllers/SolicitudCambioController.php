<?php

namespace App\Http\Controllers;

use App\Models\Cerrada;
use App\Models\FamilyGroup;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\SolicitudCambioCerrada;
use Illuminate\Validation\Rule;

class SolicitudCambioController extends Controller
{
    public function misSolicitudes(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado.',
            'data'    => null,
        ], 401);
    }

    $solicitudes = SolicitudCambioCerrada::with([
            'cerradaProveniente:id,group_name,description',
            'cerradaDestino:id,group_name,description',
            'solicitante:id,name,email',
        ])
        ->where('user_solicitud', $user->id)
        ->orderByDesc('created_at')
        ->get();

    return response()->json([
        'success' => true,
        'message' => $solicitudes->isEmpty()
            ? 'No hay solicitudes para este usuario.'
            : 'Solicitudes obtenidas correctamente.',
        'data' => [
            'count'       => $solicitudes->count(),
            'solicitudes' => $solicitudes,
        ],
    ], 200);
}
public function crearSolicitud(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado.',
            'data'    => null,
        ], 401);
    }

    $yaPendiente = SolicitudCambioCerrada::where('user_solicitud', $user->id)
        ->where('estado', 'Pendiente')
        ->exists();

    if ($yaPendiente) {
        return response()->json([
            'success' => false,
            'message' => 'Ya tienes una solicitud pendiente.',
            'data'    => null,
        ], 409); 
    }

    // 2) Tomar cerrada de origen desde la familia del usuario
    $familyId = $user->family_id ?? null;
    if (! $familyId) {
        return response()->json([
            'success' => false,
            'message' => 'No tienes una familia asociada (family_id faltante).',
            'data'    => null,
        ], 422);
    }

    $familia = FamilyGroup::select('id', 'cerrada_id')->find($familyId);
    if (! $familia) {
        return response()->json([
            'success' => false,
            'message' => 'La familia indicada no existe.',
            'data'    => null,
        ], 404);
    }

    if (! $familia->cerrada_id) {
        return response()->json([
            'success' => false,
            'message' => 'Tu familia no tiene una cerrada asociada actualmente.',
            'data'    => null,
        ], 422);
    }

    $cerradaActualId = (int) $familia->cerrada_id;

    // 3) Validación de entrada (destino distinto a la cerrada actual)
    $validated = $request->validate([
        'direccion'         => ['required', 'string', 'max:512'],
        'cerradadestino_id' => [
            'required',
            'integer',
            'exists:cerradas,id',
            Rule::notIn([$cerradaActualId]),
        ],
    ], [
        'direccion.required'        => 'La dirección es obligatoria.',
        'direccion.string'          => 'La dirección debe ser texto.',
        'direccion.max'             => 'La dirección no puede exceder 512 caracteres.',
        'cerradadestino_id.required'=> 'La cerrada de destino es obligatoria.',
        'cerradadestino_id.integer' => 'La cerrada de destino debe ser un número.',
        'cerradadestino_id.exists'  => 'La cerrada de destino no existe.',
        'cerradadestino_id.not_in'  => 'La cerrada de destino debe ser distinta a la cerrada actual.',
    ]);

    // 4) Crear la solicitud
    $solicitud = SolicitudCambioCerrada::create([
        'direccion'             => $validated['direccion'],
        'cerradaproveniente_id' => $cerradaActualId,
        'cerradadestino_id'     => (int) $validated['cerradadestino_id'],
        'user_solicitud'        => $user->id,
        'proveniente'           => false,
        'destino'               => false,
        'estado'                => 'Pendiente',
    ]);

    // 5) Cargar relaciones para la respuesta
    $solicitud->load([
        'cerradaProveniente:id,group_name,description',
        'cerradaDestino:id,group_name,description',
        'solicitante:id,name,email',
    ]);

    return response()->json([
        'success' => true,
        'message' => 'Solicitud creada correctamente.',
        'data'    => $solicitud,
    ], 201);
}

public function cerradasExceptoMiFamilia(Request $request)
{
    $user = $request->user();
    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado.',
            'data'    => null,
        ], 401);
    }

    $miCerradaId = null;
    if (!empty($user->family_id)) {
        $familia = FamilyGroup::find($user->family_id, ['id', 'cerrada_id']);
        $miCerradaId = $familia?->cerrada_id;
    }

    $query = Cerrada::select('id', 'group_name')->orderBy('group_name');
    if (!empty($miCerradaId)) {
        $query->where('id', '!=', $miCerradaId);
    }

    $cerradas = $query->get();

    return response()->json([
        'success' => true,
        'message' => 'Cerradas obtenidas correctamente (excluyendo la de tu familia).',
        'data'    => [
            'mi_cerrada_id' => $miCerradaId,
            'count'         => $cerradas->count(),
            'cerradas'      => $cerradas,
        ],
    ], 200);
}

public function solicitudesmicerradaprovenienteodestino(Request $request)
{
    $user = $request->user();

    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado.',
            'data'    => null,
        ], 401);
    }

    // Cerradas que administro
    $misCerradasIds = Cerrada::where('jefe_cerrada_id', $user->id)->pluck('id');

    if ($misCerradasIds->isEmpty()) {
        return response()->json([
            'success' => true,
            'message' => 'No administras ninguna cerrada.',
            'data'    => [
                'mis_cerradas_ids'        => [],
                'total'                   => 0,
                'count_proveniente'       => 0,
                'count_destino'           => 0,
                'proveniente_flag'        => false,
                'destino_flag'            => false,
                'solicitudes_proveniente' => [],
                'solicitudes_destino'     => [],
            ],
        ], 200);
    }

    // Donde mis cerradas son ORIGEN (proveniente)
    $solProveniente = SolicitudCambioCerrada::with([
            'cerradaProveniente:id,group_name,description',
            'cerradaDestino:id,group_name,description',
            'solicitante:id,name,email',
        ])
        ->whereIn('cerradaproveniente_id', $misCerradasIds)
        ->orderByDesc('created_at')
        ->get();

    $solProveniente->transform(function ($s) {
        $s->setAttribute('es_proveniente', true);
        return $s;
    });

    // Donde mis cerradas son DESTINO
    $solDestino = SolicitudCambioCerrada::with([
            'cerradaProveniente:id,group_name,description',
            'cerradaDestino:id,group_name,description',
            'solicitante:id,name,email',
        ])
        ->whereIn('cerradadestino_id', $misCerradasIds)
        ->orderByDesc('created_at')
        ->get();

    $solDestino->transform(function ($s) {
        $s->setAttribute('es_proveniente', false);
        return $s;
    });

    $countProveniente = $solProveniente->count();
    $countDestino     = $solDestino->count();
    $total            = $countProveniente + $countDestino;

    // ✅ Flags independientes: si hay en ambos lados, ambos true
    $provenienteFlag = $countProveniente > 0;
    $destinoFlag     = $countDestino > 0;

    return response()->json([
        'success' => true,
        'message' => $total === 0
            ? 'No hay solicitudes donde tus cerradas sean origen ni destino.'
            : 'Solicitudes de tus cerradas (proveniente/destino) obtenidas correctamente.',
        'data'    => [
            'mis_cerradas_ids'        => $misCerradasIds->values(),
            'total'                   => $total,
            'proveniente_flag'        => $provenienteFlag,
            'destino_flag'            => $destinoFlag,
            'count_proveniente'       => $countProveniente,
            'count_destino'           => $countDestino,
            'solicitudes_proveniente' => $solProveniente,
            'solicitudes_destino'     => $solDestino,
        ],
    ], 200);
}



  public function resolverSolicitud(Request $request, int $solicitud_id)
    {
        $user = $request->user();
        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'No autenticado.',
                'data'    => null,
            ], 401);
        }

        $validated = $request->validate([
            'aprobado'    => ['required', 'boolean'],
            'perspectiva' => ['required', 'boolean'], // true = proveniente, false = destino
            'comentario'  => ['nullable', 'string', 'max:256', 'required_if:aprobado,true'],
        ], [
            'aprobado.required'      => 'El campo aprobado es obligatorio.',
            'aprobado.boolean'       => 'El campo aprobado debe ser verdadero o falso.',
            'perspectiva.required'   => 'El campo perspectiva es obligatorio.',
            'perspectiva.boolean'    => 'El campo perspectiva debe ser verdadero o falso.',
            'comentario.required_if' => 'El comentario es obligatorio cuando se aprueba.',
            'comentario.max'         => 'El comentario no puede exceder 256 caracteres.',
        ]);

        $solicitud = SolicitudCambioCerrada::with([
            'cerradaProveniente:id,group_name,description',
            'cerradaDestino:id,group_name,description',
            'solicitante:id,name,email',
        ])->find($solicitud_id);

        if (! $solicitud) {
            return response()->json([
                'success' => false,
                'message' => 'La solicitud indicada no existe.',
                'data'    => null,
            ], 404);
        }

        $aprobado      = (bool) $validated['aprobado'];
        $esProveniente = (bool) $validated['perspectiva'];

        // Comentario a guardar:
        // - Si aprueba: texto fijo por defecto.
        // - Si rechaza: el comentario recibido (puede ser nulo).
        $comentario = $aprobado
            ? 'Solicitud Aprobada sin inconvenientes'
            : (isset($validated['comentario']) ? trim($validated['comentario']) : null);

        if ($esProveniente) {
            $solicitud->proveniente = $aprobado ? 1 : 0;
            $solicitud->comentarioproveniente = $comentario;
        } else {
            $solicitud->destino = $aprobado ? 1 : 0;
            $solicitud->comentariodestino = $comentario;
        }

        // Determinar estado:
        // - Ambos true -> Aprobado
        // - Alguno false con comentario no nulo -> Rechazado
        // - Caso contrario: se mantiene (ej. Pendiente)
        $p  = (bool) $solicitud->proveniente;
        $d  = (bool) $solicitud->destino;
        $cp = $solicitud->comentarioproveniente;
        $cd = $solicitud->comentariodestino;

        if ($p && $d) {
            $solicitud->estado = 'Aprobado';
        } elseif ((($p === false) && !is_null($cp)) || (($d === false) && !is_null($cd))) {
            $solicitud->estado = 'Rechazado';
        }

        $solicitud->save();

        // ✅ Si quedó Aprobado, mover a la familia del solicitante a la cerrada destino
        if ($solicitud->estado === 'Aprobado') {
            $solicitante = User::find($solicitud->user_solicitud, ['id', 'family_id']);
            if ($solicitante && $solicitante->family_id) {
                $familia = FamilyGroup::find($solicitante->family_id);
                if ($familia) {
                    $familia->cerrada_id = (int) $solicitud->cerradadestino_id;
                    $familia->save();
                }
            }
        }

        $solicitud->refresh()->load([
            'cerradaProveniente:id,group_name,description',
            'cerradaDestino:id,group_name,description',
            'solicitante:id,name,email',
        ]);

        return response()->json([
            'success' => true,
            'message' => $aprobado
                ? 'Resolución registrada: aprobado.'
                : 'Resolución registrada: rechazado.',
            'data'    => $solicitud,
        ], 200);
    }
}
