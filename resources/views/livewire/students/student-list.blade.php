<div wire:init="loadStudents">
    <div wire:ignore.self class="modal fade" id="StudentModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-user-edit"></i>
                        Editar Estudiante: {{ $userForm->user->name ?? '' }}
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
                                    class="fas fa-id-card"></i> Datos Generales</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'academic' ? 'active font-weight-bold text-primary' : '' }}"
                                wire:click.prevent="$set('activeTab', 'academic')" href="#"><i
                                    class="fas fa-graduation-cap"></i> Info. Académica</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'medical' ? 'active font-weight-bold text-primary' : '' }}"
                                wire:click.prevent="$set('activeTab', 'medical')" href="#"><i
                                    class="fas fa-notes-medical"></i> Ficha Médica</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab == 'enrollment' ? 'active font-weight-bold text-primary' : '' }}"
                                wire:click.prevent="$set('activeTab', 'enrollment')" href="#">
                                <i class="fas fa-school"></i> Inscripción
                            </a>
                        </li>
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
                                    <label class="text-sm mb-1">Correo Electrónico <span
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
                                    <label class="text-sm mb-1">Contraseña <small>(Opcional)</small></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-lock"></i></span></div>
                                        <input type="password" class="form-control" wire:model="userForm.password"
                                            placeholder="Dejar en blanco para no cambiar">
                                    </div>
                                    @error('userForm.password')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="text-sm mb-1">Estado del Estudiante <span
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
                            </div>
                        </div>

                        <div class="tab-pane {{ $activeTab == 'academic' ? 'd-block' : 'd-none' }}">
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Código Personal (MINEDUC)</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-barcode"></i></span></div>
                                        <input type="text" wire:model="studentForm.personal_code"
                                            class="form-control" placeholder="Ej. A123B456">
                                    </div>
                                    @error('studentForm.personal_code')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Carné Interno</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend"><span class="input-group-text"><i
                                                    class="fas fa-id-badge"></i></span></div>
                                        <input type="text" wire:model="studentForm.carne" class="form-control"
                                            placeholder="Ej. 2026-001">
                                    </div>
                                    @error('studentForm.carne')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-4 form-group mb-3 d-flex align-items-end">
                                    <div class="custom-control custom-switch mb-2">
                                        <input type="checkbox" wire:model="studentForm.is_own_guardian"
                                            class="custom-control-input" id="is_own_guardian">
                                        <label class="custom-control-label text-sm" for="is_own_guardian">Es su propio
                                            encargado (Mayor de edad)</label>
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
                                            class="custom-control-input" id="takes_medication_student">
                                        <label class="custom-control-label text-sm"
                                            for="takes_medication_student">¿Toma medicamentos?</label>
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
                                            class="custom-control-input" id="has_disease_student">
                                        <label class="custom-control-label text-sm" for="has_disease_student">¿Padece
                                            alguna enfermedad?</label>
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
                                            class="custom-control-input" id="has_allergies_student">
                                        <label class="custom-control-label text-sm" for="has_allergies_student">¿Tiene
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
                                            class="custom-control-input" id="had_surgery_student">
                                        <label class="custom-control-label text-sm" for="had_surgery_student">¿Tuvo
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

                        <div class="tab-pane {{ $activeTab == 'enrollment' ? 'd-block' : 'd-none' }}">

                            {{-- Inscripción año actual --}}
                            <div class="card card-outline card-primary mb-3">
                                <div class="card-header">
                                    <h3 class="card-title"><i class="fas fa-edit mr-1"></i> Inscripción
                                        {{ date('Y') }}</h3>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-8 form-group mb-2">
                                            <label class="text-sm mb-1">Aula Asignada <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-chalkboard"></i></span>
                                                </div>
                                                <select wire:model="enrollment_classroom_id"
                                                    class="form-control @error('enrollment_classroom_id') is-invalid @enderror">
                                                    <option value="">-- Seleccione un aula --</option>
                                                    @foreach ($currentYearClassrooms as $classroom)
                                                        <option value="{{ $classroom->id }}">
                                                            {{ $classroom->level->level_name }} -
                                                            {{ $classroom->grade->grade_name }} -
                                                            {{ $classroom->section->section_name }}
                                                            ({{ $classroom->year }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('enrollment_classroom_id')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-4 form-group mb-2">
                                            <label class="text-sm mb-1">Estado <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-toggle-on"></i></span>
                                                </div>
                                                <select wire:model="enrollment_status"
                                                    class="form-control @error('enrollment_status') is-invalid @enderror">
                                                    <option value="Activo">Activo</option>
                                                    <option value="Retirado">Retirado</option>
                                                </select>
                                                @error('enrollment_status')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button wire:click.prevent="saveEnrollment" class="btn btn-primary btn-sm"
                                        wire:loading.attr="disabled" wire:target="saveEnrollment">
                                        <span wire:loading.remove wire:target="saveEnrollment">
                                            <i class="fas fa-save"></i> Guardar Inscripción
                                        </span>
                                        <span wire:loading wire:target="saveEnrollment">
                                            <i class="fas fa-spinner fa-pulse"></i> Guardando...
                                        </span>
                                    </button>
                                </div>
                            </div>

                            {{-- Historial de años anteriores --}}
                            @if (count($enrollmentHistory) > 0)
                                <div class="card card-outline card-secondary">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-history mr-1"></i> Historial de
                                            Inscripciones</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-striped table-hover mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>Año</th>
                                                    <th>Nivel</th>
                                                    <th>Grado</th>
                                                    <th>Sección</th>
                                                    <th>Estado</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($enrollmentHistory as $enrollment)
                                                    <tr>
                                                        <td><span
                                                                class="badge badge-secondary">{{ $enrollment->classroom->year }}</span>
                                                        </td>
                                                        <td>{{ $enrollment->classroom->level->level_name }}</td>
                                                        <td>{{ $enrollment->classroom->grade->grade_name }}</td>
                                                        <td>{{ $enrollment->classroom->section->section_name }}</td>
                                                        <td>
                                                            @if ($enrollment->status === 'Activo')
                                                                <span class="badge badge-success">Activo</span>
                                                            @else
                                                                <span class="badge badge-danger">Retirado</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-light border text-center text-muted">
                                    <i class="fas fa-history fa-2x mb-2"></i><br>
                                    No hay historial de inscripciones anteriores.
                                </div>
                            @endif

                        </div>

                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" wire:click="resetFields"
                        data-dismiss="modal">Cancelar</button>
                    <button wire:click.prevent="update" type="button" class="btn btn-warning"
                        wire:loading.attr="disabled" wire:target="update">
                        <span wire:loading.remove wire:target="update">
                            <i class="fas fa-save"></i> Actualizar Estudiante
                        </span>
                        <span wire:loading wire:target="update">
                            <i class="fas fa-spinner fa-pulse"></i> Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>


    <div wire:ignore.self class="modal fade" id="GuardianModal" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-users"></i> Perfil Familiar de: {{ $managingStudent->name ?? '' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"
                        wire:click="cancelGuardianForm">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>

                <div class="modal-body bg-light">

                    @if (!$showGuardianForm)
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="m-0 text-bold text-secondary">Encargados Asignados</h6>
                            <button wire:click="createGuardian" class="btn btn-sm btn-primary shadow-sm"><i
                                    class="fas fa-plus"></i> Agregar Encargado</button>
                        </div>

                        @if ($managingStudent && $managingStudent->student && $managingStudent->student->guardians->count() > 0)
                            <div class="row">
                                @foreach ($managingStudent->student->guardians as $guardian)
                                    <div class="col-md-6 mb-3">
                                        <div class="card card-outline card-info shadow-sm h-100">
                                            <div
                                                class="card-header p-2 d-flex justify-content-between align-items-center">
                                                <h6 class="card-title text-sm m-0 font-weight-bold">
                                                    {{ $guardian->first_name }} {{ $guardian->last_name }}
                                                </h6>
                                                <span
                                                    class="badge badge-info">{{ $guardian->pivot->relationship_type }}</span>
                                            </div>
                                            <div class="card-body p-2 text-sm">
                                                <p class="m-0"><i class="fas fa-id-card text-muted mr-1"></i>
                                                    <strong>CUI:</strong> {{ $guardian->cui }}
                                                </p>
                                                <p class="m-0"><i class="fas fa-phone text-muted mr-1"></i>
                                                    <strong>Teléfono:</strong> {{ $guardian->phone ?? 'N/A' }}
                                                </p>
                                                <p class="m-0 text-truncate"><i
                                                        class="fas fa-envelope text-muted mr-1"></i>
                                                    <strong>Correo:</strong> {{ $guardian->email ?? 'N/A' }}
                                                </p>
                                            </div>
                                            <div class="card-footer p-2 text-right bg-white border-top">
                                                <button wire:click="editGuardian({{ $guardian->id }})"
                                                    class="btn btn-xs btn-warning px-3"><i class="fas fa-edit"></i>
                                                    Editar Datos</button>

                                                @if ($guardian->pivot->relationship_type !== 'Encargado')
                                                    <button wire:click="detachGuardian({{ $guardian->id }})"
                                                        class="btn btn-xs btn-danger px-3 ml-1"><i
                                                            class="fas fa-unlink"></i> Retirar</button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="alert alert-light text-center border mt-3">
                                <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
                                <h5>Sin información familiar</h5>
                                <p class="text-muted">Este estudiante aún no tiene asignado a un Padre, Madre o
                                    Encargado.</p>
                            </div>
                        @endif
                    @else
                        <div class="card shadow-sm border-info mb-0">
                            <div class="card-header bg-white">
                                <h6 class="card-title text-bold text-info m-0">
                                    <i
                                        class="fas {{ $guardianForm->guardian ? 'fa-user-edit' : 'fa-user-plus' }} mr-1"></i>
                                    {{ $guardianForm->guardian ? 'Editando información de ' . $guardianForm->first_name : 'Registrar Nuevo Encargado' }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label class="text-sm bg-info text-white px-2 py-1 rounded mb-2">Parentesco con
                                            el Estudiante <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-project-diagram"></i></span></div>
                                            <select wire:model="relationship_type" class="form-control">
                                                <option value="">Seleccione parentesco...</option>
                                                @foreach ($this->getAvailableRelationships() as $rel)
                                                    <option value="{{ $rel }}">{{ $rel }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('relationship_type')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-2">
                                    </div>

                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">CUI <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-id-card"></i></span></div>
                                            <input type="text" class="form-control" wire:model="guardianForm.cui">
                                        </div>
                                        @error('guardianForm.cui')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Extendido en</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-map-marker-alt"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.cui_extended_in">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Nombres <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.first_name">
                                        </div>
                                        @error('guardianForm.first_name')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Apellidos <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-user"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.last_name">
                                        </div>
                                        @error('guardianForm.last_name')
                                            <span class="text-danger small">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Fecha de Nacimiento</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-calendar"></i></span></div>
                                            <input type="date" class="form-control"
                                                wire:model="guardianForm.birthdate">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Lugar de Nacimiento</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-map"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.birthplace">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Nacionalidad</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-flag"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.nationality">
                                        </div>
                                    </div>
                                    <div class="col-md-3 form-group mb-2">
                                        <label class="text-sm mb-1">Profesión / Oficio</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-briefcase"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.profession">
                                        </div>
                                    </div>

                                    <div class="col-md-4 form-group mb-2">
                                        <label class="text-sm mb-1">Teléfono Móvil</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-phone"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.phone">
                                        </div>
                                    </div>
                                    <div class="col-md-8 form-group mb-2">
                                        <label class="text-sm mb-1">Correo Electrónico</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-envelope"></i></span></div>
                                            <input type="email" class="form-control"
                                                wire:model="guardianForm.email">
                                        </div>
                                    </div>
                                    <div class="col-md-12 form-group mb-2">
                                        <label class="text-sm mb-1">Dirección de Residencia</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-home"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.residence_address">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <hr class="my-2">
                                    </div>
                                    <div class="col-12"><label class="text-sm text-muted">Información Laboral
                                            (Opcional)</label></div>

                                    <div class="col-md-4 form-group mb-2">
                                        <label class="text-sm mb-1">Lugar de Trabajo</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-building"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.company_name">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="text-sm mb-1">Teléfono Trabajo</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-phone-square-alt"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.company_phone">
                                        </div>
                                    </div>
                                    <div class="col-md-4 form-group mb-2">
                                        <label class="text-sm mb-1">Dirección Trabajo</label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend"><span class="input-group-text"><i
                                                        class="fas fa-map-pin"></i></span></div>
                                            <input type="text" class="form-control"
                                                wire:model="guardianForm.company_address">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <button wire:click.prevent="cancelGuardianForm" class="btn btn-secondary mr-2"><i
                                        class="fas fa-arrow-left"></i> Volver a la lista</button>
                                <button wire:click.prevent="saveGuardian" class="btn btn-success">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </div>
                        </div>
                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-dismiss="modal"
                        wire:click="cancelGuardianForm">Terminar y Cerrar Panel</button>
                </div>
            </div>
        </div>
    </div>


    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h3 class="card-title text-primary"><i class="fas fa-user-graduate mr-1"></i> Directorio de
                        Estudiantes</h3>
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
                            name="buscar" id="buscador" placeholder="Buscar por CUI, nombre..." autocomplete="search">
                        <div class="input-group-append"><button type="button" class="btn btn-default"><i
                                    class="fas fa-search"></i></button></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            @if (count($students))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor: pointer" wire:click="order('cui')">CUI @if ($sort == 'cui')
                                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
                            <th style="cursor: pointer" wire:click="order('surname')">Nombre Completo @if ($sort == 'surname')
                                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                                @else
                                    <i class="fas fa-sort text-muted"></i>
                                @endif
                            </th>
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
                        @foreach ($students as $student)
                            <tr>
                                <td>{{ $student->cui }}</td>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>
                                    @if ($student->is_active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.students.pdf', $student->id) }}" target="_blank"
                                        class="btn btn-sm btn-danger shadow-sm mr-1" title="Imprimir Ficha PDF">
                                        <i class="fas fa-file-pdf"></i>
                                    </a>

                                    <button wire:click="manageGuardians({{ $student->id }})" data-toggle="modal"
                                        data-target="#GuardianModal" class="btn btn-sm btn-info shadow-sm mr-1"
                                        title="Ver Perfil Familiar (Encargados)">
                                        <i class="fas fa-users"></i>
                                    </button>

                                    <button wire:click="edit({{ $student->id }})" data-toggle="modal"
                                        data-target="#StudentModal" class="btn btn-sm btn-warning shadow-sm"
                                        title="Editar Información">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando estudiantes...
                    @else
                        <i class="fas fa-user-graduate fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>
        @if ($readyToLoad && $students->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $students->links() }}</div>
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
                border-top-color: #007bff;
                font-weight: bold;
                background-color: #f8f9fa;
            }
        </style>
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('closeModalMessaje', (event) => {
                    let payload = event[0] || event;

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

                Livewire.on('toastMessage', (event) => {
                    let payload = event[0] || event;
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                        didOpen: (toast) => {
                            toast.addEventListener('mouseenter', Swal.stopTimer)
                            toast.addEventListener('mouseleave', Swal.resumeTimer)
                        }
                    });

                    Toast.fire({
                        icon: payload.type,
                        title: payload.message
                    });
                });

                // NUEVO LISTENER PARA CONFIRMAR CAMBIO DE AULA
                Livewire.on('confirmClassroomChange', (event) => {
                    let payload = event[0] || event;
                    Swal.fire({
                        title: payload.title,
                        text: payload.text,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: '<i class="fas fa-trash-alt"></i> Sí, cambiar y borrar notas',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Si el usuario acepta, dispara el evento hacia el backend
                            Livewire.dispatch('triggerConfirmSaveEnrollment');
                        }
                    });
                });

            });
        </script>
    @endpush
</div>
