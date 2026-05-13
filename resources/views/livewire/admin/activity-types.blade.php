<div wire:init="loadTypes">

    {{-- Señuelo: evita que Chrome autorrellene el buscador con credenciales guardadas --}}
    <div style="position:fixed;top:-200px;left:-200px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <input type="text" autocomplete="username" tabindex="-1">
        <input type="password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- Modal --}}
    <div wire:ignore.self class="modal fade" id="ActivityTypeModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $editing ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title">
                        <i class="fas {{ $editing ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                        {{ $editing ? 'Editar Tipo: ' . $editing->name : 'Nuevo Tipo de Actividad' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Nombre <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-tag"></i></span>
                            </div>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Ej. Examen, Tarea, Laboratorio">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="isExtraSwitch"
                                wire:model="is_extra">
                            <label class="custom-control-label text-sm" for="isExtraSwitch">
                                Actividad extra (puntos adicionales que no cuentan en el límite de 100)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">
                        Cancelar
                    </button>
                    <button wire:click.prevent="save" type="button"
                        class="btn btn-sm {{ $editing ? 'btn-warning' : 'btn-primary' }}"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save"></i>
                            {{ $editing ? 'Actualizar' : 'Guardar' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-pulse"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Card principal --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    @can('admin.activity-types.create')
                        <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                            data-target="#ActivityTypeModal" wire:click="resetFields">
                            <i class="fas fa-plus-circle"></i> Nuevo Tipo
                        </button>
                    @endcan
                </div>
                <div class="col-md-8 d-flex justify-content-end align-items-center">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Buscar tipo..." autocomplete="new-password">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            @if ($readyToLoad && count($types))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('id')">
                                # <i class="fas fa-sort{{ $sort === 'id' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th style="cursor:pointer" wire:click="order('name')">
                                Nombre <i class="fas fa-sort{{ $sort === 'name' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th class="text-center">Tipo</th>
                            <th class="text-center">En uso</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($types as $type)
                            <tr>
                                <td>{{ $type->id }}</td>
                                <td>{{ $type->name }}</td>
                                <td class="text-center">
                                    @if ($type->is_extra)
                                        <span class="badge badge-success">Extra</span>
                                    @else
                                        <span class="badge badge-primary">Normal</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $inUse = $type->configurationActivities()->exists() || $type->gradeBookActivities()->exists();
                                    @endphp
                                    @if ($inUse)
                                        <span class="badge badge-secondary" title="No se puede eliminar">
                                            <i class="fas fa-lock"></i> Sí
                                        </span>
                                    @else
                                        <span class="badge badge-light text-muted">No</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @can('admin.activity-types.edit')
                                        <button wire:click="edit({{ $type->id }})" data-toggle="modal"
                                            data-target="#ActivityTypeModal"
                                            class="btn btn-sm btn-warning shadow-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                    @can('admin.activity-types.delete')
                                        <button
                                            onclick="confirmDelete({{ $type->id }}, '¿Eliminar el tipo {{ addslashes($type->name) }}?')"
                                            class="btn btn-sm btn-danger shadow-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando tipos...
                    @else
                        <i class="fas fa-tag fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($types) && $types->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $types->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            function confirmDelete(id, mensaje) {
                Swal.fire({
                    title: mensaje || '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.delete(id);
                    }
                });
            }

            document.addEventListener('livewire:init', () => {
                Livewire.on('closeModalMessaje', (event) => {
                    let payload = event[0] || event;
                    if (payload.modalId) {
                        $('#' + payload.modalId).modal('hide');
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                    }
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                });

                Livewire.on('showAlert', (event) => {
                    let payload = event[0] || event;
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3500
                    });
                });
            });
        </script>
    @endpush
</div>
