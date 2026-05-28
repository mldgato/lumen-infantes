<div>
    {{-- ============================================================
         CONTROLES
         ============================================================ --}}
    <div class="card card-primary card-outline">
        <div class="card-header d-flex align-items-center flex-wrap" style="gap:.5rem">
            <h5 class="m-0 text-bold flex-grow-1">
                <i class="fas fa-chalkboard-teacher mr-1"></i> Profesores
            </h5>

            {{-- Buscador --}}
            <div class="input-group input-group-sm" style="width:220px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control" placeholder="Buscar..." autocomplete="new-password">
            </div>

            {{-- Filtro año --}}
            <div class="input-group input-group-sm" style="width:130px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                </div>
                <select wire:model.live="filterYear" class="form-control">
                    <option value="">-- Año --</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
            </div>

            @if (! $readyToLoad)
                <button wire:click="loadProfessors" class="btn btn-primary btn-sm">
                    <i class="fas fa-sync mr-1"></i> Cargar
                </button>
            @endif
        </div>

        <div class="card-body p-0" wire:init="loadProfessors">
            @if ($readyToLoad)
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:30px">#</th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('surname')" class="text-white">
                                    Apellidos
                                    @if ($sortField === 'surname')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>
                                <a href="#" wire:click.prevent="sortBy('first_name')" class="text-white">
                                    Nombres
                                    @if ($sortField === 'first_name')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} ml-1"></i>
                                    @endif
                                </a>
                            </th>
                            <th>Correo</th>
                            <th class="text-center">Fecha ingreso</th>
                            <th class="text-center">Título</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($professors as $prof)
                            <tr>
                                <td class="text-muted">{{ $professors->firstItem() + $loop->index }}</td>
                                <td>{{ $prof->user->surname }} {{ $prof->user->second_surname }}</td>
                                <td>{{ $prof->user->first_name }} {{ $prof->user->middle_name }}</td>
                                <td>{{ $prof->user->email }}</td>
                                <td class="text-center">
                                    {{ $prof->hire_date ? $prof->hire_date->format('d/m/Y') : '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $prof->title ?: '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="openDetail({{ $prof->id }})"
                                        class="btn btn-sm btn-outline-info"
                                        title="Ver cursos asignados">
                                        <i class="fas fa-book-open"></i>
                                    </button>
                                    @can('admin.professors.edit')
                                        <button wire:click="openEdit({{ $prof->id }})"
                                            class="btn btn-sm btn-outline-primary ml-1"
                                            title="Editar datos laborales">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-chalkboard-teacher fa-2x mb-2"></i><br>
                                    No se encontraron profesores.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-3 py-2">{{ $professors->links() }}</div>
            @else
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-spinner fa-pulse mr-1"></i> Cargando...
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================
         MODAL: CURSOS ASIGNADOS (DETALLE)
         ============================================================ --}}
    @if ($detailProfessorId && $detailProfessor)
        <div class="modal fade show" style="display:block; background:rgba(0,0,0,.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-book-open mr-2"></i>
                            Cursos — {{ $detailProfessor->user->full_full_name }}
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeDetail">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        @if ($detailAssignments->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-book fa-2x mb-2"></i><br>
                                No hay cursos asignados
                                @if ($filterYear) para el año {{ $filterYear }} @endif
                                en los niveles autorizados.
                            </div>
                        @else
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">Año</th>
                                        <th>Nivel</th>
                                        <th>Grado / Sección</th>
                                        <th>Curso</th>
                                        <th class="text-center">Unidad</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detailAssignments as $asgn)
                                        <tr>
                                            <td class="text-center">{{ $asgn->classroom->year }}</td>
                                            <td>{{ $asgn->classroom->level->level_name }}</td>
                                            <td>
                                                {{ $asgn->classroom->grade->grade_name }}
                                                {{ $asgn->classroom->section->section_name }}
                                            </td>
                                            <td>{{ $asgn->pensumCourse->course->course_name }}</td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">U{{ $asgn->unit }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeDetail">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         MODAL: EDITAR DATOS LABORALES
         ============================================================ --}}
    @if ($editingProfessorId)
        <div class="modal fade show" style="display:block; background:rgba(0,0,0,.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-2"></i> Editar Datos Laborales
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeEdit">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Fecha de ingreso <span class="text-danger">*</span></label>
                                <input type="date" wire:model="professorForm.hire_date"
                                    class="form-control form-control-sm @error('professorForm.hire_date') is-invalid @enderror">
                                @error('professorForm.hire_date')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">NIT</label>
                                <input type="text" wire:model="professorForm.nit"
                                    class="form-control form-control-sm @error('professorForm.nit') is-invalid @enderror"
                                    placeholder="NIT">
                                @error('professorForm.nit')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Cédula docente</label>
                                <input type="text" wire:model="professorForm.teaching_cedula"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Afiliación IGSS</label>
                                <input type="text" wire:model="professorForm.igss_affiliation"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Título</label>
                                <input type="text" wire:model="professorForm.title"
                                    class="form-control form-control-sm" placeholder="Ej. Licenciado">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Grado académico</label>
                                <input type="text" wire:model="professorForm.bachelor_degree"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Nombre del cónyuge</label>
                                <input type="text" wire:model="professorForm.spouse_name"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Teléfono del cónyuge</label>
                                <input type="text" wire:model="professorForm.spouse_phone"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeEdit">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" wire:click="save"
                            wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-1"></i> Guardar
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-pulse"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
