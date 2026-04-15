<div wire:init="loadGradeBooks">

    {{-- Señuelo: evita que Chrome autorrellene el buscador con credenciales guardadas --}}
    <div style="position:fixed;top:-200px;left:-200px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <input type="text" autocomplete="username" tabindex="-1">
        <input type="password" autocomplete="current-password" tabindex="-1">
    </div>

    @if (!$gradeBook)

        {{-- Lista de asignaciones --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-4">
                        <h5 class="m-0 text-bold text-secondary">
                            <i class="fas fa-chalkboard-teacher mr-1"></i>
                            Mis Asignaciones {{ date('Y') }}
                        </h5>
                    </div>
                    <div class="col-md-8 d-flex justify-content-end align-items-center">
                        <div class="input-group input-group-sm" style="width: 280px;">
                            <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                                name="buscar" id="buscador" placeholder="Buscar curso o aula..." autocomplete="search">
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
                            @foreach ($assignments as $groupKey => $group)
                                @php $first = $group->first(); @endphp
                                <tr>
                                    <td>{{ $first->classroom->level->level_name }}</td>
                                    <td>{{ $first->classroom->grade->grade_name }}</td>
                                    <td>{{ $first->classroom->section->section_name }}</td>
                                    <td>{{ $first->pensumCourse->course->course_name }}</td>
                                    <td>
                                        @foreach ($group as $assignment)
                                            @php
                                                $status = $assignment->gradeBook?->status;
                                                $btnClass = match ($status) {
                                                    'open' => 'btn-success',
                                                    'locked' => 'btn-secondary',
                                                    'rejected' => 'btn-danger',
                                                    'approved' => 'btn-primary',
                                                    default => 'btn-outline-secondary',
                                                };
                                                $icon = match ($status) {
                                                    'open' => 'fa-book-open',
                                                    'locked' => 'fa-lock',
                                                    'rejected' => 'fa-times-circle',
                                                    'approved' => 'fa-check-circle',
                                                    default => 'fa-plus-circle',
                                                };
                                                $title = match ($status) {
                                                    'open' => 'Abierto',
                                                    'locked' => 'Bloqueado',
                                                    'rejected' => 'Rechazado',
                                                    'approved' => 'Aprobado',
                                                    default => 'Sin cuadro',
                                                };
                                            @endphp
                                            <button wire:click="openGradeBook({{ $assignment->id }})"
                                                class="btn btn-sm {{ $btnClass }} shadow-sm mr-1"
                                                title="{{ $title }}">
                                                <i class="fas {{ $icon }} mr-1"></i> U{{ $assignment->unit }}
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
        {{-- Cuadro de calificaciones --}}
        <div class="card card-outline card-primary">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button wire:click="closeGradeBook" class="btn btn-sm btn-secondary mr-2">
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
                    <div class="d-flex align-items-center">
                        @if ($gradeBook->status === 'open')
                            <span class="badge badge-success mr-2">Abierto</span>
                        @elseif ($gradeBook->status === 'locked')
                            <span class="badge badge-secondary mr-2">Bloqueado</span>
                        @elseif ($gradeBook->status === 'rejected')
                            <span class="badge badge-danger mr-2">Rechazado</span>
                        @elseif ($gradeBook->status === 'approved')
                            <span class="badge badge-primary mr-2">Aprobado</span>
                        @endif

                        @if ($gradeBook->isApproved())
                            <a href="{{ route('profesor.grade-books.pdf', $gradeBook->id) }}" target="_blank"
                                class="btn btn-sm btn-danger shadow-sm">
                                <i class="fas fa-file-pdf"></i> Descargar PDF
                            </a>
                        @endif

                        @if ($gradeBook->isOpen())
                            <button wire:click="openActivityForm" class="btn btn-sm btn-primary mr-2 shadow-sm">
                                <i class="fas fa-plus"></i> Nueva Actividad
                            </button>
                            <button onclick="confirmLock()" class="btn btn-sm btn-secondary shadow-sm">
                                <i class="fas fa-lock"></i> Bloquear Cuadro
                            </button>
                        @endif

                        @if ($gradeBook->isRejected())
                            <button onclick="confirmReopen()" class="btn btn-sm btn-warning shadow-sm">
                                <i class="fas fa-lock-open"></i> Reabrir para Edición
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">

                @if ($gradeBook->isRejected() && $gradeBook->rejection_reason)
                    <div class="alert alert-danger">
                        <i class="fas fa-times-circle mr-1"></i>
                        <strong>Motivo de rechazo:</strong> {{ $gradeBook->rejection_reason }}
                    </div>
                @endif

                {{-- Formulario de actividad --}}
                @if ($showActivityForm && $gradeBook->isOpen())
                    <div class="card border-primary shadow-sm mb-3">
                        <div class="card-header bg-white">
                            <h6 class="card-title text-bold text-primary m-0">
                                <i class="fas {{ $editingActivityId ? 'fa-edit' : 'fa-plus' }} mr-1"></i>
                                {{ $editingActivityId ? 'Editar Actividad' : 'Nueva Actividad' }}
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Tipo <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-tag"></i></span>
                                        </div>
                                        <select wire:model.live="activity_type_id"
                                            class="form-control @error('activity_type_id') is-invalid @enderror">
                                            <option value="">-- Seleccione --</option>
                                            @if ($configMode === 'assigned')
                                                @foreach ($configActivities as $configActivity)
                                                    @php
                                                        $usedCount = $gradeBook->activities
                                                            ->where(
                                                                'activity_type_id',
                                                                $configActivity->activity_type_id,
                                                            )
                                                            ->count();
                                                        $remaining = $configActivity->quantity - $usedCount;
                                                        // Si estamos editando y el tipo es el mismo, no restamos
                                                        if ($editingActivityId) {
                                                            $editingType = $gradeBook->activities->firstWhere(
                                                                'id',
                                                                $editingActivityId,
                                                            );
                                                            if (
                                                                $editingType &&
                                                                $editingType->activity_type_id ==
                                                                    $configActivity->activity_type_id
                                                            ) {
                                                                $remaining++;
                                                            }
                                                        }
                                                    @endphp
                                                    @if ($remaining > 0)
                                                        <option value="{{ $configActivity->activity_type_id }}">
                                                            {{ $configActivity->activityType->name }}
                                                            ({{ $remaining }}
                                                            restante{{ $remaining > 1 ? 's' : '' }})
                                                        </option>
                                                    @endif
                                                @endforeach
                                            @else
                                                @foreach ($activityTypes as $type)
                                                    <option value="{{ $type->id }}">
                                                        {{ $type->name }}{{ $type->is_extra ? ' (Extra)' : '' }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('activity_type_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Nombre <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-pencil-alt"></i></span>
                                        </div>
                                        <input type="text" wire:model="activityName"
                                            class="form-control @error('activityName') is-invalid @enderror"
                                            placeholder="Ej. Tarea 1">
                                        @error('activityName')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-2 form-group mb-3">
                                    <label class="text-sm mb-1">Puntos <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-star"></i></span>
                                        </div>
                                        <input type="number" wire:model="max_points"
                                            class="form-control @error('max_points') is-invalid @enderror"
                                            placeholder="Ej. 10" min="0.01" step="0.01"
                                            {{ $configMode === 'assigned' ? 'readonly' : '' }}>
                                        @error('max_points')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    @if ($configMode === 'assigned')
                                        <small class="text-muted">Definido por la configuración académica.</small>
                                    @endif
                                </div>
                                <div class="col-md-2 form-group mb-3">
                                    <label class="text-sm mb-1">Orden <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i
                                                    class="fas fa-sort-numeric-up"></i></span>
                                        </div>
                                        <input type="number" wire:model="ordering"
                                            class="form-control @error('ordering') is-invalid @enderror"
                                            min="0">
                                        @error('ordering')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-right bg-white">
                            <button wire:click="resetActivityForm" class="btn btn-secondary btn-sm mr-2">
                                <i class="fas fa-times"></i> Cancelar
                            </button>
                            <button wire:click.prevent="saveActivity" class="btn btn-primary btn-sm"
                                wire:loading.attr="disabled" wire:target="saveActivity">
                                <span wire:loading.remove wire:target="saveActivity">
                                    <i class="fas fa-save"></i> {{ $editingActivityId ? 'Actualizar' : 'Guardar' }}
                                </span>
                                <span wire:loading wire:target="saveActivity">
                                    <i class="fas fa-spinner fa-pulse"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Formulario de calificaciones --}}
                @if ($showScoresForm && $scoringActivity)
                    @php
                        $config = $gradeBook->academicConfiguration;
                        $hasImprovement = $config->improvement_type !== 'none';
                        $improvementLabel = match ($config->improvement_type) {
                            'none' => 'Sin proceso de mejora',
                            'full' => 'Mejora (máx: ' . $scoringActivity->max_points . ' pts)',
                            'percentage' => 'Mejora (máx: ' .
                                number_format(
                                    ($scoringActivity->max_points * $config->improvement_percentage) / 100,
                                    2,
                                ) .
                                ' pts — ' .
                                $config->improvement_percentage .
                                '%)',
                            'additive' => 'Mejora (suma sin sobrepasar ' . $scoringActivity->max_points . ' pts)',
                            default => 'Mejora',
                        };
                    @endphp
                    <div class="card border-success shadow-sm mb-3">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="card-title text-bold text-success m-0">
                                    <i class="fas fa-pen mr-1"></i>
                                    Calificaciones — {{ $scoringActivity->name }}
                                    <span class="badge badge-secondary ml-1">Máx: {{ $scoringActivity->max_points }}
                                        pts</span>
                                </h6>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Proceso de mejora:
                                    @if ($config->improvement_type === 'none')
                                        <span class="badge badge-secondary">Sin proceso de mejora</span>
                                    @elseif ($config->improvement_type === 'full')
                                        <span class="badge badge-success">100% — hasta
                                            {{ $scoringActivity->max_points }} pts</span>
                                    @elseif ($config->improvement_type === 'percentage')
                                        <span class="badge badge-warning">{{ $config->improvement_percentage }}% —
                                            hasta
                                            {{ number_format(($scoringActivity->max_points * $config->improvement_percentage) / 100, 2) }}
                                            pts</span>
                                    @elseif ($config->improvement_type === 'additive')
                                        <span class="badge badge-info">Suma — depende de la nota original</span>
                                    @endif
                                </small>
                            </div>
                            <button wire:click="closeScores" class="btn btn-sm btn-secondary">
                                <i class="fas fa-times"></i> Cerrar
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover table-striped mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width:40px">#</th>
                                        <th>Estudiante</th>
                                        <th style="width:160px">
                                            Nota
                                            <small class="d-block text-muted font-weight-normal">Máx:
                                                {{ $scoringActivity->max_points }}</small>
                                        </th>
                                        @if ($hasImprovement)
                                            <th style="width:200px">
                                                {{ $improvementLabel }}
                                                <small class="d-block text-muted font-weight-normal">Dejar vacío si no
                                                    aplica</small>
                                            </th>
                                        @endif
                                        <th style="width:120px" class="text-center">
                                            Nota Efectiva
                                            @if ($hasImprovement)
                                                <small class="d-block text-muted font-weight-normal">Con mejora</small>
                                            @endif
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($students as $index => $student)
                                        @php
                                            $currentScore = $scores[$student->id] ?? 0;
                                            $currentImprovement = $improvement_scores[$student->id] ?? null;
                                            $effective = $config->effectiveScore(
                                                (float) $currentScore,
                                                is_numeric($currentImprovement) ? (float) $currentImprovement : null,
                                                (float) $scoringActivity->max_points,
                                            );
                                            $maxImprovement = $config->maxImprovementScore(
                                                (float) $currentScore,
                                                (float) $scoringActivity->max_points,
                                            );
                                        @endphp
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->user->full_full_name }}</td>
                                            <td>
                                                <input type="number" wire:model.live="scores.{{ $student->id }}"
                                                    class="score-input form-control form-control-sm @error('scores.' . $student->id) is-invalid @enderror"
                                                    data-index="{{ $index }}" data-type="score"
                                                    min="0" max="{{ $scoringActivity->max_points }}"
                                                    step="0.01">
                                                @error('scores.' . $student->id)
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            @if ($hasImprovement)
                                                <td>
                                                    <input type="number"
                                                        wire:model.live="improvement_scores.{{ $student->id }}"
                                                        class="improvement-input form-control form-control-sm @error('improvement_scores.' . $student->id) is-invalid @enderror"
                                                        data-index="{{ $index }}" data-type="improvement"
                                                        min="0" max="{{ $maxImprovement }}" step="0.01"
                                                        placeholder="—">
                                                    @error('improvement_scores.' . $student->id)
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            @endif
                                            <td class="text-center">
                                                <span
                                                    class="badge {{ $effective > $currentScore ? 'badge-success' : 'badge-secondary' }} px-2 py-1">
                                                    {{ number_format($effective, 2) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-right bg-white">
                            <button wire:click.prevent="saveScores" class="btn btn-success btn-sm"
                                wire:loading.attr="disabled" wire:target="saveScores">
                                <span wire:loading.remove wire:target="saveScores">
                                    <i class="fas fa-save"></i> Guardar Calificaciones
                                </span>
                                <span wire:loading wire:target="saveScores">
                                    <i class="fas fa-spinner fa-pulse"></i> Guardando...
                                </span>
                            </button>
                        </div>
                    </div>
                @endif

                {{-- Tabla del cuadro --}}
                @if ($gradeBook->activities->count() > 0)
                    @php
                        $normalMax = $gradeBook->activities
                            ->filter(fn($a) => !$a->activityType->is_extra)
                            ->sum('max_points');
                        $extraMax = $gradeBook->activities
                            ->filter(fn($a) => $a->activityType->is_extra)
                            ->sum('max_points');
                    @endphp

                    {{-- Resumen de puntos --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
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
                            <div class="col-md-4">
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
                    </div>

                    @if ($normalMax != 100 && $gradeBook->isOpen())
                        <div class="alert alert-warning py-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Las actividades normales deben sumar <strong>100 puntos</strong> para poder bloquear el
                            cuadro. Actualmente suman <strong>{{ number_format($normalMax, 2) }}</strong>.
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0 bg-white">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width:200px">Estudiante</th>
                                    @foreach ($gradeBook->activities as $activity)
                                        <th class="text-center {{ $activity->activityType->is_extra ? 'table-warning' : '' }}"
                                            style="min-width:120px">
                                            <div>{{ $activity->name }}</div>
                                            <small class="text-muted">{{ $activity->activityType->name }}</small>
                                            <div><span class="badge badge-secondary">{{ $activity->max_points }}
                                                    pts</span></div>
                                            @if ($gradeBook->isOpen())
                                                <div class="mt-1">
                                                    <button wire:click="openExcelModal({{ $activity->id }})"
                                                        class="btn btn-xs btn-info px-1 text-white"
                                                        title="Descargar Plantilla Excel">
                                                        <i class="fas fa-file-excel"></i>
                                                    </button>
                                                    <button wire:click="openScores({{ $activity->id }})"
                                                        class="btn btn-xs btn-success px-1" title="Calificar">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <button wire:click="editActivity({{ $activity->id }})"
                                                        class="btn btn-xs btn-warning px-1" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        onclick="confirmDeleteActivity({{ $activity->id }}, '{{ addslashes($activity->name) }}')"
                                                        class="btn btn-xs btn-danger px-1" title="Eliminar">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            @endif
                                        </th>
                                    @endforeach
                                    <th class="text-center bg-light" style="min-width:100px">
                                        Normal
                                    </th>
                                    @if ($extraMax > 0)
                                        <th class="text-center bg-warning" style="min-width:100px">
                                            Extra
                                        </th>
                                    @endif
                                    <th class="text-center bg-light" style="min-width:100px">
                                        Total
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($students as $student)
                                    @php
                                        $total = $gradeBook->totals->firstWhere('student_id', $student->id);
                                    @endphp
                                    <tr>
                                        <td>{{ $student->user->full_full_name }}</td>
                                        @foreach ($gradeBook->activities as $activity)
                                            @php
                                                $score = $activity->scores->firstWhere('student_id', $student->id);
                                                $rawScore = $score ? (float) $score->score : null;
                                                $improvement = $score ? $score->improvement_score : null;
                                                $effective = $score
                                                    ? $gradeBook->academicConfiguration->effectiveScore(
                                                        (float) $rawScore,
                                                        $improvement,
                                                        (float) $activity->max_points,
                                                    )
                                                    : null;
                                            @endphp
                                            <td
                                                class="text-center {{ $activity->activityType->is_extra ? 'table-warning' : '' }}">
                                                @if (!is_null($rawScore))
                                                    <span>{{ number_format($rawScore, 2) }}</span>
                                                    @if (!is_null($improvement) && $improvement > 0)
                                                        <br>
                                                        <small class="text-success" title="Mejora">
                                                            <i class="fas fa-arrow-up"></i>
                                                            {{ number_format($improvement, 2) }}
                                                        </small>
                                                        <br>
                                                        <span
                                                            class="badge badge-success">{{ number_format($effective, 2) }}</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        @endforeach
                                        <td class="text-center font-weight-bold">
                                            {{ $total ? number_format($total->normal_points, 2) : '0.00' }}
                                        </td>
                                        @if ($extraMax > 0)
                                            <td class="text-center font-weight-bold text-warning">
                                                {{ $total ? number_format($total->extra_points, 2) : '0.00' }}
                                            </td>
                                        @endif
                                        <td class="text-center font-weight-bold text-primary">
                                            {{ $total ? number_format($total->total_points, 2) : '0.00' }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-light border text-center text-muted">
                        <i class="fas fa-book-open fa-3x mb-2"></i><br>
                        No hay actividades en este cuadro aún.
                        @if ($gradeBook->isOpen())
                            <br>Usa el botón <strong>Nueva Actividad</strong> para comenzar.
                        @endif
                    </div>
                @endif

            </div>
        </div>

    @endif

    {{-- Modal para Excel (Descarga e Importación) --}}
    @if ($showExcelModal)
        <div class="modal fade show" style="display: block; background: rgba(0,0,0,0.5);" tabindex="-1"
            role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-file-excel mr-2"></i>Gestionar Calificaciones por Excel
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeExcelModal"
                            aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <h6 class="text-center font-weight-bold text-primary mb-4">{{ $excelActivityName }}</h6>

                        <div class="mb-4">
                            <h6 class="font-weight-bold text-secondary border-bottom pb-2 mb-3">1. Descargar Plantilla
                            </h6>
                            <p class="text-sm text-muted mb-3">Descarga el archivo con la lista oficial. Úsalo para
                                calificar fuera del sistema.</p>
                            <button type="button" class="btn btn-outline-info btn-block shadow-sm"
                                wire:click="downloadExcelTemplate" wire:loading.attr="disabled"
                                wire:target="downloadExcelTemplate">
                                <span wire:loading.remove wire:target="downloadExcelTemplate">
                                    <i class="fas fa-download mr-1"></i> Descargar Archivo Excel
                                </span>
                                <span wire:loading wire:target="downloadExcelTemplate">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Generando archivo...
                                </span>
                            </button>
                        </div>

                        <div>
                            <h6 class="font-weight-bold text-secondary border-bottom pb-2 mb-3">2. Subir Calificaciones
                            </h6>

                            <div class="alert alert-warning text-sm py-2 px-3 mb-3 shadow-sm">
                                <ul class="mb-0 pl-3" style="line-height: 1.6;">
                                    <li>Las notas que subas <strong>sobrescribirán</strong> cualquier calificación que
                                        el estudiante ya tenga.</li>
                                    <li>Si dejas la celda de la nota <strong>en blanco</strong>, se asignará
                                        <strong>cero (0)</strong>.
                                    </li>
                                    <li>No alteres el ID del sistema en la columna A.</li>
                                </ul>
                            </div>

                            <div class="form-group mb-0">
                                <div class="custom-file">
                                    <input type="file" wire:model.live="excelFile"
                                        class="custom-file-input @error('excelFile') is-invalid @enderror"
                                        id="customFile" accept=".xlsx, .xls">
                                    <label class="custom-file-label text-truncate" for="customFile"
                                        data-browse="Buscar">
                                        {{ $excelFile ? $excelFile->getClientOriginalName() : 'Seleccionar archivo Excel...' }}
                                    </label>
                                </div>
                                <div wire:loading wire:target="excelFile" class="text-sm text-info mt-2">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Leyendo archivo temporal...
                                </div>
                                @error('excelFile')
                                    <span class="text-danger text-sm d-block mt-2 font-weight-bold">
                                        <i class="fas fa-times-circle mr-1"></i> {{ $message }}
                                    </span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary" wire:click="closeExcelModal">
                            Cerrar
                        </button>
                        <button type="button" class="btn btn-success shadow-sm" wire:click="importExcel"
                            wire:loading.attr="disabled" wire:target="importExcel, excelFile"
                            {{ !$excelFile ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="importExcel">
                                <i class="fas fa-upload mr-1"></i> Subir y Procesar Notas
                            </span>
                            <span wire:loading wire:target="importExcel">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('js')
        <script>
            function confirmLock() {
                Swal.fire({
                    title: '¿Bloquear el cuadro?',
                    text: 'Una vez bloqueado no podrás agregar ni modificar actividades o calificaciones.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#6c757d',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, bloquear',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.lockGradeBook();
                    }
                });
            }

            function confirmDeleteActivity(id, nombre) {
                Swal.fire({
                    title: '¿Eliminar ' + nombre + '?',
                    text: 'Se eliminarán también todas las calificaciones de esta actividad.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.deleteActivity(id);
                    }
                });
            }

            function confirmReopen() {
                Swal.fire({
                    title: '¿Reabrir el cuadro?',
                    text: 'El cuadro volverá al estado Abierto para que puedas corregirlo y bloquearlo nuevamente.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#f6a821',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, reabrir',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.reopenGradeBook();
                    }
                });
            }

            document.addEventListener('livewire:init', () => {
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
            });
            document.addEventListener('livewire:navigated', setupEnterNavigation);
            document.addEventListener('livewire:init', setupEnterNavigation);

            function setupEnterNavigation() {
                document.addEventListener('keydown', function(e) {
                    if (e.key !== 'Enter') return;

                    const target = e.target;
                    const isScore = target.classList.contains('score-input');
                    const isImprovement = target.classList.contains('improvement-input');

                    if (!isScore && !isImprovement) return;

                    e.preventDefault();

                    const currentIndex = parseInt(target.getAttribute('data-index'));

                    if (isScore) {
                        // Intentar ir a la nota del siguiente estudiante
                        const nextScore = document.querySelector(`.score-input[data-index="${currentIndex + 1}"]`);
                        if (nextScore) {
                            nextScore.focus();
                            nextScore.select();
                        } else {
                            // Era la última nota, saltar a la primera mejora
                            const firstImprovement = document.querySelector('.improvement-input[data-index="0"]');
                            if (firstImprovement) {
                                firstImprovement.focus();
                                firstImprovement.select();
                            }
                        }
                    } else if (isImprovement) {
                        // Intentar ir a la mejora del siguiente estudiante
                        const nextImprovement = document.querySelector(
                            `.improvement-input[data-index="${currentIndex + 1}"]`);
                        if (nextImprovement) {
                            nextImprovement.focus();
                            nextImprovement.select();
                        }
                    }
                });
            }
        </script>
    @endpush
</div>
