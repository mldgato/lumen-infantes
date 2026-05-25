<div wire:init="loadData">

    {{-- Señuelo: evita que Chrome autorrellene el buscador con credenciales guardadas --}}
    <div style="position:fixed;top:-200px;left:-200px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <input type="text" autocomplete="username" tabindex="-1">
        <input type="password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- MODAL GUARDIÁN --}}
    <div wire:ignore.self class="modal fade" id="GuardianModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-user-friends mr-1"></i>
                        {{ $form->guardian ? $form->first_name . ' ' . $form->last_name : 'Guardián' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0">
                    <ul class="nav nav-tabs px-3 pt-2 bg-light" id="guardianTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-guardian-datos-link" data-toggle="tab"
                                href="#tab-guardian-datos" role="tab">
                                <i class="fas fa-id-card mr-1"></i> Datos del Guardián
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-guardian-alumnos-link" data-toggle="tab"
                                href="#tab-guardian-alumnos" role="tab">
                                <i class="fas fa-user-graduate mr-1"></i> Estudiantes Vinculados
                                <span class="badge badge-secondary ml-1">{{ $students->count() }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">

                        {{-- TAB: DATOS DEL GUARDIÁN --}}
                        <div class="tab-pane fade show active" id="tab-guardian-datos" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Primer Nombre <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.first_name"
                                            class="form-control @error('form.first_name') is-invalid @enderror"
                                            placeholder="Primer nombre">
                                        @error('form.first_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Apellido(s) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.last_name"
                                            class="form-control @error('form.last_name') is-invalid @enderror"
                                            placeholder="Apellidos">
                                        @error('form.last_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">CUI <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.cui"
                                            class="form-control @error('form.cui') is-invalid @enderror"
                                            placeholder="13 dígitos">
                                        @error('form.cui')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Extendida en</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.cui_extended_in"
                                            class="form-control @error('form.cui_extended_in') is-invalid @enderror"
                                            placeholder="Municipio / Departamento">
                                        @error('form.cui_extended_in')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Nacionalidad</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-flag"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.nationality"
                                            class="form-control @error('form.nationality') is-invalid @enderror"
                                            placeholder="Ej. Guatemalteco/a">
                                        @error('form.nationality')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Lugar de Nacimiento</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-city"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.birthplace"
                                            class="form-control @error('form.birthplace') is-invalid @enderror"
                                            placeholder="Lugar de nacimiento">
                                        @error('form.birthplace')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Fecha de Nacimiento</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-birthday-cake"></i></span>
                                        </div>
                                        <input type="date" wire:model="form.birthdate"
                                            class="form-control @error('form.birthdate') is-invalid @enderror">
                                        @error('form.birthdate')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Profesión</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-briefcase"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.profession"
                                            class="form-control @error('form.profession') is-invalid @enderror"
                                            placeholder="Profesión u oficio">
                                        @error('form.profession')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Teléfono</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.phone"
                                            class="form-control @error('form.phone') is-invalid @enderror"
                                            placeholder="Número de teléfono">
                                        @error('form.phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Correo Electrónico</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" wire:model="form.email"
                                            class="form-control @error('form.email') is-invalid @enderror"
                                            placeholder="correo@ejemplo.com">
                                        @error('form.email')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Dirección de Residencia</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-home"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.residence_address"
                                            class="form-control @error('form.residence_address') is-invalid @enderror"
                                            placeholder="Dirección completa">
                                        @error('form.residence_address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-12"><hr class="my-1"><p class="text-sm text-muted font-weight-bold mb-2">Información Laboral</p></div>

                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Empresa / Institución</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-building"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.company_name"
                                            class="form-control @error('form.company_name') is-invalid @enderror"
                                            placeholder="Nombre de la empresa">
                                        @error('form.company_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Dirección Laboral</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-map-marker-alt"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.company_address"
                                            class="form-control @error('form.company_address') is-invalid @enderror"
                                            placeholder="Dirección laboral">
                                        @error('form.company_address')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Teléfono Laboral</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-phone-office"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.company_phone"
                                            class="form-control @error('form.company_phone') is-invalid @enderror"
                                            placeholder="Teléfono laboral">
                                        @error('form.company_phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TAB: ESTUDIANTES VINCULADOS --}}
                        <div class="tab-pane fade" id="tab-guardian-alumnos" role="tabpanel">
                            @if ($students->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-user-graduate fa-2x mb-2 text-gray"></i><br>
                                    No hay estudiantes vinculados a este guardián.
                                </div>
                            @else
                                <table class="table table-sm table-striped">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>#</th>
                                            <th>Nombre del Estudiante</th>
                                            <th>Relación</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($students as $idx => $student)
                                            <tr>
                                                <td>{{ $idx + 1 }}</td>
                                                <td>{{ $student->user->name }}</td>
                                                <td>
                                                    <span class="badge badge-info">
                                                        {{ $student->pivot->relationship_type }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">
                        Cerrar
                    </button>
                    @can('admin.guardians.edit')
                        <button wire:click.prevent="save" type="button" class="btn btn-success btn-sm"
                            wire:loading.attr="disabled" wire:target="save"
                            id="btnSaveGuardian">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save"></i> Guardar Cambios
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-pulse"></i> Guardando...
                            </span>
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- LISTADO --}}
    <div class="card card-success card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="m-0 text-bold"><i class="fas fa-user-friends mr-1"></i> Guardianes / Tutores</h5>
                </div>
                <div class="col-md-8 d-flex justify-content-end align-items-center">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 300px;">
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Buscar por nombre, CUI, teléfono..." autocomplete="new-password">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            @if (!$readyToLoad)
                <div class="text-center py-5">
                    <i class="fas fa-spinner fa-spin fa-2x text-success"></i>
                    <p class="mt-2 text-muted">Cargando guardianes...</p>
                </div>
            @elseif ($readyToLoad && count($guardians))
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Nombre</th>
                            <th>CUI</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th class="text-center" style="width:100px">Estudiantes</th>
                            <th class="text-center" style="width:100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($guardians as $guardian)
                            <tr>
                                <td>{{ $guardian->id }}</td>
                                <td>
                                    <strong>{{ $guardian->first_name }} {{ $guardian->last_name }}</strong>
                                    @if ($guardian->profession)
                                        <br><small class="text-muted">{{ $guardian->profession }}</small>
                                    @endif
                                </td>
                                <td><small class="text-monospace">{{ $guardian->cui }}</small></td>
                                <td><small>{{ $guardian->phone ?? '—' }}</small></td>
                                <td><small>{{ $guardian->email ?? '—' }}</small></td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $guardian->students_count > 0 ? 'success' : 'secondary' }}">
                                        {{ $guardian->students_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="openModal({{ $guardian->id }})"
                                        data-toggle="modal" data-target="#GuardianModal"
                                        class="btn btn-sm btn-warning shadow-sm"
                                        title="Ver / Editar datos del guardián">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-user-friends fa-3x mb-3 text-gray"></i><br>
                    No se encontraron guardianes.
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($guardians) && $guardians->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $guardians->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('openGuardianModal', () => {
                    $('#tab-guardian-datos-link').tab('show');
                    $('#GuardianModal').modal('show');
                });

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
                        timer: 3000,
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
                        timer: 3500,
                    });
                });

                // Ocultar botón Guardar en la pestaña de estudiantes (es solo lectura)
                document.querySelectorAll('#guardianTabs .nav-link').forEach(tab => {
                    tab.addEventListener('shown.bs.tab', function (e) {
                        const isAlumnos = e.target.id === 'tab-guardian-alumnos-link';
                        const btn = document.getElementById('btnSaveGuardian');
                        if (btn) btn.style.display = isAlumnos ? 'none' : '';
                    });
                });
            });
        </script>
    @endpush
</div>
