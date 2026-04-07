<div>
    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card card-success card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-filter mr-1"></i> Filtros de Asistencia
            </h5>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <select wire:model.live="filterYear"
                            class="form-control @error('filterYear') is-invalid @enderror">
                            <option value="">-- Año --</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Nivel <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                        </div>
                        <select wire:model.live="filterLevel" class="form-control" {{ !$filterYear ? 'disabled' : '' }}>
                            <option value="">-- Nivel --</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Grado <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                        </div>
                        <select wire:model.live="filterGrade" class="form-control"
                            {{ !$filterLevel ? 'disabled' : '' }}>
                            <option value="">-- Grado --</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Sección <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                        </div>
                        <select wire:model.live="filterSection" class="form-control"
                            {{ !$filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Sección --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Curso <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-book"></i></span>
                        </div>
                        <select wire:model.live="filterCourse" class="form-control"
                            {{ !$filterSection ? 'disabled' : '' }}>
                            <option value="">-- Curso --</option>
                            @foreach ($pensumCourses as $pc)
                                <option value="{{ $pc->id }}">{{ $pc->course->course_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Unidad <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                        </div>
                        <select wire:model.live="filterUnit" class="form-control"
                            {{ !$filterCourse ? 'disabled' : '' }}>
                            <option value="">-- Unidad --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">Unidad {{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ============================================================
         RESULTADOS
         ============================================================ --}}
    @if ($assignmentId && $assignment)

        {{-- Encabezado del resultado --}}
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <span class="text-muted text-sm">Mostrando asistencia de:</span>
                <strong class="ml-2">
                    {{ $assignment->classroom->level->level_name }} —
                    {{ $assignment->classroom->grade->grade_name }}
                    {{ $assignment->classroom->section->section_name }} —
                    {{ $assignment->pensumCourse->course->course_name }}
                    <span class="badge badge-secondary ml-1">U{{ $assignment->unit }}</span>
                </strong>
                <span class="ml-3 text-muted text-sm">
                    <i class="fas fa-chalkboard-teacher mr-1"></i>
                    {{ $assignment->professor->user->name }}
                </span>
            </div>
            <button wire:click="openPdfModal" class="btn btn-sm btn-danger shadow-sm">
                <i class="fas fa-file-pdf"></i> Generar PDF
            </button>
        </div>

        <div class="card card-outline card-success">
            <div class="card-header">
                <h5 class="m-0 text-bold text-secondary">
                    <i class="fas fa-history mr-1"></i> Sesiones Registradas
                    <span class="badge badge-secondary ml-2">{{ $attendanceRecords->total() }} sesión(es)</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @if ($attendanceRecords->count() > 0)
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th class="text-center text-success">Presentes</th>
                                <th class="text-center text-danger">Ausentes</th>
                                <th class="text-center">Sin registro</th>
                                <th class="text-center">Total alumnos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($attendanceRecords as $record)
                                @php
                                    $present = $record->entries->where('present', true)->count();
                                    $absent = $record->entries->where('present', false)->count();
                                    $recorded = $record->entries->count();
                                    $noRecord = max(0, $totalStudents - $recorded);
                                @endphp
                                <tr>
                                    <td>
                                        <i class="fas fa-calendar-day text-muted mr-1"></i>
                                        {{ $record->date->format('d/m/Y') }}
                                    </td>
                                    <td class="text-center font-weight-bold text-success">{{ $present }}</td>
                                    <td class="text-center font-weight-bold text-danger">{{ $absent }}</td>
                                    <td class="text-center text-muted">{{ $noRecord }}</td>
                                    <td class="text-center">{{ $totalStudents }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-3 py-2">
                        {{ $attendanceRecords->links() }}
                    </div>
                @else
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-calendar-times fa-2x mb-2"></i><br>
                        No hay registros de asistencia para esta asignación.
                    </div>
                @endif
            </div>
        </div>
    @elseif ($filterUnit !== '')
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No se encontró una asignación con los filtros seleccionados.
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
                            Selecciona el rango de fechas a incluir en el PDF.
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
                Livewire.on('downloadAttendancePdfAdmin', (event) => {
                    let payload = event[0] || event;
                    window.open(payload.url, '_blank');
                });
            });
        </script>
    @endpush
</div>
