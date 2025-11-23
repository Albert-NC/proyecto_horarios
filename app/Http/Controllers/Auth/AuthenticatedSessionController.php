<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\SolicitudLogin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

public function store(SolicitudLogin $request): RedirectResponse
{
    // Autenticamos con nuestra lógica personalizada
    $request->autenticar();

    // Regeneramos sesión por seguridad
    $request->session()->regenerate();

    // Obtenemos el usuario autenticado
    $usuario = $request->user(); // o: $usuario = \Illuminate\Support\Facades\Auth::user();

    // Mandamos el mensaje flash de éxito
    return redirect()
        ->intended(route('dashboard', absolute: false))
        ->with('success', 'Bienvenido, ' . $usuario->name . ' 👋');
}


    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
