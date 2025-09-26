<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('tareas')) {
            Schema::create('tareas', function (Blueprint $table) {
                $table->id();
                $table->string('nombre', 150);
                $table->string('descripcion', 255);
                $table->enum('estado', ['pendiente', 'en_progreso', 'completada'])->default('pendiente');
                $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
                $table->date('fecha_vencimiento')->nullable();
                $table->timestamps();
                $table->index('usuario_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
