<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioResolucion extends Model
{
    protected $table = 'horario_resolucions';

    protected $fillable = [
        'carrera',
        'ciclo',
        'seccion',
        'pdf_path',
        'comentario',
    ];
}