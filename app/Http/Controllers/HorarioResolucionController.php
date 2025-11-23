<?php

namespace App\Http\Controllers;

use App\Models\HorarioResolucion;
use Illuminate\Http\Request;

class HorarioResolucionController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'ciclo'       => ['required', 'integer', 'between:1,10'],
            'seccion'     => ['nullable', 'in:A,B'],
            'archivo_pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB
            'comentario'  => ['nullable', 'string', 'max:2000'],
        ]);

        // guardar PDF en storage/app/public/resoluciones
        $pdfPath = $request->file('archivo_pdf')
            ->store('resoluciones', 'public');

        HorarioResolucion::create([
            'carrera'    => 'Ingeniería Informática',
            'ciclo'      => $data['ciclo'],
            'seccion'    => $data['seccion'] ?: null,
            'pdf_path'   => $pdfPath,
            'comentario' => $data['comentario'] ?? null,
        ]);

        return back()->with('success', 'Resolución del ciclo guardada correctamente.');
    }
}
