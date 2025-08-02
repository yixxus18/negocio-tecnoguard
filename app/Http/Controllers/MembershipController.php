<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cerradas\JefeCerradaReq;
use App\Http\Requests\Cerradas\SetLocalidadCerradaReq;
use App\Http\Requests\Cerradas\StrCerradaReq;
use App\Http\Requests\Cerradas\UpdCerradaReq;
use App\Models\Cerrada;
use App\Models\LocalidadEntrada;
use Illuminate\Http\JsonResponse;
use Log;
use App\Services\FileUploadService;
use App\Models\ConfigurationPayDate;
use App\Models\MembershipDetail;
use App\Models\Membership;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class MembershipController extends Controller
{
   public function RechazarMembershipDetail(Request $request, $detailId): JsonResponse
    {
        $detail = MembershipDetail::find($detailId);
        if (! $detail) {
            return response()->json(['message' => 'Detalle no encontrado.'], 404);
        }

        $membership = Membership::find($detail->membership_id);
        if (! $membership) {
            return response()->json(['message' => 'Membresía no encontrada.'], 404);
        }

        DB::transaction(function () use ($detail, $membership) {
            
            $nextPayRaw = $membership->getOriginal('next_pay');

            $tz          = new \DateTimeZone('America/Monterrey');
            $today       = new \DateTime('today', $tz);
            $nextPayDate = \DateTime::createFromFormat('Y-m-d', $nextPayRaw, $tz);

            if ($nextPayDate < $today) {
               
                $detail->status       = 'Rechazado';
                $membership->is_active = false;
                $membership->save();
            } else {
                $detail->status = 'Pendiente';
            }

            $detail->save();
        });

        return response()->json([
            'message'           => 'Detalle de pago procesado correctamente.',
            'membership_detail' => $detail->fresh(),
            'membership'        => $membership->fresh(),
        ], 200);
    }


    public function AprobarMembershipDetail(Request $request, $detailId): JsonResponse
    {
        
        $detail = MembershipDetail::find($detailId);
        if (! $detail) {
            return response()->json(['message' => 'Detalle no encontrado.'], 404);
        }

       
        $membership = Membership::find($detail->membership_id);
        if (! $membership) {
            return response()->json(['message' => 'Membresía no encontrada.'], 404);
        }

        
        DB::transaction(function () use ($detail, $membership) {
            
            $nextPayRaw = $membership->getOriginal('next_pay');

            
            $detail->status            = 'Validado';
            $detail->date_finalization = $nextPayRaw;
            $detail->save();      
          $membership->last_pay  = $detail->date_pay;
            $membership->is_active = true;
            $membership->save();
        });

        
        return response()->json([
            'message'           => 'Detalle de pago aprobado correctamente.',
            'membership_detail' => $detail->fresh(),
            'membership'        => $membership->fresh(),
        ], 200);
    }


    public function procesarPagoFamilia(Request $request): JsonResponse
    {
       
        $validated = $request->validate([
            'membership_id' => 'required|integer|exists:memberships,id',
            'ticket'        => 'required|file|max:5120|mimes:jpg,png',
        ], [
            'membership_id.required' => 'El campo membership_id es obligatorio.',
            'membership_id.integer'  => 'El campo membership_id debe ser un número entero.',
            'membership_id.exists'   => 'La membresía seleccionada no existe.',
            'ticket.required'        => 'El ticket es obligatorio.',
            'ticket.file'            => 'El ticket debe ser un archivo.',
            'ticket.max'             => 'El ticket no debe pesar más de 5 MB.',
            'ticket.mimes'           => 'El ticket debe ser una imagen JPG o PNG.',
        ]);

        $jefe  = $request->user();
        $familyGroup = $jefe->familyGroup;
        if (! $familyGroup) {
            return response()->json([
                'message' => 'Error: No pertenece a ningún grupo familiar.',
                'status'  => false,
            ], 403);
        }

       
        $cerrada = Cerrada::find($familyGroup->cerrada_id);
        if (! $cerrada) {
            return response()->json([
                'message' => 'Error: Cerrada no encontrada.',
                'status'  => false,
            ], 404);
        }
        $config = ConfigurationPayDate::find($cerrada->configuration_pay_date);
        if (! $config) {
            return response()->json([
                'message' => 'Error: Configuración de pago no encontrada.',
                'status'  => false,
            ], 404);
        }

        $membership = Membership::with('familyGroups')->find($validated['membership_id']);
        if (! $membership
            || ! $membership->familyGroups
            || $membership->familyGroups->cerrada_id !== $cerrada->id
        ) {
            return response()->json([
                'message' => 'Error: TG-RES-004, La familia no existe o no pertenece a su cerrada.',
                'status'  => false,
            ], 403);
        }

        $upload = FileUploadService::uploadFile($validated['ticket']);
        if ($upload['success'] !== true) {
            return response()->json([
                'message' => 'Error: TG-SRV-001, no se pudo subir la imagen del ticket.',
                'status'  => false,
                'error'   => $upload['error'],
            ], 500);
        }

        $tz        = new \DateTimeZone('America/Monterrey');
        $hoy       = new \DateTime('today', $tz);
        $fechaCorte = (int) $config->fecha_corte;    
        $prorroga   = (int) $config->tiempo_prorroga; 

        $nextCutoff = new \DateTime('first day of next month', $tz);
        $nextCutoff->setDate(
            (int) $nextCutoff->format('Y'),
            (int) $nextCutoff->format('m'),
            $fechaCorte
        );
        $nextCutoff->modify("+{$prorroga} days");

        $datePay          = $hoy->format('Y-m-d');
        $dateFinalization = $nextCutoff->format('Y-m-d');

       
        DB::transaction(function () use (
            $membership,
            $upload,
            $datePay,
            $dateFinalization,
            $config
        ) {
            MembershipDetail::create([
                'membership_id'     => $membership->id,
                'amount'            => $config->pay,
                'estatus'           => 'Pendiente',
                'date_pay'          => $datePay,
                'ticket'            => $upload['file_name'],
                'date_finalization' => $dateFinalization,
            ]);
        });

        $detail = MembershipDetail::latest()
            ->where('membership_id', $membership->id)
            ->first();

        $detail->ticket_url = $upload['url'];

        return response()->json([
            'message' => 'Pago procesado exitosamente.',
            'data'    => $detail,
            'status'  => true,
        ], 201);
    }
}
