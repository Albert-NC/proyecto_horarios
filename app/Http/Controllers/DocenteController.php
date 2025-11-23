<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class DocenteController extends Controller
{
    // LISTADO
    public function index(Request $request)
    {
        $query = Docente::with('user')->orderBy('user_id', 'asc');

        // filtro por sede si viene ?lugar=Trujillo|Valle
        if ($request->filled('lugar') && in_array($request->lugar, ['Trujillo', 'Valle'])) {
            $query->where('lugar', $request->lugar);
        }

        $docentes = $query->paginate(10)->appends($request->only('lugar'));

        return view('docentes.index', compact('docentes'));
    }

    // FORMULARIO CREACIÓN MANUAL
    public function create()
    {
        return view('docentes.create');
    }

    // GUARDAR NUEVO DOCENTE + USER (manual)
    public function store(Request $request)
    {
        $request->validate([
            'codigo'   => ['required', 'string', 'max:20', 'unique:users,codigo'],
            'nombre'   => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
            'lugar'    => ['nullable', 'string', 'max:50'],
        ]);

        // 1. Usuario
        $user = User::create([
            'codigo'   => $request->codigo,
            'name'     => $request->nombre,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'profesor',
        ]);

        // 2. Perfil docente
        Docente::create([
            'user_id'   => $user->id,
            'categoria' => null,
            'modalidad' => null,
            'horas'     => 0,
            'estado'    => 'activo',
            'lugar'     => $request->lugar,
        ]);

        return redirect()
            ->route('docentes.index')
            ->with('success', 'Docente creado correctamente.');
    }

    // IMPORTAR DESDE EXCEL (DOCENTES)
    // Columnas esperadas (en cualquier orden):
    // Código | Nombre | Correo | Contraseña | Lugar
    public function importar(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $file   = $request->file('archivo');
        $sheets = Excel::toArray([], $file);
        $rows   = $sheets[0] ?? [];

        if (empty($rows)) {
            return back()->with('success', 'El archivo está vacío.');
        }

        // 1️⃣ Encabezados - Normalización mejorada
        $headers = $rows[0] ?? [];

        $normHeaders = [];
        foreach ($headers as $i => $h) {
            $normHeaders[$i] = Str::of($h ?? '')
                ->ascii()           // sin tildes
                ->lower()           // minúsculas
                ->trim()            // quitar espacios al inicio/fin
                ->replace(['  ', '   '], ' ') // quitar espacios dobles/triples
                ->value();
        }

        // 2️⃣ Detectar índices de columnas - MEJORADO
        $idxCodigo = $idxNombre = $idxCorreo = $idxPass = $idxLugar = null;

        foreach ($normHeaders as $i => $h) {
            // Buscar "Codigo" o "Código"
            if (Str::contains($h, 'codigo') || $h === 'codigo') {
                $idxCodigo = $i;
            } 
            // Buscar "Nombre" o "Docente"
            elseif (Str::contains($h, 'nombre') || $h === 'nombre' || $h === 'docente') {
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
            // Buscar "Lugar" o "Sede"
            elseif (Str::contains($h, 'lugar') || Str::contains($h, 'sede') || $h === 'lugar' || $h === 'sede') {
                $idxLugar = $i;
            }
        }

        // 3️⃣ Validar que tengamos lo mínimo
        if ($idxCodigo === null || $idxNombre === null || $idxCorreo === null) {
            return back()->withErrors([
                'archivo' => 'No se pudieron detectar las columnas de Código, Nombre y Correo en el encabezado del archivo.',
            ]);
        }

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
            $password = $idxPass  !== null ? trim((string)($row[$idxPass]  ?? '')) : '';
            $lugar    = $idxLugar !== null ? trim((string)($row[$idxLugar] ?? '')) : null;

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
                $user = User::create([
                    'codigo'   => $codigo,
                    'name'     => $nombre,
                    'email'    => $correo,
                    'password' => Hash::make($password ?: 'Cambio123!'),
                    'role'     => 'profesor',
                ]);
                $creados++;
            } else {
                // Actualizar datos básicos
                $user->codigo = $codigo;
                $user->name   = $nombre;
                $user->email  = $correo;

                if ($password !== '') {
                    $user->password = Hash::make($password);
                }

                if ($user->role === null) {
                    $user->role = 'profesor';
                }

                $user->save();
                $actualizados++;
            }

            // 6️⃣ Perfil docente
            $docente = Docente::firstOrNew(['user_id' => $user->id]);

            if (! $docente->exists) {
                $docente->categoria = null;
                $docente->modalidad = null;
                $docente->horas     = 0;
                $docente->estado    = 'activo';
            }

            if ($lugar !== null && $lugar !== '') {
                $docente->lugar = $lugar;
            }

            $docente->save();
        }

        return redirect()
            ->route('docentes.index')
            ->with(
                'success',
                "Importación completada. Nuevos: $creados, actualizados: $actualizados, filas saltadas: $saltados."
            );
    }
}