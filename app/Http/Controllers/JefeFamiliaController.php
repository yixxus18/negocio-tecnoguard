<?php

namespace App\Http\Controllers;

use App\Http\Requests\Guardia\CrearTokenReq;
use App\Http\Requests\JefeCerrada\CrearPagoReq;
use App\Http\Requests\JefeFamilia\AddMiembroReq;
use App\Models\FamilyGroup;
use App\Models\Membership;
use App\Models\MembershipDetail;
use App\Models\SolicitudCambioCerrada;
use App\Models\TokenAcceso;
use App\Models\User;
use App\Services\FileService;
use Carbon\Carbon;


use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Log;
use Str;

class JefeFamiliaController extends Controller
{
    

    /**
     * Agregar miembro de familia
     */
    public function agregarMiembroFamilia(AddMiembroReq $request): JsonResponse
    {
        $data = $request->validated();
        $jefe_familia = $request->user();
        if($jefe_familia->family_id == null){
            return response()->json([
                'message' => 'No cuenta con una familia asignada.',
                'status' => false
            ], 400);
        }
        $family_members_count = User::where('family_id', $jefe_familia->family_id)->count();

        if ($family_members_count >= 4) {
            return response()->json([
                'message' => 'Ha alcanzado el límite de 3 miembros por familia.',
                'status' => false
            ], 422);
        }
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
        $miembros = User::whereNotNull('family_id')->where('family_id', $jefe_familia->family_id)
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
   public function obtenerHistorialMembresia(Request $request)
{
    
    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'No autenticado.',
            'status'  => false,
            'data'    => null,
        ], 401);
    }

    if (!$user->family_id) {
        return response()->json([
            'message' => 'No perteneces a ninguna familia.',
            'status'  => false,
            'data'    => null,
        ], 404);
    }

    $family = FamilyGroup::find($user->family_id);
    if (!$family) {
        return response()->json([
            'message' => 'La familia no existe.',
            'status'  => false,
            'data'    => null,
        ], 404);
    }

    if (!$family->membership_id) {
        return response()->json([
            'message' => 'La familia no tiene una membresía asociada.',
            'status'  => false,
            'data'    => null,
        ], 404);
    }

    $membership = Membership::find($family->membership_id);
    if (!$membership) {
        return response()->json([
            'message' => 'La membresía no existe.',
            'status'  => false,
            'data'    => null,
        ], 404);
    }

    
    $details = MembershipDetail::where('membership_id', $membership->id)
        ->orderByDesc('date_pay')
        ->get();

    return response()->json([
        'message' => 'Historial de membresía obtenido exitosamente',
        'status'  => true,
        'data'    => $details,  // arreglo (puede venir vacío si no hay pagos)
    ], 200);
}


    // public function procesarPagoFamilia(CrearPagoReq $request): JsonResponse
    // {
    //     $data = $request->validated();
    //     $jefe_cerrada = $request->user();
    //     $membership = Membership::find($data['membership_id'])->load('familyGroups');
        
    //     if (!$membership->familyGroups) {
    //         return response()->json([
    //             'message' => 'Error: TG-RES-004, La familia no existe o no pertenece a su cerrada',
    //             'status' => false
    //         ]);
    //     }
    //     $ticket = FileService::uploadFile($data['ticket']);
    //     if ($ticket['success'] != true) {
    //         return response()->json([
    //             'message' => 'Error: TG-SRV-001, Error al querer subir la imagen del ticket',
    //             'status' => false,
    //             'error' => $ticket['error']
    //         ]);
    //     }

    //     $membership_detail = MembershipDetail::create([
    //         'membership_id' => $data['membership_id'],
    //         'amount' => $data['amount'],
    //         'estatus' => $data['status'],
    //         'date_pay' => $data['date_pay'],
    //         'ticket' => $ticket['file_name'],
    //         'date_finalization' => Carbon::parse($data['date_pay'])->addMonth()->format('Y-m-d'),
    //     ]);

    //     $solicitud = SolicitudCambioCerrada::create($validated);

    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Solicitud de cambio creada correctamente.',
    //         'data'    => $solicitud->load(['cerradaProveniente', 'cerradaDestino', 'solicitante']),
    //     ], 201);
    // }

    /**
     * Actualizar una solicitud existente.
     */
    public function editarSolicitudCambio(Request $request, int $id): JsonResponse
    {
        $solicitud = SolicitudCambioCerrada::findOrFail($id);

        $validated = $request->validate([
            'direccion'                 => 'sometimes|string|max:512',
            'cerradaproveniente_id'     => 'sometimes|exists:cerradas,id',
            'cerradadestino_id'         => 'sometimes|exists:cerradas,id',
            'proveniente'               => 'sometimes|boolean',
            'destino'                   => 'sometimes|boolean',
            'user_solicitud'            => 'sometimes|exists:users,id',
            'estado'                    => 'sometimes|in:Pendiente,Aprobado,Rechazado',
            'comentariodestino'         => 'nullable|string',
            'comentarioproveniente'     => 'nullable|string|max:255',
        ], [
            'direccion.max'                   => 'La dirección no puede exceder de :max caracteres.',
            'cerradaproveniente_id.exists'    => 'La cerrada de origen no existe.',
            'cerradadestino_id.exists'        => 'La cerrada de destino no existe.',
            'user_solicitud.exists'           => 'El usuario solicitante no existe.',
            'estado.in'                       => 'El estado debe ser Pendiente, Aprobado o Rechazado.',
            'comentarioproveniente.max'       => 'El comentario del proveniente no puede exceder de :max caracteres.',
        ]);

        $solicitud->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de cambio actualizada correctamente.',
            'data'    => $solicitud->load(['cerradaProveniente', 'cerradaDestino', 'solicitante']),
        ], 200);
    }

    /**
     * Eliminar una solicitud (hard delete).
     */
    public function eliminarSolicitudCambio(int $id): JsonResponse
    {
        $solicitud = SolicitudCambioCerrada::findOrFail($id);
        $solicitud->delete();

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de cambio eliminada correctamente.',
        ], 200);
    }
}
