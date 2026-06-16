<div>
    {{-- ════════════════════════════════════════
         FILTROS
    ════════════════════════════════════════ --}}
    <div class="card card-outline card-primary">
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="control-label">Ciclo Escolar</label>
                        <select wire:model.live="filterYear" class="form-control">
                            <option value="">— Todos —</option>
                            @foreach ($this->availableYears as $yr)
                                <option value="{{ $yr }}">{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="control-label">Nivel</label>
                        <select wire:model.live="filterLevel" class="form-control">
                            <option value="">— Todos —</option>
                            @foreach ($this->allLevels as $level)
                                <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label class="control-label">Estado</label>
                        <select wire:model.live="filterStatus" class="form-control">
                            <option value="">— Todos —</option>
                            <option value="in_progress">En proceso (sin aceptar/rechazar)</option>
                            <option value="pending">Pendiente</option>
                            <option value="emailed">Correo enviado</option>
                            <option value="reviewed">Documentación completa</option>
                            <option value="billed">Facturado</option>
                            <option value="psychometric">Psicométrica registrada</option>
                            <option value="academic">Evaluaciones académicas</option>
                            <option value="accepted">Aceptado</option>
                            <option value="rejected">Rechazado</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label class="control-label">Buscar</label>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control" placeholder="Nombre del alumno o correo del encargado..."
                            autocomplete="new-password">
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="control-label">Por página</label>
                        <select wire:model.live="cant" class="form-control">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         TABLA + BOTÓN EXPORTAR
    ════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-header d-flex align-items-center justify-content-between">
            <span>
                <i class="fas fa-list mr-1"></i>
                Solicitudes encontradas: <strong>{{ $this->applications->total() }}</strong>
            </span>
            <button wire:click="exportExcel" wire:loading.attr="disabled" class="btn btn-success btn-sm">
                <span wire:loading.remove wire:target="exportExcel">
                    <i class="fas fa-file-excel mr-1"></i> Exportar a Excel
                </span>
                <span wire:loading wire:target="exportExcel">
                    <i class="fas fa-spinner fa-spin mr-1"></i> Generando...
                </span>
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Año</th>
                            <th>Alumno</th>
                            <th>Nivel / Grado</th>
                            <th>Encargado</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->applications as $app)
                            <tr>
                                <td class="align-middle">{{ $app->year }}</td>
                                <td class="align-middle">{{ $app->fullStudentName() }}</td>
                                <td class="align-middle">
                                    {{ $app->level?->level_name ?? '—' }}
                                    @if ($app->grade)
                                        / {{ $app->grade->grade_name }}
                                    @endif
                                </td>
                                <td class="align-middle">{{ $app->guardian_name ?? '—' }}</td>
                                <td class="align-middle">{{ $app->guardian_email ?? '—' }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-{{ $this->statusColor($app->current_status ?? 'pending') }}">
                                        {{ $this->statusLabel($app->current_status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="align-middle text-nowrap">{{ $app->created_at->format('d/m/Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-search mr-1"></i> No se encontraron solicitudes con los filtros actuales.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($this->applications->hasPages())
            <div class="card-footer">
                {{ $this->applications->links() }}
            </div>
        @endif
    </div>

    @script
    <script>
        $wire.on('showAlert', (params) => {
            const p = Array.isArray(params) ? params[0] : params;
            Swal.fire({
                icon: p.type ?? 'info',
                title: p.title ?? '',
                toast: false,
                position: 'center',
                showConfirmButton: true,
            });
        });
    </script>
    @endscript
</div>
