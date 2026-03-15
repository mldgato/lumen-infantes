<div>
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold"><i class="fas fa-filter mr-1"></i> Filtros</h5>
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
                    <label class="text-sm mb-1">Unidad <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-bookmark"></i></span>
                        </div>
                        <select wire:model.live="filterUnit"
                            class="form-control @error('filterUnit') is-invalid @enderror"
                            {{ !$filterSection ? 'disabled' : '' }}>
                            <option value="">-- Unidad --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">Unidad {{ $unit }}</option>
                            @endforeach
                        </select>
                        @error('filterUnit')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-2 form-group mb-3 d-flex align-items-end gap-2">
                    <button wire:click.prevent="download" class="btn btn-danger btn-sm flex-fill shadow-sm"
                        wire:loading.attr="disabled" wire:target="download"
                        {{ $approvedCount === 0 ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="download">
                            <i class="fas fa-file-archive"></i> ZIP
                            @if ($approvedCount > 0)
                                <span class="badge badge-light ml-1">{{ $approvedCount }}</span>
                            @endif
                        </span>
                        <span wire:loading wire:target="download">
                            <i class="fas fa-spinner fa-pulse"></i>
                        </span>
                    </button>

                    <button wire:click.prevent="viewAll" class="btn btn-secondary btn-sm flex-fill shadow-sm"
                        wire:loading.attr="disabled" wire:target="viewAll"
                        {{ $approvedCount === 0 ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="viewAll">
                            <i class="fas fa-eye"></i> Ver todos
                        </span>
                        <span wire:loading wire:target="viewAll">
                            <i class="fas fa-spinner fa-pulse"></i>
                        </span>
                    </button>
                </div>

            </div>

            @if ($filterUnit && $approvedCount === 0)
                <div class="alert alert-warning py-2 mb-0">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    No hay cuadros aprobados para los filtros seleccionados.
                </div>
            @endif

            @if ($approvedCount > 0)
                <div class="alert alert-success py-2 mb-0">
                    <i class="fas fa-check-circle mr-1"></i>
                    Se encontraron <strong>{{ $approvedCount }}</strong> cuadro(s) aprobado(s) listos para descargar.
                </div>
            @endif
        </div>
    </div>

    {{-- Tabla de cuadros aprobados --}}
    @if (count($approvedGradeBooks) > 0)
        <div class="card card-outline card-secondary mt-3 mb-0">
            <div class="card-header py-2">
                <h6 class="m-0 text-bold text-secondary">
                    <i class="fas fa-list mr-1"></i> Cuadros Aprobados
                </h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>#</th>
                            <th>Curso</th>
                            <th>Profesor(a)</th>
                            <th class="text-center">Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($approvedGradeBooks as $idx => $gb)
                            <tr>
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $gb['curso'] }}</td>
                                <td>{{ $gb['profesor'] }}</td>
                                <td class="text-center">
                                    <a href="{{ $gb['view_url'] }}" target="_blank"
                                        class="btn btn-sm btn-danger shadow-sm">
                                        <i class="fas fa-eye"></i> Ver PDF
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('downloadCuadros', (event) => {
                    let payload = event[0] || event;
                    window.location.href = payload.url;
                });

                Livewire.on('viewAllCuadros', (event) => {
                    let payload = event[0] || event;
                    window.open(payload.url, '_blank');
                });
            });
        </script>
    @endpush
</div>
