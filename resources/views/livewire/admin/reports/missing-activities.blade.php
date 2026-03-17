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
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-calendar-alt"></i></span></div>
                        <select wire:model.live="filterYear"
                            class="form-control @error('filterYear') is-invalid @enderror">
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
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-layer-group"></i></span></div>
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
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-graduation-cap"></i></span></div>
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
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-door-open"></i></span></div>
                        <select wire:model.live="filterSection" class="form-control"
                            {{ !$filterGrade ? 'disabled' : '' }}>
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
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-bookmark"></i></span></div>
                        <select wire:model.live="filterUnit" class="form-control"
                            {{ !$filterSection ? 'disabled' : '' }}>
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
                        <span wire:loading.remove wire:target="generateReport"><i class="fas fa-search"></i>
                            Generar</span>
                        <span wire:loading wire:target="generateReport"><i class="fas fa-spinner fa-pulse"></i>
                            Generando...</span>
                    </button>

                    @if ($generated && count($coursesData) > 0)
                        @php
                            $classroom = \App\Models\Classroom::where('year', $filterYear)
                                ->where('level_id', $filterLevel)
                                ->where('grade_id', $filterGrade)
                                ->where('section_id', $filterSection)
                                ->first();
                        @endphp
                        @if ($classroom)
                            <a href="{{ route('admin.reports.missing-activities.export', [
                                'classroom_id' => $classroom->id,
                                'unit' => $filterUnit,
                            ]) }}"
                                target="_blank" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-file-excel"></i> Descargar Excel
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($generated)
        @forelse ($coursesData as $courseBlock)
            <div class="card card-outline card-secondary mb-3">
                <div class="card-header py-2">
                    <h6 class="m-0 text-bold">
                        <i class="fas fa-book mr-1 text-danger"></i>
                        {{ $courseBlock['course_name'] }}
                        <small class="text-muted font-weight-normal ml-2">
                            Prof. {{ $courseBlock['professor_name'] }}
                            <span
                                class="badge badge-{{ $courseBlock['status'] === 'approved' ? 'success' : ($courseBlock['status'] === 'locked' ? 'warning' : 'secondary') }} ml-1">
                                {{ match ($courseBlock['status']) {'approved' => 'Aprobado','locked' => 'En revisión','rejected' => 'Rechazado',default => 'Abierto'} }}
                            </span>
                        </small>
                    </h6>
                    <div class="card-tools">
                        <span class="text-sm text-muted">
                            <i class="fas fa-check-circle text-success mr-1"></i> Entregado &nbsp;
                            <i class="fas fa-times-circle text-danger mr-1"></i> No entregado
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center" style="width:45px">No.</th>
                                <th>Estudiante</th>
                                @foreach ($courseBlock['activities'] as $activity)
                                    <th class="text-center" style="min-width:90px; font-size:0.75rem;">
                                        {{ $activity['name'] }}
                                        <br><small
                                            class="text-muted font-weight-normal">{{ $activity['type'] }}</small>
                                    </th>
                                @endforeach
                                <th class="text-center" style="width:90px">Faltantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($courseBlock['students'] as $row)
                                <tr>
                                    <td class="text-center">{{ $row['clave'] }}</td>
                                    <td><small>{{ $row['name'] }}</small></td>
                                    @foreach ($courseBlock['activities'] as $activity)
                                        <td class="text-center">
                                            @if ($row['results'][$activity['id']] ?? false)
                                                <i class="fas fa-check-circle text-success"></i>
                                            @else
                                                <i class="fas fa-times-circle text-danger"></i>
                                            @endif
                                        </td>
                                    @endforeach
                                    <td class="text-center">
                                        @if ($row['missing_count'] === 0)
                                            <span class="badge badge-success">0</span>
                                        @else
                                            <span class="badge badge-danger">{{ $row['missing_count'] }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No se encontraron cuadros con actividades para los filtros seleccionados.
            </div>
        @endforelse
    @elseif ($filterUnit)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> Presiona <strong>Generar</strong> para ver el reporte.
        </div>
    @endif
</div>
