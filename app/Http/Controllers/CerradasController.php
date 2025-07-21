<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cerradas\JefeCerradaReq;
use App\Http\Requests\Cerradas\StrCerradaReq;
use App\Http\Requests\Cerradas\UpdCerradaReq;
use App\Models\Cerrada;
use App\Models\LocalidadEntrada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Log;

class CerradasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return response()->json(['data' => Cerrada::all(), 'message' => 'Lista de cerradas obtenida exitosamente.', 'status' => true]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StrCerradaReq $request)
    {
        try {
            $data = $request->validated();
            $cerrada = Cerrada::create([
                'group_name' => $data['nombre']
            ]);

            $localidad = LocalidadEntrada::create([
                'latitud' => $data['latitud'],
                'longitud' => $data['longitud']
            ]);

            $cerrada->localidadesEntradas()->attach($localidad->id);

            return response()->json([
                'data' => $cerrada,
                'message' => 'Cerrada creada exitosamente',
                'status' => true
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating cerrada: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());

            return response()->json([
                'message' => 'Error interno del servidor: ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdCerradaReq $request, string $id)
    {
        $data = $request->validated();
        $cerrada = Cerrada::find($id);
        if (!$cerrada) {
            return response()->json(['message' => 'La cerrada no fue encontrada', 'status' => false], 404);
        }
        $cerrada->update($data);
        return response()->json(['data' => $cerrada, 'message' => 'Cerrada actualizada exitosamente', 'status' => true]);
    }


    public function setJefeDeCerrada(int $id, JefeCerradaReq $request)
    {
        $data = $request->validated();
        $cerrada = Cerrada::find($id);
        if (!$cerrada) {
            return response()->json([
                'message' => 'El usuario o la cerrada especificados no existen.',
                'status' => false
            ], 404);
        }
        $cerrada->jefe_cerrada_id = $data->user_id;
        $cerrada->save();
        return response()->json(['data' => $cerrada, 'message' => 'Jefe de cerrada asignado exitosamente a ' . $cerrada->group_name, 'status' => true]);
    }

    /**
     * Asociar localidades a una cerrada
     */
    public function asociarLocalidades(int $cerradaId, Request $request)
    {
        $request->validate([
            'localidades' => 'required|array',
            'localidades.*' => 'exists:localidades_entradas,id'
        ]);

        $cerrada = Cerrada::find($cerradaId);
        if (!$cerrada) {
            return response()->json(['message' => 'Cerrada no encontrada', 'status' => false], 404);
        }

        // Asociar localidades sin quitar las existentes
        $cerrada->localidadesEntradas()->syncWithoutDetaching($request->localidades);

        return response()->json([
            'message' => 'Localidades asociadas exitosamente',
            'status' => true
        ]);
    }

    /**
     * Desasociar una localidad de una cerrada
     */
    public function desasociarLocalidad(int $cerradaId, int $localidadId)
    {
        $cerrada = Cerrada::find($cerradaId);
        if (!$cerrada) {
            return response()->json(['message' => 'Cerrada no encontrada', 'status' => false], 404);
        }

        $cerrada->localidadesEntradas()->detach($localidadId);

        return response()->json([
            'message' => 'Localidad desasociada exitosamente',
            'status' => true
        ]);
    }

}
