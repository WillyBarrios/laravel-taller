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
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->rol !== 'admin') {
            return response()->json([
                'message' => 'Solo los usuarios con rol admin pueden ser actualizados.',
                'status' => false
            ], 422);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|max:150|unique:usuarios,email,' . $usuario->id,
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

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $usuario
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
