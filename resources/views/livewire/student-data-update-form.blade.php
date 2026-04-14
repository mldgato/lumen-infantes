<div class="public-card-wide">

    <div class="public-header">
        <h1><i class="fas fa-edit mr-2"></i>Actualización de Datos</h1>
        <p>{{ config('app.institution_name', 'EduCheck') }}</p>
    </div>

    <div class="public-body">

        {{-- Token expirado --}}
        @if ($tokenExpired)
            <div class="text-center py-4">
                <i class="fas fa-clock fa-4x text-warning mb-3"></i>
                <h5 class="text-dark mb-2">El enlace ha expirado</h5>
                <p class="text-muted mb-4">
                    El enlace de actualización ya no es válido.<br>
                    Por favor solicita uno nuevo.
                </p>
                <a href="{{ route('student.data.request') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver al inicio
                </a>
            </div>

        {{-- Formulario --}}
        @else

            {{-- Cabecera con nombre del estudiante --}}
            <div class="mb-4 pb-3 border-bottom">
                <p class="text-muted mb-1" style="font-size:13px;">Actualizando información de:</p>
                <h5 class="mb-0 text-dark">{{ trim("$firstName $middleName $surname $secondSurname") }}</h5>
                <small class="text-muted">CUI: {{ $cui }}</small>
            </div>

            {{-- Tabs de navegación --}}
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item">
                    <a href="#"
                       wire:click.prevent="$set('activeTab','personal')"
                       class="nav-link {{ $activeTab === 'personal' ? 'active' : '' }}">
                        <i class="fas fa-user mr-1"></i>
                        <span class="d-none d-sm-inline">Datos Personales</span>
                        <span class="d-inline d-sm-none">Personal</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#"
                       wire:click.prevent="$set('activeTab','medical')"
                       class="nav-link {{ $activeTab === 'medical' ? 'active' : '' }}">
                        <i class="fas fa-heartbeat mr-1"></i>
                        <span class="d-none d-sm-inline">Ficha Médica</span>
                        <span class="d-inline d-sm-none">Médica</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#"
                       wire:click.prevent="$set('activeTab','guardian')"
                       class="nav-link {{ $activeTab === 'guardian' ? 'active' : '' }}">
                        <i class="fas fa-users mr-1"></i>
                        <span class="d-none d-sm-inline">Encargados</span>
                        <span class="d-inline d-sm-none">Encargados</span>
                    </a>
                </li>
            </ul>

            <form wire:submit="save">

                {{-- Aviso de eliminación de guardianes --}}
                @if ($showDeleteWarning)
                    <div class="alert alert-warning mt-3" role="alert">
                        <h6 class="font-weight-bold mb-2">
                            <i class="fas fa-exclamation-triangle mr-1"></i> Atención
                        </h6>
                        <p class="mb-2">Los siguientes registros serán eliminados al guardar:</p>
                        <ul class="mb-3">
                            @foreach ($pendingDeleteLabels as $label)
                                <li>{{ $label }}</li>
                            @endforeach
                        </ul>
                        <button type="button"
                                wire:click="confirmDeleteAndSave"
                                class="btn btn-danger btn-sm mr-2">
                            <i class="fas fa-trash mr-1"></i> Sí, eliminar y guardar
                        </button>
                        <button type="button"
                                wire:click="cancelDeleteWarning"
                                class="btn btn-outline-secondary btn-sm">
                            Cancelar
                        </button>
                    </div>
                @endif

                {{-- ═══════════════════════════════════════════════
                     TAB 1 — DATOS PERSONALES
                ═══════════════════════════════════════════════ --}}
                <div class="{{ $activeTab !== 'personal' ? 'd-none' : '' }}">

                    <h6 class="font-weight-bold text-muted text-uppercase mb-3" style="font-size:11px;letter-spacing:.5px;">
                        Datos de solo lectura
                    </h6>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Primer Nombre</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $firstName }}" readonly>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Segundo Nombre</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $middleName }}" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Primer Apellido</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $surname }}" readonly>
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Segundo Apellido</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $secondSurname }}" readonly>
                        </div>
                    </div>

                    <hr class="my-3">
                    <h6 class="font-weight-bold text-muted text-uppercase mb-3" style="font-size:11px;letter-spacing:.5px;">
                        Datos editables
                    </h6>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Apellido de casada</label>
                            <input type="text"
                                   wire:model="marriedSurname"
                                   class="form-control form-control-sm @error('marriedSurname') is-invalid @enderror"
                                   placeholder="(opcional)">
                            @error('marriedSurname')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Estado civil</label>
                            <select wire:model="civilStatus"
                                    class="form-control form-control-sm @error('civilStatus') is-invalid @enderror">
                                <option value="">— Seleccionar —</option>
                                <option>Soltero/a</option>
                                <option>Casado/a</option>
                                <option>Divorciado/a</option>
                                <option>Viudo/a</option>
                                <option>Unión de hecho</option>
                            </select>
                            @error('civilStatus')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Fecha de nacimiento <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   wire:model="birthdate"
                                   class="form-control form-control-sm @error('birthdate') is-invalid @enderror">
                            @error('birthdate')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Género <span class="text-danger">*</span>
                            </label>
                            <select wire:model="gender"
                                    class="form-control form-control-sm @error('gender') is-invalid @enderror">
                                <option value="">— Seleccionar —</option>
                                <option>Masculino</option>
                                <option>Femenino</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:13px;">
                            Correo electrónico
                            <span class="badge badge-secondary" style="font-size:10px;">pre-registrado</span>
                        </label>
                        <input type="email"
                               class="form-control form-control-sm bg-light"
                               value="{{ $email }}"
                               readonly>
                        <small class="form-text text-muted">Se usará como tu correo de acceso al sistema.</small>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Teléfono / Celular</label>
                            <input type="text"
                                   wire:model="cellphone"
                                   class="form-control form-control-sm @error('cellphone') is-invalid @enderror"
                                   placeholder="Ej: 5555-1234">
                            @error('cellphone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Dirección</label>
                            <input type="text"
                                   wire:model="address"
                                   class="form-control form-control-sm @error('address') is-invalid @enderror"
                                   placeholder="Calle, zona, municipio">
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-end mt-3">
                        <button type="button"
                                wire:click="$set('activeTab','medical')"
                                class="btn btn-primary btn-sm">
                            Siguiente <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>

                </div>

                {{-- ═══════════════════════════════════════════════
                     TAB 2 — FICHA MÉDICA
                ═══════════════════════════════════════════════ --}}
                <div class="{{ $activeTab !== 'medical' ? 'd-none' : '' }}">

                    {{-- Medicación --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   wire:model.live="takesMedication"
                                   class="custom-control-input"
                                   id="takesMedication">
                            <label class="custom-control-label font-weight-bold" for="takesMedication">
                                ¿Toma medicación?
                            </label>
                        </div>
                        @if ($takesMedication)
                            <textarea wire:model="medicationDescription"
                                      class="form-control form-control-sm mt-2 @error('medicationDescription') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Describe la medicación que toma..."></textarea>
                            @error('medicationDescription')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    {{-- Enfermedad --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   wire:model.live="hasDisease"
                                   class="custom-control-input"
                                   id="hasDisease">
                            <label class="custom-control-label font-weight-bold" for="hasDisease">
                                ¿Padece alguna enfermedad?
                            </label>
                        </div>
                        @if ($hasDisease)
                            <textarea wire:model="diseaseDescription"
                                      class="form-control form-control-sm mt-2 @error('diseaseDescription') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Describe la enfermedad..."></textarea>
                            @error('diseaseDescription')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    {{-- Alergias --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   wire:model.live="hasAllergies"
                                   class="custom-control-input"
                                   id="hasAllergies">
                            <label class="custom-control-label font-weight-bold" for="hasAllergies">
                                ¿Tiene alergias?
                            </label>
                        </div>
                        @if ($hasAllergies)
                            <textarea wire:model="allergiesDescription"
                                      class="form-control form-control-sm mt-2 @error('allergiesDescription') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Describe las alergias..."></textarea>
                            @error('allergiesDescription')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    {{-- Cirugías --}}
                    <div class="form-group">
                        <div class="custom-control custom-switch">
                            <input type="checkbox"
                                   wire:model.live="hadSurgery"
                                   class="custom-control-input"
                                   id="hadSurgery">
                            <label class="custom-control-label font-weight-bold" for="hadSurgery">
                                ¿Ha tenido cirugías?
                            </label>
                        </div>
                        @if ($hadSurgery)
                            <textarea wire:model="surgeryDescription"
                                      class="form-control form-control-sm mt-2 @error('surgeryDescription') is-invalid @enderror"
                                      rows="2"
                                      placeholder="Describe las cirugías..."></textarea>
                            @error('surgeryDescription')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>

                    <div class="form-row">
                        <div class="form-group col-sm-4">
                            <label class="font-weight-bold" style="font-size:13px;">Tipo de sangre</label>
                            <select wire:model="bloodType"
                                    class="form-control form-control-sm @error('bloodType') is-invalid @enderror">
                                <option value="">— Seleccionar —</option>
                                @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $bt)
                                    <option>{{ $bt }}</option>
                                @endforeach
                            </select>
                            @error('bloodType')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-4">
                            <label class="font-weight-bold" style="font-size:13px;">Peso (kg)</label>
                            <input type="number"
                                   wire:model="weight"
                                   class="form-control form-control-sm @error('weight') is-invalid @enderror"
                                   placeholder="Ej: 65.5"
                                   step="0.1" min="0" max="500">
                            @error('weight')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-4">
                            <label class="font-weight-bold" style="font-size:13px;">Estatura (m)</label>
                            <input type="number"
                                   wire:model="height"
                                   class="form-control form-control-sm @error('height') is-invalid @enderror"
                                   placeholder="Ej: 1.65"
                                   step="0.01" min="0" max="3">
                            @error('height')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button"
                                wire:click="$set('activeTab','personal')"
                                class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Anterior
                        </button>
                        <button type="button"
                                wire:click="$set('activeTab','guardian')"
                                class="btn btn-primary btn-sm">
                            Siguiente <i class="fas fa-arrow-right ml-1"></i>
                        </button>
                    </div>

                </div>

                {{-- ═══════════════════════════════════════════════
                     TAB 3 — ENCARGADOS
                ═══════════════════════════════════════════════ --}}
                <div class="{{ $activeTab !== 'guardian' ? 'd-none' : '' }}">

                    @foreach ([
                        ['padre',     'Padre',     'fa-male',        'border-primary',   'text-primary'],
                        ['madre',     'Madre',     'fa-female',      'border-danger',    'text-danger'],
                        ['encargado', 'Encargado', 'fa-user-shield', 'border-secondary', 'text-secondary'],
                    ] as [$key, $label, $icon, $borderClass, $iconClass])
                        <div class="card {{ $borderClass }} mb-3">
                            <div class="card-header py-2 bg-light">
                                <div class="d-flex align-items-center">
                                    <div class="custom-control custom-switch mr-3">
                                        <input type="checkbox"
                                               wire:model.live="guardians.{{ $key }}.enabled"
                                               class="custom-control-input"
                                               id="guardian_{{ $key }}_enabled">
                                        <label class="custom-control-label font-weight-bold"
                                               for="guardian_{{ $key }}_enabled">
                                            <i class="fas {{ $icon }} {{ $iconClass }} mr-1"></i>
                                            {{ $label }}
                                        </label>
                                    </div>
                                    @if (! $guardians[$key]['enabled'])
                                        <small class="text-muted">No registrado</small>
                                    @else
                                        <small class="{{ $iconClass }}">
                                            <i class="fas fa-check-circle mr-1"></i> Activo
                                        </small>
                                    @endif
                                </div>
                            </div>

                            @if ($guardians[$key]['enabled'])
                                <div class="card-body">

                                    {{-- Radio de autocompletar — solo para encargado --}}
                                    @if ($key === 'encargado')
                                        <div class="row mb-3 pb-3 border-bottom">
                                            <div class="col-12">
                                                <label class="font-weight-bold d-block mb-2 text-secondary"
                                                       style="font-size:12px;">
                                                    <i class="fas fa-magic mr-1"></i>
                                                    ¿Desea autocompletar la información del encargado?
                                                </label>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="encargado_estudiante"
                                                           wire:model.live="encargadoRole"
                                                           value="estudiante"
                                                           class="custom-control-input">
                                                    <label class="custom-control-label"
                                                           for="encargado_estudiante">El mismo estudiante</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="encargado_padre"
                                                           wire:model.live="encargadoRole"
                                                           value="padre"
                                                           class="custom-control-input">
                                                    <label class="custom-control-label"
                                                           for="encargado_padre">Papá</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="encargado_madre"
                                                           wire:model.live="encargadoRole"
                                                           value="madre"
                                                           class="custom-control-input">
                                                    <label class="custom-control-label"
                                                           for="encargado_madre">Mamá</label>
                                                </div>
                                                <div class="custom-control custom-radio custom-control-inline">
                                                    <input type="radio" id="encargado_otro"
                                                           wire:model.live="encargadoRole"
                                                           value="otro"
                                                           class="custom-control-input">
                                                    <label class="custom-control-label"
                                                           for="encargado_otro">Otra persona (Manual)</label>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-row">
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Nombres <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.first_name"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.first_name') is-invalid @enderror"
                                                   placeholder="Nombre completo">
                                            @error('guardians.' . $key . '.data.first_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Apellidos <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.last_name"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.last_name') is-invalid @enderror"
                                                   placeholder="Apellido completo">
                                            @error('guardians.' . $key . '.data.last_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Fecha de nacimiento <span class="text-danger">*</span>
                                            </label>
                                            <input type="date"
                                                   wire:model="guardians.{{ $key }}.data.birthdate"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.birthdate') is-invalid @enderror">
                                            @error('guardians.' . $key . '.data.birthdate')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">Lugar de nacimiento</label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.birthplace"
                                                   class="form-control form-control-sm"
                                                   placeholder="Ciudad, país">
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                CUI / DPI <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.cui"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.cui') is-invalid @enderror"
                                                   placeholder="Número de DPI">
                                            @error('guardians.' . $key . '.data.cui')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Lugar de extensión del CUI <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.cui_extended_in"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.cui_extended_in') is-invalid @enderror"
                                                   placeholder="Municipio, departamento">
                                            @error('guardians.' . $key . '.data.cui_extended_in')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Nacionalidad <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.nationality"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.nationality') is-invalid @enderror"
                                                   placeholder="Guatemalteco/a">
                                            @error('guardians.' . $key . '.data.nationality')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-sm-6">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Profesión u oficio <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.profession"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.profession') is-invalid @enderror"
                                                   placeholder="Profesión">
                                            @error('guardians.' . $key . '.data.profession')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group col-sm-8">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Dirección de residencia <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.residence_address"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.residence_address') is-invalid @enderror"
                                                   placeholder="Dirección completa">
                                            @error('guardians.' . $key . '.data.residence_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="form-group col-sm-4">
                                            <label class="font-weight-bold" style="font-size:13px;">
                                                Teléfono <span class="text-danger">*</span>
                                            </label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.phone"
                                                   class="form-control form-control-sm @error('guardians.' . $key . '.data.phone') is-invalid @enderror"
                                                   placeholder="5555-1234">
                                            @error('guardians.' . $key . '.data.phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="font-weight-bold" style="font-size:13px;">Correo electrónico</label>
                                        <input type="email"
                                               wire:model="guardians.{{ $key }}.data.email"
                                               class="form-control form-control-sm"
                                               placeholder="correo@ejemplo.com (opcional)">
                                    </div>

                                    <hr class="my-2">
                                    <p class="text-muted text-uppercase font-weight-bold mb-2"
                                       style="font-size:11px;letter-spacing:.5px;">
                                        Datos laborales (opcional)
                                    </p>

                                    <div class="form-row">
                                        <div class="form-group col-sm-5">
                                            <label class="font-weight-bold" style="font-size:13px;">Empresa / Institución</label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.company_name"
                                                   class="form-control form-control-sm"
                                                   placeholder="Nombre de la empresa">
                                        </div>
                                        <div class="form-group col-sm-5">
                                            <label class="font-weight-bold" style="font-size:13px;">Dirección de la empresa</label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.company_address"
                                                   class="form-control form-control-sm"
                                                   placeholder="Dirección">
                                        </div>
                                        <div class="form-group col-sm-2">
                                            <label class="font-weight-bold" style="font-size:13px;">Tel. empresa</label>
                                            <input type="text"
                                                   wire:model="guardians.{{ $key }}.data.company_phone"
                                                   class="form-control form-control-sm"
                                                   placeholder="Teléfono">
                                        </div>
                                    </div>

                                </div>
                            @endif
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between mt-3">
                        <button type="button"
                                wire:click="$set('activeTab','medical')"
                                class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Anterior
                        </button>
                        <button type="submit"
                                class="btn btn-success"
                                wire:loading.attr="disabled">
                            <span wire:loading.remove>
                                <i class="fas fa-save mr-1"></i> Guardar información
                            </span>
                            <span wire:loading>
                                <i class="fas fa-spinner fa-spin mr-1"></i> Guardando...
                            </span>
                        </button>
                    </div>

                </div>

            </form>
        @endif

    </div>

    <div class="public-footer">
        &copy; {{ date('Y') }} {{ config('app.institution_name', 'EduCheck') }}
    </div>

</div>
