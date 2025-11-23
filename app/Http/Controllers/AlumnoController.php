<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class AlumnoController extends Controller
{
    // LISTADO
    public function index()
    {
        $alumnos = Alumno::with('user')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('alumnos.index', compact('alumnos'));
    }

    // FORMULARIO CREACIÓN MANUAL
    public function create()
    {
        return view('alumnos.create');
    }

    // GUARDAR NUEVO ALUMNO + USER
    public function store(Request $request)
    {
        $request->validate([
            'codigo'   => ['required', 'string', 'max:20', 'unique:users,codigo'],
            'nombre'   => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        // 1. Usuario
        $user = User::create([
            'codigo'   => $request->codigo,
            'name'     => $request->nombre,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'alumno',
        ]);

        // 2. Perfil alumno
        Alumno::create([
            'user_id' => $user->id,
            'estado'  => 'activo',
        ]);

        return redirect()
            ->route('alumnos.index')
            ->with('success', 'Alumno creado correctamente.');
    }

    // IMPORTAR DESDE EXCEL (ALUMNOS)
    // Columnas esperadas (en cualquier orden):
    // Código | Nombre | Correo | Contraseña
    public function importar(Request $request)
    {
        // 🔍 DEBUG
        Log::info('=== INICIO IMPORTACIÓN ALUMNOS ===');
        Log::info('Archivo recibido: ' . ($request->hasFile('archivo') ? 'SÍ' : 'NO'));
        
        if ($request->hasFile('archivo')) {
            Log::info('Nombre archivo: ' . $request->file('archivo')->getClientOriginalName());
            Log::info('Tamaño: ' . $request->file('archivo')->getSize() . ' bytes');
        }

        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $file   = $request->file('archivo');
        $sheets = Excel::toArray([], $file);
        $rows   = $sheets[0] ?? [];

        Log::info('Total de filas en Excel: ' . count($rows));

        if (empty($rows)) {
            return back()->with('success', 'El archivo está vacío.');
        }

        // 1️⃣ Encabezados - Normalización mejorada
        $headers = $rows[0] ?? [];

        Log::info('Encabezados detectados: ' . json_encode($headers));

        $normHeaders = [];
        foreach ($headers as $i => $h) {
            $normHeaders[$i] = Str::of($h ?? '')
                ->ascii()           // sin tildes
                ->lower()           // minúsculas
                ->trim()            // quitar espacios al inicio/fin
                ->replace(['  ', '   '], ' ') // quitar espacios dobles/triples
                ->value();
        }

        // 2️⃣ Detectar índices de columnas
        $idxCodigo = $idxNombre = $idxCorreo = $idxPass = null;

        foreach ($normHeaders as $i => $h) {
            // Buscar "Codigo" o "Código"
            if (Str::contains($h, 'codigo') || $h === 'codigo') {
                $idxCodigo = $i;
            } 
            // Buscar "Nombre" o "Alumno"
            elseif (Str::contains($h, 'nombre') || $h === 'nombre' || $h === 'alumno') {
                $idxNombre = $i;
            } 
            // Buscar "Correo" o "Email"
            elseif (Str::contains($h, 'correo') || Str::contains($h, 'email') || $h === 'correo' || $h === 'email') {
                $idxCorreo = $i;
            } 
            // Buscar "Contraseña" o "Password"
            elseif (Str::contains($h, 'contrasena') || Str::contains($h, 'password') || Str::contains($h, 'pass') || $h === 'contrasena') {
                $idxPass = $i;
            }
        }

        // 3️⃣ Validar que tengamos lo mínimo
        if ($idxCodigo === null || $idxNombre === null || $idxCorreo === null) {
            Log::error('Columnas no detectadas - Codigo: ' . $idxCodigo . ', Nombre: ' . $idxNombre . ', Correo: ' . $idxCorreo);
            
            return back()->withErrors([
                'archivo' => 'No se pudieron detectar las columnas de Código, Nombre y Correo en el encabezado del archivo.',
            ]);
        }

        Log::info('Índices detectados - Codigo: ' . $idxCodigo . ', Nombre: ' . $idxNombre . ', Correo: ' . $idxCorreo . ', Password: ' . $idxPass);

        $creados      = 0;
        $actualizados = 0;
        $saltados     = 0;

        // 4️⃣ Recorrer filas de datos (desde la 2ª)
        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // saltar encabezado
            }

            $codigo   = trim((string)($row[$idxCodigo] ?? ''));
            $nombre   = trim((string)($row[$idxNombre] ?? ''));
            $correo   = trim((string)($row[$idxCorreo] ?? ''));
            $password = $idxPass !== null ? trim((string)($row[$idxPass] ?? '')) : '';

            // Fila totalmente vacía → saltar
            if ($codigo === '' && $nombre === '' && $correo === '') {
                continue;
            }

            // Datos mínimos
            if ($codigo === '' || $nombre === '' || $correo === '') {
                $saltados++;
                continue;
            }

            // Validar que el correo tenga formato válido
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                Log::warning("Email inválido en fila {$index}: {$correo}");
                $saltados++;
                continue;
            }

            // Recortar código a 20 chars
            $codigo = mb_substr($codigo, 0, 20, 'UTF-8');

            // 5️⃣ Buscar usuario por código o correo
            $user = User::where('codigo', $codigo)
                ->orWhere('email', $correo)
                ->first();

            if (! $user) {
                // Crear usuario nuevo
                try {
                    $user = User::create([
                        'codigo'   => $codigo,
                        'name'     => $nombre,
                        'email'    => $correo,
                        'password' => Hash::make($password ?: 'Cambio123!'),
                        'role'     => 'alumno',
                    ]);
                    Log::info("Usuario creado: {$user->name} (ID: {$user->id}) - Codigo: {$codigo}");
                    $creados++;
                } catch (\Exception $e) {
                    Log::error("Error creando usuario con código {$codigo}: " . $e->getMessage());
                    $saltados++;
                    continue;
                }
            } else {
                // Actualizar datos básicos
                $user->codigo = $codigo;
                $user->name   = $nombre;
                $user->email  = $correo;

                if ($password !== '') {
                    $user->password = Hash::make($password);
                }

                if ($user->role === null) {
                    $user->role = 'alumno';
                }

                $user->save();
                Log::info("Usuario actualizado: {$user->name} (ID: {$user->id})");
                $actualizados++;
            }

            // 6️⃣ Perfil alumno
            $alumno = Alumno::where('user_id', $user->id)->first();

            if ($alumno) {
                // Ya existe, solo actualizar estado si está vacío
                if (empty($alumno->estado)) {
                    $alumno->estado = 'activo';
                    $alumno->save();
                }
            } else {
                // Crear nuevo perfil
                try {
                    Alumno::create([
                        'user_id' => $user->id,
                        'estado'  => 'activo',
                    ]);
                    Log::info("Perfil alumno creado para user_id: {$user->id}");
                } catch (\Exception $e) {
                    Log::error("Error creando perfil alumno para user_id {$user->id}: " . $e->getMessage());
                }
            }
        }

        Log::info("=== FIN IMPORTACIÓN ALUMNOS === Creados: {$creados}, Actualizados: {$actualizados}, Saltados: {$saltados}");

        return redirect()
            ->route('alumnos.index')
            ->with(
                'success',
                "Importación completada. Nuevos: $creados, actualizados: $actualizados, filas saltadas: $saltados."
            );
    }
}