@extends('layouts.panel')

@section('contenido')
<div
    class="rounded-xl border border-white/10 px-6 py-4 mb-4 max-w-xl"
    :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
>
    <h2 class="text-lg font-semibold mb-1">Registrar carga horaria</h2>
    <p class="text-sm text-gray-400 mb-4">
        Asigna categoría, horas y modalidad a un docente usando su código institucional.
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

    <form method="POST" action="{{ route('carga-horaria-docentes.store') }}" class="space-y-4">
        @csrf

        {{-- Código --}}
        <div>
            <label class="block text-xs font-semibold text-gray-300 mb-1">
                Código del docente
            </label>
            <input
                type="text"
                name="codigo"
                value="{{ old('codigo') }}"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm
                       focus:outline-none focus:ring-2 focus:ring-purple-500"
                placeholder="000000DOC001"
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
                <option value="">Seleccione...</option>
                <option value="asociado" {{ old('categoria') === 'asociado' ? 'selected' : '' }}>Asociado</option>
                <option value="principal" {{ old('categoria') === 'principal' ? 'selected' : '' }}>Principal</option>
                <option value="auxiliar" {{ old('categoria') === 'auxiliar' ? 'selected' : '' }}>Auxiliar</option>
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
                value="{{ old('horas', 0) }}"
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
                <option value="">Seleccione...</option>
                <option value="tiempo completo" {{ old('modalidad') === 'tiempo completo' ? 'selected' : '' }}>
                    Tiempo Completo
                </option>
                <option value="tiempo parcial" {{ old('modalidad') === 'tiempo parcial' ? 'selected' : '' }}>
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
                Guardar
            </button>
        </div>
    </form>
</div>
@endsection
