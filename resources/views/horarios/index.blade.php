@extends('layouts.panel')

@section('contenido')
<div
    class="rounded-xl border border-white/10 px-6 py-4 mb-4"
    :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold">Gestión de horarios</h2>
            <p class="text-sm text-gray-400">
                Aquí se visualizan los horarios por ciclo y sección de la carrera de Ingeniería Informática.
            </p>
        </div>

        {{-- NUEVO HORARIO MANUAL --}}
        <a href="{{ route('horarios.create') }}"
           class="px-4 py-2 text-sm font-semibold rounded-lg bg-purple-600 hover:bg-purple-700 text-white">
            + Nuevo horario
        </a>
    </div>

    {{-- FILTROS DINÁMICOS --}}
    @php
        // Obtener ciclos y secciones que realmente existen en la BD
        $ciclosExistentes = \App\Models\Horario::where('carrera', 'Ingeniería Informática')
            ->distinct()
            ->orderBy('ciclo')
            ->pluck('ciclo');
        
        $seccionesExistentes = \App\Models\Horario::where('carrera', 'Ingeniería Informática')
            ->when(request('ciclo'), fn($q) => $q->where('ciclo', request('ciclo')))
            ->distinct()
            ->orderBy('seccion')
            ->pluck('seccion');
    @endphp

    @if($ciclosExistentes->count() > 0)
        <div class="flex flex-wrap items-center gap-3 mb-4 text-xs">
            <div class="flex items-center gap-2">
                <span class="text-gray-400">Ciclo:</span>

                @foreach ($ciclosExistentes as $c)
                    <a href="{{ route('horarios.index', ['ciclo' => $c]) }}"
                       class="px-3 py-1 rounded-full border
                              {{ request('ciclo') == $c ? 'bg-purple-600 border-purple-500 text-white'
                                                         : 'border-white/10 text-gray-300 hover:bg-white/5' }}">
                        Ciclo {{ $c }}
                    </a>
                @endforeach
            </div>

            @if(request('ciclo') && $seccionesExistentes->count() > 0)
                <div class="flex items-center gap-2">
                    <span class="text-gray-400">Sección:</span>

                    <a href="{{ route('horarios.index', ['ciclo' => request('ciclo')]) }}"
                       class="px-3 py-1 rounded-full border
                              {{ !request('seccion') ? 'bg-purple-600 border-purple-500 text-white'
                                                      : 'border-white/10 text-gray-300 hover:bg-white/5' }}">
                        Todas
                    </a>

                    @foreach ($seccionesExistentes as $s)
                        <a href="{{ route('horarios.index', ['ciclo' => request('ciclo'), 'seccion' => $s]) }}"
                           class="px-3 py-1 rounded-full border
                                  {{ request('seccion') == $s ? 'bg-purple-600 border-purple-500 text-white'
                                                               : 'border-white/10 text-gray-300 hover:bg-white/5' }}">
                            Sección {{ $s }}
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    @else
        <div class="mb-4 bg-yellow-500/10 border border-yellow-500/40 text-yellow-200 px-4 py-3 rounded-lg text-xs">
            <p class="font-semibold mb-1">⚠️ No hay horarios creados todavía</p>
            <p>Haz clic en <strong>"+ Nuevo horario"</strong> o importa un Excel para comenzar.</p>
        </div>
    @endif

    {{-- MENSAJE DE ÉXITO --}}
    @if (session('success'))
        <div class="mt-4 mb-4 bg-emerald-500/10 border border-emerald-500/40 text-emerald-200 px-4 py-2 rounded-lg text-xs">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABLA DE HORARIOS --}}
    @if(request('ciclo'))
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm text-left table-fixed">
                <thead class="text-gray-300 border-b border-white/10">
                    <tr>
                        {{-- Columna de hora con ancho fijo y monoespaciada --}}
                        <th class="w-40 py-2 pr-4 font-medium text-xs uppercase tracking-wide">
                            Hora
                        </th>
                        <th class="py-2 px-2 font-medium text-xs uppercase tracking-wide">Lunes</th>
                        <th class="py-2 px-2 font-medium text-xs uppercase tracking-wide">Martes</th>
                        <th class="py-2 px-2 font-medium text-xs uppercase tracking-wide">Miércoles</th>
                        <th class="py-2 px-2 font-medium text-xs uppercase tracking-wide">Jueves</th>
                        <th class="py-2 px-2 font-medium text-xs uppercase tracking-wide">Viernes</th>
                        <th class="py-2 px-2 font-medium text-xs uppercase tracking-wide">Sábado</th>
                    </tr>
                </thead>
                <tbody class="text-gray-200">
                    @foreach ($bloques as $fila)
                        @php
                            $hi = \Carbon\Carbon::createFromFormat('H:i:s', $fila['hora_inicio']);
                            $hf = \Carbon\Carbon::createFromFormat('H:i:s', $fila['hora_fin']);
                        @endphp
                        <tr class="border-b border-white/5 hover:bg-white/5">
                            {{-- Columna hora bien alineada --}}
                            <td class="w-40 px-3 py-3 align-middle">
                                <div class="font-mono text-xs text-gray-200 whitespace-nowrap leading-tight">
                                    {{ $hi->format('g:i a') }} – {{ $hf->format('g:i a') }}
                                </div>
                            </td>

                            @foreach (['lunes','martes','miercoles','jueves','viernes','sabado'] as $dia)
                                @php $h = $fila[$dia] ?? null; @endphp
                                <td class="px-2 py-3 align-top">
                                    @if ($h)
                                        <div class="rounded-lg bg-slate-800/70 border border-white/10 px-3 py-2 text-xs leading-snug">
                                            {!! nl2br(e($h->descripcion)) !!}
                                            <div class="mt-1 text-[10px] text-gray-400">
                                                Ciclo {{ $h->ciclo }} – Sección {{ $h->seccion }}
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-gray-400 text-sm">👈 Selecciona un ciclo para ver los horarios</p>
        </div>
    @endif

    {{-- SECCIÓN DE RESOLUCIONES PDF --}}
    @if ($resoluciones && $resoluciones->count() > 0)
        <div class="mt-8 pt-6 border-t border-white/10">
            <h3 class="text-sm font-semibold mb-4">📄 Resoluciones de horarios</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($resoluciones as $key => $grupo)
                    @foreach ($grupo as $resolucion)
                        <div class="rounded-lg border border-white/10 bg-slate-800/50 p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <h4 class="text-sm font-semibold">
                                        Ciclo {{ $resolucion->ciclo }}{{ $resolucion->seccion ? ' - Sección ' . $resolucion->seccion : '' }}
                                    </h4>
                                    <p class="text-xs text-gray-400 mt-1">
                                        {{ $resolucion->created_at->format('d/m/Y H:i') }}
                                    </p>
                                </div>
                                
                                <form method="POST" action="{{ route('horarios.resolucion.destroy', $resolucion) }}"
                                      onsubmit="return confirm('¿Eliminar esta resolución?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-400 hover:text-red-300 text-xs">
                                        🗑️
                                    </button>
                                </form>
                            </div>

                            @if ($resolucion->comentario)
                                <p class="text-xs text-gray-300 mb-3 italic">
                                    "{{ $resolucion->comentario }}"
                                </p>
                            @endif

                            <a href="{{ Storage::url($resolucion->pdf_path) }}" 
                               target="_blank"
                               class="inline-flex items-center gap-2 px-3 py-1.5 text-xs rounded-lg 
                                      bg-purple-600 hover:bg-purple-700 text-white font-semibold">
                                <span>📄</span>
                                Ver PDF
                            </a>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
    @endif

    {{-- BOTÓN PARA SUBIR NUEVA RESOLUCIÓN --}}
    <div class="mt-6 pt-4 border-t border-white/10">
        <button
            type="button"
            @click="$refs.modalResolucion.showModal()"
            class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">
            📄 Subir resolución PDF
        </button>
    </div>
</div>

{{-- BOTÓN FLOTANTE: IMPORTAR DESDE EXCEL --}}
<button
    type="button"
    @click="$refs.modalImportarHorarios.showModal()"
    class="fixed bottom-6"
    :class="menu === 'izquierda' ? 'right-6' : 'left-6'"
>
    <div
        class="w-12 h-12 rounded-full bg-purple-600 hover:bg-purple-700 shadow-2xl
               flex items-center justify-center text-white text-2xl"
    >
        +
    </div>
</button>

{{-- MODAL IMPORTAR EXCEL --}}
<dialog
    x-ref="modalImportarHorarios"
    class="rounded-xl border border-white/10 w-full max-w-md bg-slate-900 text-gray-100"
>
    <form method="dialog">
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
            <h3 class="text-sm font-semibold">Importar horarios desde Excel</h3>
            <button type="submit" class="text-gray-400 hover:text-gray-200 text-xl">&times;</button>
        </div>
    </form>

    <form
        method="POST"
        action="{{ route('horarios.import') }}"
        enctype="multipart/form-data"
        class="px-4 py-4 space-y-4"
    >
        @csrf

        <p class="text-xs text-gray-400">
            Sube un archivo Excel con varias hojas. Cada hoja debe tener formato
            <strong>2A</strong>, <strong>3B</strong>, etc. (ciclo + sección).
        </p>

        <div>
            <label class="block mb-2 text-xs font-semibold">Tipo de ciclo</label>
            <div class="flex gap-3 text-xs">
                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="tipo_ciclo" value="par"
                           class="rounded border-white/30 bg-slate-900/60"
                           checked>
                    <span>Par (2,4,6,8,10)</span>
                </label>

                <label class="inline-flex items-center gap-2">
                    <input type="radio" name="tipo_ciclo" value="impar"
                           class="rounded border-white/30 bg-slate-900/60">
                    <span>Impar (1,3,5,7,9)</span>
                </label>
            </div>
        </div>

        <div>
            <label class="block mb-2 text-xs font-semibold">Archivo Excel</label>
            <input
                type="file"
                name="archivo"
                accept=".xlsx,.xls"
                class="w-full text-xs text-gray-300"
                required
            >
            <p class="text-[11px] text-gray-400 mt-1">
                Formato esperado: columna <strong>HORA</strong> y columnas
                <strong>LUNES, MARTES, MIERCOLES, JUEVES, VIERNES, SABADO</strong>.
            </p>
        </div>

        @error('archivo')
            <p class="text-xs text-red-400">{{ $message }}</p>
        @enderror

        <div class="flex justify-end gap-2 pt-2 border-t border-white/10">
            <button
                type="button"
                class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10"
                @click="$refs.modalImportarHorarios.close()"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="px-3 py-1.5 text-xs rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold"
            >
                Importar
            </button>
        </div>
    </form>
</dialog>

{{-- MODAL SUBIR RESOLUCIÓN --}}
<dialog
    x-ref="modalResolucion"
    class="rounded-xl border border-white/10 w-full max-w-md bg-slate-900 text-gray-100"
>
    <form method="dialog">
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
            <h3 class="text-sm font-semibold">Subir resolución PDF</h3>
            <button type="submit" class="text-gray-400 hover:text-gray-200 text-xl">&times;</button>
        </div>
    </form>

    <form
        method="POST"
        action="{{ route('horarios.resolucion.store') }}"
        enctype="multipart/form-data"
        class="px-4 py-4 space-y-4"
    >
        @csrf

        <div>
            <label class="block mb-2 text-xs font-semibold">Ciclo</label>
            <select name="ciclo" class="w-full bg-slate-900/60 border border-white/10 rounded-lg px-3 py-2 text-sm" required>
                @for ($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}">Ciclo {{ $i }}</option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block mb-2 text-xs font-semibold">Sección (opcional)</label>
            <select name="seccion" class="w-full bg-slate-900/60 border border-white/10 rounded-lg px-3 py-2 text-sm">
                <option value="">Todas las secciones</option>
                <option value="A">Sección A</option>
                <option value="B">Sección B</option>
                <option value="R">Sección R</option>
                <option value="U">Sección U</option>
            </select>
        </div>

        <div>
            <label class="block mb-2 text-xs font-semibold">Archivo PDF</label>
            <input
                type="file"
                name="pdf"
                accept=".pdf"
                class="w-full text-xs text-gray-300"
                required
            >
        </div>

        <div>
            <label class="block mb-2 text-xs font-semibold">Comentario (opcional)</label>
            <textarea
                name="comentario"
                rows="3"
                class="w-full bg-slate-900/60 border border-white/10 rounded-lg px-3 py-2 text-sm"
                placeholder="Ejemplo: Resolución Decanal N° 123-2024"
            ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-white/10">
            <button
                type="button"
                class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10"
                @click="$refs.modalResolucion.close()"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="px-3 py-1.5 text-xs rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
            >
                Subir resolución
            </button>
        </div>
    </form>
</dialog>
@endsection