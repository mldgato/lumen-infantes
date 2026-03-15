<div wire:init="loadRoles">

    {{-- Modal Crear Rol --}}
    <div wire:ignore.self class="modal fade" id="CreateRoleModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-plus-circle"></i> Nuevo Rol
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Nombre del Rol <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                            </div>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror" placeholder="Ej. Coordinador">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-sm mb-1 text-bold text-primary">
                            <i class="fas fa-user-shield mr-1"></i> Permisos
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" wire:model.live="permissionSearchCreate" class="form-control"
                                placeholder="Buscar permisos...">
                            @if ($permissionSearchCreate)
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        wire:click="clearPermissionSearchCreate">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-xs btn-outline-primary mr-1"
                                onclick="selectAll('#CreateRoleModal .perm-check-create')">
                                <i class="fas fa-check-circle"></i> Todos
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                onclick="deselectAll('#CreateRoleModal .perm-check-create')">
                                <i class="fas fa-times-circle"></i> Ninguno
                            </button>
                            <small class="text-muted ml-2">
                                <span class="badge badge-info">{{ count($selectedPermissions) }}</span> seleccionados
                            </small>
                        </div>
                        <div
                            style="max-height:300px; overflow-y:auto; border:1px solid #dee2e6; border-radius:4px; padding:10px;">
                            @forelse($filteredPermissionsCreate as $permission)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox" class="custom-control-input perm-check-create"
                                        id="c_perm_{{ $permission->id }}" value="{{ $permission->id }}"
                                        wire:model="selectedPermissions">
                                    <label class="custom-control-label" for="c_perm_{{ $permission->id }}">
                                        {{ $permission->description }}
                                        <small class="text-muted d-block">{{ $permission->name }}</small>
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted text-center mb-0">No se encontraron permisos.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">Cancelar</button>
                    <button wire:click.prevent="save" type="button" class="btn btn-primary btn-sm"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save"><i class="fas fa-save"></i> Guardar</span>
                        <span wire:loading wire:target="save"><i class="fas fa-spinner fa-pulse"></i>
                            Guardando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar Rol --}}
    <div wire:ignore.self class="modal fade" id="EditRoleModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Editar Rol
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Nombre del Rol <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-user-tag"></i></span>
                            </div>
                            <input type="text" wire:model="name"
                                class="form-control @error('name') is-invalid @enderror"
                                placeholder="Ej. Coordinador">
                            @error('name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-sm mb-1 text-bold text-primary">
                            <i class="fas fa-user-shield mr-1"></i> Permisos
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" wire:model.live="permissionSearch" class="form-control"
                                placeholder="Buscar permisos...">
                            @if ($permissionSearch)
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        wire:click="clearPermissionSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-xs btn-outline-primary mr-1"
                                onclick="selectAll('#EditRoleModal .perm-check-edit')">
                                <i class="fas fa-check-circle"></i> Todos
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary"
                                onclick="deselectAll('#EditRoleModal .perm-check-edit')">
                                <i class="fas fa-times-circle"></i> Ninguno
                            </button>
                            <small class="text-muted ml-2">
                                <span class="badge badge-info">{{ count($selectedPermissions) }}</span> seleccionados
                            </small>
                        </div>
                        <div
                            style="max-height:300px; overflow-y:auto; border:1px solid #dee2e6; border-radius:4px; padding:10px;">
                            @forelse($filteredPermissions as $permission)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox" class="custom-control-input perm-check-edit"
                                        id="e_perm_{{ $permission->id }}" value="{{ $permission->id }}"
                                        wire:model="selectedPermissions">
                                    <label class="custom-control-label" for="e_perm_{{ $permission->id }}">
                                        {{ $permission->description }}
                                        <small class="text-muted d-block">{{ $permission->name }}</small>
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted text-center mb-0">No se encontraron permisos.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">Cancelar</button>
                    <button wire:click.prevent="update" type="button" class="btn btn-warning btn-sm"
                        wire:loading.attr="disabled" wire:target="update">
                        <span wire:loading.remove wire:target="update"><i class="fas fa-save"></i> Actualizar</span>
                        <span wire:loading wire:target="update"><i class="fas fa-spinner fa-pulse"></i>
                            Actualizando...</span>
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
                    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                        data-target="#CreateRoleModal" wire:click="resetFields">
                        <i class="fas fa-plus-circle"></i> Nuevo Rol
                    </button>
                </div>
                <div class="col-md-8 d-flex justify-content-end align-items-center">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="input-group input-group-sm" style="width:250px">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Buscar rol...">
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
            @if ($readyToLoad && count($roles))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('name')">
                                Rol <i
                                    class="fas fa-sort{{ $sort === 'name' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Permisos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($roles as $role)
                            <tr>
                                <td><strong>{{ $role->name }}</strong></td>
                                <td>
                                    <span class="badge badge-info mr-1">{{ $role->permissions->count() }}
                                        permisos</span>
                                    <small class="text-muted">
                                        {{ $role->permissions->take(3)->pluck('description')->implode(', ') }}
                                        @if ($role->permissions->count() > 3)
                                            ...
                                        @endif
                                    </small>
                                </td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $role->id }})" data-toggle="modal"
                                        data-target="#EditRoleModal" class="btn btn-sm btn-warning shadow-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $role->id }})"
                                        class="btn btn-sm btn-danger shadow-sm ml-1">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando roles...
                    @else
                        <i class="fas fa-user-tag fa-3x mb-3 text-gray"></i><br>No se encontraron roles.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($roles) && $roles->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $roles->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            function selectAll(selector) {
                document.querySelectorAll(selector).forEach(cb => {
                    if (!cb.checked) cb.click();
                });
            }

            function deselectAll(selector) {
                document.querySelectorAll(selector).forEach(cb => {
                    if (cb.checked) cb.click();
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

                Livewire.on('confirmDeleteRole', (event) => {
                    let payload = event[0] || event;
                    Swal.fire({
                        title: '¿Eliminar este rol?',
                        text: 'Esta acción no se puede deshacer.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar',
                    }).then(result => {
                        if (result.isConfirmed) {
                            @this.delete(payload.id);
                        }
                    });
                });
            });
        </script>
    @endpush
</div>
