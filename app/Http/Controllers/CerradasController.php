<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cerradas\JefeCerradaReq;
use App\Http\Requests\Cerradas\StrCerradaReq;
use App\Http\Requests\Cerradas\UpdCerradaReq;
use App\Models\Cerrada;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|alpha|unique:cerradas,group_name|max:127|min:5',
            'latitud' => 'required|decimal:10,6',
            'longitud' => 'required|decimal:10,6',
        ]);
        $cerrada = Cerrada::create($data);
        return response()->json(['data' => $cerrada, 'message' => 'Cerrada creada exitosamente', 'status' => true], 201);
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

}
