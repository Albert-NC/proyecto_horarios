<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Docente extends Model
{
    use HasFactory;

    protected $table = 'docentes';

   protected $fillable = [
    'user_id',
    'categoria',
    'modalidad',
    'horas',
    'estado',
    'lugar',  
];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
