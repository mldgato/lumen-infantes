<div>
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
                        @error('filterYear')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
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

                <div class="col-md-3 form-group mb-3 d-flex">
                    <button wire:click="generateReport" class="btn btn-danger btn-sm shadow-sm mr-2"
                        wire:loading.attr="disabled" wire:target="generateReport" {{ !$filterUnit ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="generateReport"><i class="fas fa-search"></i> Generar</span>
                        <span wire:loading wire:target="generateReport"><i class="fas fa-spinner fa-pulse"></i> Generando...</span>
                    </button>

                    @if ($generated && count($reportData) > 0)
                        @php
                            $classroom = \App\Models\Classroom::where('year', $filterYear)
                                ->where('level_id', $filterLevel)
                                ->where('grade_id', $filterGrade)
                                ->where('section_id', $filterSection)
                                ->first();
                        @endphp
                        @if ($classroom)
                            <a href="{{ route('admin.reports.activity-summary.export', [
                                'classroom_id' => $classroom->id,
                                'unit'         => $filterUnit,
                            ]) }}" target="_blank" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Descargar Excel
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($generated)
        @if (count($reportData) > 0)
            <div class="card card-outline card-secondary">
                <div class="card-header py-2">
                    <h6 class="m-0 text-bold">
                        <i class="fas fa-table mr-1 text-danger"></i>
                        Resumen de actividades por estudiante — Unidad {{ $filterUnit }}
                    </h6>
                    <div class="card-tools">
                        <span class="text-sm text-muted">
                            <span class="badge badge-success px-2">X/X</span> Completo &nbsp;
                            <span class="badge badge-warning px-2">X/Y</span> Parcial &nbsp;
                            <span class="badge badge-danger px-2">X/Y</span> Bajo &nbsp;
                            <span class="badge badge-secondary px-2">—</span> Sin cuadro
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center align-middle" style="width:45px">No.</th>
                                <th class="align-middle">Estudiante</th>
                                @foreach ($courseHeaders as $header)
                                    <th class="text-center align-middle" style="min-width:110px; font-size:0.72rem; line-height:1.2;">
                                        {{ $header['name'] }}
                                        @if ($header['has_activities'])
                                            <br><small class="text-warning font-weight-normal">{{ $header['total'] }} act.</small>
                                        @endif
                                    </th>
                                @endforeach
                                <th class="text-center align-middle" style="width:85px">Faltantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reportData as $row)
                                <tr>
                                    <td class="text-center align-middle">{{ $row['number'] }}</td>
                                    <td class="align-middle"><small>{{ $row['name'] }}</small></td>
                                    @foreach ($row['courses'] as $course)
                                        <td class="text-center align-middle">
                                            @if (! $course['has_activities'])
                                                <span class="badge badge-secondary">—</span>
                                            @else
                                                @php
                                                    $done  = $course['done'];
                                                    $total = $course['total'];
                                                    $ratio = $total > 0 ? $done / $total : 1;
                                                    $cls   = $ratio >= 1 ? 'success' : ($ratio >= 0.5 ? 'warning' : 'danger');
                                                @endphp
                                                <span class="badge badge-{{ $cls }} px-2" style="font-size:0.8rem;">
                                                    {{ $done }}/{{ $total }}
                                                </span>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center align-middle">
                                        @if ($row['total_missing'] === 0)
                                            <span class="badge badge-success">0</span>
                                        @elseif ($row['total_missing'] <= 3)
                                            <span class="badge badge-warning">{{ $row['total_missing'] }}</span>
                                        @else
                                            <span class="badge badge-danger">{{ $row['total_missing'] }}</span>
                                        @endif
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
                No se encontraron estudiantes inscritos o cuadros con actividades para los filtros seleccionados.
            </div>
        @endif
    @elseif ($filterUnit)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> Presiona <strong>Generar</strong> para ver el reporte.
        </div>
    @endif
</div>
