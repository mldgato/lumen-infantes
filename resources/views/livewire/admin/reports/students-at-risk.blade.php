<div>
    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-exclamation-triangle mr-1"></i> Filtros — Estudiantes en Riesgo
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
                        @error('filterYear')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Nivel <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                        </div>
                        <select wire:model.live="filterLevel"
                            class="form-control @error('filterLevel') is-invalid @enderror"
                            {{ ! $filterYear ? 'disabled' : '' }}>
                            <option value="">-- Nivel --</option>
                            @foreach ($levels as $level)
                                <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                            @endforeach
                        </select>
                        @error('filterLevel')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Grado <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                        </div>
                        <select wire:model.live="filterGrade"
                            class="form-control @error('filterGrade') is-invalid @enderror"
                            {{ ! $filterLevel ? 'disabled' : '' }}>
                            <option value="">-- Grado --</option>
                            @foreach ($grades as $grade)
                                <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                            @endforeach
                        </select>
                        @error('filterGrade')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Sección <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                        </div>
                        <select wire:model.live="filterSection"
                            class="form-control @error('filterSection') is-invalid @enderror"
                            {{ ! $filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Sección --</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                        @error('filterSection')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Umbral de riesgo</label>
                    <div class="input-group input-group-sm">
                        <input type="number" wire:model.live="riskThreshold"
                            class="form-control text-center" min="1" max="100" step="1">
                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3 d-flex align-items-end">
                    <button wire:click="generateReport"
                        class="btn btn-warning btn-sm w-100 shadow-sm"
                        {{ ! $filterSection ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="generateReport">
                            <i class="fas fa-search mr-1"></i> Generar reporte
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
        @if (! $classroom)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                No se encontró un aula con los filtros seleccionados.
            </div>
        @elseif (! $pensum)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle mr-1"></i>
                El aula seleccionada no tiene un pensum configurado para este año.
            </div>
        @elseif ($rows->isEmpty())
            <div class="alert alert-success">
                <i class="fas fa-check-circle mr-1"></i>
                No hay estudiantes en riesgo de reprobación con el umbral actual ({{ $riskThreshold }}%).
            </div>
        @else
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted text-sm">
                    <strong>{{ $classroom->level->level_name }}</strong> —
                    {{ $classroom->grade->grade_name }}
                    {{ $classroom->section->section_name }}
                    ({{ $classroom->year }})
                    — umbral: <strong>{{ $riskThreshold }}%</strong>
                </span>
                <span class="badge badge-danger badge-pill" style="font-size:.85rem">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    {{ $rows->count() }} estudiante(s) en riesgo
                </span>
            </div>

            @foreach ($rows as $row)
                <div class="card card-outline card-danger mb-3">
                    <div class="card-header py-2">
                        <h6 class="m-0 text-bold text-danger">
                            <i class="fas fa-user-times mr-1"></i> {{ $row['name'] }}
                            <span class="badge badge-danger ml-2">
                                {{ count($row['at_risk_courses']) }} curso(s) en riesgo
                            </span>
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Curso</th>
                                    @for ($u = 1; $u <= $pensum->units; $u++)
                                        <th class="text-center" style="width:70px">U{{ $u }}</th>
                                    @endfor
                                    <th class="text-center" style="width:110px">Promedio pond.</th>
                                    <th class="text-center" style="width:90px">Avance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($row['at_risk_courses'] as $course)
                                    <tr>
                                        <td>{{ $course['course'] }}</td>
                                        @for ($u = 1; $u <= $pensum->units; $u++)
                                            <td class="text-center">
                                                @if (isset($course['scores'][$u]))
                                                    <span class="{{ $course['scores'][$u] < $riskThreshold ? 'text-danger font-weight-bold' : 'text-success' }}">
                                                        {{ $course['scores'][$u] }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                        @endfor
                                        <td class="text-center font-weight-bold text-danger">
                                            {{ $course['weighted'] }}%
                                        </td>
                                        <td class="text-center text-muted text-sm">
                                            {{ $course['covered'] }}% pend.
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endforeach
        @endif
    @endif
</div>
