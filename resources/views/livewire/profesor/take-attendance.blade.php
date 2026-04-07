<div wire:init="loadAssignments">

    @if (!$assignment)
        {{-- ============================================================
             LISTA DE ASIGNACIONES
             ============================================================ --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="m-0 text-bold text-secondary">
                            <i class="fas fa-user-check mr-1"></i>
                            Asistencia — Mis Asignaciones {{ date('Y') }}
                        </h5>
                    </div>
                    <div class="col-md-8 d-flex justify-content-end align-items-center">
                        <div class="input-group input-group-sm" style="width: 280px;">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                placeholder="Buscar curso o aula...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                @if ($readyToLoad && $assignments->count())
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr>
                                <th>Nivel</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Curso</th>
                                <th>Unidades</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($assignments as $group)
                                @php $first = $group->first(); @endphp
                                <tr>
                                    <td>{{ $first->classroom->level->level_name }}</td>
                                    <td>{{ $first->classroom->grade->grade_name }}</td>
                                    <td>{{ $first->classroom->section->section_name }}</td>
                                    <td>{{ $first->pensumCourse->course->course_name }}</td>
                                    <td>
                                        @foreach ($group as $item)
                                            <button wire:click="openAssignment({{ $item->id }})"
                                                class="btn btn-sm {{ $item->attendance_records_count > 0 ? 'btn-success' : 'btn-outline-secondary' }} shadow-sm mr-1"
                                                title="{{ $item->attendance_records_count }} sesión(es) registrada(s)">
                                                <i class="fas fa-user-check mr-1"></i> U{{ $item->unit }}
                                                @if ($item->attendance_records_count > 0)
                                                    <span
                                                        class="badge badge-light ml-1">{{ $item->attendance_records_count }}</span>
                                                @endif
                                            </button>
                                        @endforeach
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-4 text-center text-muted">
                        @if (!$readyToLoad)
                            <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando asignaciones...
                        @else
                            <i class="fas fa-chalkboard fa-3x mb-3 text-gray"></i><br>No tienes asignaciones para este
                            año.
                        @endif
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- ============================================================
             PANEL DE ASISTENCIA
             ============================================================ --}}

        {{-- Encabezado --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <button wire:click="closeAssignment" class="btn btn-sm btn-secondary mr-2">
                    <i class="fas fa-arrow-left"></i> Volver
                </button>
                <strong>
                    {{ $assignment->classroom->level->level_name }} —
                    {{ $assignment->classroom->grade->grade_name }}
                    {{ $assignment->classroom->section->section_name }} —
                    {{ $assignment->pensumCourse->course->course_name }}
                    <span class="badge badge-secondary ml-1">U{{ $assignment->unit }}</span>
                </strong>
            </div>
            <button wire:click="openPdfModal" class="btn btn-sm btn-danger shadow-sm">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </button>
        </div>

        {{-- ============================================================
             TARJETA: FORMULARIO DE ASISTENCIA
             ============================================================ --}}
        <div id="attendance-form-card" class="card card-primary card-outline mb-3">
            <div class="card-header">
                <h5 class="m-0 text-bold">
                    <i class="fas fa-{{ $currentRecordId ? 'edit' : 'plus-circle' }} mr-1"></i>
                    {{ $currentRecordId ? 'Editando registro del' : 'Nueva asistencia' }}
                    @if ($currentRecordId)
                        <span class="text-primary">{{ \Carbon\Carbon::parse($attendanceDate)->format('d/m/Y') }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body">

                {{-- Selector de fecha --}}
                <div class="row align-items-end mb-3">
                    <div class="col-md-4 form-group mb-0">
                        <label class="text-sm mb-1">Fecha <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-day"></i></span>
                            </div>
                            <input type="date" wire:model="attendanceDate"
                                class="form-control @error('attendanceDate') is-invalid @enderror">
                            @error('attendanceDate')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-auto">
                        <button wire:click="loadDate" class="btn btn-primary btn-sm shadow-sm"
                            wire:loading.attr="disabled" wire:target="loadDate">
                            <span wire:loading.remove wire:target="loadDate">
                                <i class="fas fa-search"></i> Cargar
                            </span>
                            <span wire:loading wire:target="loadDate">
                                <i class="fas fa-spinner fa-pulse"></i>
                            </span>
                        </button>
                        @if ($formLoaded)
                            <button wire:click="clearForm" class="btn btn-outline-secondary btn-sm ml-1"
                                title="Limpiar formulario">
                                <i class="fas fa-times"></i> Limpiar
                            </button>
                        @endif
                    </div>
                </div>

                {{-- Tabla de estudiantes --}}
                @if ($formLoaded)
                    @if ($students->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm mb-3 bg-white">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Estudiante</th>
                                        <th class="text-center" style="width:120px">Presente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $index => $student)
                                        <tr
                                            class="{{ isset($entries[$student->id]) && !$entries[$student->id] ? 'table-danger' : '' }}">
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->user->full_full_name }}</td>
                                            <td class="text-center">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        wire:model.live="entries.{{ $student->id }}"
                                                        class="custom-control-input" id="student_{{ $student->id }}">
                                                    <label class="custom-control-label"
                                                        for="student_{{ $student->id }}">
                                                    </label>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light">
                                    <tr>
                                        <td colspan="2" class="text-right font-weight-bold text-sm">
                                            Presentes:
                                            <span
                                                class="text-success">{{ collect($entries)->filter()->count() }}</span>
                                            &nbsp;/&nbsp; Ausentes:
                                            <span
                                                class="text-danger">{{ collect($entries)->reject(fn($v) => $v)->count() }}</span>
                                        </td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <div class="text-right">
                            <button wire:click.prevent="saveAttendance" class="btn btn-success btn-sm shadow-sm"
                                wire:loading.attr="disabled" wire:target="saveAttendance">
                                <span wire:loading.remove wire:target="saveAttendance">
                                    <i class="fas fa-save"></i> Guardar Asistencia
                                </span>
                                <span wire:loading wire:target="saveAttendance">
                                    <i class="fas fa-spinner fa-pulse"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            No hay estudiantes activos en esta sección.
                        </div>
                    @endif
                @else
                    <div class="alert alert-light border text-center text-muted py-3">
                        <i class="fas fa-info-circle mr-1"></i>
                        Selecciona una fecha y haz clic en <strong>Cargar</strong> para registrar o editar asistencia.
                    </div>
                @endif

            </div>
        </div>

        {{-- ============================================================
             TARJETA: HISTORIAL
             ============================================================ --}}
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="m-0 text-bold text-secondary">
                            <i class="fas fa-history mr-1"></i> Historial de Asistencia
                        </h5>
                    </div>
                    <div class="col-md-4">
                        <select wire:model.live="historyMonth" class="form-control form-control-sm">
                            <option value="">-- Todos los meses --</option>
                            @foreach (['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'] as $num => $name)
                                <option value="{{ $num }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="date" wire:model.live="historyDate" class="form-control form-control-sm"
                            placeholder="Buscar fecha exacta">
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                @if ($historyRecords && $historyRecords->count() > 0)
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th class="text-center text-success">Presentes</th>
                                <th class="text-center text-danger">Ausentes</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($historyRecords as $record)
                                @php
                                    $present = $record->entries->where('present', true)->count();
                                    $absent = $record->entries->where('present', false)->count();
                                    $total = $record->entries->count();
                                @endphp
                                <tr class="{{ $currentRecordId === $record->id ? 'table-primary' : '' }}">
                                    <td>
                                        <i class="fas fa-calendar-day text-muted mr-1"></i>
                                        {{ $record->date->format('d/m/Y') }}
                                        @if ($currentRecordId === $record->id)
                                            <span class="badge badge-primary ml-1">Editando</span>
                                        @endif
                                    </td>
                                    <td class="text-center font-weight-bold text-success">{{ $present }}</td>
                                    <td class="text-center font-weight-bold text-danger">{{ $absent }}</td>
                                    <td class="text-center">{{ $total }}</td>
                                    <td class="text-center">
                                        <button wire:click="editRecord({{ $record->id }})"
                                            class="btn btn-xs btn-warning shadow-sm px-2"
                                            title="Editar este registro">
                                            <i class="fas fa-edit"></i> Editar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-3 py-2">
                        {{ $historyRecords->links() }}
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                        No hay registros de asistencia
                        {{ $historyMonth || $historyDate ? 'para el filtro seleccionado.' : 'aún para esta asignación.' }}
                    </div>
                @endif
            </div>
        </div>
    @endif

    {{-- ============================================================
         MODAL PDF
         ============================================================ --}}
    @if ($showPdfModal)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-file-pdf mr-2"></i> Generar PDF de Asistencia
                        </h5>
                        <button type="button" class="close text-white" wire:click="closePdfModal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <p class="text-muted text-sm mb-3">
                            Selecciona el rango de fechas. El PDF se generará con las sesiones registradas en ese
                            período.
                            Si el rango contiene muchas fechas, se partirá en múltiples páginas automáticamente.
                        </p>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Desde <span class="text-danger">*</span></label>
                                <input type="date" wire:model="pdfFrom"
                                    class="form-control form-control-sm @error('pdfFrom') is-invalid @enderror">
                                @error('pdfFrom')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Hasta <span class="text-danger">*</span></label>
                                <input type="date" wire:model="pdfTo"
                                    class="form-control form-control-sm @error('pdfTo') is-invalid @enderror">
                                @error('pdfTo')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closePdfModal">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-danger btn-sm shadow-sm" wire:click="downloadPdf"
                            wire:loading.attr="disabled" wire:target="downloadPdf">
                            <span wire:loading.remove wire:target="downloadPdf">
                                <i class="fas fa-download mr-1"></i> Descargar PDF
                            </span>
                            <span wire:loading wire:target="downloadPdf">
                                <i class="fas fa-spinner fa-pulse"></i> Generando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('showAlert', (event) => {
                    let payload = event[0] || event;
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3500,
                    });
                });

                Livewire.on('toastMessage', (event) => {
                    let payload = event[0] || event;
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                    Toast.fire({
                        icon: payload.type,
                        title: payload.message
                    });
                });

                Livewire.on('downloadAttendancePdf', (event) => {
                    let payload = event[0] || event;
                    window.open(payload.url, '_blank');
                });

                Livewire.on('scrollToForm', () => {
                    const el = document.getElementById('attendance-form-card');
                    if (el) el.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                });
            });
        </script>
    @endpush
</div>
