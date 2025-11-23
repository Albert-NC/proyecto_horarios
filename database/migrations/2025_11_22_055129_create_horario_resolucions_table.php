<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::create('horario_resolucions', function (Blueprint $table) {
        $table->id();
        $table->string('carrera', 100)->index();
        $table->unsignedTinyInteger('ciclo')->index(); // 1–10
        $table->string('seccion', 2)->nullable()->index(); // A, B o null (todas)
        $table->string('pdf_path'); // ruta en storage
        $table->text('comentario')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horario_resolucions');
    }
};
