<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('codigo', 20)
                  ->nullable()
                  ->unique()
                  ->after('email');

            $table->enum('role', ['admin', 'profesor', 'alumno'])
                  ->default('alumno')
                  ->after('codigo');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['codigo', 'role']);
        });
    }
};
