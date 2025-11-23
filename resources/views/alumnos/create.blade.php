@extends('layouts.panel')

@section('contenido')
<div
    class="max-w-xl mx-auto rounded-xl border border-white/10 px-6 py-5"
    :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
>
    <h2 class="text-lg font-semibold mb-1">Nuevo alumno</h2>
    <p class="text-sm text-gray-400 mb-4">
        Registra un alumno que podrá iniciar sesión con su código o correo.
    </p>

    @if ($errors->any())
        <div class="mb-4 bg-white border-2 border-black text-red-700 px-4 py-2 rounded-lg text-sm">
            <p class="font-semibold mb-1">Hay errores en el formulario:</p>
            <ul class="list-disc ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('alumnos.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-xs font-semibold mb-1">Código</label>
            <input
                type="text"
                name="codigo"
                value="{{ old('codigo') }}"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm"
                required
            >
        </div>

        <div>
            <label class="block text-xs font-semibold mb-1">Nombre completo</label>
            <input
                type="text"
                name="nombre"
                value="{{ old('nombre') }}"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm"
                required
            >
        </div>

        <div>
            <label class="block text-xs font-semibold mb-1">Correo institucional</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm"
                required
            >
        </div>

        <div>
            <label class="block text-xs font-semibold mb-1">Contraseña</label>
            <input
                type="password"
                name="password"
                class="w-full px-3 py-2 rounded-lg bg-slate-900/60 border border-white/10 text-sm"
                required
            >
            <p class="text-[11px] text-gray-400 mt-1">
                El alumno podrá cambiarla luego desde la opción de cambiar contraseña.
            </p>
        </div>

        <div class="flex justify-end gap-2 mt-4">
            <a href="{{ route('alumnos.index') }}"
               class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10">
                Cancelar
            </a>

            <button
                type="submit"
                class="px-4 py-1.5 text-xs rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold"
            >
                Guardar alumno
            </button>
        </div>
    </form>
</div>
@endsection
