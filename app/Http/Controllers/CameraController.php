<?php

namespace App\Http\Controllers;

use App\Events\ImagenCamara;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CameraController extends Controller
{
    public function imagenes(Request $request)
    {
        $imagen = $request->input('imagen');
        $timestamp = Carbon::nowWithSameTz()->format('Y-m-d h-m-s');

        event(new ImagenCamara($imagen, $timestamp));

        return response()->json(['status' => 'ok']);
    }
}
