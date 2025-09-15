<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tareas;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class TareasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rows = DB::table('tareas as t')
            ->join('usuarios as u', 'u.id', '=', 't.usuario_id')
            ->select([
                't.id',
                't.nombre',
                't.estado',
                't.fecha_vencimiento',
                'u.nombre as usuario',
            ])
            ->get();

        return response()->json($rows);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // No se usa en API REST; utilizar store()
        return response()->json([
            'message' => 'Usa POST /api/tareas/addTask (store) para crear tareas',
        ], 405);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Normalizar payload: permitir "Fecha de vencimiento" y estados en mayúsculas
        $payload = $request->all();

        // Mapear clave alternativa a snake_case esperado
        if (isset($payload['Fecha de vencimiento']) && !isset($payload['fecha_vencimiento'])) {
            $payload['fecha_vencimiento'] = $payload['Fecha de vencimiento'];
        }

        // Normalizar estado a minúsculas para que coincida con el ENUM
        if (isset($payload['estado'])) {
            $payload['estado'] = strtolower($payload['estado']);
        }

        // Validación
        $validated = validator($payload, [
            'nombre' => 'required|string|max:150',
            'descripcion' => 'required|string|max:255',
            'estado' => 'required|string|in:pendiente,en_progreso,completada',
            'usuario_id' => 'required|integer|exists:usuarios,id',
            'fecha_vencimiento' => 'nullable|date',
        ])->validate();

        $tarea = Tareas::create($validated);
        if (!$tarea) {
            return response()->json([
                'message' => 'Error al crear la tarea',
                'status' => false
            ], 500);
        }
        return response()->json([
            'message' => 'Tarea creada correctamente',
            'status' => true,
            'data' => [
                'id' => $tarea->id,
            ],
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tarea = Tareas::find($id);
        if (!$tarea) {
            return response()->json([
                'message' => 'Tarea no encontrada',
            ], 404);
        }
        return response()->json($tarea);
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

        $payload = $request->all();
        if (isset($payload['Fecha de vencimiento']) && !isset($payload['fecha_vencimiento'])) {
            $payload['fecha_vencimiento'] = $payload['Fecha de vencimiento'];
        }
        if (isset($payload['estado'])) {
            $payload['estado'] = strtolower($payload['estado']);
        }

        $validated = validator($payload, [
            'nombre' => 'sometimes|required|string|max:150',
            'descripcion' => 'sometimes|required|string|max:255',
            'estado' => 'sometimes|required|string|in:pendiente,en_progreso,completada',
            'usuario_id' => 'sometimes|required|integer|exists:usuarios,id',
            'fecha_vencimiento' => 'sometimes|nullable|date',
        ])->validate();
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
