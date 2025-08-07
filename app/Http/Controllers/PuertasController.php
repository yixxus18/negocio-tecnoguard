<?php

namespace App\Http\Controllers;

use App\Events\AbrirPuerta;
use App\Http\Controllers\Controller;
use App\Models\FamilyGroup;
use Illuminate\Http\Request;
use Log;

class PuertasController extends Controller
{
    public function openDoor(Request $request){
        $data = $request->input('data');
        $user = $request->user();
        $family = FamilyGroup::find($user->family_id);
        event(new AbrirPuerta($family->cerrada_id.$data));
        return response()->json([
            'message' => 'Puerta abierta!',
            'status' => true
        ]);
    }
}
