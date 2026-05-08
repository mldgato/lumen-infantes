<div wire:init="loadUsers">
    <div wire:ignore.self class="modal fade" id="UserModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $userForm->user ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title">
                        <i class="fas {{ $userForm->user ? 'fa-user-edit' : 'fa-user-plus' }}"></i>
                        {{ $userForm->user ? 'Editar Personal: ' . $userForm->user->name : 'Nuevo Personal' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <ul class="nav nav-tabs custom-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'general' ? 'active font-weight-bold text-primary' : '' }}"
                                wire:click.prevent="$set('activeTab', 'general')" href="#"><i
                                    class="fas fa-id-card"></i> Datos Generales y Roles</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'medical' ? 'active font-weight-bold text-primary' : '' }}"
                                wire:click.prevent="$set('activeTab', 'medical')" href="#"><i
                                    class="fas fa-notes-medical"></i> Ficha Médica</a>
                        </li>
                        @if (in_array('Profesor', $selected_roles))
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab == 'professor' ? 'active font-weight-bold text-primary' : '' }}"
                                    wire:click.prevent="$set('activeTab', 'professor')" href="#"><i
                                        class="fas fa-chalkboard-teacher"></i> Info. Docente</a>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content p-3">
                        <div class="tab-pane {{ $activeTab == 'general' ? 'd-block' : 'd-none' }}">
                            <div class="row">
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">CUI <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-id-card"></i></span></div>
                                        <input type="text" class="form-control" wire:model="userForm.cui">
                                    </div>
                                    @error('userForm.cui')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Primer Nombre <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control" wire:model="userForm.first_name">
                                    </div>
                                    @error('userForm.first_name')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Segundo Nombre</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control" wire:model="userForm.middle_name">
                                    </div>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Primer Apellido <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control" wire:model="userForm.surname">
                                    </div>
                                    @error('userForm.surname')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Segundo Apellido</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control" wire:model="userForm.second_surname">
                                    </div>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Apellido de Casada</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-user"></i></span></div>
                                        <input type="text" class="form-control"
                                            wire:model="userForm.married_surname">
                                    </div>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Fecha de Nacimiento <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-calendar"></i></span></div>
                                        <input type="date" class="form-control" wire:model="userForm.birthdate">
                                    </div>
                                    @error('userForm.birthdate')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Género <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-venus-mars"></i></span></div>
                                        <select class="form-control" wire:model="userForm.gender">
                                            <option value="">Seleccione...</option>
                                            <option value="Masculino">Masculino</option>
                                            <option value="Femenino">Femenino</option>
                                        </select>
                                    </div>
                                    @error('userForm.gender')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Estado Civil <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-ring"></i></span></div>
                                        <select class="form-control" wire:model="userForm.civil_status">
                                            <option value="">Seleccione...</option>
                                            <option value="Soltero">Soltero(a)</option>
                                            <option value="Casado">Casado(a)</option>
                                            <option value="Divorciado">Divorciado(a)</option>
                                            <option value="Viudo">Viudo(a)</option>
                                        </select>
                                    </div>
                                    @error('userForm.civil_status')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Correo Institucional <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-envelope"></i></span></div>
                                        <input type="email" class="form-control" wire:model="userForm.email">
                                    </div>
                                    @error('userForm.email')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Correo Personal</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-envelope"></i></span></div>
                                        <input type="email" class="form-control"
                                            wire:model="userForm.personal_email">
                                    </div>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Celular</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-phone"></i></span></div>
                                        <input type="text" class="form-control" wire:model="userForm.cellphone">
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-2">
                                    <label class="text-sm mb-1">Dirección Completa</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-map-marker-alt"></i></span></div>
                                        <input type="text" class="form-control" wire:model="userForm.address">
                                    </div>
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Contraseña {!! $userForm->user ? '<small>(Opcional)</small>' : '<span class="text-danger">*</span>' !!}</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-lock"></i></span></div>
                                        <input type="password" class="form-control" wire:model="userForm.password">
                                    </div>
                                    @error('userForm.password')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Estado del Usuario <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-toggle-on"></i></span></div>
                                        <select class="form-control" wire:model="userForm.is_active">
                                            <option value="1">Activo</option>
                                            <option value="0">Inactivo</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 mt-3">
                                    <label class="text-sm bg-light px-2 py-1 rounded border w-100"><i
                                            class="fas fa-user-shield mr-1"></i> Roles Asignados <span
                                            class="text-danger">*</span></label>
                                    <div class="row px-2">
                                        @foreach ($roles as $role)
                                            <div class="col-md-3">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" wire:model.live="selected_roles"
                                                        value="{{ $role->name }}" class="custom-control-input"
                                                        id="role_{{ $role->id }}">
                                                    <label class="custom-control-label font-weight-normal text-sm"
                                                        for="role_{{ $role->id }}">
                                                        {{ $role->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('selected_roles')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-12 mt-3">
                                    <label class="text-sm bg-light px-2 py-1 rounded border w-100">
                                        <i class="fas fa-layer-group mr-1"></i> Niveles Asignados
                                    </label>
                                    <div class="row px-2">
                                        @foreach ($levels as $level)
                                            <div class="col-md-3">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" wire:model="selected_levels"
                                                        value="{{ $level->id }}" class="custom-control-input"
                                                        id="level_{{ $level->id }}">
                                                    <label class="custom-control-label font-weight-normal text-sm"
                                                        for="level_{{ $level->id }}">
                                                        {{ $level->level_name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane {{ $activeTab == 'medical' ? 'd-block' : 'd-none' }}">
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Tipo de Sangre</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-tint text-danger"></i></span></div>
                                        <select wire:model="medicalForm.blood_type" class="form-control">
                                            <option value="">Seleccione...</option>
                                            <option value="O+">O Positivo (O+)</option>
                                            <option value="O-">O Negativo (O-)</option>
                                            <option value="A+">A Positivo (A+)</option>
                                            <option value="A-">A Negativo (A-)</option>
                                            <option value="B+">B Positivo (B+)</option>
                                            <option value="B-">B Negativo (B-)</option>
                                            <option value="AB+">AB Positivo (AB+)</option>
                                            <option value="AB-">AB Negativo (AB-)</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Peso (lb)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-weight"></i></span></div>
                                        <input type="number" step="0.01" wire:model="medicalForm.weight"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Estatura (m)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-ruler-vertical"></i></span></div>
                                        <input type="number" step="0.01" wire:model="medicalForm.height"
                                            class="form-control">
                                    </div>
                                </div>
                                <div class="col-12">
                                    <hr class="my-2">
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <div class="custom-control custom-switch mt-1">
                                        <input type="checkbox" wire:model.live="medicalForm.takes_medication"
                                            class="custom-control-input" id="takes_medication_user">
                                        <label class="custom-control-label text-sm" for="takes_medication_user">¿Toma
                                            medicamentos?</label>
                                    </div>
                                </div>
                                <div class="col-md-8 form-group mb-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-pen"></i></span></div>
                                        <input type="text" wire:model="medicalForm.medication_description"
                                            class="form-control" placeholder="Especifique cuáles..."
                                            @if (!$medicalForm->takes_medication) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <div class="custom-control custom-switch mt-1">
                                        <input type="checkbox" wire:model.live="medicalForm.has_disease"
                                            class="custom-control-input" id="has_disease_user_list">
                                        <label class="custom-control-label text-sm"
                                            for="has_disease_user_list">¿Padece alguna enfermedad?</label>
                                    </div>
                                </div>
                                <div class="col-md-8 form-group mb-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-hospital"></i></span>
                                        </div>
                                        <input type="text" wire:model="medicalForm.disease_description"
                                            class="form-control" placeholder="Especifique cuál..."
                                            @if (!$medicalForm->has_disease) disabled @endif>
                                    </div>
                                    @error('medicalForm.disease_description')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <div class="custom-control custom-switch mt-1">
                                        <input type="checkbox" wire:model.live="medicalForm.has_allergies"
                                            class="custom-control-input" id="has_allergies_user">
                                        <label class="custom-control-label text-sm" for="has_allergies_user">¿Tiene
                                            alergias?</label>
                                    </div>
                                </div>
                                <div class="col-md-8 form-group mb-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-pen"></i></span></div>
                                        <input type="text" wire:model="medicalForm.allergies_description"
                                            class="form-control" placeholder="Especifique cuáles..."
                                            @if (!$medicalForm->has_allergies) disabled @endif>
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-2">
                                    <div class="custom-control custom-switch mt-1">
                                        <input type="checkbox" wire:model.live="medicalForm.had_surgery"
                                            class="custom-control-input" id="had_surgery_user">
                                        <label class="custom-control-label text-sm" for="had_surgery_user">¿Tuvo
                                            cirugía previa?</label>
                                    </div>
                                </div>
                                <div class="col-md-8 form-group mb-2">
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-pen"></i></span></div>
                                        <input type="text" wire:model="medicalForm.surgery_description"
                                            class="form-control" placeholder="Especifique cuáles..."
                                            @if (!$medicalForm->had_surgery) disabled @endif>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (in_array('Profesor', $selected_roles))
                            <div class="tab-pane {{ $activeTab == 'professor' ? 'd-block' : 'd-none' }}">
                                <div class="alert alert-info py-1 px-2 text-sm mb-3">
                                    <i class="fas fa-info-circle"></i> Nota: Estos datos se vincularán al usuario
                                    porque has seleccionado el rol "Profesor".
                                </div>
                                <div class="row">
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Fecha Contratación</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-calendar-alt"></i></span></div>
                                            <input type="date" class="form-control"
                                                wire:model="professorForm.hire_date">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">NIT</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-id-badge"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="professorForm.nit">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Afiliación IGSS</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-clinic-medical"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="professorForm.igss_affiliation">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Cédula Docente</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-id-card-alt"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="professorForm.teaching_cedula">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label class="text-sm mb-1">Título Principal</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-graduation-cap"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="professorForm.title">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label class="text-sm mb-1">Licenciatura / Especialidad</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-university"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="professorForm.bachelor_degree">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label class="text-sm mb-1">Nombre del Cónyuge</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user-friends"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="professorForm.spouse_name">
                                        </div>
                                    </div>
                                    <div class="col-md-6 form-group mb-2">
                                        <label class="text-sm mb-1">Teléfono del Cónyuge</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-phone"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="professorForm.spouse_phone">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="resetFields"
                        data-dismiss="modal">Cancelar</button>

                    <button wire:click.prevent="save" type="button"
                        class="btn {{ $userForm->user ? 'btn-warning' : 'btn-primary' }}"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save"></i>
                            {{ $userForm->user ? 'Actualizar Información' : 'Guardar Nuevo Usuario' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-pulse"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-success card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#UserModal"
                        wire:click="resetFields">
                        <i class="fas fa-plus-circle"></i> Nuevo Personal
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
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                            name="buscar" id="buscador" placeholder="Buscar por CUI, nombre..." autocomplete="search">
                        <div class="input-group-append"><button type="button" class="btn btn-default"><i
                                    class="fas fa-search"></i></button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            @if (count($users))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor: pointer" wire:click="order('cui')">CUI @if ($sort == 'cui')
                                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th style="cursor: pointer" wire:click="order('name')">Nombre Completo @if ($sort == 'name')
                                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Roles</th>
                            <th style="cursor: pointer" wire:click="order('email')">Correo @if ($sort == 'email')
                                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->cui }}</td>
                                <td>{{ $user->name }}</td>
                                <td>
                                    @foreach ($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->name }}</span>
                                    @endforeach
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @if ($user->is_active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $user->id }})" data-toggle="modal"
                                        data-target="#UserModal" class="btn btn-sm btn-warning shadow-sm"
                                        title="Editar"><i class="fas fa-edit"></i></button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-success"></i><br>Cargando personal...
                    @else
                        <i class="fas fa-users fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>
        @if ($readyToLoad && $users->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $users->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <style>
            .custom-tabs .nav-link {
                border-top-width: 3px;
                border-top-color: transparent;
            }

            .custom-tabs .nav-link.active {
                border-top-color: #28a745;
                font-weight: bold;
                background-color: #f8f9fa;
            }
        </style>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('closeModalMessaje', (event) => {
                    // Livewire 3 envía los datos dentro de un array
                    let payload = event[0] || event;

                    // Cierre limpio de Bootstrap
                    $('#' + payload.modalId).modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');

                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                });
            });
        </script>
    @endpush
</div>
