<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';

    protected $fillable = [
        'carrera',
        'ciclo',
        'tipo_ciclo',
        'seccion',
        'dia',
        'hora_inicio',
        'hora_fin',
        'descripcion',
        'sheet_name',
    ];

    // Sin casts - las horas se manejan como strings
}