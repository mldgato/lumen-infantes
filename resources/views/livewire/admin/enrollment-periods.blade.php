<div>

    {{-- FORMULARIO --}}
    @if ($showForm)
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h5 class="m-0 text-bold">
                    <i class="fas fa-{{ $editingId ? 'edit' : 'plus-circle' }} mr-1"></i>
                    {{ $editingId ? 'Editar Período' : 'Nuevo Período' }}
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 form-group">
                        <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
                        <input type="number" wire:model="year" class="form-control form-control-sm @error('year') is-invalid @enderror" min="2020" max="2099">
                        @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="text-sm mb-1">Fecha de inicio <span class="text-danger">*</span></label>
                        <input type="date" wire:model="start_date" class="form-control form-control-sm @error('start_date') is-invalid @enderror">
                        @error('start_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-3 form-group">
                        <label class="text-sm mb-1">Fecha de fin <span class="text-danger">*</span></label>
                        <input type="date" wire:model="end_date" class="form-control form-control-sm @error('end_date') is-invalid @enderror">
                        @error('end_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="text-sm mb-1">Permite inscripciones</label>
                        <div class="custom-control custom-switch mt-1">
                            <input type="checkbox" class="custom-control-input" id="allow_enrollments" wire:model="allow_enrollments">
                            <label class="custom-control-label" for="allow_enrollments">
                                {{ $allow_enrollments ? 'Sí' : 'No' }}
                            </label>
                        </div>
                        @error('allow_enrollments') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-2 form-group">
                        <label class="text-sm mb-1">Permite actualización</label>
                        <div class="custom-control custom-switch mt-1">
                            <input type="checkbox" class="custom-control-input" id="allow_data_updates" wire:model="allow_data_updates">
                            <label class="custom-control-label" for="allow_data_updates">
                                {{ $allow_data_updates ? 'Sí' : 'No' }}
                            </label>
                        </div>
                        @error('allow_data_updates') <div class="text-danger text-xs mt-1">{{ $message }}</div> @enderror
                    </div>
                </div>
            </div>
            <div class="card-footer text-right">
                <button wire:click="cancelForm" class="btn btn-secondary btn-sm mr-1">
                    <i class="fas fa-times mr-1"></i> Cancelar
                </button>
                <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary btn-sm">
                    <span wire:loading.remove><i class="fas fa-save mr-1"></i> Guardar</span>
                    <span wire:loading><i class="fas fa-spinner fa-spin mr-1"></i> Guardando...</span>
                </button>
            </div>
        </div>
    @else
        @can('admin.enrollment-periods.create')
            <div class="mb-3">
                <button wire:click="openCreate" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-plus mr-1"></i> Nuevo período
                </button>
            </div>
        @endcan
    @endif

    {{-- TABLA --}}
    <div class="card card-outline card-secondary">
        <div class="card-header py-2">
            <h6 class="m-0 text-bold">
                <i class="fas fa-calendar-alt mr-1 text-primary"></i>
                Períodos de inscripción y actualización
            </h6>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" style="width:70px">Año</th>
                        <th>Fecha inicio</th>
                        <th>Fecha fin</th>
                        <th class="text-center">Inscripciones</th>
                        <th class="text-center">Actualización datos</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center" style="width:100px">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($periods as $period)
                        @php
                            $active = $period->start_date->toDateString() <= $today && $period->end_date->toDateString() >= $today;
                        @endphp
                        <tr>
                            <td class="text-center font-weight-bold">{{ $period->year }}</td>
                            <td>{{ $period->start_date->format('d/m/Y') }}</td>
                            <td>{{ $period->end_date->format('d/m/Y') }}</td>
                            <td class="text-center">
                                @if ($period->allow_enrollments)
                                    <span class="badge badge-success">Habilitado</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($period->allow_data_updates)
                                    <span class="badge badge-info">Habilitado</span>
                                @else
                                    <span class="badge badge-secondary">No</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if ($active)
                                    <span class="badge badge-success">Vigente</span>
                                @elseif ($period->end_date->toDateString() < $today)
                                    <span class="badge badge-secondary">Vencido</span>
                                @else
                                    <span class="badge badge-warning">Próximo</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @can('admin.enrollment-periods.edit')
                                    <button wire:click="openEdit({{ $period->id }})" class="btn btn-xs btn-warning" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                @endcan
                                @can('admin.enrollment-periods.delete')
                                    <button wire:click="delete({{ $period->id }})"
                                        wire:confirm="¿Eliminar este período?"
                                        class="btn btn-xs btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No hay períodos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($periods->hasPages())
            <div class="card-footer py-2">
                {{ $periods->links() }}
            </div>
        @endif
    </div>

</div>
