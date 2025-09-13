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
        if (!Schema::hasTable("usuarios")) {
            Schema::create('usuarios', function (Blueprint $table) {
                $table->id(); // equivale a BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY
                $table->string('nombre', 150);
                $table->string('email', 150)->unique();
                $table->string('password', 255);
                $table->enum('rol', ['admin', 'usuario'])->default('usuario');
                $table->timestamps(); // crea columnas created_at y updated_at
            });
        }
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};
