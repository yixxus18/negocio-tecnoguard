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
     * Generar token de acceso
     */
    public function generarTokenAcceso(CrearTokenReq $request): JsonResponse
    {
        $familiar = $request->user();
        $data = $request->validated();
        if (!array_key_exists('tipo_token', $data) || !$data['tipo_token']) {
            $data['tipo_token'] = 'visita';
        }
        $code = random_int(100000, 999999);
        $data['fecha_expiracion'] = Carbon::now('America/Monterrey')->addHours(5)->format('Y-m-d h:i:s');
        $data['usuario_id'] = $familiar->id;
        $data['usos'] = 1;
        $data['valor'] = $code;
        $token = TokenAcceso::create($data);
        return response()->json([
            'message' => 'Aceeso creado correctamente!',
            'data' => $token,
            'status' => true
        ]);
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

        $autenticado=$request->user();
        $user=User::where('id',$autenticado->id)->get();
        return response()->json([
            'message' => 'Historial de membresía obtenido exitosamente',
            'data' => []
        ]);
    }

   public function procesarPagoFamilia(Request $request): JsonResponse
{
    // 1) Validación inline (solo archivo, 2MB, imagen)
    $request->validate(
        [
            'ticket' => 'required|file|mimes:jpeg,jpg,png,webp|max:2048',
        ],
        [
            'ticket.required' => 'El ticket es obligatorio.',
            'ticket.file'     => 'El ticket debe ser un archivo.',
            'ticket.mimes'    => 'Formato inválido. Usa JPG, PNG o WebP.',
            'ticket.max'      => 'El archivo debe pesar máximo 2 MB.',
        ]
    );

    $user = $request->user();

    // 2) Verifica que el usuario tenga family_id
    if (!$user || !$user->family_id) {
        return response()->json([
            'message' => 'No perteneces a una familia válida.',
            'status'  => false,
        ], 404);
    }

    // 3) Obtiene la familia y el membership_id
    $family = FamilyGroup::find($user->family_id);
    if (!$family || !$family->membership_id) {
        return response()->json([
            'message' => 'La familia no existe o no tiene una membresía asociada.',
            'status'  => false,
        ], 404);
    }

    $membership = Membership::find($family->membership_id);
    if (!$membership) {
        return response()->json([
            'message' => 'La membresía no existe.',
            'status'  => false,
        ], 404);
    }

    // 4) Subir el archivo a DigitalOcean Spaces (disk "s3"), carpeta "tickets"
    $file = $request->file('ticket');
    $ext  = strtolower($file->getClientOriginalExtension() ?: $file->extension());
    $name = 'ticket_' . Str::uuid() . '.' . $ext;
    $path = 'tickets/' . $name;

    try {
        // Sube como público
        Storage::disk('s3')->putFileAs('tickets', $file, $name, [
            'visibility'  => 'public',
            'ACL'         => 'public-read',              // para Spaces
            'ContentType' => $file->getMimeType(),       // útil para servir correcto
        ]);
    } catch (\Throwable $e) {
        Log::error('Error subiendo a DO Spaces: ' . $e->getMessage());
        return response()->json([
            'message' => 'No se pudo subir la imagen del ticket.',
            'status'  => false,
        ], 500);
    }

    // 5) Construir URL pública (igual que en tu otro método "store")
    $baseUrl  = rtrim(config('filesystems.disks.s3.url'), '/'); // DO_SPACES_URL
    $publicUrl = $baseUrl . '/' . ltrim($path, '/');

    // 6) Crear el detalle con estatus "revision" y fechas
    try {
        $detail = MembershipDetail::create([
            'membership_id'     => $membership->id,
            'amount'            => 150,                     // sin monto
            'estatus'           => 'revision',               // default que pediste
            'date_pay'          => Carbon::now(),            // ahora
            'ticket'            => $publicUrl,               // URL pública
            'date_finalization' => Carbon::now()->addMonth()->toDateString(),
        ]);
    } catch (\Throwable $e) {
        
        try { Storage::disk('s3')->delete($path); } catch (\Throwable $e2) {}
        Log::error('Error creando MembershipDetail: ' . $e->getMessage());
        return response()->json([
            'message' => 'No fue posible registrar el pago.',
            'status'  => false,
        ], 500);
    }

    return response()->json([
        'message' => 'Pago enviado correctamente. Queda en revisión.',
        'data'    => $detail,
        'status'  => true,
    ], 201);
}


    public function crearSolicitudCambio(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'direccion'                 => 'required|string|max:512',
            'cerradaproveniente_id'     => 'required|exists:cerradas,id',
            'cerradadestino_id'         => 'required|exists:cerradas,id',
            'proveniente'               => 'sometimes|boolean',
            'destino'                   => 'sometimes|boolean',
            'user_solicitud'            => 'required|exists:users,id',
            'estado'                    => 'required|Pendiente',
            'comentariodestino'         => 'nullable|string',
            'comentarioproveniente'     => 'nullable|string|max:255',
        ], [
            'direccion.required'                => 'La dirección es obligatoria.',
            'direccion.max'                     => 'La dirección no puede exceder de :max caracteres.',
            'cerradaproveniente_id.required'    => 'La cerrada de origen es obligatoria.',
            'cerradaproveniente_id.exists'      => 'La cerrada de origen no existe.',
            'cerradadestino_id.required'        => 'La cerrada de destino es obligatoria.',
            'cerradadestino_id.exists'          => 'La cerrada de destino no existe.',
            'user_solicitud.required'           => 'El usuario solicitante es obligatorio.',
            'user_solicitud.exists'             => 'El usuario solicitante no existe.',
            'estado.required'                   => 'El estado es obligatorio.',
            'estado.in'                         => 'El estado debe ser Pendiente',
            'comentarioproveniente.max'         => 'El comentario del proveniente no puede exceder de :max caracteres.',
        ]);

        $solicitud = SolicitudCambioCerrada::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Solicitud de cambio creada correctamente.',
            'data'    => $solicitud->load(['cerradaProveniente', 'cerradaDestino', 'solicitante']),
        ], 201);
    }

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
