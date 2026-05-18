<div>
    {{-- ── FILTROS ──────────────────────────────────────────────────── --}}
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold"><i class="fas fa-filter mr-1"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
                        <select wire:model.live="filterYear" class="form-control @error('filterYear') is-invalid @enderror">
                            <option value="">-- Año --</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        @error('filterYear')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Nivel <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-layer-group"></i></span></div>
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
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-graduation-cap"></i></span></div>
                        <select wire:model.live="filterGrade" class="form-control" {{ !$filterLevel ? 'disabled' : '' }}>
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
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-door-open"></i></span></div>
                        <select wire:model.live="filterSection" class="form-control" {{ !$filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Sección --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-1 form-group mb-3">
                    <label class="text-sm mb-1">Unidad <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-bookmark"></i></span></div>
                        <select wire:model.live="filterUnit" class="form-control" {{ !$filterSection ? 'disabled' : '' }}>
                            <option value="">--</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">U{{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-3 form-group mb-3 d-flex align-items-end">
                    <button wire:click="generateReport" class="btn btn-danger btn-sm shadow-sm mr-2"
                        wire:loading.attr="disabled" wire:target="generateReport" {{ !$filterUnit ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="generateReport"><i class="fas fa-search"></i> Generar</span>
                        <span wire:loading wire:target="generateReport"><i class="fas fa-spinner fa-pulse"></i> Generando...</span>
                    </button>

                    @if ($generated && count($studentList) > 0)
                        @php $firstRow = $studentList[0]; @endphp
                        <a href="{{ route('admin.reports.student-activity-detail.pdf.classroom', [
                            'classroom_id' => $firstRow['classroom_id'],
                            'unit'         => $filterUnit,
                        ]) }}" target="_blank" class="btn btn-dark btn-sm shadow-sm">
                            <i class="fas fa-file-pdf"></i> PDF Sección
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── LISTADO DE ESTUDIANTES ───────────────────────────────────── --}}
    @if ($generated)
        @if (count($studentList) > 0)
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h6 class="m-0 text-bold">
                        <i class="fas fa-users mr-1 text-danger"></i>
                        Estudiantes — Unidad {{ $filterUnit }}
                        <span class="badge badge-secondary ml-1">{{ count($studentList) }}</span>
                    </h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" style="width:45px">No.</th>
                                <th>Estudiante</th>
                                <th class="text-center" style="width:130px">Actividades</th>
                                <th class="text-center" style="width:100px">Faltantes</th>
                                <th class="text-center" style="width:130px">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($studentList as $row)
                                @php
                                    $ratio = $row['total'] > 0 ? $row['done'] / $row['total'] : 1;
                                    $badgeCls = $ratio >= 1 ? 'success' : ($ratio >= 0.5 ? 'warning' : 'danger');
                                    $missingCls = $row['missing'] === 0 ? 'success' : ($row['missing'] <= 3 ? 'warning' : 'danger');
                                @endphp
                                <tr>
                                    <td class="text-center align-middle">{{ $row['number'] }}</td>
                                    <td class="align-middle"><small>{{ $row['name'] }}</small></td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $badgeCls }} px-2" style="font-size:.8rem;">
                                            {{ $row['done'] }}/{{ $row['total'] }}
                                        </span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <span class="badge badge-{{ $missingCls }}">{{ $row['missing'] }}</span>
                                    </td>
                                    <td class="text-center align-middle">
                                        <button wire:click="loadStudentDetail({{ $row['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="loadStudentDetail({{ $row['id'] }})"
                                            class="btn btn-xs btn-info mr-1" title="Ver detalle">
                                            <span wire:loading.remove wire:target="loadStudentDetail({{ $row['id'] }})">
                                                <i class="fas fa-eye"></i>
                                            </span>
                                            <span wire:loading wire:target="loadStudentDetail({{ $row['id'] }})">
                                                <i class="fas fa-spinner fa-pulse"></i>
                                            </span>
                                        </button>
                                        <a href="{{ route('admin.reports.student-activity-detail.pdf.student', [
                                            'classroom_id' => $row['classroom_id'],
                                            'student_id'   => $row['id'],
                                            'unit'         => $filterUnit,
                                        ]) }}" target="_blank" class="btn btn-xs btn-danger" title="Descargar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No se encontraron estudiantes inscritos para los filtros seleccionados.
            </div>
        @endif
    @elseif ($filterUnit)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> Presiona <strong>Generar</strong> para ver el listado.
        </div>
    @endif

    {{-- ── MODAL DE DETALLE ─────────────────────────────────────────── --}}
    <div class="modal fade" id="studentDetailModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                @if (!empty($selectedStudentDetail))
                    <div class="modal-header bg-dark text-white py-2">
                        <h5 class="modal-title">
                            <i class="fas fa-user-graduate mr-2"></i>
                            {{ $selectedStudentDetail['name'] }}
                            <small class="font-weight-normal ml-2">— Unidad {{ $selectedStudentDetail['unit'] }}</small>
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-3">
                        @foreach ($selectedStudentDetail['courses'] as $course)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="text-sm">
                                        <i class="fas fa-book text-danger mr-1"></i>
                                        {{ $course['course_name'] }}
                                        <small class="text-muted font-weight-normal ml-1">
                                            Prof. {{ $course['professor_name'] }}
                                        </small>
                                    </strong>
                                    @if ($course['has_activities'])
                                        @php
                                            $r = $course['total'] > 0 ? $course['done'] / $course['total'] : 1;
                                            $bc = $r >= 1 ? 'success' : ($r >= 0.5 ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge badge-{{ $bc }}">
                                            {{ $course['done'] }}/{{ $course['total'] }}
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">Sin cuadro</span>
                                    @endif
                                </div>

                                @if ($course['has_activities'])
                                    <table class="table table-xs table-bordered mb-0" style="font-size:.78rem;">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Actividad</th>
                                                <th style="width:100px">Tipo</th>
                                                <th class="text-center" style="width:90px">Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($course['activities'] as $activity)
                                                <tr>
                                                    <td>{{ $activity['name'] }}</td>
                                                    <td class="text-muted">{{ $activity['type'] }}</td>
                                                    <td class="text-center">
                                                        @if ($activity['done'])
                                                            <span class="badge badge-success"><i class="fas fa-check"></i> Entregada</span>
                                                        @else
                                                            <span class="badge badge-danger"><i class="fas fa-times"></i> No entregada</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <p class="text-muted text-sm mb-0"><i class="fas fa-info-circle mr-1"></i>El profesor aún no ha registrado actividades en este cuadro.</p>
                                @endif
                            </div>
                            @if (!$loop->last)<hr class="my-2">@endif
                        @endforeach
                    </div>
                    <div class="modal-footer py-2">
                        <a href="{{ route('admin.reports.student-activity-detail.pdf.student', [
                            'classroom_id' => $selectedStudentDetail['classroom_id'],
                            'student_id'   => $selectedStudentDetail['student_id'],
                            'unit'         => $selectedStudentDetail['unit'],
                        ]) }}" target="_blank" class="btn btn-danger btn-sm">
                            <i class="fas fa-file-pdf mr-1"></i> Descargar PDF
                        </a>
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@push('js')
<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('openStudentDetailModal', () => {
            $('#studentDetailModal').modal('show');
        });
    });
</script>
@endpush
