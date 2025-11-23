<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AulaController;
use App\Http\Controllers\DocenteController;
use App\Http\Controllers\CursoController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\HorarioController;
use App\Http\Controllers\CargaHorariaDocenteController;
use App\Http\Controllers\AlumnoController;

// Página raíz → enviar al login
Route::get('/', fn () => redirect()->route('login'));

require __DIR__.'/auth.php';

// RUTAS PROTEGIDAS
Route::middleware(['auth'])->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    // CRUDs BÁSICOS
    Route::resource('aulas', AulaController::class);
    Route::resource('cursos', CursoController::class);
    Route::resource('grupos', GrupoController::class);

    // DOCENTES
    Route::resource('docentes', DocenteController::class);
    Route::post('docentes/importar', [DocenteController::class, 'importar'])
        ->name('docentes.importar');

    // CARGA HORARIA DE DOCENTES
    Route::get('carga-horaria-docentes', [CargaHorariaDocenteController::class, 'index'])
        ->name('carga-horaria-docentes.index');
    Route::get('carga-horaria-docentes/crear', [CargaHorariaDocenteController::class, 'create'])
        ->name('carga-horaria-docentes.create');
    Route::post('carga-horaria-docentes', [CargaHorariaDocenteController::class, 'store'])
        ->name('carga-horaria-docentes.store');
    Route::post('carga-horaria-docentes/importar', [CargaHorariaDocenteController::class, 'importar'])
        ->name('carga-horaria-docentes.importar');
    Route::get('carga-horaria-docentes/{docente}/editar', [CargaHorariaDocenteController::class, 'edit'])
        ->name('carga-horaria-docentes.edit');
    Route::put('carga-horaria-docentes/{docente}', [CargaHorariaDocenteController::class, 'update'])
        ->name('carga-horaria-docentes.update');

    // ALUMNOS
    Route::resource('alumnos', AlumnoController::class);
    Route::post('alumnos/importar', [AlumnoController::class, 'importar'])
        ->name('alumnos.importar');

    // ========================================
    // HORARIOS
    // ========================================
    
    // Vista principal y creación manual
    Route::resource('horarios', HorarioController::class)
        ->only(['index', 'create', 'store']);

    // Cargar horarios existentes para edición
    Route::get('horarios/cargar/{ciclo}/{seccion}', [HorarioController::class, 'cargarHorarios'])
        ->name('horarios.cargar');

    // Guardar múltiples horarios a la vez (desde el editor visual)
    Route::post('horarios/store-multiple', [HorarioController::class, 'storeMultiple'])
        ->name('horarios.store-multiple');

    // Importar horarios desde Excel
    Route::post('horarios/importar', [HorarioController::class, 'import'])
        ->name('horarios.import');

    // Subir resolución PDF para un ciclo/sección
    Route::post('horarios/resolucion', [HorarioController::class, 'storeResolucion'])
        ->name('horarios.resolucion.store');

    // Eliminar resolución
    Route::delete('horarios/resolucion/{resolucion}', [HorarioController::class, 'destroyResolucion'])
        ->name('horarios.resolucion.destroy');
});