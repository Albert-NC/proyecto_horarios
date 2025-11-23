<?php

namespace App\Http\Controllers;

use App\Models\Horario;
use App\Models\HorarioResolucion;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;

class HorarioController extends Controller
{
    // LISTADO / GRILLA
    public function index(Request $request)
    {
        $tipo    = $request->get('tipo');     // todos | par | impar
        $seccion = $request->get('seccion');  // A | B | null

        $query = Horario::where('carrera', 'Ingeniería Informática');

        // filtro por tipo de ciclo (par / impar)
        if ($tipo === 'par') {
            $query->whereRaw('ciclo % 2 = 0');
        } elseif ($tipo === 'impar') {
            $query->whereRaw('ciclo % 2 = 1');
        }

        // filtro por sección
        if ($seccion) {
            $query->where('seccion', strtoupper($seccion));
        }

        $items = $query->orderBy('hora_inicio')->get();

        // Obtener las resoluciones para mostrar debajo de cada horario
        $resoluciones = HorarioResolucion::where('carrera', 'Ingeniería Informática')
            ->when($tipo === 'par', fn($q) => $q->whereRaw('ciclo % 2 = 0'))
            ->when($tipo === 'impar', fn($q) => $q->whereRaw('ciclo % 2 = 1'))
            ->when($seccion, fn($q) => $q->where('seccion', strtoupper($seccion)))
            ->get()
            ->groupBy(function($item) {
                return $item->ciclo . '-' . $item->seccion;
            });

        $dias    = ['lunes','martes','miercoles','jueves','viernes','sabado'];
        $bloques = [];

        // bloques de 7:00 a 20:00 (7am–8pm)
        for ($h = 7; $h < 20; $h++) {
            $inicio = sprintf('%02d:00:00', $h);
            $fin    = sprintf('%02d:00:00', $h + 1);

            $fila = [
                'hora_inicio' => $inicio,
                'hora_fin'    => $fin,
            ];

            foreach ($dias as $d) {
                $fila[$d] = null;
            }

            $bloques[$inicio] = $fila;
        }

        foreach ($items as $horario) {
            // Convertir Carbon a string en formato H:i:s
            $hi = $horario->hora_inicio instanceof \Carbon\Carbon 
                ? $horario->hora_inicio->format('H:i:s') 
                : $horario->hora_inicio;
                
            if (isset($bloques[$hi]) && isset($bloques[$hi][$horario->dia])) {
                $bloques[$hi][$horario->dia] = $horario;
            }
        }

        return view('horarios.index', [
            'bloques' => array_values($bloques),
            'tipo'    => $tipo,
            'seccion' => $seccion,
            'resoluciones' => $resoluciones,
        ]);
    }

    // FORMULARIO PARA UN BLOQUE MANUAL (AHORA ES EL EDITOR VISUAL)
    public function create()
    {
        return view('horarios.create');
    }

    // GUARDA UN BLOQUE MANUAL (siempre de 1 hora)
    public function store(Request $request)
    {
        $request->validate([
            'ciclo'       => ['required', 'integer', 'between:1,10'],
            'seccion'     => ['required', 'string', 'max:2'],
            'dia'         => ['required', 'in:lunes,martes,miercoles,jueves,viernes,sabado'],
            'hora_inicio' => ['required'],   // 07:00:00
            'descripcion' => ['required', 'string'],
        ]);

        $carrera    = 'Ingeniería Informática';
        $horaInicio = $request->hora_inicio;
        $horaFin    = date('H:i:s', strtotime($horaInicio . ' +1 hour'));

        // evitar duplicado
        $exists = Horario::where('carrera', $carrera)
            ->where('ciclo', $request->ciclo)
            ->where('seccion', strtoupper($request->seccion))
            ->where('dia', $request->dia)
            ->where('hora_inicio', $horaInicio)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors([
                    'hora_inicio' => 'Ya existe un curso en ese bloque para ese ciclo, sección y día.',
                ]);
        }

        Horario::create([
            'carrera'     => $carrera,
            'ciclo'       => $request->ciclo,
            'tipo_ciclo'  => ($request->ciclo % 2 === 0) ? 'par' : 'impar',
            'seccion'     => strtoupper($request->seccion),
            'dia'         => $request->dia,
            'hora_inicio' => $horaInicio,
            'hora_fin'    => $horaFin,
            'descripcion' => $request->descripcion,
        ]);

        return redirect()
            ->route('horarios.index')
            ->with('success', 'Bloque de horario registrado correctamente.');
    }

    // GUARDAR MÚLTIPLES HORARIOS A LA VEZ (DESDE EL EDITOR VISUAL)
    public function storeMultiple(Request $request)
    {
        $horarios = $request->input('horarios', []);
        $carrera = 'Ingeniería Informática';
        $insertados = 0;

        foreach ($horarios as $horario) {
            $ciclo = $horario['ciclo'];
            $seccion = strtoupper($horario['seccion']);
            $dia = $horario['dia'];
            $horaInicio = $horario['hora_inicio'];
            $descripcion = $horario['descripcion'];
            
            $horaFin = date('H:i:s', strtotime($horaInicio . ' +1 hour'));
            $tipoCiclo = ($ciclo % 2 === 0) ? 'par' : 'impar';

            Horario::updateOrCreate(
                [
                    'carrera' => $carrera,
                    'ciclo' => $ciclo,
                    'seccion' => $seccion,
                    'dia' => $dia,
                    'hora_inicio' => $horaInicio,
                ],
                [
                    'tipo_ciclo' => $tipoCiclo,
                    'hora_fin' => $horaFin,
                    'descripcion' => $descripcion,
                ]
            );

            $insertados++;
        }

        return response()->json([
            'success' => true,
            'message' => "Se guardaron {$insertados} horarios correctamente"
        ]);
    }

    // SUBIR RESOLUCIÓN PDF PARA UN CICLO/SECCIÓN
    public function storeResolucion(Request $request)
    {
        $request->validate([
            'ciclo' => ['required', 'integer', 'between:1,10'],
            'seccion' => ['nullable', 'string', 'max:2'],
            'pdf' => ['required', 'file', 'mimes:pdf', 'max:10240'], // 10MB max
            'comentario' => ['nullable', 'string', 'max:500'],
        ]);

        $carrera = 'Ingeniería Informática';
        $ciclo = $request->ciclo;
        $seccion = $request->seccion ? strtoupper($request->seccion) : null;

        // Guardar el PDF
        $pdfPath = $request->file('pdf')->store('resoluciones', 'public');

        // Crear o actualizar la resolución
        HorarioResolucion::updateOrCreate(
            [
                'carrera' => $carrera,
                'ciclo' => $ciclo,
                'seccion' => $seccion,
            ],
            [
                'pdf_path' => $pdfPath,
                'comentario' => $request->comentario,
            ]
        );

        return redirect()
            ->route('horarios.index', array_filter([
                'tipo' => ($ciclo % 2 === 0) ? 'par' : 'impar',
                'seccion' => $seccion,
            ]))
            ->with('success', 'Resolución subida correctamente.');
    }

    // ELIMINAR RESOLUCIÓN
    public function destroyResolucion(HorarioResolucion $resolucion)
    {
        // Eliminar archivo PDF
        if ($resolucion->pdf_path && Storage::disk('public')->exists($resolucion->pdf_path)) {
            Storage::disk('public')->delete($resolucion->pdf_path);
        }

        $resolucion->delete();

        return redirect()
            ->route('horarios.index')
            ->with('success', 'Resolución eliminada correctamente.');
    }

    // IMPORTAR TODOS LOS HORARIOS DESDE UN EXCEL CON VARIAS HOJAS
    public function import(Request $request)
    {
        Log::info('=== INICIO IMPORTACIÓN HORARIOS ===');
        
        $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls'],
            'tipo_ciclo' => ['nullable', 'in:par,impar'],
        ]);

        $carrera = 'Ingeniería Informática';
        $tipoCiclo = $request->input('tipo_ciclo'); // par, impar, o null

        $file = $request->file('archivo');
        $path = $file->getRealPath();

        Log::info('Archivo: ' . $file->getClientOriginalName());
        Log::info('Tipo de ciclo seleccionado: ' . ($tipoCiclo ?? 'todos'));

        $spreadsheet = IOFactory::load($path);
        $sheets      = $spreadsheet->getAllSheets();

        Log::info('Total de hojas: ' . count($sheets));

        $diasKeys = ['lunes','martes','miercoles','jueves','viernes','sabado'];

        $insertados = 0;
        $saltados   = 0;
        $ignoradas  = 0;

        foreach ($sheets as $sheet) {
            $title = trim($sheet->getTitle()); // ej: "2A", "2B", "3R"
            
            Log::info("Procesando hoja: '{$title}'");

            // detectar ciclo y seccion del nombre de la hoja
            // Acepta: "2A", "2 A", "2-A", "2a", etc.
            if (!preg_match('/(\d+)\s*[_\-\s]*([A-Za-z])/', $title, $m)) {
                Log::warning("Hoja '{$title}' no coincide con formato ciclo+sección. Ignorada.");
                $ignoradas++;
                continue;
            }

            $ciclo   = (int) $m[1];
            $seccion = strtoupper($m[2]);

            Log::info("Detectado: Ciclo {$ciclo}, Sección {$seccion}");

            if ($ciclo < 1 || $ciclo > 10) {
                Log::warning("Ciclo {$ciclo} fuera de rango. Hoja ignorada.");
                $ignoradas++;
                continue;
            }

            // Determinar tipo de ciclo (par/impar)
            $tipoCicloDetectado = ($ciclo % 2 === 0) ? 'par' : 'impar';
            Log::info("Tipo de ciclo detectado: {$tipoCicloDetectado}");

            // 🔍 Filtrar por tipo de ciclo si se especificó
            if ($tipoCiclo === 'par' && $ciclo % 2 !== 0) {
                Log::info("Ciclo {$ciclo} es impar, pero se pidió solo pares. Hoja ignorada.");
                $ignoradas++;
                continue;
            }

            if ($tipoCiclo === 'impar' && $ciclo % 2 === 0) {
                Log::info("Ciclo {$ciclo} es par, pero se pidió solo impares. Hoja ignorada.");
                $ignoradas++;
                continue;
            }

            $rows = $sheet->toArray(null, true, true, false); // array de filas

            if (empty($rows)) {
                Log::warning("Hoja '{$title}' está vacía.");
                continue;
            }

            Log::info("Total de filas en hoja '{$title}': " . count($rows));

            // 1️⃣ Buscar la fila de encabezados (HORA, LUNES, MARTES, ...)
            $headerIndex = null;
            $idxHora     = null;
            $idxDia      = [
                'lunes'     => null,
                'martes'    => null,
                'miercoles' => null,
                'jueves'    => null,
                'viernes'   => null,
                'sabado'    => null,
            ];

            foreach ($rows as $rIndex => $cols) {
                foreach ($cols as $cIndex => $value) {
                    $norm = Str::of($value ?? '')
                        ->ascii()
                        ->lower()
                        ->trim()
                        ->value();

                    if (Str::contains($norm, 'hora') && $idxHora === null) {
                        $idxHora     = $cIndex;
                        $headerIndex = $rIndex;
                        Log::info("Columna HORA encontrada en índice {$cIndex}, fila {$rIndex}");
                    }

                    foreach ($idxDia as $diaKey => $current) {
                        if ($current !== null) {
                            continue;
                        }
                        if (Str::contains($norm, $diaKey) || $norm === $diaKey) {
                            $idxDia[$diaKey] = $cIndex;
                            if ($headerIndex === null) {
                                $headerIndex = $rIndex;
                            }
                            Log::info("Columna {$diaKey} encontrada en índice {$cIndex}");
                        }
                    }
                }
            }

            if ($idxHora === null || $headerIndex === null) {
                Log::error("No se encontraron encabezados en hoja '{$title}'");
                $saltados++;
                continue;
            }

            Log::info("Encabezados detectados. Inicio de datos en fila " . ($headerIndex + 1));

            // 2️⃣ Recorrer filas de datos
            for ($i = $headerIndex + 1; $i < count($rows); $i++) {
                $row = $rows[$i];

                $horaTexto = trim((string)($row[$idxHora] ?? ''));
                
                if ($horaTexto === '') {
                    continue;
                }

                Log::info("Procesando fila {$i}, HORA: '{$horaTexto}'");

                // extraer HORA INICIO del texto: "7:00 a 8:00 a.m." o "1:00 a 2:00 p.m"
                if (!preg_match('/(\d{1,2})\s*:\s*(\d{2})/', $horaTexto, $mHora)) {
                    Log::warning("No se pudo extraer hora de: '{$horaTexto}'");
                    $saltados++;
                    continue;
                }

                $inicioH = (int) $mHora[1];
                $inicioM = (int) $mHora[2];
                
                // 🔥 Detectar si es PM y ajustar la hora
                $horaTextoLower = strtolower($horaTexto);
                if (str_contains($horaTextoLower, 'p.m') || str_contains($horaTextoLower, 'pm')) {
                    // Si es PM y la hora es menor a 12, sumar 12
                    if ($inicioH < 12) {
                        $inicioH += 12;
                    }
                }
                
                if ($inicioH < 7 || $inicioH > 19) {
                    Log::warning("Hora {$inicioH} fuera de rango 7-19. Fila {$i} saltada.");
                    $saltados++;
                    continue;
                }

                $horaInicio = sprintf('%02d:%02d:00', $inicioH, $inicioM);
                $horaFin    = date('H:i:s', strtotime($horaInicio . ' +1 hour'));

                Log::info("Hora normalizada: {$horaInicio} - {$horaFin}");

                // por cada día
                foreach ($idxDia as $diaKey => $colIdx) {
                    if ($colIdx === null) {
                        continue;
                    }

                    $descripcion = trim((string)($row[$colIdx] ?? ''));

                    if ($descripcion === '') {
                        continue; // celda vacía → no hay curso en ese bloque
                    }

                    Log::info("Insertando: Ciclo {$ciclo}, Sec {$seccion}, {$diaKey}, {$horaInicio}: '{$descripcion}'");

                    try {
                        Horario::updateOrCreate(
                            [
                                'carrera'     => $carrera,
                                'ciclo'       => $ciclo,
                                'seccion'     => $seccion,
                                'dia'         => $diaKey,
                                'hora_inicio' => $horaInicio,
                            ],
                            [
                                'tipo_ciclo'  => $tipoCicloDetectado,
                                'hora_fin'    => $horaFin,
                                'descripcion' => $descripcion,
                            ]
                        );

                        $insertados++;
                    } catch (\Exception $e) {
                        Log::error("Error insertando horario: " . $e->getMessage());
                        $saltados++;
                    }
                }
            }
        }

        Log::info("=== FIN IMPORTACIÓN HORARIOS === Insertados: {$insertados}, Ignoradas: {$ignoradas}, Saltados: {$saltados}");

        return redirect()
            ->route('horarios.index')
            ->with(
                'success',
                "Importación completada. Bloques creados/actualizados: {$insertados}. ".
                "Hojas ignoradas: {$ignoradas}. Filas saltadas: {$saltados}."
            );
    }
    // Agregar al HorarioController.php

// Cargar horarios existentes de un ciclo/sección (para el editor)
public function cargarHorarios($ciclo, $seccion)
{
    $horarios = Horario::where('carrera', 'Ingeniería Informática')
        ->where('ciclo', $ciclo)
        ->where('seccion', strtoupper($seccion))
        ->get(['dia', 'hora_inicio', 'descripcion']);

    return response()->json([
        'success' => true,
        'horarios' => $horarios
    ]);
}
}