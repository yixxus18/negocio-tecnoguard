<?php

namespace App\Http\Controllers;

use App\Http\Requests\Guardia\CrearTokenReq;
use App\Http\Requests\JefeCerrada\CrearPagoReq;
use App\Http\Requests\JefeFamilia\AddMiembroReq;
use App\Models\Membership;
use App\Models\MembershipDetail;
use App\Models\TokenAcceso;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Log;

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

        $autenticado=Auth::user();
        $user=User::where('id',$autenticado->id)->get();
        return response()->json([
            'message' => 'Historial de membresía obtenido exitosamente',
            'data' => []
        ]);
    }

    public function procesarPagoFamilia(CrearPagoReq $request): JsonResponse
    {
        $data = $request->validated();
        $jefe_cerrada = $request->user();
        $membership = Membership::find($data['membership_id'])->load('familyGroups');
        
        if (!$membership->familyGroups) {
            return response()->json([
                'message' => 'Error: TG-RES-004, La familia no existe o no pertenece a su cerrada',
                'status' => false
            ]);
        }
        $ticket = FileUploadService::uploadFile($data['ticket']);
        if ($ticket['success'] != true) {
            return response()->json([
                'message' => 'Error: TG-SRV-001, Error al querer subir la imagen del ticket',
                'status' => false,
                'error' => $ticket['error']
            ]);
        }

        $membership_detail = MembershipDetail::create([
            'membership_id' => $data['membership_id'],
            'amount' => $data['amount'],
            'estatus' => $data['status'],
            'date_pay' => $data['date_pay'],
            'ticket' => $ticket['file_name'],
            'date_finalization' => Carbon::parse($data['date_pay'])->addMonth()->format('Y-m-d'),
        ]);
        $membership_detail->ticket_url = $ticket['url'];
        return response()->json([
            'message' => 'Pago procesado exitosamente',
            'data' => $membership_detail,
            'status' => true
        ]);
    }
}
