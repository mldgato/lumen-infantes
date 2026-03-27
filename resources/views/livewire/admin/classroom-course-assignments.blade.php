<div wire:init="loadClassrooms">

    {{-- Modal Gestión de Asignaciones --}}
    <div wire:ignore.self class="modal fade" id="AssignmentsModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-chalkboard-teacher"></i>
                        Asignación de Profesores —
                        {{ $managingClassroom?->level->level_name ?? '' }}
                        {{ $managingClassroom?->grade->grade_name ?? '' }}
                        {{ $managingClassroom?->section->section_name ?? '' }}
                        {{ $managingClassroom ? '(' . $managingClassroom->year . ')' : '' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" wire:click="resetManaging">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">

                    @if ($managingClassroom)
                        @if (!$pensum)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                No existe un pénsum para <strong>{{ $managingClassroom->grade->grade_name }}</strong>
                                del año <strong>{{ $managingClassroom->year }}</strong>.
                                Debe crear el pénsum antes de asignar profesores.
                            </div>
                        @else
                            {{-- Asignación en bloque a otros classrooms --}}
                            @if (count($sameGradeClassrooms) > 0)
                                <div class="card card-outline card-info mb-3">
                                    <div class="card-header p-2">
                                        <h6 class="card-title m-0 text-info">
                                            <i class="fas fa-copy mr-1"></i>
                                            Aplicar también a otros aulas del mismo grado y año
                                        </h6>
                                    </div>
                                    <div class="card-body p-2">
                                        <div class="d-flex flex-wrap">
                                            @foreach ($sameGradeClassrooms as $sc)
                                                <div class="custom-control custom-checkbox mr-4">
                                                    <input type="checkbox" wire:model="selectedClassrooms"
                                                        value="{{ $sc['id'] }}" class="custom-control-input"
                                                        id="sc_{{ $sc['id'] }}">
                                                    <label class="custom-control-label" for="sc_{{ $sc['id'] }}">
                                                        {{ $sc['section']['section_name'] }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif

                            {{-- Tabla de cursos y asignaciones por unidad --}}
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0 bg-white">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width:220px">Curso</th>
                                            <th>Tipo</th>
                                            @for ($u = 1; $u <= $pensum->units; $u++)
                                                <th class="text-center" style="min-width:160px">
                                                    <span class="badge badge-secondary">Unidad
                                                        {{ $u }}</span>
                                                </th>
                                            @endfor
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($pensum->mainCourses as $pc)
                                            {{-- Fila del curso principal o independiente --}}
                                            <tr class="{{ $pc->is_main ? 'table-info' : '' }}">
                                                <td>
                                                    <strong>{{ $pc->course->course_name }}</strong>
                                                </td>
                                                <td>
                                                    @if ($pc->is_main)
                                                        <span class="badge badge-info">Principal</span>
                                                    @elseif ($pc->units !== null && count($pc->units) < $pensum->units)
                                                        <span class="badge badge-warning">Parcial</span>
                                                    @else
                                                        <span class="badge badge-success">Completo</span>
                                                    @endif
                                                </td>
                                                @for ($u = 1; $u <= $pensum->units; $u++)
                                                    <td class="p-1">
                                                        @if (!$pc->is_main && ($pc->units === null || in_array($u, $pc->units)))
                                                            @if (isset($lockedAssignments[$pc->id][$u]))
                                                                <select disabled
                                                                    class="form-control form-control-sm bg-light"
                                                                    title="No modificable: Cuadro de notas ya creado">
                                                                    @foreach ($professors as $professor)
                                                                        <option value="{{ $professor->id }}"
                                                                            {{ ($assignments[$pc->id][$u] ?? '') == $professor->id ? 'selected' : '' }}>
                                                                            {{ $professor->user->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @else
                                                                <select
                                                                    wire:model="assignments.{{ $pc->id }}.{{ $u }}"
                                                                    class="form-control form-control-sm">
                                                                    <option value="">-- Sin asignar --</option>
                                                                    @foreach ($professors as $professor)
                                                                        <option value="{{ $professor->id }}">
                                                                            {{ $professor->user->name }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            @endif
                                                        @elseif ($pc->is_main)
                                                            <span class="text-muted text-sm d-block text-center">Ver sub
                                                                cursos</span>
                                                        @else
                                                            <span
                                                                class="text-muted text-sm d-block text-center">—</span>
                                                        @endif
                                                    </td>
                                                @endfor
                                            </tr>

                                            {{-- Filas de sub cursos --}}
                                            @foreach ($pc->subCourses as $sub)
                                                <tr class="bg-light">
                                                    <td class="pl-4">
                                                        <i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1"></i>
                                                        {{ $sub->course->course_name }}
                                                    </td>
                                                    <td>
                                                        <span class="badge badge-secondary">Sub curso</span>
                                                    </td>
                                                    @for ($u = 1; $u <= $pensum->units; $u++)
                                                        <td class="p-1">
                                                            @if (in_array($u, $sub->units ?? []))
                                                                @if (isset($lockedAssignments[$sub->id][$u]))
                                                                    <select disabled
                                                                        class="form-control form-control-sm bg-light"
                                                                        title="No modificable: Cuadro de notas ya creado">
                                                                        @foreach ($professors as $professor)
                                                                            <option value="{{ $professor->id }}"
                                                                                {{ ($assignments[$sub->id][$u] ?? '') == $professor->id ? 'selected' : '' }}>
                                                                                {{ $professor->user->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @else
                                                                    <select
                                                                        wire:model="assignments.{{ $sub->id }}.{{ $u }}"
                                                                        class="form-control form-control-sm">
                                                                        <option value="">-- Sin asignar --
                                                                        </option>
                                                                        @foreach ($professors as $professor)
                                                                            <option value="{{ $professor->id }}">
                                                                                {{ $professor->user->name }}
                                                                            </option>
                                                                        @endforeach
                                                                    </select>
                                                                @endif
                                                            @else
                                                                <span
                                                                    class="text-muted text-sm d-block text-center">—</span>
                                                            @endif
                                                        </td>
                                                    @endfor
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        @endif
                    @endif

                </div>
                <div class="modal-footer bg-white">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetManaging">
                        Cerrar
                    </button>
                    @if ($pensum)
                        <button wire:click.prevent="saveAssignments" class="btn btn-primary btn-sm"
                            wire:loading.attr="disabled" wire:target="saveAssignments">
                            <span wire:loading.remove wire:target="saveAssignments">
                                <i class="fas fa-save"></i> Guardar Asignaciones
                            </span>
                            <span wire:loading wire:target="saveAssignments">
                                <i class="fas fa-spinner fa-pulse"></i> Guardando...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Card principal --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <select wire:model.live="filterYear" class="form-control">
                            <option value="">-- Todos los años --</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-9 d-flex justify-content-end align-items-center">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Buscar aula...">
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
            @if ($readyToLoad && count($classrooms))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('id')">
                                # <i
                                    class="fas fa-sort{{ $sort === 'id' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Nivel</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th style="cursor:pointer" wire:click="order('year')">
                                Año <i
                                    class="fas fa-sort{{ $sort === 'year' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Pénsum</th>
                            <th class="text-center">Asignaciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classrooms as $classroom)
                            <tr>
                                <td>{{ $classroom->id }}</td>
                                <td>{{ $classroom->level->level_name }}</td>
                                <td>{{ $classroom->grade->grade_name }}</td>
                                <td>{{ $classroom->section->section_name }}</td>
                                <td>{{ $classroom->year }}</td>
                                <td>
                                    @if ($classroom->has_pensum)
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Sí</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-times mr-1"></i>Sin
                                            pénsum</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($classroom->course_assignments_count > 0)
                                        <span class="badge badge-info mr-2">{{ $classroom->course_assignments_count }}
                                            asignadas</span>
                                    @endif
                                    <button wire:click="manageAssignments({{ $classroom->id }})" data-toggle="modal"
                                        data-target="#AssignmentsModal" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-chalkboard-teacher"></i> Gestionar
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando aulas...
                    @else
                        <i class="fas fa-school fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($classrooms) && $classrooms->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $classrooms->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
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

                Livewire.on('toastMessage', (event) => {
                    let payload = event[0] || event;
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                    Toast.fire({
                        icon: payload.type,
                        title: payload.message
                    });
                });

                Livewire.on('closeModal', (event) => {
                    let payload = event[0] || event;
                    $('#' + payload.modalId).modal('hide');
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                });
            });
        </script>
    @endpush
</div>
