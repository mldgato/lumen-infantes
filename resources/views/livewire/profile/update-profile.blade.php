<div class="row">
    <div class="col-lg-9 col-md-12 mb-3">
        <div class="card card-primary card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Información General</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <label>Primer Nombre</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-user"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->first_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>Segundo Nombre</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-user"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->middle_name }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>Primer Apellido</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-user"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->surname }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>Segundo Apellido</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-user"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->second_surname }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>Apellido Casada</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-user"></i></span></div>
                            <div class="form-control bg-light text-truncate">
                                {{ auth()->user()->married_surname ?? 'N/A' }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label>CUI</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-id-card"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->cui }}</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label>Correo Electrónico</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-envelope"></i></span></div>
                            <div class="form-control bg-light text-truncate" title="{{ auth()->user()->email }}">
                                {{ auth()->user()->email }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label>Correo Personal</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-envelope"></i></span></div>
                            <div class="form-control bg-light text-truncate"
                                title="{{ auth()->user()->personal_email }}">{{ auth()->user()->personal_email }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>Celular</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-phone"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->cellphone }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>Fecha de Nac.</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-calendar"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->birthdate }}</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>Género</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-venus-mars"></i></span></div>
                            <div class="form-control bg-light text-truncate">{{ auth()->user()->gender }}</div>
                        </div>
                    </div>

                    <div class="col-12">
                        <label>Dirección</label>
                        <div class="input-group mb-3">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-map-marker-alt"></i></span></div>
                            <div class="form-control bg-light text-wrap" style="height: auto; min-height: 38px;">
                                {{ auth()->user()->address }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-12 mb-3 d-flex flex-column">
        <div class="card card-danger card-outline flex-grow-1 mb-3">
            <div class="card-header text-center">
                <h3 class="card-title float-none">Imagen de Perfil</h3>
            </div>
            <div class="card-body d-flex flex-column justify-content-center align-items-center">
                <div class="mb-4">
                    <img src="{{ auth()->user()->adminlte_image() }}" class="img-circle elevation-2"
                        style="width: 150px; height: 150px; object-fit: cover;">
                </div>
                <div class="form-group w-100">
                    <div class="custom-file text-left">
                        <input type="file" wire:model="photo" class="custom-file-input" id="customFile"
                            accept="image/*">
                        <label class="custom-file-label text-truncate" for="customFile" data-browse="Buscar">Elegir
                            imagen</label>
                    </div>
                    @error('photo')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>
                <div wire:loading wire:target="photo" class="text-muted small">
                    <i class="fas fa-sync fa-spin"></i> Actualizando...
                </div>
            </div>
        </div>

        <div class="card card-success card-outline mb-0">
            <div class="card-header">
                <h3 class="card-title text-sm">Seguridad</h3>
            </div>
            <form wire:submit="updatePassword">
                <div class="card-body p-3">
                    <div class="form-group mb-2">
                        <label class="text-xs">Contraseña Actual</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-unlock"></i></span></div>
                            <input type="password" wire:model="current_password" class="form-control">
                        </div>
                    </div>
                    <div class="form-group mb-2">
                        <label class="text-xs">Nueva Contraseña</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-lock"></i></span></div>
                            <input type="password" wire:model="password" class="form-control">
                        </div>
                    </div>
                    <div class="form-group mb-0">
                        <label class="text-xs">Confirmar</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend"><span class="input-group-text"><i
                                        class="fas fa-check"></i></span></div>
                            <input type="password" wire:model="password_confirmation" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="card-footer p-2">
                    <button type="submit" class="btn btn-success btn-sm btn-block shadow-sm">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (session()->has('image_message') || session()->has('password_message'))
    @push('js')
        <script>
            Swal.fire({
                title: '¡Hecho!',
                text: '{{ session('image_message') ?? session('password_message') }}',
                icon: 'success',
                confirmButtonText: 'Entendido',
                confirmButtonColor: '#28a745'
            });
        </script>
    @endpush
@endif
