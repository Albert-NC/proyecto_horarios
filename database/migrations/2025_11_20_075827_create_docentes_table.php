<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docentes', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->enum('categoria', ['asociado', 'principal', 'auxiliar'])->nullable();
            $table->enum('modalidad', ['tiempo completo', 'tiempo parcial'])->nullable();
            $table->integer('horas')->default(0);
            $table->enum('estado', ['activo', 'inactivo'])->default('activo');

            // 👇 AQUÍ AÑADIMOS LUGAR DIRECTAMENTE EN LA CREACIÓN
            $table->string('lugar', 50)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docentes');
    }
};
