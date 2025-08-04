<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AdminController extends Controller
{
    /**
     * Crear una nueva cerrada
     */
    public function crearCerrada(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para crear cerrada
        return response()->json([
            'message' => 'Cerrada creada exitosamente',
            'data' => []
        ], 201);
    }

    /**
     * Obtener todas las cerradas
     */
    public function obtenerTodasCerradas(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para obtener todas las cerradas
        return response()->json([
            'message' => 'Cerradas obtenidas exitosamente',
            'data' => []
        ]);
    }

    /**
     * Actualizar una cerrada específica
     */
    public function actualizarCerrada(Request $request, $id_cerrada): JsonResponse
    {
        // TODO: Implementar lógica para actualizar cerrada
        return response()->json([
            'message' => 'Cerrada actualizada exitosamente',
            'data' => ['id' => $id_cerrada]
        ]);
    }

    /**
     * Asignar jefe a una cerrada
     */
    public function asignarJefeCerrada(Request $request, $id_cerrada): JsonResponse
    {
        // TODO: Implementar lógica para asignar jefe a cerrada
        return response()->json([
            'message' => 'Jefe asignado exitosamente',
            'data' => ['id_cerrada' => $id_cerrada]
        ]);
    }

    /**
     * Crear usuario administrativo
     */
  public function crearUsuarioAdministrativo(Request $request): JsonResponse
{
    
    $validator = Validator::make($request->all(), [
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|max:255|unique:users,email',
        'phone'    => 'required|string|size:10|unique:users,phone',
        'role_id'  => 'required|integer|exists:roles,id',
        'password' => [
            'required',
            'string',
            'min:8',
            'regex:/[A-Z]/',    
            'regex:/\d/',       
            'regex:/[#$%]/',   
        ],
    ], [
        'name.required'     => 'El nombre es obligatorio.',
        'name.string'       => 'El nombre debe ser una cadena de texto.',
        'name.max'          => 'El nombre no puede exceder los 255 caracteres.',
        'email.required'    => 'El correo electrónico es obligatorio.',
        'email.email'       => 'El formato del correo no es válido.',
        'email.max'         => 'El correo no puede exceder los 255 caracteres.',
        'email.unique'      => 'El correo ya está registrado.',
        'phone.required'    => 'El teléfono es obligatorio.',
        'phone.string'      => 'El teléfono debe ser una cadena de texto.',
        'phone.size'        => 'El teléfono debe tener exactamente 10 dígitos.',
        'phone.unique'      => 'El teléfono ya está registrado.',
        'role_id.required'  => 'El role_id es obligatorio.',
        'role_id.integer'   => 'El role_id debe ser un número entero.',
        'role_id.exists'    => 'El role_id especificado no existe.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min'      => 'La contraseña debe tener al menos 8 caracteres.',
        'password.regex'    => 'La contraseña debe contener al menos una mayúscula, un número y un carácter especial (#, $, %).',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error'   => 'validation_failed',
            'message' => 'Los datos proporcionados no son válidos.',
            'data'    => ['errors' => $validator->errors()],
            'status'  => false,
        ], 422);
    }

    
    $user = User::create([
        'name'              => $request->input('name'),
        'email'             => $request->input('email'),
        'phone'             => $request->input('phone'),
        'role_id'           => $request->input('role_id'),
        'password'          => Hash::make($request->input('password')),
        'is_active'         => true,
        'email_verified_at' => now(),
    ]);

    
    return response()->json([
        'message' => 'Usuario administrativo creado exitosamente.',
        'data'    => [
            'id'       => $user->id,
            'name'     => $user->name,
            'email'    => $user->email,
            'phone'    => $user->phone,
            'role_id'  => $user->role_id,
        ],
        'status'  => true,
    ], 201);
}



    /**
     * Obtener detalles de un usuario
     */
    public function obtenerDetallesUsuario(Request $request, $id): JsonResponse
    {
        // TODO: Implementar lógica para obtener detalles de usuario
        return response()->json([
            'message' => 'Detalles de usuario obtenidos exitosamente',
            'data' => ['id' => $id]
        ]);
    }

    /**
     * Listar configuraciones de pagos
     */
    public function listarConfiguracionesPagos(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para listar configuraciones de pagos
        return response()->json([
            'message' => 'Configuraciones de pagos obtenidas exitosamente',
            'data' => []
        ]);
    }

    /**
     * Crear configuración de pagos
     */
    public function crearConfiguracionPagos(Request $request): JsonResponse
    {
        // TODO: Implementar lógica para crear configuración de pagos
        return response()->json([
            'message' => 'Configuración de pagos creada exitosamente',
            'data' => []
        ], 201);
    }

    /**
     * Obtener detalle de configuración de pagos
     */
    public function obtenerDetalleConfiguracion(Request $request, $id): JsonResponse
    {
        // TODO: Implementar lógica para obtener detalle de configuración
        return response()->json([
            'message' => 'Detalle de configuración obtenido exitosamente',
            'data' => ['id' => $id]
        ]);
    }

    /**
     * Actualizar configuración de pagos
     */
    public function actualizarConfiguracionPagos(Request $request, $id): JsonResponse
    {
        // TODO: Implementar lógica para actualizar configuración de pagos
        return response()->json([
            'message' => 'Configuración de pagos actualizada exitosamente',
            'data' => ['id' => $id]
        ]);
    }
}
