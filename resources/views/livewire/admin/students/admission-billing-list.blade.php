<div>
    {{-- ════════════════════════════════════════
         FILTROS
    ════════════════════════════════════════ --}}
    <div class="card card-outline card-primary">
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-sm-12 col-md-3">
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
                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label class="control-label">Estado</label>
                        <select wire:model.live="filterStatus" class="form-control">
                            <option value="">— Todos —</option>
                            <option value="pending">Pendiente</option>
                            <option value="emailed">Correo enviado</option>
                            <option value="reviewed">Documentación completa</option>
                            <option value="billed">Facturado</option>
                            <option value="psychometric">Psicométrica registrada</option>
                            <option value="accepted">Aceptado</option>
                            <option value="rejected">Rechazado</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
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
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         TABLA
    ════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Alumno</th>
                            <th>Nivel / Grado</th>
                            <th>Ciclo</th>
                            <th>Encargado</th>
                            <th>NIT</th>
                            <th>Estado</th>
                            <th class="text-center">Factura</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->applications as $app)
                            <tr>
                                <td>
                                    <strong>{{ $app->student_first_surname }} {{ $app->student_second_surname }}</strong>
                                    <br>
                                    <small>{{ $app->student_first_name }} {{ $app->student_second_name }}</small>
                                </td>
                                <td>
                                    <small>{{ $app->level?->level_name ?? '—' }}</small><br>
                                    <small class="text-muted">{{ $app->grade?->grade_name ?? '—' }}</small>
                                </td>
                                <td>{{ $app->year }}</td>
                                <td>
                                    <small>{{ $app->guardian_name }}</small><br>
                                    <small class="text-muted">{{ $app->guardian_email }}</small>
                                </td>
                                <td>
                                    <small>{{ $app->guardianNit() ?? '—' }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-{{ $app->statusColor() }}">
                                        {{ $app->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($app->billing)
                                        <span class="badge badge-success" title="Factura No. {{ $app->billing->invoice_number }}">
                                            <i class="fas fa-check mr-1"></i> Registrada
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($app->url_payment)
                                        <button wire:click="openModal({{ $app->id }})"
                                            class="btn btn-xs btn-info" title="Ver / Registrar factura">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-xs btn-secondary" disabled
                                            title="Sin boleta de pago registrada">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No se encontraron solicitudes.
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

    {{-- ════════════════════════════════════════
         MODAL — Facturación
    ════════════════════════════════════════ --}}
    <div class="modal fade" id="billingModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                @if ($viewing)
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-invoice-dollar mr-2"></i>
                            {{ $viewing->fullStudentName() }}
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    @if ($viewing->billing)
                        {{-- ── Vista de detalle (factura ya registrada) ── --}}
                        <div class="modal-body">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Número de factura</dt>
                                <dd class="col-sm-7">{{ $viewing->billing->invoice_number }}</dd>

                                <dt class="col-sm-5">Fecha de factura</dt>
                                <dd class="col-sm-7">{{ $viewing->billing->invoice_date->format('d/m/Y') }}</dd>

                                <dt class="col-sm-5">Registrado por</dt>
                                <dd class="col-sm-7">
                                    {{ $viewing->billing->user->first_name }}
                                    {{ $viewing->billing->user->first_surname }}
                                </dd>

                                <dt class="col-sm-5">Candidato</dt>
                                <dd class="col-sm-7">{{ $viewing->fullStudentName() }}</dd>

                                <dt class="col-sm-5">Grado solicitado</dt>
                                <dd class="col-sm-7">
                                    {{ $viewing->level?->level_name ?? '—' }}
                                    — {{ $viewing->grade?->grade_name ?? '—' }}
                                    ({{ $viewing->year }})
                                </dd>

                                <dt class="col-sm-5">Registrado el</dt>
                                <dd class="col-sm-7">
                                    <small>{{ $viewing->billing->created_at->format('d/m/Y H:i') }}</small>
                                </dd>
                            </dl>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cerrar</button>
                        </div>
                    @else
                        {{-- ── Formulario (sin factura aún) ── --}}
                        <div class="modal-body">
                            <div class="form-group">
                                <label>Número de factura <span class="text-danger">*</span></label>
                                <input type="text" wire:model="invoiceNumber"
                                    class="form-control @error('invoiceNumber') is-invalid @enderror"
                                    placeholder="Ej: 000-001-00-00000123"
                                    autocomplete="new-password">
                                @error('invoiceNumber')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label>Fecha de la factura <span class="text-danger">*</span></label>
                                <input type="date" wire:model="invoiceDate"
                                    class="form-control @error('invoiceDate') is-invalid @enderror">
                                @error('invoiceDate')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group mb-0">
                                <label>Boleta de pago</label>
                                <div>
                                    <a href="{{ $viewing->url_payment }}" target="_blank"
                                        class="btn btn-sm btn-outline-secondary">
                                        <i class="fas fa-external-link-alt mr-1"></i> Ver boleta de pago
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button wire:click="saveBilling"
                                wire:loading.attr="disabled" wire:target="saveBilling"
                                class="btn btn-primary btn-sm">
                                <span wire:loading.remove wire:target="saveBilling">
                                    <i class="fas fa-save mr-1"></i> Guardar factura
                                </span>
                                <span wire:loading wire:target="saveBilling">
                                    <i class="fas fa-spinner fa-spin mr-1"></i> Guardando...
                                </span>
                            </button>
                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">Cancelar</button>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('openBillingModal', () => {
        $('#billingModal').modal('show');
    });

    $wire.on('showAlert', (data) => {
        let p = Array.isArray(data) ? (data[0] || {}) : (data || {});
        Swal.fire({
            position: 'top-end',
            icon: p.type || 'info',
            title: p.title,
            text: p.message,
            showConfirmButton: false,
            timer: 3500
        });
    });

    $wire.on('toastMessage', (data) => {
        let p = Array.isArray(data) ? (data[0] || {}) : (data || {});
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({
            icon: p.type || 'info',
            title: p.message || p.title
        });
    });
</script>
@endscript

@push('css')
<style>
    dl.row dt { font-size: .85rem; color: #6c757d; }
    dl.row dd { font-size: .9rem; margin-bottom: .5rem; }
</style>
@endpush
