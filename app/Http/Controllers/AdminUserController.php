<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminUserController extends Controller
{
    /**
     * Crear nuevo usuario administrativo
     * POST /api/v1/admin/users
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'email' => 'required|email|max:255|unique:users,email',
                'password' => 'required|string|min:8',
                'phone' => 'required|string|max:20',
                'role' => 'required|string|in:jefe_cerrada,guardia,jefe_familia,familiar'
            ], [
                'name.required' => 'El nombre es obligatorio.',
                'email.required' => 'El email es obligatorio.',
                'email.email' => 'El formato del email no es válido.',
                'email.unique' => 'El email ya está en uso.',
                'password.required' => 'La contraseña es obligatoria.',
                'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
                'phone.required' => 'El teléfono es obligatorio.',
                'role.required' => 'El rol es obligatorio.',
                'role.in' => 'El rol especificado no es válido.'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'message' => 'Los datos proporcionados no son válidos.',
                    'data' => [
                        'errors' => $validator->errors()
                    ],
                    'status' => false
                ], 422);
            }

            // Mapear roles a IDs
            $roleMapping = [
                'jefe_cerrada' => 2,
                'guardia' => 3,
                'jefe_familia' => 4,
                'familiar' => 5
            ];

            $roleId = $roleMapping[$request->role];

            // Crear usuario en la base de datos local
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'phone' => $request->phone,
                'role_id' => $roleId
            ]);

            return response()->json([
                'message' => 'Usuario creado y rol asignado exitosamente.',
                'data' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $request->role
                ],
                'status' => 201
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'server_error',
                'message' => 'Error interno del servidor.',
                'data' => null,
                'status' => false
            ], 500);
        }
    }

    /**
     * Obtener lista de usuarios
     * GET /api/v1/admin/users
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $users = User::all();
            // Transformar datos para incluir nombre del rol
            // $roleMapping = [
            //     1 => 'admin',
            //     2 => 'jefe_cerrada',
            //     3 => 'guardia',
            //     4 => 'jefe_familia',
            //     5 => 'familiar'
            // ];

            // $users->transform(function($user) use ($roleMapping) {
            //     $user->role_name = $roleMapping[$user->role_id] ?? 'unknown';
            //     return $user;
            // });
            $users->load('role');

            return response()->json([
                'message' => 'Usuarios obtenidos exitosamente.',
                'data' => $users,
                'status' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'server_error',
                'message' => 'Error interno del servidor.',
                'data' => null,
                'status' => false
            ], 500);
        }
    }

    /**
     * Obtener usuario específico
     * GET /api/v1/admin/users/{id}
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Usuario no encontrado.',
                    'data' => null,
                    'status' => false
                ], 404);
            }

            // // Mapear role_id a nombre de rol
            // $roleMapping = [
            //     1 => 'admin',
            //     2 => 'jefe_cerrada',
            //     3 => 'guardia',
            //     4 => 'jefe_familia',
            //     5 => 'familiar'
            // ];

            // $user->role_name = $roleMapping[$user->role_id] ?? 'unknown';

            $user->load('role');

            return response()->json([
                'message' => 'Usuario obtenido exitosamente.',
                'data' => $user,
                'status' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'server_error',
                'message' => 'Error interno del servidor.',
                'data' => null,
                'status' => false
            ], 500);
        }
    }

    /**
     * Actualizar usuario
     * PUT /api/v1/admin/users/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Usuario no encontrado.',
                    'data' => null,
                    'status' => false
                ], 404);
            }

            // Validar datos de entrada
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|max:255|unique:users,email,' . $id,
                'phone' => 'sometimes|string|max:20',
            ], [
                'name.string' => 'El nombre debe ser una cadena de texto.',
                'email.email' => 'El formato del email no es válido.',
                'email.unique' => 'El email ya está en uso.',
                'phone.string' => 'El teléfono debe ser una cadena de texto.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'error' => 'validation_failed',
                    'message' => 'Los datos proporcionados no son válidos.',
                    'data' => [
                        'errors' => $validator->errors()
                    ],
                    'status' => false
                ], 422);
            }

            $updateData = $request->only(['name', 'email', 'phone']);
            $user->update($updateData);

            return response()->json([
                'message' => 'Usuario actualizado exitosamente.',
                'data' => $user,
                'status' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'server_error',
                'message' => 'Error interno del servidor.',
                'data' => null,
                'status' => false
            ], 500);
        }
    }

    /**
     * Eliminar usuario (Soft Delete)
     * DELETE /api/v1/admin/users/{id}
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'Usuario no encontrado.',
                    'data' => null,
                    'status' => false
                ], 404);
            }

            $user->update(['is_active' => $user->is_active]);

            return response()->json([
                'message' => 'Usuario eliminado exitosamente.',
                'data' => ['id' => $id],
                'status' => 200
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'server_error',
                'message' => 'Error interno del servidor.',
                'data' => null,
                'status' => false
            ], 500);
        }
    }
}
