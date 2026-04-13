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
                        <span class="d-none d-sm-inline">Encargado</span>
                        <span class="d-inline d-sm-none">Encargado</span>
                    </a>
                </li>
            </ul>

            <form wire:submit="save">

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
                     TAB 3 — ENCARGADO
                ═══════════════════════════════════════════════ --}}
                <div class="{{ $activeTab !== 'guardian' ? 'd-none' : '' }}">

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Nombre(s) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   wire:model="guardianFirstName"
                                   class="form-control form-control-sm @error('guardianFirstName') is-invalid @enderror"
                                   placeholder="Nombre completo">
                            @error('guardianFirstName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Apellido(s) <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   wire:model="guardianLastName"
                                   class="form-control form-control-sm @error('guardianLastName') is-invalid @enderror"
                                   placeholder="Apellido completo">
                            @error('guardianLastName')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">Lugar de nacimiento</label>
                            <input type="text"
                                   wire:model="guardianBirthplace"
                                   class="form-control form-control-sm @error('guardianBirthplace') is-invalid @enderror"
                                   placeholder="Ciudad, país">
                            @error('guardianBirthplace')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Fecha de nacimiento <span class="text-danger">*</span>
                            </label>
                            <input type="date"
                                   wire:model="guardianBirthdate"
                                   class="form-control form-control-sm @error('guardianBirthdate') is-invalid @enderror">
                            @error('guardianBirthdate')
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
                                   wire:model="guardianNationality"
                                   class="form-control form-control-sm @error('guardianNationality') is-invalid @enderror"
                                   placeholder="Guatemalteco/a">
                            @error('guardianNationality')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                CUI / DPI <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   wire:model="guardianCui"
                                   class="form-control form-control-sm @error('guardianCui') is-invalid @enderror"
                                   placeholder="Número de DPI">
                            @error('guardianCui')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Lugar de extensión del CUI <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   wire:model="guardianCuiExtendedIn"
                                   class="form-control form-control-sm @error('guardianCuiExtendedIn') is-invalid @enderror"
                                   placeholder="Municipio, departamento">
                            @error('guardianCuiExtendedIn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-6">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Profesión u oficio <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   wire:model="guardianProfession"
                                   class="form-control form-control-sm @error('guardianProfession') is-invalid @enderror"
                                   placeholder="Profesión">
                            @error('guardianProfession')
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
                                   wire:model="guardianResidenceAddress"
                                   class="form-control form-control-sm @error('guardianResidenceAddress') is-invalid @enderror"
                                   placeholder="Dirección completa">
                            @error('guardianResidenceAddress')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-group col-sm-4">
                            <label class="font-weight-bold" style="font-size:13px;">
                                Teléfono <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   wire:model="guardianPhone"
                                   class="form-control form-control-sm @error('guardianPhone') is-invalid @enderror"
                                   placeholder="5555-1234">
                            @error('guardianPhone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold" style="font-size:13px;">Correo electrónico del encargado</label>
                        <input type="email"
                               wire:model="guardianEmail"
                               class="form-control form-control-sm @error('guardianEmail') is-invalid @enderror"
                               placeholder="correo@ejemplo.com (opcional)">
                        @error('guardianEmail')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr class="my-3">
                    <h6 class="font-weight-bold text-muted text-uppercase mb-3" style="font-size:11px;letter-spacing:.5px;">
                        Datos laborales del encargado (opcional)
                    </h6>

                    <div class="form-row">
                        <div class="form-group col-sm-5">
                            <label class="font-weight-bold" style="font-size:13px;">Empresa / Institución</label>
                            <input type="text"
                                   wire:model="guardianCompanyName"
                                   class="form-control form-control-sm"
                                   placeholder="Nombre de la empresa">
                        </div>
                        <div class="form-group col-sm-5">
                            <label class="font-weight-bold" style="font-size:13px;">Dirección de la empresa</label>
                            <input type="text"
                                   wire:model="guardianCompanyAddress"
                                   class="form-control form-control-sm"
                                   placeholder="Dirección">
                        </div>
                        <div class="form-group col-sm-2">
                            <label class="font-weight-bold" style="font-size:13px;">Tel. empresa</label>
                            <input type="text"
                                   wire:model="guardianCompanyPhone"
                                   class="form-control form-control-sm"
                                   placeholder="Teléfono">
                        </div>
                    </div>

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
