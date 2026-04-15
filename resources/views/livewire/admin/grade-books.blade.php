<div wire:init="loadGradeBooks">

    {{-- Modal Rechazo --}}
    <div wire:ignore.self class="modal fade" id="RejectModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-times-circle"></i> Rechazar Cuadro
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Motivo del Rechazo <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-comment-alt"></i></span>
                            </div>
                            <textarea wire:model="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror"
                                rows="3" placeholder="Describa el motivo del rechazo..."></textarea>
                            @error('rejection_reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button wire:click.prevent="reject" type="button" class="btn btn-danger btn-sm"
                        wire:loading.attr="disabled" wire:target="reject">
                        <span wire:loading.remove wire:target="reject">
                            <i class="fas fa-times-circle"></i> Rechazar
                        </span>
                        <span wire:loading wire:target="reject">
                            <i class="fas fa-spinner fa-pulse"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if (!$viewingGradeBook)

        {{-- Tabla principal --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="row g-2">

                    {{-- Fila 1: Año, Nivel, Grado, Sección, Unidad --}}
                    <div class="col-md-2">
                        <select wire:model.live="filterYear" class="form-control form-control-sm">
                            <option value="">-- Todos los años --</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filterLevel" class="form-control form-control-sm">
                            <option value="">-- Todos los niveles --</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filterGrade" class="form-control form-control-sm"
                            {{ !$filterLevel ? 'disabled' : '' }}>
                            <option value="">-- Todos los grados --</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select wire:model.live="filterSection" class="form-control form-control-sm"
                            {{ !$filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Todas las secciones --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-1">
                        <select wire:model.live="filterUnit" class="form-control form-control-sm"
                            {{ !$filterSection ? 'disabled' : '' }}>
                            <option value="">-- U. --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">U{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select wire:model.live="filterStatus" class="form-control form-control-sm">
                            <option value="">-- Todos los estados --</option>
                            <option value="open">Abierto</option>
                            <option value="locked">Bloqueado</option>
                            <option value="rejected">Rechazado</option>
                            <option value="approved">Aprobado</option>
                        </select>
                    </div>

                    {{-- Fila 2: Mostrar + Buscar --}}
                    <div class="col-md-12 d-flex justify-content-end align-items-center mt-2">
                        <span class="mr-2 text-sm">Mostrar</span>
                        <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                placeholder="Buscar profesor, curso..." autocomplete="off">
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
                @if ($readyToLoad && count($gradeBooks))
                    <table class="table table-hover table-striped table-sm text-nowrap">
                        <thead>
                            <tr>
                                <th style="cursor:pointer" wire:click="order('created_at')">
                                    Fecha
                                    <i
                                        class="fas fa-sort{{ $sort === 'created_at' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                                </th>
                                <th>Profesor</th>
                                <th>Nivel</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Curso</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-center">Año</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gradeBooks as $gb)
                                <tr>
                                    <td>{{ $gb->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <div class="text-sm font-weight-bold">
                                            {{ $gb->assignment->professor->user->name }}</div>
                                        <small class="text-muted">ID: {{ $gb->id }}</small>
                                    </td>
                                    <td>{{ $gb->assignment->classroom->level->level_name }}</td>
                                    <td>{{ $gb->assignment->classroom->grade->grade_name }}</td>
                                    <td class="text-center">{{ $gb->assignment->classroom->section->section_name }}
                                    </td>
                                    <td>{{ $gb->assignment->pensumCourse->course->course_name }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-info">U{{ $gb->assignment->unit }}</span>
                                    </td>
                                    <td class="text-center">{{ $gb->assignment->classroom->year }}</td>
                                    <td class="text-center">
                                        @if ($gb->status === 'open')
                                            <span class="badge badge-success">Abierto</span>
                                        @elseif ($gb->status === 'locked')
                                            <span class="badge badge-secondary">Bloqueado</span>
                                        @elseif ($gb->status === 'rejected')
                                            <span class="badge badge-danger">Rechazado</span>
                                        @elseif ($gb->status === 'approved')
                                            <span class="badge badge-primary">Aprobado</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="openGradeBook({{ $gb->id }})"
                                            class="btn btn-xs btn-info shadow-sm" title="Ver detalle del cuadro">
                                            <i class="fas fa-eye"></i> Ver Notas
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-4 text-center text-muted">
                        @if (!$readyToLoad)
                            <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando cuadros...
                        @else
                            <i class="fas fa-book-open fa-3x mb-3 text-gray"></i><br>No se encontraron cuadros.
                        @endif
                    </div>
                @endif
            </div>

            @if ($readyToLoad && count($gradeBooks) && $gradeBooks->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">{{ $gradeBooks->links() }}</div>
                </div>
            @endif
        </div>
    @else
        {{-- Vista Detalle del Cuadro --}}
        @php
            $config = $viewingGradeBook->academicConfiguration;
            $normalMax = $viewingGradeBook->activities
                ->filter(fn($a) => !$a->activityType->is_extra)
                ->sum('max_points');
            $extraMax = $viewingGradeBook->activities->filter(fn($a) => $a->activityType->is_extra)->sum('max_points');
        @endphp

        <div class="card card-outline card-info">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button wire:click="closeGradeBook" class="btn btn-sm btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                        <strong>
                            {{ $viewingGradeBook->assignment->classroom->level->level_name }} —
                            {{ $viewingGradeBook->assignment->classroom->grade->grade_name }}
                            {{ $viewingGradeBook->assignment->classroom->section->section_name }} —
                            {{ $viewingGradeBook->assignment->pensumCourse->course->course_name }}
                            <span class="badge badge-secondary ml-1">U{{ $viewingGradeBook->assignment->unit }}</span>
                        </strong>
                        <span class="ml-2 text-muted text-sm">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>
                            {{ $viewingGradeBook->assignment->professor->user->name }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center">
                        @if ($viewingGradeBook->status === 'open')
                            <span class="badge badge-success mr-2">Abierto</span>
                        @elseif ($viewingGradeBook->status === 'locked')
                            <span class="badge badge-secondary mr-2">Bloqueado</span>
                        @elseif ($viewingGradeBook->status === 'rejected')
                            <span class="badge badge-danger mr-2">Rechazado</span>
                        @elseif ($viewingGradeBook->status === 'approved')
                            <span class="badge badge-primary mr-2">Aprobado</span>
                        @endif

                        @if ($viewingGradeBook->status === 'locked')
                            <button onclick="confirmApprove({{ $viewingGradeBook->id }})"
                                class="btn btn-sm btn-primary shadow-sm mr-1">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                            <button wire:click="openRejectModal({{ $viewingGradeBook->id }})" data-toggle="modal"
                                data-target="#RejectModal" class="btn btn-sm btn-danger shadow-sm">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">

                @if ($viewingGradeBook->status === 'rejected' && $viewingGradeBook->rejection_reason)
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle mr-1"></i>
                        <strong>Motivo de rechazo:</strong> {{ $viewingGradeBook->rejection_reason }}
                    </div>
                @endif

                {{-- Info configuración --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <div class="info-box mb-0">
                            <span class="info-box-icon {{ $normalMax == 100 ? 'bg-success' : 'bg-warning' }}">
                                <i class="fas fa-star"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sm">Puntos Normales</span>
                                <span class="info-box-number">{{ number_format($normalMax, 2) }} / 100</span>
                            </div>
                        </div>
                    </div>
                    @if ($extraMax > 0)
                        <div class="col-md-3">
                            <div class="info-box mb-0">
                                <span class="info-box-icon bg-warning">
                                    <i class="fas fa-plus-circle"></i>
                                </span>
                                <div class="info-box-content">
                                    <span class="info-box-text text-sm">Puntos Extra</span>
                                    <span class="info-box-number">{{ number_format($extraMax, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <div class="info-box mb-0">
                            <span class="info-box-icon bg-info">
                                <i class="fas fa-chart-line"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sm">Proceso de Mejora</span>
                                <span class="info-box-number text-sm">
                                    @if ($config->improvement_type === 'none')
                                        Ninguno
                                    @elseif ($config->improvement_type === 'full')
                                        100%
                                    @elseif ($config->improvement_type === 'percentage')
                                        {{ $config->improvement_percentage }}%
                                    @elseif ($config->improvement_type === 'additive')
                                        Suma
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="info-box mb-0">
                            <span class="info-box-icon bg-secondary">
                                <i class="fas fa-users"></i>
                            </span>
                            <div class="info-box-content">
                                <span class="info-box-text text-sm">Estudiantes</span>
                                <span class="info-box-number">{{ $students->count() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @if ($viewingGradeBook->activities->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 bg-white">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width:220px">Estudiante</th>
                                    @foreach ($viewingGradeBook->activities as $activity)
                                        <th class="text-center {{ $activity->activityType->is_extra ? 'table-warning' : '' }}"
                                            style="min-width:130px">
                                            <div>{{ $activity->name }}</div>
                                            <small class="text-muted">{{ $activity->activityType->name }}</small>
                                            <div>
                                                <span class="badge badge-secondary">{{ $activity->max_points }}
                                                    pts</span>
                                            </div>
                                        </th>
                                    @endforeach
                                    <th class="text-center bg-light" style="min-width:100px">
                                        Normal
                                        <small class="d-block text-muted font-weight-normal">Máx:
                                            {{ number_format($normalMax, 2) }}</small>
                                    </th>
                                    @if ($extraMax > 0)
                                        <th class="text-center bg-warning" style="min-width:100px">
                                            Extra
                                            <small class="d-block text-muted font-weight-normal">Máx:
                                                {{ number_format($extraMax, 2) }}</small>
                                        </th>
                                    @endif
                                    <th class="text-center bg-light" style="min-width:100px">
                                        Total
                                        <small class="d-block text-muted font-weight-normal">Máx:
                                            {{ number_format($normalMax + $extraMax, 2) }}</small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    @php
                                        // Variables para sumar en tiempo real lo que se ve en la fila
                                        $calcNormal = 0;
                                        $calcExtra = 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $student->user->name }}</td>
                                        @foreach ($viewingGradeBook->activities as $activity)
                                            @php
                                                $score = $activity->scores->firstWhere('student_id', $student->id);
                                                $rawScore = $score ? (float) $score->score : null;
                                                $improvement = $score ? $score->improvement_score : null;
                                                $effective = $score
                                                    ? $config->effectiveScore(
                                                        (float) $rawScore,
                                                        $improvement,
                                                        (float) $activity->max_points,
                                                    )
                                                    : null;

                                                // Sumamos la nota a la categoría correspondiente
                                                if (!is_null($effective)) {
                                                    if ($activity->activityType->is_extra) {
                                                        $calcExtra += $effective;
                                                    } else {
                                                        $calcNormal += $effective;
                                                    }
                                                }
                                            @endphp
                                            <td
                                                class="text-center {{ $activity->activityType->is_extra ? 'table-warning' : '' }}">
                                                @if (!is_null($rawScore))
                                                    <span>{{ number_format($rawScore, 2) }}</span>
                                                    @if ($config->improvement_type !== 'none' && !is_null($improvement) && $improvement > 0)
                                                        <br>
                                                        <small class="text-success" title="Mejora">
                                                            <i class="fas fa-arrow-up"></i>
                                                            {{ number_format($improvement, 2) }}
                                                        </small>
                                                        <br>
                                                        <span class="badge badge-success">
                                                            {{ number_format($effective, 2) }}
                                                        </span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        {{-- Impresión de los totales calculados dinámicamente --}}
                                        <td class="text-center font-weight-bold">
                                            {{ number_format($calcNormal, 2) }}
                                        </td>
                                        @if ($extraMax > 0)
                                            <td class="text-center font-weight-bold text-warning">
                                                {{ number_format($calcExtra, 2) }}
                                            </td>
                                        @endif
                                        <td class="text-center font-weight-bold text-primary">
                                            {{ number_format($calcNormal + $calcExtra, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border text-center text-muted">
                        <i class="fas fa-book-open fa-3x mb-2"></i><br>
                        Este cuadro no tiene actividades registradas aún.
                    </div>
                @endif

            </div>
        </div>

    @endif

    @push('js')
        <script>
            function confirmApprove(id) {
                Swal.fire({
                    title: '¿Aprobar este cuadro?',
                    text: 'Una vez aprobado no podrá ser modificado.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, aprobar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.approve(id);
                    }
                });
            }

            document.addEventListener('livewire:init', () => {
                Livewire.on('closeModalMessaje', (event) => {
                    let payload = event[0] || event;
                    if (payload.modalId) {
                        $('#' + payload.modalId).modal('hide');
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                    }
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                });

                Livewire.on('showAlert', (event) => {
                    let payload = event[0] || event;
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3500
                    });
                });
            });
        </script>
    @endpush
</div>
