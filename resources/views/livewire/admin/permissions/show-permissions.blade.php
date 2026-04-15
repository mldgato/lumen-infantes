<div wire:init="loadPermissions">

    {{-- Modal Crear Permiso --}}
    <div wire:ignore.self class="modal fade" id="CreatePermissionModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-plus-circle"></i> Nuevo Permiso
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="text" wire:model="name"
                                    class="form-control @error('name') is-invalid @enderror"
                                    placeholder="Ej. admin.reports.index">
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Descripción <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                </div>
                                <input type="text" wire:model="description"
                                    class="form-control @error('description') is-invalid @enderror"
                                    placeholder="Ej. Ver reportes">
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-sm mb-1 text-bold text-primary">
                            <i class="fas fa-user-tag mr-1"></i> Asignar a Roles
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" wire:model.live="roleSearchCreate" class="form-control"
                                placeholder="Buscar roles...">
                            @if ($roleSearchCreate)
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        wire:click="clearRoleSearchCreate">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div
                            style="max-height:200px; overflow-y:auto; border:1px solid #dee2e6; border-radius:4px; padding:10px;">
                            @forelse($filteredRolesCreate as $role)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox" class="custom-control-input" id="c_role_{{ $role->id }}"
                                        value="{{ $role->id }}" wire:model="selectedRoles">
                                    <label class="custom-control-label" for="c_role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted text-center mb-0">No se encontraron roles.</p>
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

    {{-- Modal Editar Permiso --}}
    <div wire:ignore.self class="modal fade" id="EditPermissionModal" tabindex="-1" role="dialog"
        data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-edit"></i> Editar Permiso
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Nombre <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-key"></i></span>
                                </div>
                                <input type="text" wire:model="name"
                                    class="form-control @error('name') is-invalid @enderror">
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Descripción <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-file-alt"></i></span>
                                </div>
                                <input type="text" wire:model="description"
                                    class="form-control @error('description') is-invalid @enderror">
                                @error('description')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-sm mb-1 text-bold text-primary">
                            <i class="fas fa-user-tag mr-1"></i> Asignar a Roles
                        </label>
                        <div class="input-group input-group-sm mb-2">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" wire:model.live="roleSearch" class="form-control"
                                placeholder="Buscar roles...">
                            @if ($roleSearch)
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary btn-sm" type="button"
                                        wire:click="clearRoleSearch">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div
                            style="max-height:200px; overflow-y:auto; border:1px solid #dee2e6; border-radius:4px; padding:10px;">
                            @forelse($filteredRoles as $role)
                                <div class="custom-control custom-checkbox mb-1">
                                    <input type="checkbox" class="custom-control-input"
                                        id="e_role_{{ $role->id }}" value="{{ $role->id }}"
                                        wire:model="selectedRoles">
                                    <label class="custom-control-label" for="e_role_{{ $role->id }}">
                                        {{ $role->name }}
                                    </label>
                                </div>
                            @empty
                                <p class="text-muted text-center mb-0">No se encontraron roles.</p>
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
                        data-target="#CreatePermissionModal" wire:click="resetFields">
                        <i class="fas fa-plus-circle"></i> Nuevo Permiso
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
                            placeholder="Buscar permiso..." autocomplete="new-password">
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
            @if ($readyToLoad && count($permissions))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('name')">
                                Nombre <i
                                    class="fas fa-sort{{ $sort === 'name' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th style="cursor:pointer" wire:click="order('description')">
                                Descripción <i
                                    class="fas fa-sort{{ $sort === 'description' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Roles</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($permissions as $permission)
                            <tr>
                                <td><code class="text-sm">{{ $permission->name }}</code></td>
                                <td>{{ $permission->description }}</td>
                                <td>
                                    @if ($permission->roles->count())
                                        <span class="badge badge-info mr-1">{{ $permission->roles->count() }}
                                            roles</span>
                                        <small class="text-muted">
                                            {{ $permission->roles->take(3)->pluck('name')->implode(', ') }}
                                            @if ($permission->roles->count() > 3)
                                                ...
                                            @endif
                                        </small>
                                    @else
                                        <span class="badge badge-secondary">Sin roles</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $permission->id }})" data-toggle="modal"
                                        data-target="#EditPermissionModal" class="btn btn-sm btn-warning shadow-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button wire:click="confirmDelete({{ $permission->id }})"
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
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando permisos...
                    @else
                        <i class="fas fa-key fa-3x mb-3 text-gray"></i><br>No se encontraron permisos.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($permissions) && $permissions->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $permissions->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
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

                Livewire.on('confirmDeletePermission', (event) => {
                    let payload = event[0] || event;
                    Swal.fire({
                        title: '¿Eliminar este permiso?',
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
