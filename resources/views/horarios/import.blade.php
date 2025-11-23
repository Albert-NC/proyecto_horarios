@extends('layouts.panel')

@section('contenido')
<div
    class="rounded-xl border border-white/10 px-6 py-4 mb-4"
    :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold">Importar horarios desde Excel</h2>
            <p class="text-sm text-gray-400">
                Sube un archivo con varias hojas. El nombre de cada hoja define el ciclo y la sección
                (ej: <span class="font-mono">2A</span>, <span class="font-mono">4B</span>, etc.).
            </p>
        </div>

        <a href="{{ route('horarios.index') }}"
           class="px-4 py-2 text-sm font-semibold rounded-lg bg-slate-700 hover:bg-slate-800 text-white">
            Volver a horarios
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-500/10 border border-red-500 text-red-200 px-4 py-2 rounded-lg text-sm">
            <ul class="list-disc pl-5 space-y-1">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ route('horarios.import') }}"
          enctype="multipart/form-data"
          class="space-y-4 text-sm">
        @csrf

        <div>
            <label class="block mb-1 font-medium">Tipo de ciclo que estás subiendo</label>
            <div class="flex gap-3">
                <label class="inline-flex items-center gap-1">
                    <input type="radio" name="tipo_ciclo" value="par"
                           class="rounded border-white/30 bg-slate-900/60"
                           @checked(old('tipo_ciclo','par') === 'par')>
                    <span>Par (2,4,6,8,10)</span>
                </label>

                <label class="inline-flex items-center gap-1">
                    <input type="radio" name="tipo_ciclo" value="impar"
                           class="rounded border-white/30 bg-slate-900/60"
                           @checked(old('tipo_ciclo') === 'impar')>
                    <span>Impar (1,3,5,7,9)</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block mb-1 font-medium">Archivo Excel</label>
            <input
                type="file"
                name="archivo"
                accept=".xlsx,.xls"
                class="w-full text-xs text-gray-300"
                required
            >
            <p class="text-xs text-gray-400 mt-1">
                Cada hoja debe tener la grilla con encabezados:
                <strong>HORA, LUNES, MARTES, MIERCOLES, JUEVES, VIERNES, SABADO</strong>.<br>
                Las celdas internas pueden contener el texto completo del curso (curso, grupo, aula, docente).
            </p>
        </div>

        <div class="pt-2 flex justify-end gap-2 border-t border-white/10">
            <a href="{{ route('horarios.index') }}"
               class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10">
                Cancelar
            </a>

            <button type="submit"
                    class="px-3 py-1.5 text-xs rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold">
                Importar
            </button>
        </div>
    </form>
</div>
@endsection
