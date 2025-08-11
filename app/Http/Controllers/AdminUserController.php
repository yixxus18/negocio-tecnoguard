<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Cerrada;
use App\Models\FamilyGroup;
use Log;
use Illuminate\Support\Str;

class AdminUserController extends Controller
{
    /**
     * Crear nuevo usuario administrativo
     * POST /api/v1/admin/users
     */
    public function store(Request $request): JsonResponse
    {
        try {
          
            $validator = Validator::make($request->all(), [
                'phone' => 'required|string|max:10|unique:users,phone',
                'cerrada_id' => 'required|integer|exists:cerradas,id'
            ], [
                'phone.required' => 'El teléfono es obligatorio.',
                'phone.max' => 'El teléfono es de  maximo 10 digitos .',
                'phone.unique' => 'El teléfono ya está registrado.',
                'cerrada_id.required' => 'La cerrada es obligatoria.',
                'cerrada_id.exists' => 'La cerrada especificada no existe.'
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

            $cerrada = Cerrada::find($request->cerrada_id);
            if (!$cerrada) {
                return response()->json([
                    'error' => 'not_found',
                    'message' => 'La cerrada especificada no existe.',
                    'data' => null,
                    'status' => false
                ], 404);
            }

            $familyGroup = FamilyGroup::create([
                'cerrada_id' => $request->cerrada_id,
                'is_active' => true
            ]);
            $randomEmail = Str::uuid() . '@temp.com';
            $user = User::create([
                'name' => 'Pendiente de registro',
                'email' => $randomEmail,
                'password' => Hash::make('temporal123'),
                'phone' => $request->phone,
                'role_id' => 4, 
                'family_id' => $familyGroup->id,
                'is_active' => true,
                'email_verified_at' => null, 
                'direccion' => null,
                'direccion_verified' => false,
                'two_factor_enabled' => false
            ]);

            return response()->json([
                'message' => 'Usuario creado exitosamente. El usuario podrá completar su registro con su teléfono.',
                'data' => [
                    'id' => $user->id,
                    'phone' => $user->phone,
                    'family_id' => $familyGroup->id,
                    'cerrada' => [
                        'id' => $cerrada->id,
                        'nombre' => $cerrada->nombre
                    ],
                    'status' => 'pendiente_registro'
                ],
                'status' => 201
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating cerrada: ' . $e->getMessage());
            return response()->json([
                'error' => 'server_error',
                'message' => 'Error interno del servidor.',
                'data' => null,
                'status' => false
            ], 500);
        }
    }

    public function obtenermiscerradasadministradas(Request $request): JsonResponse
{
    $user = $request->user();

    $cerradas = Cerrada::with(['configurationPayDate', 'assignedGuard'])
        ->where('jefe_cerrada_id', $user->id)
        ->get();

    if ($cerradas->isEmpty()) {
        return response()->json([
            'message' => 'No tienes cerradas asignadas!',
            'data'    => [],
            'status'  => false,
        ], 404);
    }

    return response()->json([
        'message' => 'Cerradas administradas obtenidas correctamente.',
        'data'    => $cerradas,
        'status'  => true,
    ], 200);
}

    /**
     * Obtener lista de usuarios
     * GET /api/v1/admin/users
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::all();
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

   public function getColaborators(): JsonResponse
{
    try {
       
        $users = User::whereIn('role_id', [2, 3])
                     ->with('role')
                     ->get();

       
        $data = $users->map(function (User $user) {
            $cerrada = Cerrada::where('jefe_cerrada_id', $user->id)
                              ->orWhere('guard_id', $user->id)
                              ->first();

            return [
                'user_name'=> $user->name,
                'user_id'    => $user->id,
                'role_id'    => $user->role_id,
                'role_name'  => $user->role->name,
                'cerrada_id' => $cerrada->id ?? null,
                'group_name' => $cerrada->group_name ?? null,
            ];
        });

        return response()->json([
            'message' => 'Colaboradores obtenidos exitosamente.',
            'data'    => $data,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error en getColaborators: ' . $e->getMessage());
        return response()->json([
            'message' => 'Error interno del servidor.',
            'data'    => null,
        ], 500);
    }
}



}
