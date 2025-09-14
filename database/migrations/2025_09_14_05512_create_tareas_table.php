<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable("tareas")) {
            Schema::create('tareas', function (Blueprint $table) {
                $table->id(); // equivale a BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
                $table->string('nombre', 150);
                $table->string('descripcion', 255);
                $table->enum('estado', ['pendiente', 'en_progreso', 'completada'])->default('pendiente');
                $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
                $table->date('fecha_vencimiento')->nullable();
                $table->timestamps(); // crea columnas created_at y updated_at

                $table->index('usuario_id');
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
    }
};
