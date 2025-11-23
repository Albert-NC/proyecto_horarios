<?php

namespace App\Http\Controllers;

use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AulaController extends Controller
{
    public function index()
    {
        $aulas = Aula::latest('id')->paginate(10);
        return view('aulas.index', compact('aulas'));
    }

    public function create()
    {
        return view('aulas.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'codigo'    => ['required','string','max:20','unique:aulas,codigo'],
            'nombre'    => ['required','string','max:100'],
            'tipo'      => ['required', Rule::in(['Laboratorio','Teoria-Practica'])],
            'capacidad' => ['required','integer','min:1'],
            'piso'      => ['nullable','string','max:10'],
            'edificio'  => ['nullable','string','max:50'],
            'activo'    => ['sometimes','boolean'],
        ]);
        $data['activo'] = $request->boolean('activo', true);

        Aula::create($data);
        return redirect()->route('aulas.index')->with('ok','Aula creada.');
    }

    public function edit(Aula $aula)
    {
        return view('aulas.edit', compact('aula'));
    }

    public function update(Request $request, Aula $aula)
    {
        $data = $request->validate([
            'codigo'    => ['required','string','max:20', Rule::unique('aulas','codigo')->ignore($aula->id)],
            'nombre'    => ['required','string','max:100'],
            'tipo'      => ['required', Rule::in(['Laboratorio','Teoria-Practica'])],
            'capacidad' => ['required','integer','min:1'],
            'piso'      => ['nullable','string','max:10'],
            'edificio'  => ['nullable','string','max:50'],
            'activo'    => ['sometimes','boolean'],
        ]);
        $data['activo'] = $request->boolean('activo', true);

        $aula->update($data);
        return redirect()->route('aulas.index')->with('ok','Aula actualizada.');
    }

    public function destroy(Aula $aula)
    {
        $aula->delete();
        return back()->with('ok','Aula eliminada.');
    }

    // show() no es obligatorio para CRUD básico
    public function show(Aula $aula)
    {
        return view('aulas.show', compact('aula'));
    }
}
