<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class SolicitudLogin extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'    => ['required', 'string'], 
            'password' => ['required', 'string'],
        ];
    }

    public function autenticar(): void
    {
        $entrada = $this->input('login');

        // Detectar si es correo o código
        $campo = filter_var($entrada, FILTER_VALIDATE_EMAIL) ? 'email' : 'codigo';

        // 1. Verificar si el usuario existe
        $usuario = \App\Models\User::where($campo, $entrada)->first();

        if (! $usuario) {
            throw ValidationException::withMessages([
                'login' => $campo === 'email' 
                    ? 'Correo inválido.' 
                    : 'Código inválido.',
            ]);
        }

        // 2. Verificar contraseña
        if (! Hash::check($this->password, $usuario->password)) {
            throw ValidationException::withMessages([
                'login' => 'Contraseña inválida.',
            ]);
        }

        // 3. Iniciar sesión
        Auth::login($usuario);
    }
}
