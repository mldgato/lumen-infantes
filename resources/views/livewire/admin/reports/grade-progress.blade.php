<div>
    {{-- Señuelo: evita que Chrome autorrellene el buscador con credenciales guardadas --}}
    <div style="position:fixed;top:-200px;left:-200px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <input type="text" autocomplete="username" tabindex="-1">
        <input type="password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-filter mr-1"></i> Filtros
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-2 form-group mb-2">
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
                    @error('filterYear')
                        <span class="text-danger text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Unidad <span class="text-muted">(opcional)</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-list-ol"></i></span>
                        </div>
                        <select wire:model.live="filterUnit" class="form-control"
                            {{ !$filterYear ? 'disabled' : '' }}>
                            <option value="">-- Todas --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">Unidad {{ $unit }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Nivel <span class="text-muted">(opcional)</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                        </div>
                        <select wire:model.live="filterLevel" class="form-control"
                            {{ !$filterYear ? 'disabled' : '' }}>
                            <option value="">-- Todos --</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Grado <span class="text-muted">(opcional)</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                        </div>
                        <select wire:model.live="filterGrade" class="form-control"
                            {{ !$filterLevel ? 'disabled' : '' }}>
                            <option value="">-- Todos --</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Sección <span class="text-muted">(opcional)</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                        </div>
                        <select wire:model.live="filterSection" class="form-control"
                            {{ !$filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Todas --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-2 d-flex align-items-end">
                    <button wire:click="generateReport"
                        wire:loading.attr="disabled"
                        wire:target="generateReport"
                        class="btn btn-primary btn-sm shadow-sm w-100">
                        <span wire:loading.remove wire:target="generateReport">
                            <i class="fas fa-chart-line mr-1"></i> Generar
                        </span>
                        <span wire:loading wire:target="generateReport">
                            <i class="fas fa-spinner fa-pulse mr-1"></i> Generando...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- ============================================================
         RESULTADOS
         ============================================================ --}}
    @if ($generated)
        @if (count($reportData) > 0)
            @php
                $totalProfs    = count($reportData);
                $totalBooks    = array_sum(array_column($reportData, 'total'));
                $totalCreated  = array_sum(array_column($reportData, 'created'));
                $totalPending  = array_sum(array_column($reportData, 'pending'));
                $totalApproved = array_sum(array_column($reportData, 'approved'));
                $totalLocked   = array_sum(array_column($reportData, 'locked'));
                $totalOpen     = array_sum(array_column($reportData, 'open'));
                $totalRejected = array_sum(array_column($reportData, 'rejected'));
                $totalExpected = array_sum(array_column($reportData, 'expected'));
                $totalActual   = array_sum(array_column($reportData, 'actual'));
                $globalPct     = $totalExpected > 0
                    ? round(($totalActual / $totalExpected) * 100, 1)
                    : 0;
                $globalColor   = match(true) {
                    $globalPct >= 100 => 'success',
                    $globalPct >= 75  => 'info',
                    $globalPct >= 50  => 'warning',
                    default           => 'danger',
                };
            @endphp

            {{-- Info boxes de resumen --}}
            <div class="row mb-3">
                <div class="col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-primary"><i class="fas fa-chalkboard-teacher"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Docentes</span>
                            <span class="info-box-number">{{ $totalProfs }}</span>
                            <span class="progress-description">con asignaciones</span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon {{ $totalPending > 0 ? 'bg-warning' : 'bg-success' }}">
                            <i class="fas fa-book-open"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cuadros creados</span>
                            <span class="info-box-number">{{ $totalCreated }} / {{ $totalBooks }}</span>
                            <span class="progress-description {{ $totalPending > 0 ? 'text-danger' : 'text-success' }}">
                                {{ $totalPending > 0 ? $totalPending . ' sin crear' : 'todos creados' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cuadros aprobados</span>
                            <span class="info-box-number">{{ $totalApproved }} / {{ $totalCreated }}</span>
                            <span class="progress-description">
                                {{ $totalCreated - $totalApproved > 0 ? ($totalCreated - $totalApproved) . ' pendientes' : 'todos aprobados' }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-md-3">
                    <div class="info-box shadow-sm">
                        <span class="info-box-icon bg-{{ $globalColor }}">
                            <i class="fas fa-percent"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">Notas ingresadas</span>
                            <span class="info-box-number">{{ $globalPct }}%</span>
                            <span class="progress-description">
                                {{ number_format($totalActual) }} de {{ number_format($totalExpected) }} notas
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tabla por docente --}}
            <div class="card card-outline card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="m-0 text-bold text-secondary">
                        <i class="fas fa-table mr-1"></i> Detalle por Docente
                        <span class="badge badge-secondary ml-1">{{ $totalProfs }} docente(s)</span>
                        @if ($filterUnit)
                            <span class="badge badge-primary ml-1">Unidad {{ $filterUnit }}</span>
                        @endif
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0 text-sm">
                            <thead class="thead-light">
                                <tr>
                                    <th style="min-width:200px;">Docente</th>
                                    <th class="text-center" style="min-width:110px;">
                                        Cuadros<br>
                                        <small class="text-muted font-weight-normal">creados / asignados</small>
                                    </th>
                                    <th class="text-center"><span class="badge badge-secondary">Sin crear</span></th>
                                    <th class="text-center"><span class="badge badge-info">Abiertos</span></th>
                                    <th class="text-center"><span class="badge badge-warning">Bloqueados</span></th>
                                    <th class="text-center"><span class="badge badge-success">Aprobados</span></th>
                                    <th class="text-center"><span class="badge badge-danger">Rechazados</span></th>
                                    <th style="min-width:200px;">
                                        Notas ingresadas
                                        <small class="text-muted d-block font-weight-normal">ingresadas / esperadas</small>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($reportData as $row)
                                    @php
                                        $barColor = match(true) {
                                            $row['pct'] >= 100 => 'success',
                                            $row['pct'] >= 75  => 'info',
                                            $row['pct'] >= 50  => 'warning',
                                            default            => 'danger',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="font-weight-bold">
                                            <i class="fas fa-user-tie text-muted mr-1"></i>
                                            {{ $row['name'] }}
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $row['created'] }}</strong>
                                            <span class="text-muted">/ {{ $row['total'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($row['pending'] > 0)
                                                <span class="badge badge-secondary">{{ $row['pending'] }}</span>
                                            @else
                                                <i class="fas fa-check text-success"></i>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row['open'] > 0)
                                                <span class="badge badge-info">{{ $row['open'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row['locked'] > 0)
                                                <span class="badge badge-warning">{{ $row['locked'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row['approved'] > 0)
                                                <span class="badge badge-success">{{ $row['approved'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row['rejected'] > 0)
                                                <span class="badge badge-danger">{{ $row['rejected'] }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row['expected'] > 0)
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2" style="height:10px;">
                                                        <div class="progress-bar bg-{{ $barColor }}"
                                                            role="progressbar"
                                                            style="width: {{ min($row['pct'], 100) }}%">
                                                        </div>
                                                    </div>
                                                    <small class="text-nowrap font-weight-bold text-{{ $barColor }}">
                                                        {{ $row['pct'] }}%
                                                    </small>
                                                </div>
                                                <small class="text-muted">
                                                    {{ number_format($row['actual']) }} ingresadas / {{ number_format($row['expected']) }} esperadas
                                                </small>
                                            @elseif ($row['created'] > 0)
                                                <span class="text-muted text-xs">Sin actividades creadas</span>
                                            @else
                                                <span class="text-muted text-xs">Sin cuadros creados</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light font-weight-bold text-sm">
                                <tr>
                                    <td>Total general</td>
                                    <td class="text-center">
                                        {{ $totalCreated }} / {{ $totalBooks }}
                                    </td>
                                    <td class="text-center">
                                        @if ($totalPending > 0)
                                            <span class="badge badge-secondary">{{ $totalPending }}</span>
                                        @else
                                            <i class="fas fa-check text-success"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($totalOpen > 0)
                                            <span class="badge badge-info">{{ $totalOpen }}</span>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($totalLocked > 0)
                                            <span class="badge badge-warning">{{ $totalLocked }}</span>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($totalApproved > 0)
                                            <span class="badge badge-success">{{ $totalApproved }}</span>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($totalRejected > 0)
                                            <span class="badge badge-danger">{{ $totalRejected }}</span>
                                        @else <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($totalExpected > 0)
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 mr-2" style="height:10px;">
                                                    <div class="progress-bar bg-{{ $globalColor }}"
                                                        style="width: {{ min($globalPct, 100) }}%">
                                                    </div>
                                                </div>
                                                <small class="font-weight-bold text-{{ $globalColor }}">
                                                    {{ $globalPct }}%
                                                </small>
                                            </div>
                                            <small class="text-muted">
                                                {{ number_format($totalActual) }} ingresadas / {{ number_format($totalExpected) }} esperadas
                                            </small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No se encontraron asignaciones para los filtros seleccionados.
            </div>
        @endif
    @endif

</div>
