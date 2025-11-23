@extends('layouts.panel')

@section('contenido')
<div
    class="rounded-xl border border-white/10 px-6 py-4 mb-4 max-w-xl"
    :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
>
    <h2 class="text-lg font-semibold mb-1">Editar docente</h2>
    <p class="text-sm text-gray-400 mb-4">
        Aquí puedes actualizar el nombre, categoría, horas y modalidad del docente.
        El código, correo y contraseña no se pueden modificar desde esta pantalla.
    </p>

    @if ($errors->any())
        <div class="mb-4 bg-white border-2 border-black text-red-700 px-4 py-2 rounded-lg text-sm font-semibold">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Info no editable --}}
    <div class="mb-4 text-xs text-gray-300 space-y-1">
        <p><span class="font-semibold text-gray-200">Código:</span> {{ $docente->user->codigo ?? '-' }}</p>
        <p><span class="font-semibold text-gray-200">Correo:</span> {{ $docente->user->email ?? '-' }}</p>
    </div>

    <form method="POST" action="{{ route('carga-horaria-docentes.update', $docente) }}" class="space-y-4">
        @csrf
        @method('PUT')

        {{-- Nombre --}}
        <div>
            <label class="block text-xs font-semibold text-gray-300 mb-1">
                Nombre completo
            </label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre', $docente->user->name) }}"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-purple-500"
                required
            >
        </div>

        {{-- Categoría --}}
        <div>
            <label class="block text-xs font-semibold text-gray-300 mb-1">
                Categoría
            </label>
            <select
                name="categoria"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-purple-500"
                required
            >
                <option value="asociado" {{ old('categoria', $docente->categoria) === 'asociado' ? 'selected' : '' }}>
                    Asociado
                </option>
                <option value="principal" {{ old('categoria', $docente->categoria) === 'principal' ? 'selected' : '' }}>
                    Principal
                </option>
                <option value="auxiliar" {{ old('categoria', $docente->categoria) === 'auxiliar' ? 'selected' : '' }}>
                    Auxiliar
                </option>
            </select>
        </div>

        {{-- Horas --}}
        <div>
            <label class="block text-xs font-semibold text-gray-300 mb-1">
                Horas
            </label>
            <input
                type="number"
                name="horas"
                value="{{ old('horas', $docente->horas ?? 0) }}"
                min="0"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-purple-500"
                required
            >
        </div>

        {{-- Modalidad --}}
        <div>
            <label class="block text-xs font-semibold text-gray-300 mb-1">
                Modalidad
            </label>
            <select
                name="modalidad"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-purple-500"
                required
            >
                <option value="tiempo completo"
                    {{ old('modalidad', $docente->modalidad) === 'tiempo completo' ? 'selected' : '' }}>
                    Tiempo Completo
                </option>
                <option value="tiempo parcial"
                    {{ old('modalidad', $docente->modalidad) === 'tiempo parcial' ? 'selected' : '' }}>
                    Tiempo Parcial
                </option>
            </select>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-white/10">
            <a href="{{ route('carga-horaria-docentes.index') }}"
               class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10">
                Cancelar
            </a>

            <button
                type="submit"
                class="px-3 py-1.5 text-xs rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold"
            >
                Guardar cambios
            </button>
        </div>
    </form>
</div>
@endsection
