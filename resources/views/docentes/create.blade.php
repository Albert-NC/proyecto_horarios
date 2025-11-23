@extends('layouts.panel')

@section('contenido')
<div
    class="rounded-xl border border-white/10 px-6 py-4 mb-4"
    :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold">Nuevo docente</h2>
            <p class="text-sm text-gray-400">Registra un profesor y sus datos académicos.</p>
        </div>

        <a href="{{ route('docentes.index') }}"
           class="text-sm text-gray-300 hover:text-white underline">
            Volver al listado
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-white border-2 border-black text-red-700 px-4 py-2 rounded-lg text-sm">
            <p class="font-semibold mb-1">Revisa los campos marcados:</p>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('docentes.store') }}" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Nombre completo</label>
                <input type="text" name="nombre" value="{{ old('nombre') }}"
                       class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Correo institucional</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Código (opcional)</label>
                <input type="text" name="codigo" value="{{ old('codigo') }}"
                       class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100">
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Contraseña inicial</label>
                <input type="password" name="password"
                       class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Categoría</label>
                <select name="categoria"
                        class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100"
                        required>
                    <option value="" disabled {{ old('categoria') ? '' : 'selected' }}>Seleccione...</option>
                    <option value="asociado"  {{ old('categoria') === 'asociado' ? 'selected' : '' }}>Asociado</option>
                    <option value="principal" {{ old('categoria') === 'principal' ? 'selected' : '' }}>Principal</option>
                    <option value="auxiliar"  {{ old('categoria') === 'auxiliar' ? 'selected' : '' }}>Auxiliar</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Modalidad</label>
                <select name="modalidad"
                        class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100"
                        required>
                    <option value="" disabled {{ old('modalidad') ? '' : 'selected' }}>Seleccione...</option>
                    <option value="tiempo completo" {{ old('modalidad') === 'tiempo completo' ? 'selected' : '' }}>Tiempo completo</option>
                    <option value="tiempo parcial"   {{ old('modalidad') === 'tiempo parcial' ? 'selected' : '' }}>Tiempo parcial</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Horas</label>
                <input type="number" name="horas" min="0" value="{{ old('horas', 0) }}"
                       class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100"
                       required>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-300 mb-1">Estado</label>
                <select name="estado"
                        class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm text-gray-100"
                        required>
                    <option value="activo"   {{ old('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                    <option value="inactivo" {{ old('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                </select>
            </div>
        </div>

        <div class="pt-4 flex justify-end gap-3">
            <a href="{{ route('docentes.index') }}"
               class="px-4 py-2 text-sm rounded-lg border border-white/10 text-gray-200 hover:bg-white/10">
                Cancelar
            </a>
            <button type="submit"
                    class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">
                Guardar docente
            </button>
        </div>
    </form>
</div>
@endsection
