<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tareas;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TareasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(Tareas::all());
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
            'descripcion' => 'required|string|max:600',
            'estado' => 'required|string|max:50',
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'fecha_vencimiento' => 'required|date',
        ]);

        $tarea = Tareas::create($validated);
        if (!$tarea) {
            return response()->json([
                'message' => 'Error al crear la tarea',
                'status' => false
            ], 500);
        }
        return response()->json([
            'message' => 'Tarea creada correctamente',
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
        $tarea = Tareas::findOrFail($id);

        if ($tarea->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo las tareas pendientes pueden ser actualizadas.',
                'status' => false
            ], 422);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'descripcion' => 'sometimes|required|string|max:600',
            'estado' => 'sometimes|required|string|max:50',
            'usuario_id' => 'sometimes|required|integer|exists:usuarios,id',
            'fecha_vencimiento' => 'sometimes|required|date',
        ]);
        $tarea->update($validated);

        return response()->json([
            'message' => 'Tarea actualizada correctamente',
            'status' => true
        ], 200);
}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tarea = Tareas::findOrFail($id);

        if ($tarea->estado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo las tareas pendientes pueden ser eliminadas.',
                'status' => false
            ], 422);
        }

        $tarea->delete();

        return response()->json([
            'message' => 'Tarea eliminada correctamente',
            'status' => true
        ], 200);
    }
}
