@extends('layouts.panel')

@section('contenido')
    <div
        class="rounded-xl border border-white/10 px-6 py-4 mb-4"
        :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
    >
        <h1 class="text-xl font-semibold">
            Panel de administración
        </h1>
        <p class="text-sm mt-1" :class="tema === 'oscuro' ? 'text-gray-400' : 'text-gray-600'">
            Bienvenido, {{ auth()->user()->name }}.
        </p>
    </div>

    <div
        class="rounded-xl border border-white/10 px-6 py-6 mt-4"
        :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
    >
        <p class="text-sm" :class="tema === 'oscuro' ? 'text-gray-300' : 'text-gray-700'">
            Aquí colocaremos las funciones específicas de este rol (gestión de docentes, carga horaria, horarios, etc.).
        </p>
    </div>
@endsection
