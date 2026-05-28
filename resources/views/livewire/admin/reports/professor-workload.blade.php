<div>
    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-briefcase mr-1"></i> Carga Docente por Profesor
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 form-group mb-3">
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
                        @error('filterYear')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-md-2 form-group mb-3 d-flex align-items-end">
                    <button wire:click="generateReport"
                        class="btn btn-secondary btn-sm w-100 shadow-sm"
                        {{ ! $filterYear ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="generateReport">
                            <i class="fas fa-search mr-1"></i> Generar
                        </span>
                        <span wire:loading wire:target="generateReport">
                            <i class="fas fa-spinner fa-pulse"></i> Calculando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================================
         RESULTADOS
         ============================================================ --}}
    @if ($readyToLoad)
        @if ($rows->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-1"></i>
                No se encontraron profesores con asignaciones para el año {{ $filterYear }}.
            </div>
        @else
            <div class="card card-outline card-secondary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 text-bold">
                        Carga docente {{ $filterYear }}
                    </h5>
                    <span class="badge badge-secondary badge-pill">{{ $rows->count() }} profesor(es)</span>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width:30px">#</th>
                                <th>Profesor</th>
                                <th class="text-center" style="width:110px">Asignaciones</th>
                                <th class="text-center" style="width:90px">Aulas</th>
                                <th class="text-center" style="width:110px">Estudiantes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $row)
                                <tr>
                                    <td class="text-muted">{{ $i + 1 }}</td>
                                    <td>{{ $row['name'] }}</td>
                                    <td class="text-center font-weight-bold">{{ $row['courses'] }}</td>
                                    <td class="text-center">{{ $row['classrooms'] }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-primary">{{ $row['students'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary font-weight-bold">
                            <tr>
                                <td colspan="2" class="text-right">Totales:</td>
                                <td class="text-center">{{ $rows->sum('courses') }}</td>
                                <td class="text-center">{{ $rows->sum('classrooms') }}</td>
                                <td class="text-center">{{ $rows->sum('students') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
