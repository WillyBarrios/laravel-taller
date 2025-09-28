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
    public function show($tenant, Tareas $task)
    {
        return response()->json($task);
    }

    // Version para contexto central (sin parámetro $tenant)
    public function showCentral(Tareas $task)
    {
        return response()->json($task);
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
    public function update($tenant, Request $request, Tareas $task)
    {
        $dbName = \DB::connection()->getDatabaseName();
        $estadoActualNormalizado = strtolower(trim((string)$task->estado));
        \Log::info('Intento update tarea (tenant)', [
            'id' => $task->id,
            'estado_raw' => $task->estado,
            'estado_normalizado' => $estadoActualNormalizado,
            'db' => $dbName,
            'tenant_id' => tenant() ? tenant('id') : null,
        ]);

        if ($estadoActualNormalizado !== 'pendiente') {
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
        $task->update($validated);

        \Log::info('Update tarea success', [
            'id' => $task->id,
            'tenant_id' => tenant() ? tenant('id') : null,
            'db' => $dbName,
        ]);

        return response()->json([
            'message' => 'Tarea actualizada correctamente',
            'status' => true,
            'data' => [
                'id' => $task->id,
            ],
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($tenant, Tareas $task)
    {
        $estadoActualNormalizado = strtolower(trim((string)$task->estado));
        if ($estadoActualNormalizado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo las tareas pendientes pueden ser eliminadas.',
                'status' => false
            ], 422);
        }

        $task->delete();

        return response()->json([
            'message' => 'Tarea eliminada correctamente',
            'status' => true
        ], 200);
    }

    // Version para contexto central (sin parámetro $tenant)
    public function updateCentral(Request $request, Tareas $task)
    {
        $dbName = \DB::connection()->getDatabaseName();
        $estadoActualNormalizado = strtolower(trim((string)$task->estado));
        \Log::info('Intento update tarea (central)', [
            'id' => $task->id,
            'estado_raw' => $task->estado,
            'estado_normalizado' => $estadoActualNormalizado,
            'db' => $dbName,
        ]);

        if ($estadoActualNormalizado !== 'pendiente') {
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

        $task->update($validated);

        \Log::info('Update tarea success (central)', [
            'id' => $task->id,
            'db' => $dbName,
        ]);

        return response()->json([
            'message' => 'Tarea actualizada correctamente',
            'status' => true,
            'data' => [
                'id' => $task->id,
            ],
        ], 200);
    }

    public function destroyCentral(Tareas $task)
    {
        $estadoActualNormalizado = strtolower(trim((string)$task->estado));
        if ($estadoActualNormalizado !== 'pendiente') {
            return response()->json([
                'message' => 'Solo las tareas pendientes pueden ser eliminadas.',
                'status' => false
            ], 422);
        }

        $task->delete();

        return response()->json([
            'message' => 'Tarea eliminada correctamente',
            'status' => true
        ], 200);
    }
}
