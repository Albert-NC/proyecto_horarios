@extends('layouts.panel')

@section('contenido')
<div
    class="rounded-xl border border-white/10 px-6 py-4 mb-4"
    :class="tema === 'oscuro' ? 'glass-dark' : 'glass-light'"
    x-data="{
        cicloSeleccionado: '2',
        seccionSeleccionada: 'A',
        horarios: {},
        modalAbierto: false,
        horarioEditando: null,
        cargando: false,
        
        async cargarHorarios() {
            this.cargando = true;
            try {
                const response = await fetch(`/horarios/cargar/${this.cicloSeleccionado}/${this.seccionSeleccionada}`);
                const data = await response.json();
                
                // Limpiar horarios actuales
                this.horarios = {};
                
                // Cargar horarios existentes
                data.horarios.forEach(h => {
                    const key = `${h.dia}-${h.hora_inicio}`;
                    this.horarios[key] = h.descripcion;
                });
                
                console.log('Horarios cargados:', Object.keys(this.horarios).length);
            } catch (error) {
                console.error('Error cargando horarios:', error);
            } finally {
                this.cargando = false;
            }
        },
        
        abrirModal(dia, horaInicio, horaFin) {
            const key = `${dia}-${horaInicio}`;
            this.horarioEditando = {
                dia: dia,
                hora_inicio: horaInicio,
                hora_fin: horaFin,
                descripcion: this.horarios[key] || ''
            };
            this.modalAbierto = true;
        },
        
        guardarHorario() {
            const key = `${this.horarioEditando.dia}-${this.horarioEditando.hora_inicio}`;
            this.horarios[key] = this.horarioEditando.descripcion;
            this.modalAbierto = false;
        },
        
        borrarHorario() {
            const key = `${this.horarioEditando.dia}-${this.horarioEditando.hora_inicio}`;
            delete this.horarios[key];
            this.modalAbierto = false;
        },
        
        obtenerHorario(dia, horaInicio) {
            const key = `${dia}-${horaInicio}`;
            return this.horarios[key] || '';
        },
        
        async enviarFormulario() {
            // Preparar datos
            const datos = [];
            Object.keys(this.horarios).forEach(key => {
                if (this.horarios[key]) {
                    const [dia, hora] = key.split('-');
                    datos.push({
                        ciclo: this.cicloSeleccionado,
                        seccion: this.seccionSeleccionada,
                        dia: dia,
                        hora_inicio: hora,
                        descripcion: this.horarios[key]
                    });
                }
            });
            
            if (datos.length === 0) {
                alert('No hay horarios para guardar');
                return;
            }
            
            try {
                const response = await fetch('{{ route('horarios.store-multiple') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ horarios: datos })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Redirigir al index CON el ciclo y sección seleccionados
                    window.location.href = `/horarios?ciclo=${this.cicloSeleccionado}&seccion=${this.seccionSeleccionada}`;
                }
            } catch (error) {
                console.error('Error guardando:', error);
                alert('Error al guardar los horarios');
            }
        }
    }"
    x-init="cargarHorarios()"
>
    <div class="flex items-center justify-between mb-4">
        <div>
            <h2 class="text-lg font-semibold">Editor visual de horarios</h2>
            <p class="text-sm text-gray-400">
                Selecciona ciclo y sección, luego haz clic en cualquier celda para agregar o editar
            </p>
        </div>

        <a href="{{ route('horarios.index') }}"
           class="text-xs text-purple-300 hover:text-purple-100">
            ← Volver a horarios
        </a>
    </div>

    {{-- SELECTORES DE CICLO Y SECCIÓN --}}
    <div class="flex gap-4 mb-6">
        <div>
            <label class="block text-xs font-semibold mb-2">Ciclo</label>
            <select x-model="cicloSeleccionado"
                    @change="cargarHorarios()"
                    class="bg-slate-900/60 border border-white/10 rounded-lg px-4 py-2 text-sm">
                @for ($i = 1; $i <= 10; $i++)
                    <option value="{{ $i }}">Ciclo {{ $i }}</option>
                @endfor
            </select>
        </div>

        <div>
            <label class="block text-xs font-semibold mb-2">Sección</label>
            <select x-model="seccionSeleccionada"
                    @change="cargarHorarios()"
                    class="bg-slate-900/60 border border-white/10 rounded-lg px-4 py-2 text-sm">
                <option value="A">Sección A</option>
                <option value="B">Sección B</option>
                <option value="R">Sección R</option>
                <option value="U">Sección U</option>
            </select>
        </div>

        <div class="flex items-end">
            <button @click="cargarHorarios()"
                    class="px-4 py-2 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10"
                    :disabled="cargando">
                <span x-show="!cargando">🔄 Recargar</span>
                <span x-show="cargando">⏳ Cargando...</span>
            </button>
        </div>
    </div>

    {{-- INDICADOR DE CARGA --}}
    <div x-show="cargando" class="text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-purple-500"></div>
        <p class="text-sm text-gray-400 mt-2">Cargando horarios existentes...</p>
    </div>

    {{-- GRILLA DE HORARIOS --}}
    <div x-show="!cargando" class="overflow-x-auto">
        <table class="min-w-full text-xs border-collapse">
            <thead class="text-gray-300 border-b border-white/10">
                <tr>
                    <th class="w-32 py-2 px-2 text-left font-medium uppercase tracking-wide">Hora</th>
                    <th class="py-2 px-2 text-left font-medium uppercase tracking-wide">Lunes</th>
                    <th class="py-2 px-2 text-left font-medium uppercase tracking-wide">Martes</th>
                    <th class="py-2 px-2 text-left font-medium uppercase tracking-wide">Miércoles</th>
                    <th class="py-2 px-2 text-left font-medium uppercase tracking-wide">Jueves</th>
                    <th class="py-2 px-2 text-left font-medium uppercase tracking-wide">Viernes</th>
                    <th class="py-2 px-2 text-left font-medium uppercase tracking-wide">Sábado</th>
                </tr>
            </thead>
            <tbody class="text-gray-200">
                @php
                    $horas = [
                        ['07:00:00', '08:00:00', '7:00 am - 8:00 am'],
                        ['08:00:00', '09:00:00', '8:00 am - 9:00 am'],
                        ['09:00:00', '10:00:00', '9:00 am - 10:00 am'],
                        ['10:00:00', '11:00:00', '10:00 am - 11:00 am'],
                        ['11:00:00', '12:00:00', '11:00 am - 12:00 pm'],
                        ['12:00:00', '13:00:00', '12:00 pm - 1:00 pm'],
                        ['13:00:00', '14:00:00', '1:00 pm - 2:00 pm'],
                        ['14:00:00', '15:00:00', '2:00 pm - 3:00 pm'],
                        ['15:00:00', '16:00:00', '3:00 pm - 4:00 pm'],
                        ['16:00:00', '17:00:00', '4:00 pm - 5:00 pm'],
                        ['17:00:00', '18:00:00', '5:00 pm - 6:00 pm'],
                        ['18:00:00', '19:00:00', '6:00 pm - 7:00 pm'],
                        ['19:00:00', '20:00:00', '7:00 pm - 8:00 pm'],
                    ];
                    $dias = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
                @endphp

                @foreach ($horas as [$inicio, $fin, $label])
                    <tr class="border-b border-white/5 hover:bg-white/5">
                        <td class="py-2 px-2 font-mono text-[11px] text-gray-400">
                            {{ $label }}
                        </td>

                        @foreach ($dias as $dia)
                            <td class="py-2 px-2">
                                <button
                                    type="button"
                                    @click="abrirModal('{{ $dia }}', '{{ $inicio }}', '{{ $fin }}')"
                                    class="w-full min-h-[60px] p-2 rounded-lg border border-dashed border-white/20 
                                           hover:border-purple-500 hover:bg-purple-500/10 
                                           transition-all text-left text-[11px] leading-tight"
                                    :class="obtenerHorario('{{ $dia }}', '{{ $inicio }}') ? 
                                           'bg-slate-800/70 border-solid border-purple-500/50' : 
                                           'bg-slate-900/30'"
                                >
                                    <span x-text="obtenerHorario('{{ $dia }}', '{{ $inicio }}')" 
                                          class="block whitespace-pre-wrap"></span>
                                    <span x-show="!obtenerHorario('{{ $dia }}', '{{ $inicio }}')" 
                                          class="text-gray-500 text-[10px]">+ Agregar</span>
                                </button>
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- BOTÓN GUARDAR TODO Y SUBIR PDF --}}
    <div x-show="!cargando" class="flex justify-between items-center gap-2 mt-6 pt-4 border-t border-white/10">
        <button
            type="button"
            @click="$refs.modalResolucion.showModal()"
            class="px-4 py-2 text-sm rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold">
            📄 Subir resolución PDF
        </button>

        <div class="flex gap-2">
            <a href="{{ route('horarios.index') }}"
               class="px-4 py-2 text-sm rounded-lg border border-white/20 text-gray-300 hover:bg-white/10">
                Cancelar
            </a>
            <button
                type="button"
                @click="enviarFormulario()"
                class="px-4 py-2 text-sm rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold">
                💾 Guardar todos los horarios
            </button>
        </div>
    </div>

    {{-- MODAL PARA EDITAR --}}
    <div x-show="modalAbierto"
         x-cloak
         @click.self="modalAbierto = false"
         class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
        <div class="bg-slate-900 rounded-xl border border-white/10 w-full max-w-md p-6"
             @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold">
                    Editar horario
                </h3>
                <button @click="modalAbierto = false" 
                        class="text-gray-400 hover:text-gray-200 text-xl">
                    &times;
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-xs text-gray-400 mb-2">
                        <strong>Día:</strong> <span x-text="horarioEditando?.dia" class="capitalize"></span> | 
                        <strong>Hora:</strong> <span x-text="horarioEditando?.hora_inicio"></span> - <span x-text="horarioEditando?.hora_fin"></span>
                    </p>
                    <p class="text-xs text-gray-400">
                        <strong>Ciclo:</strong> <span x-text="cicloSeleccionado"></span> | 
                        <strong>Sección:</strong> <span x-text="seccionSeleccionada"></span>
                    </p>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-2">
                        Descripción del curso
                    </label>
                    <textarea
                        x-model="horarioEditando.descripcion"
                        rows="4"
                        class="w-full bg-slate-900/60 border border-white/10 rounded-lg px-3 py-2 text-sm"
                        placeholder="Ejemplo: ESTGEN - T - A - POOL12 - MARIA ROJAS"
                    ></textarea>
                    <p class="text-[11px] text-gray-400 mt-1">
                        Escribe el curso, tipo, grupo, aula y docente
                    </p>
                </div>

                <div class="flex justify-between gap-2 pt-2 border-t border-white/10">
                    <button
                        type="button"
                        @click="borrarHorario()"
                        x-show="horarioEditando?.descripcion"
                        class="px-3 py-1.5 text-xs rounded-lg bg-red-600 hover:bg-red-700 text-white font-semibold">
                        🗑️ Eliminar
                    </button>
                    
                    <div class="flex gap-2 ml-auto">
                        <button
                            type="button"
                            @click="modalAbierto = false"
                            class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10">
                            Cancelar
                        </button>
                        <button
                            type="button"
                            @click="guardarHorario()"
                            class="px-3 py-1.5 text-xs rounded-lg bg-purple-600 hover:bg-purple-700 text-white font-semibold">
                            Guardar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MODAL SUBIR RESOLUCIÓN PDF --}}
<dialog
    x-ref="modalResolucion"
    class="rounded-xl border border-white/10 w-full max-w-md bg-slate-900 text-gray-100"
>
    <form method="dialog">
        <div class="flex items-center justify-between px-4 py-3 border-b border-white/10">
            <h3 class="text-sm font-semibold">Subir resolución PDF</h3>
            <button type="submit" class="text-gray-400 hover:text-gray-200 text-xl">&times;</button>
        </div>
    </form>

    <form
        method="POST"
        action="{{ route('horarios.resolucion.store') }}"
        enctype="multipart/form-data"
        class="px-4 py-4 space-y-4"
    >
        @csrf

        <input type="hidden" name="ciclo" x-model="cicloSeleccionado">
        <input type="hidden" name="seccion" x-model="seccionSeleccionada">

        <div>
            <p class="text-xs text-gray-400 mb-3">
                <strong>Ciclo:</strong> <span x-text="cicloSeleccionado"></span> | 
                <strong>Sección:</strong> <span x-text="seccionSeleccionada"></span>
            </p>
        </div>

        <div>
            <label class="block mb-2 text-xs font-semibold">Archivo PDF</label>
            <input
                type="file"
                name="pdf"
                accept=".pdf"
                class="w-full text-xs text-gray-300"
                required
            >
        </div>

        <div>
            <label class="block mb-2 text-xs font-semibold">Comentario (opcional)</label>
            <textarea
                name="comentario"
                rows="3"
                class="w-full bg-slate-900/60 border border-white/10 rounded-lg px-3 py-2 text-sm"
                placeholder="Ejemplo: Resolución Decanal N° 123-2024"
            ></textarea>
        </div>

        <div class="flex justify-end gap-2 pt-2 border-t border-white/10">
            <button
                type="button"
                class="px-3 py-1.5 text-xs rounded-lg border border-white/20 text-gray-300 hover:bg-white/10"
                @click="$refs.modalResolucion.close()"
            >
                Cancelar
            </button>

            <button
                type="submit"
                class="px-3 py-1.5 text-xs rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold"
            >
                Subir resolución
            </button>
        </div>
    </form>
</dialog>

<style>
[x-cloak] { display: none !important; }
</style>
@endsection