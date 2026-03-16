<div>
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold"><i class="fas fa-filter mr-1"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                {{-- Año --}}
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

                {{-- Nivel --}}
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Nivel <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                        </div>
                        <select wire:model.live="filterLevel"
                            class="form-control @error('filterLevel') is-invalid @enderror">
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

                {{-- Grado --}}
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Grado <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                        </div>
                        <select wire:model.live="filterGrade"
                            class="form-control @error('filterGrade') is-invalid @enderror"
                            {{ !$filterLevel ? 'disabled' : '' }}>
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

                {{-- Sección --}}
                <div class="col-md-2 form-group mb-3">
                    <label class="text-sm mb-1">Sección <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                        </div>
                        <select wire:model.live="filterSection"
                            class="form-control @error('filterSection') is-invalid @enderror"
                            {{ !$filterGrade ? 'disabled' : '' }}>
                            <option value="">-- Sección --</option>
                            <option value="all">Todas las secciones</option>
                            @foreach ($sections as $section)
                                <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                            @endforeach
                        </select>
                        @error('filterSection')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                {{-- Unidad --}}
                <div class="col-md-1 form-group mb-3">
                    <label class="text-sm mb-1">Unidad <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-bookmark"></i></span>
                        </div>
                        <select wire:model.live="filterUnit"
                            class="form-control @error('filterUnit') is-invalid @enderror"
                            {{ !$filterSection ? 'disabled' : '' }}>
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

                {{-- Botón imprimir todos --}}
                <div class="col-md-3 form-group mb-3">
                    <button wire:click.prevent="printAll" class="btn btn-danger btn-sm shadow-sm w-100"
                        wire:loading.attr="disabled" wire:target="printAll" {{ !$filterUnit ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="printAll">
                            <i class="fas fa-print"></i>
                            Imprimir todos
                            @if (count($studentList) > 0)
                                <span class="badge badge-light ml-1">{{ count($studentList) }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="printAll">
                            <i class="fas fa-spinner fa-pulse"></i> Generando...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Listado de estudiantes --}}
    @if (count($studentList) > 0)
        <div class="card card-outline card-secondary">
            <div class="card-header py-2">
                <h6 class="m-0 text-bold text-secondary">
                    <i class="fas fa-users mr-1"></i>
                    Estudiantes — {{ count($studentList) }} encontrado(s)
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:50px">Clave</th>
                            <th>Estudiante</th>
                            <th>Código</th>
                            @if ($filterSection === 'all')
                                <th>Sección</th>
                            @endif
                            <th class="text-center" style="width:130px">Boleta</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($studentList as $s)
                            <tr>
                                <td class="text-center">{{ $s['clave'] }}</td>
                                <td>{{ $s['name'] }}</td>
                                <td><small class="text-muted">{{ $s['carnet'] }}</small></td>
                                @if ($filterSection === 'all')
                                    <td>{{ $s['section'] }}</td>
                                @endif
                                <td class="text-center">
                                    <a href="{{ $s['url'] }}" target="_blank"
                                        class="btn btn-sm btn-danger shadow-sm">
                                        <i class="fas fa-file-pdf"></i> Ver boleta
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @elseif ($filterUnit)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No se encontraron estudiantes activos para los filtros seleccionados.
        </div>
    @endif

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('openReportCardAll', (event) => {
                    let payload = event[0] || event;
                    window.open(payload.url, '_blank');
                });
            });
        </script>
    @endpush
</div>
