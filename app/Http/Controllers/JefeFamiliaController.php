<?php

namespace App\Http\Controllers;

use App\Http\Requests\Guardia\CrearTokenReq;
use App\Http\Requests\JefeCerrada\CrearPagoReq;
use App\Http\Requests\JefeFamilia\AddMiembroReq;
use App\Models\FamilyGroup;
use App\Models\Membership;
use App\Models\MembershipDetail;
use Illuminate\Support\Facades\Hash;
use App\Models\SolicitudCambioCerrada;
use App\Models\TokenAcceso;
use App\Models\User;
use App\Services\FileService;
use Carbon\Carbon;


use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Log;
use Str;

class JefeFamiliaController extends Controller
{
    

    public function TokensFamiliares(Request $request)
    {
       $user = $request->user();

    if (! $user) {
        return response()->json([
            'success' => false,
            'message' => 'No autenticado.',
            'data'    => null,
        ], 401);
    }
    $tokens= TokenAcceso::where('usuario_id',$user->id)->get()->with('usuario');
    return response()->json([
    'message'=>'Tokens Obtenidos correctamente',
    'status' => true,
    'data'=>$tokens
    ],200);
    }

    /**
     * Agregar miembro de familia
     */
    public function agregarMiembroFamiliausuarioyaexistente(AddMiembroReq $request): JsonResponse
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


    public function agregarMiembroFamilia(Request $request): JsonResponse
{
    $jefe_familia = $request->user();

    if ($jefe_familia->family_id === null) {
        return response()->json([
            'message' => 'No cuenta con una familia asignada.',
            'status'  => false,
        ], 400);
    }

    $validated = $request->validate(
        [
            'name'     => ['required', 'string', 'max:128'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'phone'    => ['required', 'string', 'regex:/^\d{10}$/', Rule::unique('users', 'phone')],
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[#$%&@!*?\._\-]).{8,}$/'
            ],
        ],
        [
            'name.required'      => 'El nombre es obligatorio.',
            'name.string'        => 'El nombre debe ser texto.',
            'name.max'           => 'El nombre no puede exceder 128 caracteres.',

            'email.required'     => 'El correo electrónico es obligatorio.',
            'email.email'        => 'El correo electrónico no es válido.',
            'email.max'          => 'El correo electrónico no puede exceder 255 caracteres.',
            'email.unique'       => 'Este correo electrónico ya está registrado.',

            'phone.required'     => 'El teléfono es obligatorio.',
            'phone.string'       => 'El teléfono debe ser texto.',
            'phone.regex'        => 'El teléfono debe contener exactamente 10 dígitos.',
            'phone.unique'       => 'Este teléfono ya está registrado.',

            'password.required'  => 'La contraseña es obligatoria.',
            'password.string'    => 'La contraseña debe ser texto.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex'     => 'La contraseña debe incluir al menos 1 mayúscula, 1 número y 1 símbolo (#, $, %, &, @, !, *, ?, ., _, -).',
        ]
    );

    $family_members_count = User::where('family_id', $jefe_familia->family_id)->count();
    if ($family_members_count >= 4) {
        return response()->json([
            'message' => 'Ha alcanzado el límite de 3 miembros por familia.',
            'status'  => false,
        ], 422);
    }

    $miembro = User::create([
        'name'       => $validated['name'],
        'email'      => $validated['email'],
        'phone'      => $validated['phone'],
        'password'   => Hash::make($validated['password']),
        'family_id'  => $jefe_familia->family_id,
        'is_active'  => true,
         'role_id'    => 5,
    ]);

    return response()->json([
        'message' => 'Miembro de familia agregado exitosamente',
        'data'    => $miembro,
        'status'  => true,
    ], 201);
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


    public function activarMiembroFamilia(Request $request, $member_id): JsonResponse
{
    $jefe_familia = $request->user();

    if (! $jefe_familia) {
        return response()->json([
            'message' => 'No autenticado.',
            'status'  => false,
            'data'    => null,
        ], 401);
    }

    if (empty($jefe_familia->family_id)) {
        return response()->json([
            'message' => 'No tienes una familia asociada para activar miembros.',
            'status'  => false,
            'data'    => null,
        ], 422);
    }

    $miembro = User::find($member_id);
    if (! $miembro) {
        return response()->json([
            'message' => 'Miembro no encontrado.',
            'status'  => false,
            'data'    => null,
        ], 404);
    }

    // Si pertenece a otra familia distinta, no permitir reasignación silenciosa
    if (!is_null($miembro->family_id) && $miembro->family_id != $jefe_familia->family_id) {
        return response()->json([
            'message' => 'El miembro pertenece a otra familia.',
            'status'  => false,
            'data'    => $miembro,
        ], 422);
    }

    $miembro->update([
        'family_id' => $jefe_familia->family_id,
        'is_active' => true,
    ]);

    return response()->json([
        'message' => 'Miembro de familia activado exitosamente',
        'data'    => $miembro,
        'status'  => true,
    ], 200);
}


public function desactivarMiembroFamilia(Request $request, $member_id): JsonResponse
{
    $jefe_familia = $request->user();

    if (! $jefe_familia) {
        return response()->json([
            'message' => 'No autenticado.',
            'status'  => false,
            'data'    => null,
        ], 401);
    }

    if (empty($jefe_familia->family_id)) {
        return response()->json([
            'message' => 'No tienes una familia asociada.',
            'status'  => false,
            'data'    => null,
        ], 422);
    }

    $miembro = User::find($member_id);
    if (! $miembro) {
        return response()->json([
            'message' => 'Miembro no encontrado.',
            'status'  => false,
            'data'    => null,
        ], 404);
    }

    if ($jefe_familia->family_id != $miembro->family_id) {
        return response()->json([
            'message' => 'El miembro no pertenece a su familia!',
            'status'  => false,
            'data'    => $miembro,
        ], 422);
    }

    $miembro->update([
        'is_active' => false,
    ]);

    return response()->json([
        'message' => 'Miembro de familia desactivado exitosamente',
        'data'    => $miembro,
        'status'  => true,
    ], 200);
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
