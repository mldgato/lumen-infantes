<div>
    {{-- ════════════════════════════════════════
         FILTROS
    ════════════════════════════════════════ --}}
    <div class="card card-outline card-primary">
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label class="control-label">Ciclo Escolar</label>
                        <select wire:model.live="filterYear" class="form-control">
                            <option value="">— Todos —</option>
                            @foreach ($this->availableYears as $yr)
                                <option value="{{ $yr }}">{{ $yr }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3">
                    <div class="form-group">
                        <label class="control-label">Estado</label>
                        <select wire:model.live="filterStatus" class="form-control">
                            <option value="">— Todos —</option>
                            <option value="pending">Pendiente</option>
                            <option value="emailed">Correo enviado</option>
                            <option value="reviewed">Documentación completa</option>
                            <option value="accepted">Aceptado</option>
                            <option value="rejected">Rechazado</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4">
                    <div class="form-group">
                        <label class="control-label">Buscar</label>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            class="form-control" placeholder="Nombre del alumno o correo del encargado..."
                            autocomplete="new-password">
                    </div>
                </div>
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="control-label">Por página</label>
                        <select wire:model.live="cant" class="form-control">
                            <option value="10">10</option>
                            <option value="15">15</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($this->pendingCount > 0)
        <div class="alert alert-warning py-2">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            Hay <strong>{{ $this->pendingCount }}</strong>
            solicitud{{ $this->pendingCount !== 1 ? 'es' : '' }} pendiente{{ $this->pendingCount !== 1 ? 's' : '' }} de revisión.
        </div>
    @endif

    {{-- ════════════════════════════════════════
         TABLA
    ════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th>Alumno</th>
                            <th>Nivel / Grado</th>
                            <th>Ciclo</th>
                            <th>Encargado</th>
                            <th>Recibida</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->applications as $app)
                            <tr>
                                <td>
                                    <strong>{{ $app->student_first_surname }} {{ $app->student_second_surname }}</strong>
                                    <br>
                                    <small>{{ $app->student_first_name }} {{ $app->student_second_name }}</small>
                                </td>
                                <td>
                                    <small>{{ $app->level?->level_name ?? '—' }}</small><br>
                                    <small class="text-muted">{{ $app->grade?->grade_name ?? '—' }}</small>
                                </td>
                                <td>{{ $app->year }}</td>
                                <td>
                                    <small>{{ $app->guardian_name }}</small><br>
                                    <small class="text-muted">{{ $app->guardian_email }}</small>
                                </td>
                                <td><small>{{ $app->created_at->format('d/m/Y') }}</small></td>
                                <td>
                                    <span class="badge badge-{{ $app->statusColor() }}">
                                        {{ $app->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <button wire:click="viewApplication({{ $app->id }})"
                                        class="btn btn-xs btn-info" title="Ver / Editar">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    @can('admin.admissions.manage')
                                        @if ($app->isPending())
                                            <button
                                                @click="Swal.fire({ title: '¿Marcar correo enviado al postulante?', icon: 'question', showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar' }).then(r => r.isConfirmed && $wire.markEmailed({{ $app->id }}))"
                                                class="btn btn-xs btn-primary" title="Correo enviado">
                                                <i class="fas fa-envelope"></i>
                                            </button>
                                        @endif
                                        @if ($app->isReviewed())
                                            <button
                                                @click="Swal.fire({ title: '¿Aceptar esta solicitud?', icon: 'question', showCancelButton: true, confirmButtonText: 'Aceptar', cancelButtonText: 'Cancelar', confirmButtonColor: '#28a745' }).then(r => r.isConfirmed && $wire.markAccepted({{ $app->id }}))"
                                                class="btn btn-xs btn-success" title="Aceptar">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button wire:click="openReject({{ $app->id }})"
                                                class="btn btn-xs btn-danger" title="Rechazar">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                        @if (! $app->isPending())
                                            <button
                                                @click="Swal.fire({ title: '¿Regresar a Pendiente?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí', cancelButtonText: 'No', confirmButtonColor: '#ffc107' }).then(r => r.isConfirmed && $wire.resetToPending({{ $app->id }}))"
                                                class="btn btn-xs btn-warning" title="Regresar a pendiente">
                                                <i class="fas fa-undo"></i>
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No se encontraron solicitudes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($this->applications->hasPages())
            <div class="card-footer">
                {{ $this->applications->links() }}
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════
         MODAL — Detalle / Edición
    ════════════════════════════════════════ --}}
    <div class="modal fade" id="admissionDetailModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                @if ($viewing)
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-file-signature mr-2"></i>
                            {{ $viewing->student_first_name }} {{ $viewing->student_first_surname }}
                            — Ciclo {{ $viewing->year }}
                            <span class="badge badge-{{ $viewing->statusColor() }} ml-2">
                                {{ $viewing->statusLabel() }}
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body p-0" x-data="{ activeTab: 'tab-alumno' }">
                        {{-- TABS --}}
                        <ul class="nav nav-tabs nav-justified border-bottom" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ 'active': activeTab === 'tab-alumno' }"
                                    @click.prevent="activeTab = 'tab-alumno'" href="#" role="tab">
                                    <i class="fas fa-user-graduate mr-1"></i> Alumno & Grado
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ 'active': activeTab === 'tab-padres' }"
                                    @click.prevent="activeTab = 'tab-padres'" href="#" role="tab">
                                    <i class="fas fa-users mr-1"></i> Padres & Encargado
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ 'active': activeTab === 'tab-papeleria' }"
                                    @click.prevent="activeTab = 'tab-papeleria'" href="#" role="tab">
                                    <i class="fas fa-folder-open mr-1"></i>
                                    Papelería
                                    @if ($viewing->documents?->isComplete())
                                        <span class="badge badge-success badge-sm ml-1">✓</span>
                                    @else
                                        <span class="badge badge-secondary badge-sm ml-1">
                                            {{ $viewing->documents?->receivedCount() ?? 0 }}/5
                                        </span>
                                    @endif
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" :class="{ 'active': activeTab === 'tab-historial' }"
                                    @click.prevent="activeTab = 'tab-historial'" href="#" role="tab">
                                    <i class="fas fa-history mr-1"></i> Historial
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-3">
                            {{-- ── TAB 1: Alumno & Grado ─────────────────── --}}
                            <div id="tab-alumno" role="tabpanel" x-show="activeTab === 'tab-alumno'">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Primer Nombre <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="editStudentFirstName"
                                                class="form-control @error('editStudentFirstName') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentFirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Segundo Nombre</label>
                                            <input type="text" wire:model="editStudentSecondName"
                                                class="form-control @error('editStudentSecondName') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentSecondName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Primer Apellido <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="editStudentFirstSurname"
                                                class="form-control @error('editStudentFirstSurname') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentFirstSurname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <div class="form-group">
                                            <label>Segundo Apellido</label>
                                            <input type="text" wire:model="editStudentSecondSurname"
                                                class="form-control @error('editStudentSecondSurname') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentSecondSurname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label>Fecha de Nacimiento <span class="text-danger">*</span></label>
                                            <input type="date" wire:model="editStudentBirthdate"
                                                class="form-control @error('editStudentBirthdate') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentBirthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label>Religión</label>
                                            <input type="text" wire:model="editStudentReligion"
                                                class="form-control @error('editStudentReligion') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentReligion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label>Colegio Anterior</label>
                                            <input type="text" wire:model="editStudentPreviousSchool"
                                                class="form-control @error('editStudentPreviousSchool') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentPreviousSchool') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <label>Dirección <span class="text-danger">*</span></label>
                                            <input type="text" wire:model="editStudentAddress"
                                                class="form-control @error('editStudentAddress') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editStudentAddress') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>

                                    <div class="col-12"><hr class="mt-0 mb-3"></div>

                                    <div class="col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label>Ciclo Escolar <span class="text-danger">*</span></label>
                                            <input type="number" wire:model="editYear"
                                                class="form-control @error('editYear') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                            @error('editYear') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="form-group">
                                            <label>Nivel <span class="text-danger">*</span></label>
                                            <select wire:model.live="editLevelId"
                                                class="form-control @error('editLevelId') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                                <option value="">— Seleccione —</option>
                                                @foreach ($this->allLevels as $level)
                                                    <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('editLevelId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div class="col-sm-12 col-md-4">
                                        <div class="form-group mb-0">
                                            <label>Grado <span class="text-danger">*</span></label>
                                            <select wire:model="editGradeId"
                                                class="form-control @error('editGradeId') is-invalid @enderror"
                                                @cannot('admin.admissions.edit') disabled @endcannot>
                                                <option value="">— Seleccione —</option>
                                                @foreach ($this->editGrades as $grade)
                                                    <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('editGradeId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- ── TAB 2: Padres & Encargado ────────────── --}}
                            <div id="tab-padres" role="tabpanel" x-show="activeTab === 'tab-padres'">

                                {{-- PADRE --}}
                                <fieldset class="edit-section mb-3">
                                    <legend>
                                        <i class="fas fa-male mr-1"></i> Padre
                                        @can('admin.admissions.edit')
                                            <div class="custom-control custom-switch d-inline-block ml-3">
                                                <input type="checkbox" class="custom-control-input" id="editFatherToggle"
                                                    wire:model.live="editFatherEnabled">
                                                <label class="custom-control-label font-weight-normal" for="editFatherToggle">
                                                    {{ $editFatherEnabled ? 'Incluido' : 'No aplica' }}
                                                </label>
                                            </div>
                                        @else
                                            <small class="text-muted font-weight-normal ml-2">
                                                {{ $editFatherEnabled ? 'Incluido' : 'No aplica' }}
                                            </small>
                                        @endcan
                                    </legend>
                                    @if ($editFatherEnabled)
                                        <div class="row">
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>Nombres <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="editFatherFirstName"
                                                        class="form-control @error('editFatherFirstName') is-invalid @enderror"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                    @error('editFatherFirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>Apellidos <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="editFatherLastName"
                                                        class="form-control @error('editFatherLastName') is-invalid @enderror"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                    @error('editFatherLastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>Teléfono <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="editFatherPhone"
                                                        class="form-control @error('editFatherPhone') is-invalid @enderror"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                    @error('editFatherPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>Lugar de Trabajo</label>
                                                    <input type="text" wire:model="editFatherWorkplace"
                                                        class="form-control"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>NIT</label>
                                                    <input type="text" wire:model="editFatherNit"
                                                        class="form-control"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>Profesión u Oficio</label>
                                                    <input type="text" wire:model="editFatherProfession"
                                                        class="form-control"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Datos del padre no registrados.
                                        </p>
                                    @endif
                                </fieldset>

                                {{-- MADRE --}}
                                <fieldset class="edit-section mb-3">
                                    <legend>
                                        <i class="fas fa-female mr-1"></i> Madre
                                        @can('admin.admissions.edit')
                                            <div class="custom-control custom-switch d-inline-block ml-3">
                                                <input type="checkbox" class="custom-control-input" id="editMotherToggle"
                                                    wire:model.live="editMotherEnabled">
                                                <label class="custom-control-label font-weight-normal" for="editMotherToggle">
                                                    {{ $editMotherEnabled ? 'Incluida' : 'No aplica' }}
                                                </label>
                                            </div>
                                        @else
                                            <small class="text-muted font-weight-normal ml-2">
                                                {{ $editMotherEnabled ? 'Incluida' : 'No aplica' }}
                                            </small>
                                        @endcan
                                    </legend>
                                    @if ($editMotherEnabled)
                                        <div class="row">
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>Nombres <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="editMotherFirstName"
                                                        class="form-control @error('editMotherFirstName') is-invalid @enderror"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                    @error('editMotherFirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>Apellidos <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="editMotherLastName"
                                                        class="form-control @error('editMotherLastName') is-invalid @enderror"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                    @error('editMotherLastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group">
                                                    <label>Teléfono <span class="text-danger">*</span></label>
                                                    <input type="text" wire:model="editMotherPhone"
                                                        class="form-control @error('editMotherPhone') is-invalid @enderror"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                    @error('editMotherPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>Lugar de Trabajo</label>
                                                    <input type="text" wire:model="editMotherWorkplace"
                                                        class="form-control"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>NIT</label>
                                                    <input type="text" wire:model="editMotherNit"
                                                        class="form-control"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                </div>
                                            </div>
                                            <div class="col-sm-12 col-md-4">
                                                <div class="form-group mb-0">
                                                    <label>Profesión u Oficio</label>
                                                    <input type="text" wire:model="editMotherProfession"
                                                        class="form-control"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-info-circle mr-1"></i>
                                            Datos de la madre no registrados.
                                        </p>
                                    @endif
                                </fieldset>

                                {{-- ENCARGADO --}}
                                <fieldset class="edit-section mb-0">
                                    <legend><i class="fas fa-user-shield mr-1"></i> Encargado</legend>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label>Encargado es <span class="text-danger">*</span></label>
                                                <select wire:model.live="editGuardianType"
                                                    class="form-control @error('editGuardianType') is-invalid @enderror"
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                                    <option value="">— Seleccione —</option>
                                                    @if ($editFatherEnabled)
                                                        <option value="father">Padre</option>
                                                    @endif
                                                    @if ($editMotherEnabled)
                                                        <option value="mother">Madre</option>
                                                    @endif
                                                    <option value="other">Otro encargado</option>
                                                </select>
                                                @error('editGuardianType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label>Nombre</label>
                                                <input type="text" wire:model="editGuardianName"
                                                    class="form-control @error('editGuardianName') is-invalid @enderror"
                                                    @if($editGuardianType !== 'other') readonly @endif
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                                @error('editGuardianName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label>Teléfono <span class="text-danger">*</span></label>
                                                <input type="text" wire:model="editGuardianPhone"
                                                    class="form-control @error('editGuardianPhone') is-invalid @enderror"
                                                    @if($editGuardianType !== 'other') readonly @endif
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                                @error('editGuardianPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                        @if ($editGuardianType === 'other')
                                            <div class="col-sm-12 col-md-3">
                                                <div class="form-group">
                                                    <label>NIT</label>
                                                    <input type="text" wire:model="editGuardianNit"
                                                        class="form-control"
                                                        @cannot('admin.admissions.edit') disabled @endcannot>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group mb-0">
                                                <label>Correo Electrónico <span class="text-danger">*</span></label>
                                                <input type="email" wire:model="editGuardianEmail"
                                                    class="form-control @error('editGuardianEmail') is-invalid @enderror"
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                                @error('editGuardianEmail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- FAMILIA --}}
                                <fieldset class="edit-section mt-3 mb-0">
                                    <legend><i class="fas fa-users mr-1"></i> Información Familiar</legend>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label>Hijos (varones)</label>
                                                <input type="number" wire:model="editSonsCount" min="0"
                                                    class="form-control"
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group">
                                                <label>Edades (separadas por coma)</label>
                                                <input type="text" wire:model="editSonsAges"
                                                    class="form-control" placeholder="Ej: 5, 3"
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group mb-0">
                                                <label>Hijas (mujeres)</label>
                                                <input type="number" wire:model="editDaughtersCount" min="0"
                                                    class="form-control"
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-3">
                                            <div class="form-group mb-0">
                                                <label>Edades (separadas por coma)</label>
                                                <input type="text" wire:model="editDaughtersAges"
                                                    class="form-control" placeholder="Ej: 8, 4"
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            {{-- ── TAB 3: Papelería & URLs ──────────────── --}}
                            <div id="tab-papeleria" role="tabpanel" x-show="activeTab === 'tab-papeleria'">

                                {{-- URLs --}}
                                <fieldset class="edit-section mb-3">
                                    <legend><i class="fas fa-link mr-1"></i> Enlace de Documentos en la Nube</legend>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group mb-0">
                                                <label>
                                                    <i class="fas fa-file-alt mr-1 text-secondary"></i>
                                                    URL — Papelería (carpeta en la nube)
                                                </label>
                                                <input type="text" wire:model="editUrlDocuments"
                                                    class="form-control @error('editUrlDocuments') is-invalid @enderror"
                                                    placeholder="https://onedrive.com/..."
                                                    @cannot('admin.admissions.manage') disabled @endcannot>
                                                @error('editUrlDocuments') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                @if ($editUrlDocuments)
                                                    <a href="{{ $editUrlDocuments }}" target="_blank" class="small">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Abrir enlace
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group mb-0">
                                                <label>
                                                    <i class="fas fa-receipt mr-1 text-warning"></i>
                                                    URL — Boleta de Pago
                                                </label>
                                                <input type="text" wire:model="editUrlPayment"
                                                    class="form-control @error('editUrlPayment') is-invalid @enderror"
                                                    placeholder="https://onedrive.com/..."
                                                    @cannot('admin.admissions.manage') disabled @endcannot>
                                                @error('editUrlPayment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                @if ($editUrlPayment)
                                                    <a href="{{ $editUrlPayment }}" target="_blank" class="small">
                                                        <i class="fas fa-external-link-alt mr-1"></i> Abrir enlace
                                                    </a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

                                {{-- Documentos --}}
                                <fieldset class="edit-section mb-3">
                                    <legend>
                                        <i class="fas fa-check-square mr-1"></i> Documentos Recibidos
                                        @if ($viewing->documents?->isComplete())
                                            <span class="badge badge-success ml-2">
                                                <i class="fas fa-check-circle mr-1"></i> Completa
                                            </span>
                                        @else
                                            <small class="text-muted font-weight-normal ml-2">
                                                {{ $viewing->documents?->receivedCount() ?? 0 }} de 5
                                            </small>
                                        @endif
                                    </legend>
                                    @can('admin.admissions.manage')
                                        @if ($viewing->current_status === 'pending')
                                            <div class="alert alert-warning py-2 mb-3">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Primero debe marcarse el correo como enviado al postulante para poder gestionar la papelería.
                                            </div>
                                        @endif
                                        <div class="row">
                                            @foreach (\App\Models\AdmissionApplicationDocument::fields() as $field => $label)
                                                <div class="col-sm-12 col-md-6 mb-2">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox"
                                                            class="custom-control-input"
                                                            id="doc_{{ $field }}"
                                                            wire:click="toggleDocument('{{ $field }}')"
                                                            @checked($viewing->documents?->$field)
                                                            @if ($viewing->current_status === 'pending') disabled @endif>
                                                        <label class="custom-control-label @if ($viewing->current_status === 'pending') text-muted @endif"
                                                            for="doc_{{ $field }}">
                                                            {{ $label }}
                                                        </label>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        @if ($viewing->documents?->completed_at)
                                            <small class="text-success d-block mt-2">
                                                <i class="fas fa-check-circle mr-1"></i>
                                                Completada el {{ $viewing->documents->completed_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    @else
                                        <p class="text-muted small mb-0">Sin permiso para gestionar papelería.</p>
                                    @endcan
                                </fieldset>

                                {{-- Cómo nos conoció --}}
                                <fieldset class="edit-section mb-0">
                                    <legend><i class="fas fa-star mr-1"></i> ¿Cómo supo de nosotros?</legend>
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group mb-0">
                                                <label>Medio por el que conoció el instituto</label>
                                                <select wire:model="editReferralSource"
                                                    class="form-control"
                                                    @cannot('admin.admissions.edit') disabled @endcannot>
                                                    <option value="">— No especificado —</option>
                                                    <option value="Instagram">Instagram</option>
                                                    <option value="Facebook">Facebook</option>
                                                    <option value="Página Web">Página Web</option>
                                                    <option value="Publicidad">Publicidad (volante, afiche, etc.)</option>
                                                    <option value="Cercanía">Cercanía (ubicación geográfica)</option>
                                                    <option value="Exalumno">Soy exalumno</option>
                                                    <option value="Referido por exalumno">Referido por un exalumno</option>
                                                    <option value="Referido por familia vecina">Referido por una familia vecina</option>
                                                    <option value="Referido por familiar">Referido por un familiar</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            {{-- ── TAB 4: Historial ─────────────────────── --}}
                            <div id="tab-historial" role="tabpanel" x-show="activeTab === 'tab-historial'">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Estado</th>
                                            <th>Notas</th>
                                            <th>Por</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($viewing->statuses->sortByDesc('created_at') as $st)
                                            <tr>
                                                <td>
                                                    <span class="badge badge-{{ \App\Models\AdmissionApplicationStatus::colorFor($st->status) }}">
                                                        {{ \App\Models\AdmissionApplicationStatus::labelFor($st->status) }}
                                                    </span>
                                                </td>
                                                <td>{{ $st->notes ?? '—' }}</td>
                                                <td>{{ $st->user?->first_name ?? 'Sistema' }}</td>
                                                <td><small>{{ $st->created_at->format('d/m/Y H:i') }}</small></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>{{-- /tab-content --}}
                    </div>{{-- /modal-body --}}

                    <div class="modal-footer justify-content-between">
                        {{-- Acciones de estado --}}
                        <div>
                            @can('admin.admissions.manage')
                                @if ($viewing->isPending())
                                    <button
                                        @click="Swal.fire({ title: '¿Marcar correo enviado al postulante?', icon: 'question', showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar' }).then(r => r.isConfirmed && $wire.markEmailed({{ $viewing->id }}))"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-envelope mr-1"></i> Correo enviado
                                    </button>
                                @endif
                                @if ($viewing->isReviewed())
                                    <button
                                        @click="Swal.fire({ title: '¿Aceptar esta solicitud de admisión?', icon: 'question', showCancelButton: true, confirmButtonText: 'Aceptar', cancelButtonText: 'Cancelar', confirmButtonColor: '#28a745' }).then(r => r.isConfirmed && $wire.markAccepted({{ $viewing->id }}))"
                                        class="btn btn-success btn-sm">
                                        <i class="fas fa-check mr-1"></i> Aceptar
                                    </button>
                                    <button wire:click="openReject({{ $viewing->id }})"
                                        class="btn btn-danger btn-sm">
                                        <i class="fas fa-times mr-1"></i> Rechazar
                                    </button>
                                @endif
                                @if (! $viewing->isPending())
                                    <button
                                        @click="Swal.fire({ title: '¿Regresar a Pendiente?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí', cancelButtonText: 'No', confirmButtonColor: '#ffc107' }).then(r => r.isConfirmed && $wire.resetToPending({{ $viewing->id }}))"
                                        class="btn btn-warning btn-sm">
                                        <i class="fas fa-undo mr-1"></i> Pendiente
                                    </button>
                                @endif
                            @endcan
                        </div>
                        {{-- Guardar cambios + Cerrar --}}
                        <div>
                            @can('admin.admissions.edit')
                                <button wire:click="updateApplication"
                                    wire:loading.attr="disabled" wire:target="updateApplication"
                                    class="btn btn-dark btn-sm">
                                    <span wire:loading.remove wire:target="updateApplication">
                                        <i class="fas fa-save mr-1"></i> Guardar cambios
                                    </span>
                                    <span wire:loading wire:target="updateApplication">
                                        <i class="fas fa-spinner fa-spin mr-1"></i> Guardando...
                                    </span>
                                </button>
                            @endcan
                            <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                                Cerrar
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         MODAL — Rechazo con notas
    ════════════════════════════════════════ --}}
    <div class="modal fade" id="rejectModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="fas fa-times-circle mr-2"></i> Rechazar Solicitud</h5>
                    <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label>Motivo del rechazo <small class="text-muted">(opcional)</small></label>
                        <textarea wire:model="rejectionNotes" class="form-control" rows="3"
                            placeholder="Indique el motivo para el registro interno..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="confirmReject" wire:loading.attr="disabled"
                        class="btn btn-danger">
                        <span wire:loading.remove wire:target="confirmReject">
                            <i class="fas fa-times mr-1"></i> Confirmar Rechazo
                        </span>
                        <span wire:loading wire:target="confirmReject">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Procesando...
                        </span>
                    </button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('openAdmissionDetailModal', () => {
        $('#rejectModal').modal('hide');
        $('#admissionDetailModal').modal('show');
    });
    $wire.on('openRejectModal', () => {
        $('#admissionDetailModal').modal('hide');
        $('#rejectModal').modal('show');
    });
    $wire.on('closeRejectModal', () => {
        $('#rejectModal').modal('hide');
    });
</script>
@endscript

@push('css')
<style>
    fieldset.edit-section {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 14px 16px 10px;
    }
    fieldset.edit-section legend {
        font-size: .9rem;
        font-weight: 600;
        color: #2c3e50;
        width: auto;
        padding: 0 8px;
    }
</style>
@endpush
