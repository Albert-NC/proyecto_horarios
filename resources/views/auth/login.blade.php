<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Iniciar Sesión - SGCGAH</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .bg-pattern {
            background-image:
                linear-gradient(rgba(30, 58, 138, 0.95), rgba(30, 58, 138, 0.85)),
                url('/images/fondo-universidad.jpg');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }

        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>
</head>
<body class="bg-pattern min-h-screen flex items-center justify-center p-4">

<div class="w-full max-w-5xl flex flex-col lg:flex-row items-center gap-8">

    <!-- Panel Izquierdo -->
    <div class="lg:w-1/2 text-white text-center lg:text-left">
        <div class="animate-float mb-8">
            <div class="flex justify-center lg:justify-start mb-6">
                <img src="/images/logo_unt.png"
                     alt="Escudo UNT"
                     class="w-32 h-32 object-contain drop-shadow-2xl">
            </div>

            <h1 class="text-5xl font-bold mb-3 drop-shadow-lg">
                Universidad Nacional de Trujillo
            </h1>
            <div class="h-1 w-24 bg-blue-300 mx-auto lg:mx-0 mb-4 rounded-full"></div>
            <p class="text-xl mb-2 text-blue-100">
                Facultad de Ciencias Físicas y Matemáticas
            </p>
            <p class="text-lg text-blue-200">
                Escuela Profesional de Informática
            </p>
        </div>

        <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
            <h2 class="text-2xl font-bold mb-3">Sistema de Gestión de Carga Horaria</h2>
            <p class="text-blue-100 leading-relaxed">
                Plataforma integral para la administración, planificación y visualización
                de horarios académicos.
            </p>
        </div>
    </div>

    <!-- Panel Derecho - Formulario -->
    <div class="lg:w-1/2 w-full">
        <div class="glass-effect rounded-3xl p-10 max-w-md mx-auto">

            <div class="text-center mb-8">

                <!-- 🔵 AQUI ESTA EL CAMBIO QUE PEDISTE -->
                <div class="inline-block p-4 bg-white rounded-2xl shadow-xl mb-4">
                    <img src="/images/escudo-unt.png"
                         alt="Escudo UNT"
                         class="w-16 h-16 object-contain mx-auto">
                </div>
                <!-- FIN DEL CAMBIO -->

                <h2 class="text-3xl font-bold text-gray-800 mb-2">Bienvenido</h2>
                <p class="text-gray-600">Ingresa tus credenciales para continuar</p>
            </div>
            
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
        class="fixed bottom-5 left-1/2 transform -translate-x-1/2 
               bg-white border-2 border-black text-green-700 
               px-6 py-4 rounded-xl shadow-2xl font-semibold 
               flex items-center gap-3 z-50"
    >
        <!-- Ícono verde -->
        <svg class="w-6 h-6 text-green-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M5 13l4 4L19 7" />
        </svg>

        <!-- Mensaje -->
        <span>{{ session('success') }}</span>
    </div>
@endif

            <!-- Toast Notification -->
@if ($errors->any())
    @php
        $msg = $errors->first('login') ?? $errors->first();

        // Normalizamos posibles mensajes por defecto de Laravel
        if ($msg === 'auth.failed' || $msg === __('auth.failed') || $msg === 'These credentials do not match our records.') {
            // Puedes ajustar este texto a lo que más te guste:
            $msg = 'Correo, código o contraseña inválidos.';
        }
    @endphp

    <div 
        x-data="{ show: true }" 
        x-init="setTimeout(() => show = false, 4000)" 
        x-show="show"
        x-transition:enter="transform ease-out duration-300 transition"
        x-transition:enter-start="translate-y-10 opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transform ease-in duration-300 transition"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-10 opacity-0"
        class="fixed bottom-5 left-1/2 transform -translate-x-1/2 
               bg-white border-2 border-black text-red-600 
               px-6 py-4 rounded-xl shadow-2xl font-semibold 
               flex items-center gap-3 z-50"
    >
        <!-- Icono rojo -->
        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 8v4m0 4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
        </svg>

        <!-- Texto del error -->
        <span>{{ $msg }}</span>
    </div>
@endif



            <!-- Formulario -->
            <form method="POST" action="{{ route('login') }}" class="space-y-6">
                @csrf

                <!-- Campo Correo o Código -->
                <div>
                    <label for="login" class="block text-sm font-bold text-gray-700 mb-2">
                        Correo Institucional o Código
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                        </div>

                        <input
                            id="login"
                            type="text"
                            name="login"
                            value="{{ old('login') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-gray-700"
                            placeholder="correo@unt.edu.pe o 1234567890">
                    </div>
                </div>

                <!-- Contraseña -->
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                        Contraseña
                    </label>
                    <div class="relative">
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full pl-12 pr-12 py-3.5 bg-gray-50 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-gray-700"
                            placeholder="••••••••••">
                    </div>
                </div>

                <!-- Botón -->
                <button
                    type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200 flex items-center justify-center group">
                    <span>Iniciar Sesión</span>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-gray-200 text-center">
                <p class="text-xs text-gray-500">
                    Sistema Académico © {{ date('Y') }}
                </p>
            </div>
        </div>
    </div>

</div>

</body>
</html>
