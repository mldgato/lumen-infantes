<div>
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-filter mr-1"></i> Mis Cuadros por Unidad
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-3 form-group mb-3">
                    <label class="text-sm mb-1">Unidad <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-bookmark"></i></span>
                        </div>
                        <select wire:model.live="filterUnit"
                            class="form-control @error('filterUnit') is-invalid @enderror">
                            <option value="">-- Seleccione una unidad --</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit }}">Unidad {{ $unit }}</option>
                            @endforeach
                        </select>
                        @error('filterUnit')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-5 form-group mb-3 d-flex" style="gap: 8px;">
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
                        wire:loading.attr="disabled" wire:target="viewAll" {{ $approvedCount === 0 ? 'disabled' : '' }}>
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
                <div class="alert alert-warning py-2 mb-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    No tienes cuadros aprobados en la Unidad {{ $filterUnit }} para este año.
                </div>
            @endif

            @if ($approvedCount > 0)
                <div class="alert alert-success py-2 mb-3">
                    <i class="fas fa-check-circle mr-1"></i>
                    <strong>{{ $approvedCount }}</strong> cuadro(s) aprobado(s) en la Unidad {{ $filterUnit }}.
                </div>
            @endif

            {{-- Tabla de cuadros --}}
            @if (count($approvedGradeBooks) > 0)
                <div class="card card-outline card-secondary mb-0">
                    <div class="card-header py-2">
                        <h6 class="m-0 text-bold text-secondary">
                            <i class="fas fa-list mr-1"></i> Cuadros Aprobados — Unidad {{ $filterUnit }}
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>#</th>
                                    <th>Nivel</th>
                                    <th>Grado</th>
                                    <th>Sección</th>
                                    <th>Curso</th>
                                    <th class="text-center">Ver</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($approvedGradeBooks as $idx => $gb)
                                    <tr>
                                        <td>{{ $idx + 1 }}</td>
                                        <td>{{ $gb['nivel'] }}</td>
                                        <td>{{ $gb['grado'] }}</td>
                                        <td>{{ $gb['seccion'] }}</td>
                                        <td>{{ $gb['curso'] }}</td>
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
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('downloadCuadrosUnidad', (event) => {
                    let payload = event[0] || event;
                    window.location.href = payload.url;
                });
                Livewire.on('viewAllCuadrosUnidad', (event) => {
                    let payload = event[0] || event;
                    window.open(payload.url, '_blank');
                });
            });
        </script>
    @endpush
</div>
