<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
    public function store(StrCerradaReq $request)
    {
        $data = $request->validated();
        $cerrada = Cerrada::create($data);
        return response()->json(['data' => $cerrada, 'message' => 'Cerrada creada exitosamente', 'status' => true]);
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
        $cerrada->update($data);
        return response()->json(['data'=> $cerrada,'message'=> 'Cerrada actualizada exitosamente', 'status'=> true]);
    }


}
