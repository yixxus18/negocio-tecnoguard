<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cerradas\JefeCerradaReq;
use App\Http\Requests\Cerradas\SetLocalidadCerradaReq;
use App\Http\Requests\Cerradas\StrCerradaReq;
use App\Http\Requests\Cerradas\UpdCerradaReq;
use App\Models\Cerrada;
use App\Models\ConfigurationPayDate;
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
        return response()->json(['data' => Cerrada::all()->load('localidadesEntradas'), 'message' => 'Lista de cerradas obtenida exitosamente.', 'status' => true]);
    }


    /**
     * Store a newly created resource in storage.
     */
     public function store(Request $request): JsonResponse
    {
        
        $validated = $request->validate([
            'nombre'                  => 'required|string|unique:cerradas,group_name|min:5|max:127',
            'description'             => 'required|string|min:10|max:255',
            'guard_id'                => 'sometimes|nullable|integer|exists:users,id',
            'configuration_pay_date'  => 'required|integer|exists:configuration_pay_date,id',
            'latitud'                 => 'required|numeric|between:-90,90',
            'longitud'                => 'required|numeric|between:-180,180',
        ], [
            'nombre.required'                 => 'El nombre de la cerrada es obligatorio.',
            'nombre.string'                   => 'El nombre debe ser una cadena de texto.',
            'nombre.unique'                   => 'El nombre de la cerrada ya está en uso.',
            'nombre.min'                      => 'El nombre debe tener al menos :min caracteres.',
            'nombre.max'                      => 'El nombre no puede exceder de :max caracteres.',
            'description.required'            => 'La descripción es obligatoria.',
            'description.string'              => 'La descripción debe ser una cadena de texto.',
            'description.min'                 => 'La descripción debe tener al menos :min caracteres.',
            'description.max'                 => 'La descripción no puede exceder de :max caracteres.',

            'guard_id.integer'                => 'El guardia asignado debe ser un identificador numérico.',
            'guard_id.exists'                 => 'El guardia asignado no existe.',

            'configuration_pay_date.required' => 'La configuración de fecha de pago es obligatoria.',
            'configuration_pay_date.integer'  => 'La configuración de fecha de pago debe ser un identificador numérico.',
            'configuration_pay_date.exists'   => 'La configuración de fecha de pago seleccionada no existe.',

            'latitud.required'                => 'La latitud es obligatoria.',
            'latitud.numeric'                 => 'La latitud debe ser un valor numérico.',
            'latitud.between'                 => 'La latitud debe estar entre :min y :max.',

            'longitud.required'               => 'La longitud es obligatoria.',
            'longitud.numeric'                => 'La longitud debe ser un valor numérico.',
            'longitud.between'                => 'La longitud debe estar entre :min y :max.',
        ]);

        
        $cerrada = Cerrada::create([
            'group_name'             => $validated['nombre'],
            'description'            => $validated['description']             ?? null,
            'guard_id'               => $validated['guard_id']               ?? null,
            'configuration_pay_date' => $validated['configuration_pay_date'] ?? null,
        ]);

        
        $localidad = LocalidadEntrada::create([
            'latitud'  => $validated['latitud'],
            'longitud' => $validated['longitud'],
        ]);

        $cerrada->localidadesEntradas()->attach($localidad->id);

       
        return response()->json([
            'data'    => $cerrada->load('localidadesEntradas'),
            'message' => 'Cerrada creada exitosamente',
            'status'  => true,
        ], 201);
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }


     public function getConfigurations(): JsonResponse
    {
        $configs = ConfigurationPayDate::all();

        return response()->json([
            'data'    => $configs,
            'message' => 'Configuraciones de fecha de pago obtenidas exitosamente.',
            'status'  => true,
        ], 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdCerradaReq $request, string $id)
    {
        $data = $request->validated();
        $cerrada = Cerrada::find($id)->load('localidadesEntradas');
        if (!$cerrada) {
            return response()->json(['message' => 'La cerrada no fue encontrada', 'status' => false], 404);
        }
        $cerrada->update($data);
        return response()->json(['data' => $cerrada, 'message' => 'Cerrada actualizada exitosamente', 'status' => true]);
    }


    public function setJefeDeCerrada(int $id, JefeCerradaReq $request)
    {
        $data = $request->validated();
        $cerrada = Cerrada::find($id)->load('localidadesEntradas');
        if (!$cerrada) {
            return response()->json([
                'message' => 'El usuario o la cerrada especificados no existen.',
                'status' => false
            ], 404);
        }
        $cerrada->jefe_cerrada_id = $data['user_id'];
        $cerrada->save();
        return response()->json([
            'data' => $cerrada,
            'message' => 'Jefe de cerrada asignado exitosamente a ' . $cerrada->group_name,
            'status' => true
        ]);
    }

    /**
     * Asociar localidades a una cerrada
     */
    public function asociarLocalidad(int $id, SetLocalidadCerradaReq $request)
    {
        $data = $request->validated();

        $cerrada = Cerrada::find($id);
        if (!$cerrada) {
            return response()->json(['message' => 'Cerrada no encontrada', 'status' => false], 404);
        }

        // Asociar localidades sin quitar las existentes
        $cerrada->localidadesEntradas()->sync($data['localidad']);

        return response()->json([
            'message' => 'Localidades asociadas exitosamente',
            'data' => $cerrada->load('localidadesEntradas'),
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
            'data' => $cerrada->load('localidadesEntradas'),
            'status' => true
        ]);
    }

}
