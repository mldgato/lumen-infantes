<div wire:init="loadData">

    {{-- Señuelo: evita que Chrome autorrellene el buscador con credenciales guardadas --}}
    <div style="position:fixed;top:-200px;left:-200px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <input type="text" autocomplete="username" tabindex="-1">
        <input type="password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- MODAL PROFESOR --}}
    <div wire:ignore.self class="modal fade" id="ProfessorModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-chalkboard-teacher mr-1"></i>
                        {{ $form->professor?->user?->name ?? 'Profesor' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body p-0">
                    <ul class="nav nav-tabs px-3 pt-2 bg-light" id="professorTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-datos-link" data-toggle="tab"
                                href="#tab-datos" role="tab">
                                <i class="fas fa-id-card mr-1"></i> Datos Docentes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-asignaciones-link" data-toggle="tab"
                                href="#tab-asignaciones" role="tab">
                                <i class="fas fa-book-open mr-1"></i> Asignaciones
                                <span class="badge badge-secondary ml-1">{{ $assignments->sum(fn($g) => $g->count()) }}</span>
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">

                        {{-- TAB: DATOS DOCENTES --}}
                        <div class="tab-pane fade show active" id="tab-datos" role="tabpanel">
                            <div class="row">
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Fecha de Contratación <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date" wire:model="form.hire_date"
                                            class="form-control @error('form.hire_date') is-invalid @enderror">
                                        @error('form.hire_date')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">NIT</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-receipt"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.nit"
                                            class="form-control @error('form.nit') is-invalid @enderror"
                                            placeholder="NIT del profesor">
                                        @error('form.nit')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Cédula de Enseñanza</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-id-badge"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.teaching_cedula"
                                            class="form-control @error('form.teaching_cedula') is-invalid @enderror"
                                            placeholder="Número de cédula">
                                        @error('form.teaching_cedula')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Afiliación IGSS</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-hospital"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.igss_affiliation"
                                            class="form-control @error('form.igss_affiliation') is-invalid @enderror"
                                            placeholder="No. afiliación">
                                        @error('form.igss_affiliation')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Título</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.title"
                                            class="form-control @error('form.title') is-invalid @enderror"
                                            placeholder="Título académico">
                                        @error('form.title')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-4 form-group mb-3">
                                    <label class="text-sm mb-1">Grado de Bachiller</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-user-graduate"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.bachelor_degree"
                                            class="form-control @error('form.bachelor_degree') is-invalid @enderror"
                                            placeholder="Bachillerato">
                                        @error('form.bachelor_degree')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Nombre del Cónyuge</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-heart"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.spouse_name"
                                            class="form-control @error('form.spouse_name') is-invalid @enderror"
                                            placeholder="Nombre completo">
                                        @error('form.spouse_name')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 form-group mb-3">
                                    <label class="text-sm mb-1">Teléfono del Cónyuge</label>
                                    <div class="input-group input-group-sm">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                        </div>
                                        <input type="text" wire:model="form.spouse_phone"
                                            class="form-control @error('form.spouse_phone') is-invalid @enderror"
                                            placeholder="Teléfono">
                                        @error('form.spouse_phone')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TAB: ASIGNACIONES --}}
                        <div class="tab-pane fade" id="tab-asignaciones" role="tabpanel">
                            @if ($assignments->isEmpty())
                                <div class="text-center text-muted py-4">
                                    <i class="fas fa-book fa-2x mb-2 text-gray"></i><br>
                                    No hay asignaciones registradas para este profesor.
                                </div>
                            @else
                                @foreach ($assignments as $year => $group)
                                    <div class="card card-outline card-primary mb-3">
                                        <div class="card-header py-2">
                                            <h6 class="m-0 text-bold">
                                                <i class="fas fa-calendar-alt mr-1 text-primary"></i>
                                                Ciclo {{ $year }}
                                                <span class="badge badge-primary ml-1">{{ $group->count() }} asignaciones</span>
                                            </h6>
                                        </div>
                                        <div class="card-body p-0">
                                            <table class="table table-sm table-striped mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th>Nivel</th>
                                                        <th>Grado / Sección</th>
                                                        <th>Curso</th>
                                                        <th class="text-center">Unidad</th>
                                                        <th class="text-center">Estado Cuadro</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($group as $assignment)
                                                        <tr>
                                                            <td>
                                                                <small>{{ $assignment->classroom->level->level_name }}</small>
                                                            </td>
                                                            <td>
                                                                <small>
                                                                    {{ $assignment->classroom->grade->grade_name }}
                                                                    {{ $assignment->classroom->section->section_name }}
                                                                </small>
                                                            </td>
                                                            <td>
                                                                <small>{{ $assignment->pensumCourse->course->course_name }}</small>
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-light border">{{ $assignment->unit }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if ($assignment->gradeBook)
                                                                    @php
                                                                        $statusMap = [
                                                                            'open'     => ['badge-success', 'Abierto'],
                                                                            'locked'   => ['badge-warning', 'Bloqueado'],
                                                                            'approved' => ['badge-primary', 'Aprobado'],
                                                                            'rejected' => ['badge-danger', 'Rechazado'],
                                                                        ];
                                                                        [$badge, $label] = $statusMap[$assignment->gradeBook->status] ?? ['badge-secondary', $assignment->gradeBook->status];
                                                                    @endphp
                                                                    <span class="badge {{ $badge }}">{{ $label }}</span>
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">
                        Cerrar
                    </button>
                    @can('admin.professors.edit')
                        <button wire:click.prevent="save" type="button" class="btn btn-warning btn-sm"
                            wire:loading.attr="disabled" wire:target="save"
                            id="btnSaveProfessor">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save"></i> Guardar Datos Docentes
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
    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <h5 class="m-0 text-bold"><i class="fas fa-chalkboard-teacher mr-1"></i> Profesores</h5>
                </div>
                <div class="col-md-8 d-flex justify-content-end align-items-center">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                        <option value="10">10</option>
                        <option value="15">15</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 280px;">
                        <input type="search" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Buscar por nombre o correo..." autocomplete="new-password">
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
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                    <p class="mt-2 text-muted">Cargando profesores...</p>
                </div>
            @elseif ($readyToLoad && count($professors))
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:50px">#</th>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Título</th>
                            <th>Contratación</th>
                            <th class="text-center" style="width:100px">Estado</th>
                            <th class="text-center" style="width:100px">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($professors as $professor)
                            <tr>
                                <td>{{ $professor->id }}</td>
                                <td>
                                    <strong>{{ $professor->user->name }}</strong>
                                    @if ($professor->title)
                                        <br><small class="text-muted">{{ $professor->title }}</small>
                                    @endif
                                </td>
                                <td><small>{{ $professor->user->email }}</small></td>
                                <td><small>{{ $professor->bachelor_degree ?? '—' }}</small></td>
                                <td>
                                    <small>
                                        {{ $professor->hire_date ? $professor->hire_date->format('d/m/Y') : '—' }}
                                    </small>
                                </td>
                                <td class="text-center">
                                    @if ($professor->user->is_active)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <button wire:click="openModal({{ $professor->id }})"
                                        data-toggle="modal" data-target="#ProfessorModal"
                                        class="btn btn-sm btn-warning shadow-sm"
                                        title="Ver / Editar datos docentes">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-5 text-center text-muted">
                    <i class="fas fa-chalkboard-teacher fa-3x mb-3 text-gray"></i><br>
                    No se encontraron profesores.
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($professors) && $professors->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $professors->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('openProfessorModal', () => {
                    // Siempre abrir en la primera pestaña al abrir el modal
                    $('#tab-datos-link').tab('show');
                    $('#ProfessorModal').modal('show');
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

                // Ocultar botón Guardar cuando la pestaña de asignaciones está activa
                document.querySelectorAll('#professorTabs .nav-link').forEach(tab => {
                    tab.addEventListener('shown.bs.tab', function (e) {
                        const isAsignaciones = e.target.id === 'tab-asignaciones-link';
                        const btn = document.getElementById('btnSaveProfessor');
                        if (btn) btn.style.display = isAsignaciones ? 'none' : '';
                    });
                });
            });
        </script>
    @endpush
</div>
