<div wire:init="loadData">
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold"><i class="fas fa-filter mr-1"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3 form-group mb-3">
                    <label class="text-sm mb-1">Aula <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-chalkboard"></i></span></div>
                        <select wire:model.live="filterClassroom"
                            class="form-control @error('filterClassroom') is-invalid @enderror">
                            <option value="">-- Aula --</option>
                            @foreach ($classrooms as $classroom)
                                <option value="{{ $classroom->id }}">{{ $classroom->grade->grade_name }}
                                    {{ $classroom->section->section_name }}</option>
                            @endforeach
                        </select>
                        @error('filterClassroom')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <label class="text-sm mb-1">Curso <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-book"></i></span></div>
                        <select wire:model.live="filterCourse"
                            class="form-control @error('filterCourse') is-invalid @enderror"
                            {{ !$filterClassroom ? 'disabled' : '' }}>
                            <option value="">-- Curso --</option>
                            @foreach ($courses as $course)
                                <option value="{{ $course['id'] }}">{{ $course['name'] }}</option>
                            @endforeach
                        </select>
                        @error('filterCourse')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Unidad <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-bookmark"></i></span></div>
                        <select wire:model.live="filterUnit"
                            class="form-control @error('filterUnit') is-invalid @enderror"
                            {{ !$filterCourse ? 'disabled' : '' }}>
                            <option value="">--</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">U{{ $unit }}</option>
                            @endforeach
                        </select>
                        @error('filterUnit')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 form-group mb-3 d-flex gap-2">
                    <button wire:click="generateReport" class="btn btn-warning btn-sm shadow-sm mr-2"
                        wire:loading.attr="disabled" wire:target="generateReport" {{ !$filterUnit ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="generateReport"><i class="fas fa-search"></i>
                            Generar</span>
                        <span wire:loading wire:target="generateReport"><i class="fas fa-spinner fa-pulse"></i>
                            Generando...</span>
                    </button>

                    @if ($generated)
                        <a href="{{ route('profesor.reports.missing-activities.export', [
                            'classroom_id' => $filterClassroom,
                            'pensum_course_id' => $filterCourse,
                            'unit' => $filterUnit,
                        ]) }}"
                            target="_blank" class="btn btn-success btn-sm shadow-sm">
                            <i class="fas fa-file-excel"></i> Descargar Excel
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if ($generated)
        <div class="card card-outline card-secondary">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="m-0 text-bold">
                    <i class="fas fa-tasks mr-1 text-warning"></i>
                    {{ $courseName }} — Unidad {{ $filterUnit }}
                    <span class="badge badge-secondary ml-2">{{ count($reportData) }} estudiantes</span>
                </h6>
                <div class="text-sm text-muted">
                    <span class="mr-3"><i class="fas fa-check-circle text-success mr-1"></i> Entregado</span>
                    <span><i class="fas fa-times-circle text-danger mr-1"></i> No entregado</span>
                </div>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th class="text-center" style="width:45px">No.</th>
                            <th>Estudiante</th>
                            @foreach ($activities as $activity)
                                <th class="text-center" style="min-width:90px; font-size:0.75rem;">
                                    {{ $activity['name'] }}
                                    <br><small class="text-muted font-weight-normal">{{ $activity['type'] }}</small>
                                </th>
                            @endforeach
                            <th class="text-center" style="width:90px">Faltantes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reportData as $row)
                            <tr>
                                <td class="text-center">{{ $row['clave'] }}</td>
                                <td><small>{{ $row['name'] }}</small></td>
                                @foreach ($activities as $activity)
                                    <td class="text-center">
                                        @if ($row['results'][$activity['id']] ?? false)
                                            <i class="fas fa-check-circle text-success" title="Entregado"></i>
                                        @else
                                            <i class="fas fa-times-circle text-danger" title="No entregado"></i>
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
    @elseif ($filterUnit)
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i> Presiona <strong>Generar</strong> para ver el reporte.
        </div>
    @endif
</div>
