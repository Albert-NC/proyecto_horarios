<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SGCGAH - Panel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine para toasts y ajustes --}}
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        .bg-dashboard {
            background: radial-gradient(circle at top left, #1d4ed8 0, #020617 45%, #020617 100%);
        }
        .glass-dark {
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(16px);
        }
        .glass-light {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
        }
    </style>

    <script>
        function dashboardConfig() {
            return {
                ajustes_abierto: false,
                tema: localStorage.getItem('tema_dashboard') || 'oscuro',
                menu: localStorage.getItem('menu_dashboard') || 'izquierda',
                menu_abierto: true, // controla colapsado

                setTema(valor) {
                    this.tema = valor;
                    localStorage.setItem('tema_dashboard', valor);
                },

                setMenu(valor) {
                    this.menu = valor;
                    localStorage.setItem('menu_dashboard', valor);
                },

                init() {
                    // espacio para lógica futura
                }
            }
        }
    </script>
</head>
<body
    x-data="dashboardConfig()"
    x-init="init()"
    :class="tema === 'oscuro' ? 'bg-dashboard text-gray-100' : 'bg-gray-100 text-slate-900'"
    class="min-h-screen"
>

@php
    $user = auth()->user();

    // Detectamos qué ruta está activa
    $esDashboard    = request()->routeIs('dashboard');
    $esCursos       = request()->routeIs('cursos.*');
    $esDocentes     = request()->routeIs('docentes.*');
    $esHorarios     = request()->routeIs('horarios.*');
    $esCargaHoraria = request()->routeIs('carga-horaria-docentes.*');

    // Clases para tema oscuro / claro según si está activo o no

    // DASHBOARD
    $darkDashboard  = $esDashboard
        ? 'bg-white/10 text-white shadow-sm'
        : 'text-gray-300 hover:bg-white/5';
    $lightDashboard = $esDashboard
        ? 'bg-slate-900/10 text-slate-900 shadow-sm'
        : 'text-slate-700 hover:bg-slate-200';

    // CURSOS
    $darkCursos  = $esCursos
        ? 'bg-white/10 text-white shadow-sm'
        : 'text-gray-300 hover:bg-white/5';
    $lightCursos = $esCursos
        ? 'bg-slate-900/10 text-slate-900 shadow-sm'
        : 'text-slate-700 hover:bg-slate-200';

    // DOCENTES
    $darkDocentes  = $esDocentes
        ? 'bg-white/10 text-white shadow-sm'
        : 'text-gray-300 hover:bg-white/5';
    $lightDocentes = $esDocentes
        ? 'bg-slate-900/10 text-slate-900 shadow-sm'
        : 'text-slate-700 hover:bg-slate-200';

    // HORARIOS
    $darkHorarios  = $esHorarios
        ? 'bg-white/10 text-white shadow-sm'
        : 'text-gray-300 hover:bg-white/5';
    $lightHorarios = $esHorarios
        ? 'bg-slate-900/10 text-slate-900 shadow-sm'
        : 'text-slate-700 hover:bg-slate-200';

    // CARGA HORARIA DOCENTES
    $darkCarga  = $esCargaHoraria
        ? 'bg-white/10 text-white shadow-sm'
        : 'text-gray-300 hover:bg-white/5';
    $lightCarga = $esCargaHoraria
        ? 'bg-slate-900/10 text-slate-900 shadow-sm'
        : 'text-slate-700 hover:bg-slate-200';
@endphp

{{-- TOAST DE MENSAJE DE ÉXITO --}}
@if (session('success'))
    <div
        x-data="{ show: true }"
        x-init="setTimeout(() => show = false, 3500)"
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-10 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform ease-in duration-300 transition"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-10 opacity-0"
        class="fixed bottom-5 left-1/2 -translate-x-1/2 bg-white border-2 border-black text-green-700
               px-6 py-3 rounded-xl shadow-2xl font-semibold flex items-center gap-3 z-50"
    >
        <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 13l4 4L19 7" />
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

<div class="flex min-h-screen" :class="menu === 'derecha' ? 'flex-row-reverse' : 'flex-row'">

    {{-- SIDEBAR --}}
    <aside
        class="border-r border-white/10 flex flex-col transition-all duration-300"
        :class="[
            tema === 'oscuro' ? 'glass-dark' : 'glass-light',
            menu_abierto ? 'w-72' : 'w-16'
        ]"
    >
        {{-- Header del sidebar: logo + título + botón 3 rayas --}}
        <div class="flex items-center px-3 py-4 border-b border-white/10">
            {{-- Logo + textos (solo cuando está abierto) --}}
            <div class="flex items-center gap-3 flex-1" x-show="menu_abierto" x-transition>
                <div class="w-10 h-10 bg-indigo-500 flex items-center justify-center rounded-lg shadow-md">
                    <img src="/images/escudo-unt.png" alt="UNT" class="w-8 h-8 object-contain">
                </div>
                <div class="truncate">
                    <p class="text-sm font-semibold tracking-wide">SGCGAH</p>
                    <p class="text-xs text-gray-400">Gestión de Carga Horaria</p>
                </div>
            </div>

            {{-- Botón 3 rayas: cuando está colapsado queda solo él --}}
            <button
                type="button"
                @click="menu_abierto = !menu_abierto"
                class="p-2 rounded-md hover:bg-white/10 transition"
                :class="menu_abierto ? '' : 'mx-auto'"
                title="Mostrar/ocultar menú"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        {{-- Usuario --}}
        <div class="px-5 py-4 border-b border-white/10" x-show="menu_abierto" x-transition>
            <p class="text-sm font-semibold truncate">
                {{ $user->name }}
            </p>
            <p class="text-xs text-indigo-300 mt-1 uppercase tracking-wide">
                @switch($user->role)
                    @case('admin') Administrador @break
                    @case('profesor') Profesor @break
                    @case('alumno') Alumno @break
                    @default Usuario
                @endswitch
            </p>
        </div>

        {{-- MENÚ LATERAL --}}
        <nav class="flex-1 px-3 py-4 space-y-1 text-sm" x-show="menu_abierto" x-transition>
            {{-- DASHBOARD --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
               :class="tema === 'oscuro' ? '{{ $darkDashboard }}' : '{{ $lightDashboard }}'">
                <span class="inline-flex w-6 justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 
                                 1h-3m-6 0h6" />
                    </svg>
                </span>
                <span>Dashboard</span>
            </a>

            @if($user->role === 'admin')
                <p class="mt-4 mb-2 px-3 text-xs text-gray-400 uppercase tracking-wide">Administración</p>

                {{-- GESTIÓN DE CURSOS --}}
                <a href="{{ route('cursos.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                   :class="tema === 'oscuro' ? '{{ $darkCursos }}' : '{{ $lightCursos }}'">
                    <span class="inline-flex w-6 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    </span>
                    <span>Gestión de cursos</span>
                </a>

                {{-- GESTIÓN DE DOCENTES --}}
                <a href="{{ route('docentes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                   :class="tema === 'oscuro' ? '{{ $darkDocentes }}' : '{{ $lightDocentes }}'">
                    <span class="inline-flex w-6 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 20h5V4H2v16h5m10 0V10m0 10H7m0 0V8" />
                        </svg>
                    </span>
                    <span>Gestión de docentes</span>
                </a>

                {{-- GESTIÓN DE HORARIOS --}}
                <a href="{{ route('horarios.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                   :class="tema === 'oscuro' ? '{{ $darkHorarios }}' : '{{ $lightHorarios }}'">
                    <span class="inline-flex w-6 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </span>
                    <span>Gestión de horarios</span>
                </a>
                
                {{-- CARGA HORARIA DE DOCENTES --}}
                <a href="{{ route('carga-horaria-docentes.index') }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg transition"
                   :class="tema === 'oscuro' ? '{{ $darkCarga }}' : '{{ $lightCarga }}'">
                    <span class="inline-flex w-6 justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M4 4h16v4H4zm0 6h10v4H4zm0 6h7v4H4z" />
                        </svg>
                    </span>
                    <span>Carga horaria de docentes</span>
                </a>

            @endif
            <a href="{{ route('alumnos.index') }}"
                class="flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-white/5 transition">
            <span class="inline-flex w-6 justify-center">
             <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 12h14M5 6h14M5 18h7" />
                    </svg>
                    </span>
                <span>Gestión de alumnos</span>
            </a>

            {{-- Aquí luego pondremos menús para profesor/alumno si hace falta --}}
        </nav>

        {{-- AJUSTES --}}
        <div class="px-3 pt-3 border-t border-white/10" x-show="menu_abierto" x-transition>
            <button
                type="button"
                @click="ajustes_abierto = true"
                class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-sm
                       bg-indigo-600 hover:bg-indigo-700 rounded-lg transition shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 4.354V2m0 20v-2.354M4.354 12H2m20 0h-2.354M5.636 5.636l-1.768-1.768M20.132 20.132l-1.768-1.768M5.636 18.364l-1.768 1.768M20.132 3.868l-1.768 1.768" />
                </svg>
                <span>Ajustes</span>
            </button>
        </div>

        {{-- Cerrar sesión --}}
        <div class="px-3 py-4 border-t border-white/10" x-show="menu_abierto" x-transition>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button
                    type="submit"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2.5 text-sm
                           bg-red-600 hover:bg-red-700 rounded-lg transition shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 
                                 01-2 2H6a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v1" />
                    </svg>
                    <span>Cerrar sesión</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- CONTENIDO PRINCIPAL --}}
    <main class="flex-1 p-6 overflow-y-auto transition-all duration-300">
        @yield('contenido')
    </main>

</div>

{{-- MODAL DE AJUSTES --}}
<div
    x-show="ajustes_abierto"
    x-transition.opacity
    class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center px-4"
>
    <div
        class="w-full max-w-md p-6 rounded-xl border border-white/10"
        :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
        @click.away="ajustes_abierto = false"
    >
        <h2 class="text-lg font-semibold mb-4">Ajustes del sistema</h2>

        {{-- TEMA --}}
        <div class="mb-6">
            <p class="text-sm font-semibold mb-2" :class="tema === 'oscuro' ? 'text-gray-200' : 'text-gray-800'">
                Tema
            </p>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="setTema('claro')"
                    class="px-4 py-2 rounded-lg border border-white/10 hover:bg-white/10 transition"
                    :class="tema === 'claro' ? 'bg-white text-black' : ''"
                >
                    Claro
                </button>

                <button
                    type="button"
                    @click="setTema('oscuro')"
                    class="px-4 py-2 rounded-lg border border-white/10 hover:bg-white/10 transition"
                    :class="tema === 'oscuro' ? 'bg-white text-black' : ''"
                >
                    Oscuro
                </button>
            </div>
        </div>

        {{-- POSICIÓN DEL MENÚ --}}
        <div class="mb-6">
            <p class="text-sm font-semibold mb-2" :class="tema === 'oscuro' ? 'text-gray-200' : 'text-gray-800'">
                Posición del menú
            </p>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    @click="setMenu('izquierda')"
                    class="px-4 py-2 rounded-lg border border-white/10 hover:bg-white/10 transition"
                    :class="menu === 'izquierda' ? 'bg-white text-black' : ''"
                >
                    Izquierda
                </button>

                <button
                    type="button"
                    @click="setMenu('derecha')"
                    class="px-4 py-2 rounded-lg border border-white/10 hover:bg-white/10 transition"
                    :class="menu === 'derecha' ? 'bg-white text-black' : ''"
                >
                    Derecha
                </button>
            </div>
        </div>

        <button
            type="button"
            @click="ajustes_abierto = false"
            class="w-full mt-4 py-2 bg-red-600 hover:bg-red-700 rounded-lg text-white"
        >
            Cerrar
        </button>
    </div>
</div>

</body>
</html>
