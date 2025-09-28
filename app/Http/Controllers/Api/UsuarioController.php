<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Usuario::all());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|string',
        ]);

        /**
         * Validar que el rol sea 'admin' o 'usuario'
         */
        if (!in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".',
                'status' => false
            ], 400);
        }


        $validated['password'] = Hash::make($validated['password']);

        $usuario = Usuario::create($validated);
        if (!$usuario) {
            return response()->json([
                'message' => 'Error al crear el usuario',
                'status' => false
            ], 500);
        }
        return response()->json([
            'message' => 'Usuario creado correctamente',
            'status' => true
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(Usuario $user)
    {
        return response()->json($user);
    }

    // Versión tenant: primer parámetro corresponde al subdominio {tenant}
    public function showTenant($tenant, Usuario $user)
    {
        return response()->json($user);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Usuario $user)
    {
        $db = \DB::connection()->getDatabaseName();
        \Log::info('Update user attempt', [
            'user_id' => $user->id,
            'tenant_id' => tenant() ? tenant('id') : null,
            'db' => $db,
        ]);
        // Autorización mediante policy: el usuario autenticado debe ser admin o el mismo usuario
        // auth()->user() es el usuario que hace la petición (token Sanctum esperado)
        if (!auth()->check()) {
            return response()->json([
                'message' => 'No autenticado',
                'status' => false
            ], 401);
        }

        if (!auth()->user()->can('update', $user)) {
            return response()->json([
                'message' => 'No autorizado para actualizar este usuario (se requiere rol admin o ser el mismo usuario).',
                'status' => false
            ], 403);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|max:150|unique:usuarios,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'rol' => 'sometimes|required|string',
        ]);

        // Mensaje claro si mandan un rol inválido
        if (isset($validated['rol']) && !in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".'
            ], 422);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // no sobrescribir con null
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $user,
        ], 200);
    }

    // Versión tenant: respeta orden (string $tenant, Usuario $user)
    public function updateTenant($tenant, Request $request, Usuario $user)
    {
        $db = \DB::connection()->getDatabaseName();
        \Log::info('Update user attempt (tenant)', [
            'user_id' => $user->id,
            'tenant_id' => tenant() ? tenant('id') : null,
            'db' => $db,
        ]);

        if (!auth()->check()) {
            return response()->json([
                'message' => 'No autenticado',
                'status' => false
            ], 401);
        }

        if (!auth()->user()->can('update', $user)) {
            return response()->json([
                'message' => 'No autorizado para actualizar este usuario (se requiere rol admin o ser el mismo usuario).',
                'status' => false
            ], 403);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|max:150|unique:usuarios,email,' . $user->id,
            'password' => 'nullable|string|min:6',
            'rol' => 'sometimes|required|string',
        ]);

        if (isset($validated['rol']) && !in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".'
            ], 422);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $user,
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usuario $user)
    {
        $user->delete();
        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ], 200);
    }

    public function destroyTenant($tenant, Usuario $user)
    {
        $user->delete();
        return response()->json([
            'message' => 'Usuario eliminado correctamente'
        ], 200);
    }
}
