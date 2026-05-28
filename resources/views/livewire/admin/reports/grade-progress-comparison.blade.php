<div>
    {{-- ============================================================
         FILTROS
         ============================================================ --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-chart-bar mr-1"></i> Comparativo de Rendimiento por Unidad
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-calendar-alt"></i></span></div>
                        <select wire:model.live="filterYear" class="form-control @error('filterYear') is-invalid @enderror">
                            <option value="">-- Año --</option>
                            @foreach ($years as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach
                        </select>
                        @error('filterYear')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Nivel <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-layer-group"></i></span></div>
                        <select wire:model.live="filterLevel" class="form-control @error('filterLevel') is-invalid @enderror" {{ !$filterYear ? 'disabled' : '' }}>
                            <option value="">-- Nivel --</option>
                            @foreach ($levels as $level)<option value="{{ $level->id }}">{{ $level->level_name }}</option>@endforeach
                        </select>
                        @error('filterLevel')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Grado <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-graduation-cap"></i></span></div>
                        <select wire:model.live="filterGrade" class="form-control @error('filterGrade') is-invalid @enderror" {{ !$filterLevel ? 'disabled' : '' }}>
                            <option value="">-- Grado --</option>
                            @foreach ($grades as $grade)<option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>@endforeach
                        </select>
                        @error('filterGrade')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Sección <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-door-open"></i></span></div>
                        <select wire:model.live="filterSection" class="form-control @error('filterSection') is-invalid @enderror" {{ !$filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Sección --</option>
                            @foreach ($sections as $section)<option value="{{ $section->id }}">{{ $section->section_name }}</option>@endforeach
                        </select>
                        @error('filterSection')<span class="invalid-feedback">{{ $message }}</span>@enderror
                    </div>
                </div>
                <div class="col-md-2 form-group mb-3 d-flex align-items-end">
                    <button wire:click="generateReport" class="btn btn-primary btn-sm w-100 shadow-sm" {{ !$filterSection ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="generateReport"><i class="fas fa-chart-bar mr-1"></i> Generar</span>
                        <span wire:loading wire:target="generateReport"><i class="fas fa-spinner fa-pulse"></i> Calculando...</span>
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
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i> No se encontró el aula.</div>
        @elseif (! $comparisonData || ! $comparisonData['pensum'])
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-1"></i> No hay pensum configurado para este aula.</div>
        @else
            @php $pensum = $comparisonData['pensum']; @endphp
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h5 class="m-0 text-bold">
                        Promedio por Unidad —
                        {{ $classroom->level->level_name }}
                        {{ $classroom->grade->grade_name }}
                        {{ $classroom->section->section_name }}
                        ({{ $classroom->year }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th>Curso</th>
                                @for ($u = 1; $u <= $pensum->units; $u++)
                                    <th class="text-center" style="width:80px">Unidad {{ $u }}</th>
                                @endfor
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Promedio global por unidad --}}
                            <tr class="table-secondary font-weight-bold">
                                <td><i class="fas fa-calculator mr-1"></i> Promedio del aula</td>
                                @for ($u = 1; $u <= $pensum->units; $u++)
                                    <td class="text-center">
                                        @if ($comparisonData['unitAverages'][$u] !== null)
                                            <span class="{{ $comparisonData['unitAverages'][$u] < 60 ? 'text-danger' : 'text-success' }}">
                                                {{ $comparisonData['unitAverages'][$u] }}
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                @endfor
                            </tr>
                            {{-- Por curso --}}
                            @forelse ($comparisonData['courses'] as $row)
                                <tr>
                                    <td>{{ $row['course'] }}</td>
                                    @for ($u = 1; $u <= $pensum->units; $u++)
                                        <td class="text-center">
                                            @if (isset($row['byUnit'][$u]) && $row['byUnit'][$u] !== null)
                                                <span class="{{ $row['byUnit'][$u] < 60 ? 'text-danger' : 'text-success' }}">
                                                    {{ $row['byUnit'][$u] }}
                                                </span>
                                            @else
                                                <span class="text-muted text-sm">—</span>
                                            @endif
                                        </td>
                                    @endfor
                                </tr>
                            @empty
                                <tr><td colspan="{{ $pensum->units + 1 }}" class="text-center text-muted py-3">Sin cursos oficiales configurados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    @endif
</div>
