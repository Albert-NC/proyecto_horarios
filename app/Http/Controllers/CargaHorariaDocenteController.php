<?php

namespace App\Http\Controllers;

use App\Models\Docente;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class CargaHorariaDocenteController extends Controller
{
    // LISTADO
    public function index(Request $request)
    {
        $query = Docente::with('user')->orderBy('user_id', 'asc');

        if ($request->filled('lugar') && in_array($request->lugar, ['Trujillo', 'Valle'])) {
            $query->where('lugar', $request->lugar);
        }

        $docentes = $query->paginate(10)->appends($request->only('lugar'));

        return view('carga_horaria_docentes.index', compact('docentes', 'request'));
    }

    // FORMULARIO MANUAL (por código)
    public function create()
    {
        return view('carga_horaria_docentes.create');
    }

    // GUARDAR / ACTUALIZAR CARGA HORARIA MANUAL
    public function store(Request $request)
    {
        $request->validate([
            'codigo'    => ['required', 'string', 'max:20', 'exists:users,codigo'],
            'categoria' => ['required', 'in:asociado,principal,auxiliar'],
            'horas'     => ['required', 'integer', 'min:0'],
            'modalidad' => ['required', 'in:tiempo completo,tiempo parcial'],
        ], [
            'codigo.exists' => 'No se encontró un docente con ese código.',
        ]);

        $user    = User::where('codigo', $request->codigo)->first();
        $docente = Docente::firstOrNew(['user_id' => $user->id]);

        $docente->categoria = $request->categoria;
        $docente->horas     = $request->horas;
        $docente->modalidad = $request->modalidad;

        if (empty($docente->estado)) {
            $docente->estado = 'activo';
        }

        $docente->save();

        return redirect()
            ->route('carga-horaria-docentes.index')
            ->with('success', 'Carga horaria registrada/actualizada correctamente.');
    }

    // EDITAR (para ajustar manualmente)
    public function edit(Docente $docente)
    {
        $docente->load('user');

        return view('carga_horaria_docentes.edit', compact('docente'));
    }

    public function update(Request $request, Docente $docente)
    {
        $request->validate([
            'nombre'    => ['required', 'string', 'max:255'],
            'categoria' => ['required', 'in:asociado,principal,auxiliar'],
            'horas'     => ['required', 'integer', 'min:0'],
            'modalidad' => ['required', 'in:tiempo completo,tiempo parcial'],
        ]);

        $user       = $docente->user;
        $user->name = $request->nombre;
        $user->save();

        $docente->categoria = $request->categoria;
        $docente->horas     = $request->horas;
        $docente->modalidad = $request->modalidad;

        $docente->save();

        return redirect()
            ->route('carga-horaria-docentes.index')
            ->with('success', 'Datos del docente actualizados correctamente.');
    }

    // IMPORTAR CARGA HORARIA DESDE EXCEL (POR CÓDIGO)
    // Columnas en cualquier orden: Codigo | Categoria | Horas | Modalidad
    public function importar(Request $request)
    {
        // DEBUG
        Log::info('=== INICIO IMPORTACIÓN CARGA HORARIA ===');
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
            return back()->withErrors([
                'archivo' => 'El archivo está vacío.',
            ]);
        }

        // 1) Encabezados
        $headers = $rows[0];
        Log::info('Encabezados detectados: ' . json_encode($headers));

        $normHeaders = [];
        foreach ($headers as $i => $h) {
            $normHeaders[$i] = Str::of($h ?? '')
                ->ascii()                       // sin tildes
                ->lower()                       // minúsculas
                ->trim()                        // quitar espacios extremos
                ->replace(['  ', '   '], ' ')   // espacios dobles/triples
                ->value();
        }

        $idxCodigo = $idxCategoria = $idxHoras = $idxModalidad = null;

        foreach ($normHeaders as $i => $h) {
            if (Str::contains($h, 'codigo') || $h === 'codigo') {
                $idxCodigo = $i;
            } elseif (Str::contains($h, 'categoria') || $h === 'categoria') {
                $idxCategoria = $i;
            } elseif (Str::contains($h, 'hora') || $h === 'horas') {
                $idxHoras = $i;
            } elseif (Str::contains($h, 'modalidad') || $h === 'modalidad') {
                $idxModalidad = $i;
            }
        }

        if ($idxCodigo === null || $idxCategoria === null || $idxHoras === null || $idxModalidad === null) {
            Log::error('Columnas no detectadas - Codigo: ' . $idxCodigo . ', Categoria: ' . $idxCategoria . ', Horas: ' . $idxHoras . ', Modalidad: ' . $idxModalidad);

            return back()->withErrors([
                'archivo' => 'No se pudieron detectar las columnas de Código, Categoría, Horas y Modalidad en el encabezado.',
            ]);
        }

        Log::info('Índices detectados - Codigo: ' . $idxCodigo . ', Categoria: ' . $idxCategoria . ', Horas: ' . $idxHoras . ', Modalidad: ' . $idxModalidad);

        $creados      = 0;
        $actualizados = 0;
        $saltados     = 0;

        foreach ($rows as $index => $row) {
            if ($index === 0) {
                continue; // encabezado
            }

            $codigo    = trim((string)($row[$idxCodigo] ?? ''));
            $categoria = trim((string)($row[$idxCategoria] ?? ''));
            $horasRaw  = $row[$idxHoras] ?? null;
            $modalidad = trim((string)($row[$idxModalidad] ?? ''));

            // fila vacía
            if (
                $codigo === '' &&
                $categoria === '' &&
                $modalidad === '' &&
                ($horasRaw === null || $horasRaw === '')
            ) {
                continue;
            }

            if ($codigo === '') {
                $saltados++;
                continue;
            }

            // recortar código a 20 chars (varchar(20))
            $codigo = mb_substr($codigo, 0, 20, 'UTF-8');

            // normalizar categoría
            $catNorm = Str::of($categoria)->ascii()->lower()->trim()->value();

            if (Str::contains($catNorm, 'asociado') || $catNorm === 'asociado') {
                $categoriaFinal = 'asociado';
            } elseif (Str::contains($catNorm, 'auxiliar') || $catNorm === 'auxiliar') {
                $categoriaFinal = 'auxiliar';
            } elseif (Str::contains($catNorm, 'principal') || $catNorm === 'principal') {
                $categoriaFinal = 'principal';
            } else {
                $saltados++;
                continue;
            }

            // normalizar modalidad
            $modNorm = Str::of($modalidad)->ascii()->lower()->trim()->value();

            if (Str::contains($modNorm, 'parcial')) {
                $modalidadFinal = 'tiempo parcial';
            } elseif (Str::contains($modNorm, 'completo')) {
                $modalidadFinal = 'tiempo completo';
            } else {
                // si no detecta nada raro, asumimos completo
                $modalidadFinal = 'tiempo completo';
            }

            // horas
            if ($horasRaw === null || $horasRaw === '' || !is_numeric($horasRaw)) {
                $saltados++;
                continue;
            }
            $horas = (int) $horasRaw;

            // buscar usuario por código (PK lógica)
            $user = User::where('codigo', $codigo)->first();
            if (! $user) {
                Log::warning("Usuario no encontrado con código: {$codigo}");
                $saltados++;
                continue;
            }

            Log::info("Usuario encontrado: {$user->name} (ID: {$user->id}) - Codigo: {$codigo}");

            // perfil docente
            $docente = Docente::where('user_id', $user->id)->first();

            if ($docente) {
                // actualizar
                $docente->categoria = $categoriaFinal;
                $docente->horas     = $horas;
                $docente->modalidad = $modalidadFinal;

                try {
                    $docente->save();
                    Log::info("Docente actualizado: user_id={$user->id}, categoria={$categoriaFinal}, horas={$horas}, modalidad={$modalidadFinal}");
                    $actualizados++;
                } catch (\Exception $e) {
                    Log::error("Error actualizando docente user_id {$user->id}: " . $e->getMessage());
                    $saltados++;
                    continue;
                }
            } else {
                // crear
                try {
                    $nuevo = Docente::create([
                        'user_id'   => $user->id,
                        'categoria' => $categoriaFinal,
                        'horas'     => $horas,
                        'modalidad' => $modalidadFinal,
                        'estado'    => 'activo',
                        'lugar'     => null,
                    ]);
                    Log::info("Docente creado: ID={$nuevo->id}, user_id={$user->id}, categoria={$categoriaFinal}, horas={$horas}");
                    $creados++;
                } catch (\Exception $e) {
                    Log::error("Error creando docente para user_id {$user->id}: " . $e->getMessage());
                    $saltados++;
                    continue;
                }
            }
        }

        Log::info("=== FIN IMPORTACIÓN === Creados: {$creados}, Actualizados: {$actualizados}, Saltados: {$saltados}");

        return redirect()
            ->route('carga-horaria-docentes.index')
            ->with(
                'success',
                "Importación completada. Nuevos perfiles de carga: $creados, actualizados: $actualizados, filas saltadas: $saltados."
            );
    }
}
