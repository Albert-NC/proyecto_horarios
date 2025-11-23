@extends('layouts.panel')

@section('contenido')
<div
    class="rounded-2xl border px-6 py-5 mb-4 shadow-sm transition-colors"
    :class="tema === 'oscuro'
        ? 'glass-dark border-violet-500/30'
        : 'glass-light border-violet-200'"
>
    {{-- CABECERA --}}
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold"
                :class="tema === 'oscuro' ? 'text-gray-100' : 'text-slate-900'">
                Gestión de docentes
            </h2>
            <p class="text-sm"
               :class="tema === 'oscuro' ? 'text-gray-400' : 'text-slate-600'">
                Listado de profesores registrados en el sistema.
            </p>
        </div>

        <a href="{{ route('docentes.create') }}"
           class="px-4 py-2 text-sm font-semibold rounded-xl bg-fuchsia-600 hover:bg-fuchsia-700
                  text-white shadow-md shadow-fuchsia-500/40 transition">
            + Nuevo docente
        </a>
    </div>

    {{-- FILTRO POR SEDE --}}
    <div class="flex items-center gap-2 mb-4 text-sm">
        <span class="mr-2"
              :class="tema === 'oscuro' ? 'text-gray-400' : 'text-slate-600'">
            Filtrar por sede:
        </span>

        @php $lugarActual = request('lugar'); @endphp

        <a href="{{ route('docentes.index') }}"
           class="px-3 py-1 rounded-full border text-xs font-medium transition"
           :class="{{ $lugarActual ? '' : 'active' }}
                tema === 'oscuro'
                    ? '{{ $lugarActual ? 'border-white/15 text-gray-300 hover:bg-white/5' : 'bg-fuchsia-600 text-white border-fuchsia-500' }}'
                    : '{{ $lugarActual ? 'border-violet-200 text-slate-700 hover:bg-violet-50' : 'bg-fuchsia-600 text-white border-fuchsia-500' }}'">
            Todos
        </a>

        <a href="{{ route('docentes.index', ['lugar' => 'Trujillo']) }}"
           class="px-3 py-1 rounded-full border text-xs font-medium transition
                  {{ $lugarActual === 'Trujillo' ? 'is-active' : '' }}"
           :class="tema === 'oscuro'
                ? '{{ $lugarActual === 'Trujillo'
                        ? 'bg-fuchsia-600 text-white border-fuchsia-500'
                        : 'border-white/15 text-gray-300 hover:bg-white/5' }}'
                : '{{ $lugarActual === 'Trujillo'
                        ? 'bg-fuchsia-600 text-white border-fuchsia-500'
                        : 'border-violet-200 text-slate-700 hover:bg-violet-50' }}'">
            Trujillo
        </a>

        <a href="{{ route('docentes.index', ['lugar' => 'Valle']) }}"
           class="px-3 py-1 rounded-full border text-xs font-medium transition
                  {{ $lugarActual === 'Valle' ? 'is-active' : '' }}"
           :class="tema === 'oscuro'
                ? '{{ $lugarActual === 'Valle'
                        ? 'bg-fuchsia-600 text-white border-fuchsia-500'
                        : 'border-white/15 text-gray-300 hover:bg-white/5' }}'
                : '{{ $lugarActual === 'Valle'
                        ? 'bg-fuchsia-600 text-white border-fuchsia-500'
                        : 'border-violet-200 text-slate-700 hover:bg-violet-50' }}'">
            Valle
        </a>
    </div>

    {{-- MENSAJE FLASH --}}
    @if (session('success'))
        <div class="mb-4 bg-white border-2 border-emerald-500 text-emerald-700 px-4 py-2 rounded-xl text-sm font-semibold shadow">
            {{ session('success') }}
        </div>
    @endif

    {{-- TABLA --}}
    <div class="overflow-x-auto rounded-xl border"
         :class="tema === 'oscuro' ? 'border-white/10' : 'border-violet-100 bg-white/70'">
        <table class="min-w-full text-sm text-left">
            <thead
                class="border-b"
                :class="tema === 'oscuro'
                    ? 'bg-slate-900/80 border-white/10 text-gray-200'
                    : 'bg-violet-50 border-violet-100 text-slate-900'">
                <tr>
                    <th class="py-2.5 px-4">Código</th>
                    <th class="py-2.5 px-4">Nombre completo</th>
                    <th class="py-2.5 px-4">Correo institucional</th>
                    <th class="py-2.5 px-4">Lugar</th>
                </tr>
            </thead>
            <tbody
                :class="tema === 'oscuro' ? 'text-gray-100' : 'text-slate-900'">
                @forelse ($docentes as $docente)
                    <tr class="border-b last:border-0 transition"
                        :class="tema === 'oscuro'
                            ? 'border-white/5 hover:bg-white/5'
                            : 'border-violet-50 hover:bg-violet-50/80'">
                        <td class="py-2.5 px-4">
                            {{ $docente->user->codigo ?? '-' }}
                        </td>
                        <td class="py-2.5 px-4">
                            {{ $docente->user->name ?? '-' }}
                        </td>
                        <td class="py-2.5 px-4">
                            {{ $docente->user->email ?? '-' }}
                        </td>
                        <td class="py-2.5 px-4">
                            {{ $docente->lugar ?? '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="py-4 px-4 text-center"
                            :class="tema === 'oscuro' ? 'text-gray-400' : 'text-slate-500'">
                            No hay docentes registrados todavía.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $docentes->links() }}
    </div>
</div>

{{-- BOTÓN FLOTANTE PARA IMPORTAR EXCEL --}}
<button
    type="button"
    @click="$refs.modalImportarDocentes.showModal()"
    class="fixed bottom-6 z-40 transition-transform hover:scale-105"
    :class="menu === 'izquierda' ? 'right-6' : 'left-6'"
>
    <div
        class="w-12 h-12 rounded-full bg-fuchsia-600 hover:bg-fuchsia-700 shadow-2xl shadow-fuchsia-500/50
               flex items-center justify-center text-white text-2xl">
        +
    </div>
</button>

{{-- MODAL IMPORTAR EXCEL DOCENTES --}}
<dialog
    x-ref="modalImportarDocentes"
    class="rounded-2xl border w-full max-w-md bg-slate-900 text-gray-100 border-white/10"
>
    <form method="dialog">
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
            <h3 class="text-sm font-semibold">Importar docentes desde Excel</h3>
            <button type="submit" class="text-gray-400 hover:text-gray-200 text-xl leading-none">&times;</button>
        </div>
    </form>

    <form
        method="POST"
        action="{{ route('docentes.importar') }}"
        enctype="multipart/form-data"
        class="px-4 py-4 space-y-4"
    >
        @csrf

        <p class="text-xs text-gray-300">
            Formato esperado: columnas
            <strong>Código</strong>, <strong>Nombre</strong>,
            <strong>Correo</strong>, <strong>Contraseña</strong>,
            <strong>Lugar</strong> (Trujillo / Valle).
        </p>

        <input
            type="file"
            name="archivo"
            accept=".xlsx,.xls,.csv"
            class="w-full text-xs text-gray-200 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg
                   file:border-0 file:bg-fuchsia-600 file:text-white hover:file:bg-fuchsia-700"
            required
        >

        @error('archivo')
            <p class="text-xs text-red-400">{{ $message }}</p>
        @enderror

        <div class="flex justify-end gap-2 pt-2 border-t border-white/10">
            <button
                type="button"
                class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10"
                @click="$refs.modalImportarDocentes.close()"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="px-3 py-1.5 text-xs rounded-lg bg-fuchsia-600 hover:bg-fuchsia-700 text-white font-semibold"
            >
                Importar
            </button>
        </div>
    </form>
</dialog>
@endsection
