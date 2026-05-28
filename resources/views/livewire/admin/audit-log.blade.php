<div wire:init="loadData">

    {{-- Señuelo: evita que Chrome autorrellene el buscador con credenciales guardadas --}}
    <div style="position:fixed;top:-200px;left:-200px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <input type="text" autocomplete="username" tabindex="-1">
        <input type="password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- FILTROS --}}
    <div class="card card-secondary card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold"><i class="fas fa-filter mr-1"></i> Filtros</h5>
            <div class="card-tools">
                <button wire:click="clearFilters" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row align-items-end">
                <div class="col-md-3 form-group mb-2">
                    <label class="text-sm mb-1">Buscar</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-search"></i></span></div>
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                            name="buscar" id="buscador" placeholder="Buscar en descripción..." autocomplete="search">
                    </div>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Módulo</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-cubes"></i></span></div>
                        <select wire:model.live="filterModule" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($modules as $module)
                                <option value="{{ $module }}">{{ $module }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Evento</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-bolt"></i></span></div>
                        <select wire:model.live="filterEvent" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($events as $event)
                                <option value="{{ $event }}">{{ $event }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Usuario</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-user"></i></span></div>
                        <select wire:model.live="filterUser" class="form-control">
                            <option value="">Todos</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2 form-group mb-2">
                    <label class="text-sm mb-1">Desde</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-calendar"></i></span></div>
                        <input type="date" wire:model.live="filterDateFrom" class="form-control">
                    </div>
                </div>
                <div class="col-md-1 form-group mb-2">
                    <label class="text-sm mb-1">Hasta</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-calendar"></i></span></div>
                        <input type="date" wire:model.live="filterDateTo" class="form-control">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TABLA --}}
    <div class="card card-outline card-dark">
        <div class="card-header py-2 d-flex justify-content-between align-items-center">
            <h6 class="m-0 text-bold">
                <i class="fas fa-history mr-1"></i> Registros de Auditoría
                @if ($readyToLoad && $logs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                    <span class="badge badge-secondary ml-1">{{ $logs->total() }}</span>
                @endif
            </h6>
            <div class="d-flex align-items-center gap-2">
                <button wire:click="export" class="btn btn-sm btn-outline-success mr-2"
                    title="Exportar a Excel con los filtros aplicados">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </button>
                <span class="text-sm mr-2">Mostrar</span>
                <select wire:model.live="cant" class="form-control form-control-sm w-auto">
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>
        <div class="card-body p-0">
            @if (!$readyToLoad)
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-secondary"></i>
                    <p class="mt-2 text-muted">Cargando registros...</p>
                </div>
            @elseif ($logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->count() > 0)
                <table class="table table-sm table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:160px">Fecha y Hora</th>
                            <th style="width:120px">Módulo</th>
                            <th style="width:140px">Evento</th>
                            <th>Descripción</th>
                            <th style="width:180px">Usuario</th>
                            <th style="width:110px">IP</th>
                            <th class="text-center" style="width:80px">Detalle</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                            <tr>
                                <td>
                                    <small class="text-muted">{{ $log->created_at->format('d/m/Y H:i:s') }}</small>
                                </td>
                                <td>
                                    <span class="badge {{ \App\Livewire\Admin\AuditLog::moduleBadge($log->module) }}">
                                        {{ $log->module }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge {{ \App\Livewire\Admin\AuditLog::eventBadge($log->event) }}">
                                        {{ $log->event }}
                                    </span>
                                </td>
                                <td><small>{{ $log->description }}</small></td>
                                <td>
                                    <small>
                                        @if ($log->user)
                                            <i class="fas fa-user-circle mr-1 text-muted"></i>
                                            {{ $log->user->name }}
                                        @else
                                            <span class="text-muted">Sistema</span>
                                        @endif
                                    </small>
                                </td>
                                <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                                <td class="text-center">
                                    @if ($log->old_values || $log->new_values)
                                        <button wire:click="viewDetail({{ $log->id }})"
                                            class="btn btn-xs btn-outline-dark" title="Ver detalle">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-history fa-3x mb-3 text-gray"></i><br>
                    No se encontraron registros de auditoría.
                </div>
            @endif
        </div>
        @if ($readyToLoad && $logs instanceof \Illuminate\Pagination\LengthAwarePaginator && $logs->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $logs->links() }}</div>
            </div>
        @endif
    </div>

    {{-- MODAL DETALLE --}}
    <div wire:ignore.self class="modal fade" id="AuditDetailModal" tabindex="-1" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-dark">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-search-plus mr-1"></i> Detalle del Registro
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>×</span></button>
                </div>
                <div class="modal-body">
                    @if ($selectedLog)
                        <dl class="row">
                            <dt class="col-sm-3">Fecha y Hora</dt>
                            <dd class="col-sm-9">{{ $selectedLog->created_at->format('d/m/Y H:i:s') }}</dd>

                            <dt class="col-sm-3">Usuario</dt>
                            <dd class="col-sm-9">{{ $selectedLog->user?->name ?? 'Sistema' }}</dd>

                            <dt class="col-sm-3">Módulo</dt>
                            <dd class="col-sm-9">
                                <span
                                    class="badge {{ \App\Livewire\Admin\AuditLog::moduleBadge($selectedLog->module) }}">
                                    {{ $selectedLog->module }}
                                </span>
                            </dd>

                            <dt class="col-sm-3">Evento</dt>
                            <dd class="col-sm-9">
                                <span
                                    class="badge {{ \App\Livewire\Admin\AuditLog::eventBadge($selectedLog->event) }}">
                                    {{ $selectedLog->event }}
                                </span>
                            </dd>

                            <dt class="col-sm-3">Descripción</dt>
                            <dd class="col-sm-9">{{ $selectedLog->description }}</dd>

                            <dt class="col-sm-3">IP</dt>
                            <dd class="col-sm-9">{{ $selectedLog->ip_address }}</dd>
                        </dl>

                        <div class="row mt-3">
                            @if ($selectedLog->old_values)
                                <div class="col-md-6">
                                    <div class="card card-outline card-danger">
                                        <div class="card-header py-1">
                                            <h6 class="m-0 text-bold text-danger">
                                                <i class="fas fa-minus-circle mr-1"></i> Valores Anteriores
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm mb-0">
                                                @foreach ($selectedLog->old_values as $key => $value)
                                                    <tr>
                                                        <td class="font-weight-bold text-sm pl-3" style="width:40%">
                                                            {{ $key }}</td>
                                                        <td class="text-sm">
                                                            @if (is_null($value))
                                                                <span class="text-muted font-italic">null</span>
                                                            @elseif (is_bool($value))
                                                                <span
                                                                    class="badge badge-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'true' : 'false' }}</span>
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            @if ($selectedLog->new_values)
                                <div class="col-md-{{ $selectedLog->old_values ? '6' : '12' }}">
                                    <div class="card card-outline card-success">
                                        <div class="card-header py-1">
                                            <h6 class="m-0 text-bold text-success">
                                                <i class="fas fa-plus-circle mr-1"></i> Valores Nuevos
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm mb-0">
                                                @foreach ($selectedLog->new_values as $key => $value)
                                                    <tr>
                                                        <td class="font-weight-bold text-sm pl-3" style="width:40%">
                                                            {{ $key }}</td>
                                                        <td class="text-sm">
                                                            @if (is_null($value))
                                                                <span class="text-muted font-italic">null</span>
                                                            @elseif (is_bool($value))
                                                                <span
                                                                    class="badge badge-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'true' : 'false' }}</span>
                                                            @else
                                                                {{ $value }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('openAuditDetailModal', () => $('#AuditDetailModal').modal('show'));
                Livewire.on('downloadAuditLog', (data) => { window.location.href = data[0].url; });
            });
        </script>
    @endpush
</div>
