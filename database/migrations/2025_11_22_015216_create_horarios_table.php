<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();

            // Carrera: Ingeniería Informática
            $table->string('carrera', 100)->index();

            // Ciclo 1–10
            $table->unsignedTinyInteger('ciclo')->index();

            // Tipo de ciclo: par / impar
            $table->string('tipo_ciclo', 10)->index(); // 'par' | 'impar'

            // Sección: A, B, etc.
            $table->string('seccion', 2)->index();

            // Día: lunes, martes, miercoles, jueves, viernes, sabado
            $table->string('dia', 20)->index();

            // Horas (bloques de 1h de 7:00 a 20:00)
            $table->time('hora_inicio');
            $table->time('hora_fin');

            // Texto completo de la celda (curso, aula, profe, etc.)
            $table->text('descripcion');

            $table->timestamps();

            $table->index(['carrera', 'ciclo', 'tipo_ciclo', 'seccion', 'dia', 'hora_inicio']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
