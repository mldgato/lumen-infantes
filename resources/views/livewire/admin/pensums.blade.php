<div wire:init="loadPensums">

    {{-- Modal Pénsum --}}
    <div wire:ignore.self class="modal fade" id="PensumModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $form->pensum ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title">
                        <i class="fas {{ $form->pensum ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                        {{ $form->pensum ? 'Editar Pénsum' : 'Nuevo Pénsum' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Grado <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                            </div>
                            <select wire:model="form.grade_id"
                                class="form-control @error('form.grade_id') is-invalid @enderror">
                                <option value="">-- Seleccione un grado --</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                                @endforeach
                            </select>
                            @error('form.grade_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="number" wire:model="form.year"
                                class="form-control @error('form.year') is-invalid @enderror" placeholder="Ej. 2026"
                                min="2000" max="2100">
                            @error('form.year')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Cantidad de Unidades <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                            </div>
                            <input type="number" wire:model="form.units"
                                class="form-control @error('form.units') is-invalid @enderror" placeholder="Ej. 4"
                                min="1" max="6">
                            @error('form.units')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    {{-- Porcentajes por Unidad --}}
                    @if ($form->units >= 1)
                        <div class="form-group mb-0">
                            <label class="text-sm mb-1">
                                Porcentaje por Unidad <span class="text-danger">*</span>
                                <small class="text-muted ml-1">(deben sumar exactamente 100%)</small>
                            </label>
                            @error('unit_percentages')
                                <div class="alert alert-danger py-1 px-2 text-sm mb-2">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <div class="row">
                                @for ($u = 1; $u <= (int) $form->units; $u++)
                                    <div
                                        class="col-md-{{ (int) $form->units <= 3 ? 4 : ((int) $form->units <= 4 ? 3 : 2) }} form-group mb-2">
                                        <label class="text-sm mb-1">Unidad {{ $u }}</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number"
                                                wire:model.live="form.unit_percentages.{{ $u - 1 }}"
                                                class="form-control @error('unit_percentages.' . ($u - 1)) is-invalid @enderror"
                                                min="0" max="100" step="1" placeholder="0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                            @error('unit_percentages.' . ($u - 1))
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endfor
                            </div>
                            @php $sum = array_sum(array_map('floatval', $form->unit_percentages)); @endphp
                            <div class="text-right">
                                <small class="{{ $sum == 100 ? 'text-success' : 'text-danger' }} font-weight-bold">
                                    <i
                                        class="fas {{ $sum == 100 ? 'fa-check-circle' : 'fa-exclamation-circle' }} mr-1"></i>
                                    Total: {{ $sum }}%
                                </small>
                            </div>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">
                        Cancelar
                    </button>
                    <button wire:click.prevent="save" type="button"
                        class="btn btn-sm {{ $form->pensum ? 'btn-warning' : 'btn-primary' }}"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save"></i>
                            {{ $form->pensum ? 'Actualizar' : 'Guardar' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-pulse"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Gestión de Cursos --}}
    <div wire:ignore.self class="modal fade" id="CoursesModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-list-alt"></i>
                        Cursos del Pénsum:
                        {{ $managingPensum?->grade->grade_name ?? '' }}
                        {{ $managingPensum ? '— ' . $managingPensum->year : '' }}
                        {{ $managingPensum ? '(' . $managingPensum->units . ' unidades)' : '' }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"
                        wire:click="resetCourseForm">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">

                    @if (!$showCourseForm)

                        {{-- Botón agregar curso --}}
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="m-0 text-bold text-secondary">Cursos Asignados</h6>
                            <button wire:click="openCourseForm" class="btn btn-sm btn-primary shadow-sm">
                                <i class="fas fa-plus"></i> Agregar Curso
                            </button>
                        </div>

                        {{-- Lista de cursos --}}
                        @if ($managingPensum && $managingPensum->mainCourses->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-striped mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Curso</th>
                                            <th>Tipo</th>
                                            <th>Oficial</th>
                                            <th>Orden</th>
                                            <th>Unidades</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($managingPensum->mainCourses as $pc)
                                            <tr class="{{ $pc->is_main ? 'table-info' : '' }}">
                                                <td>
                                                    <strong>{{ $pc->course->course_name }}</strong>
                                                </td>
                                                <td>
                                                    @if ($pc->is_main)
                                                        <span class="badge badge-info">Principal</span>
                                                    @elseif ($pc->units !== null && count($pc->units) < $managingPensum->units)
                                                        <span class="badge badge-warning">Parcial</span>
                                                    @else
                                                        <span class="badge badge-success">Completo</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($pc->is_official)
                                                        <span class="badge badge-info"><i
                                                                class="fas fa-check mr-1"></i>Sí</span>
                                                    @else
                                                        <span class="badge badge-secondary"><i
                                                                class="fas fa-times mr-1"></i>No</span>
                                                    @endif
                                                </td>
                                                <td><span class="badge badge-light border">{{ $pc->ordering }}</span>
                                                </td>
                                                <td>
                                                    @if ($pc->units)
                                                        @foreach ($pc->units as $u)
                                                            <span
                                                                class="badge badge-secondary">U{{ $u }}</span>
                                                        @endforeach
                                                    @else
                                                        <span class="text-muted text-sm">Ver sub cursos</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <button wire:click="editCourse({{ $pc->id }})"
                                                        class="btn btn-xs btn-warning px-2">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button
                                                        onclick="confirmDeleteCourse({{ $pc->id }}, '{{ addslashes($pc->course->course_name) }}')"
                                                        class="btn btn-xs btn-danger px-2 ml-1">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            {{-- Sub cursos --}}
                                            @foreach ($pc->subCourses as $sub)
                                                <tr class="bg-light">
                                                    <td class="pl-4">
                                                        <i
                                                            class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1"></i>
                                                        {{ $sub->course->course_name }}
                                                    </td>
                                                    <td><span class="badge badge-secondary">Sub curso</span></td>
                                                    <td>
                                                        @foreach ($sub->units as $u)
                                                            <span
                                                                class="badge badge-secondary">U{{ $u }}</span>
                                                        @endforeach
                                                    </td>
                                                    <td></td>
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-light border text-center text-muted">
                                <i class="fas fa-book-open fa-3x mb-2"></i><br>
                                Este pénsum aún no tiene cursos asignados.
                            </div>
                        @endif
                    @else
                        {{-- Formulario de curso --}}
                        <div class="card shadow-sm border-success mb-0">
                            <div class="card-header bg-white">
                                <h6 class="card-title text-bold text-success m-0">
                                    <i class="fas {{ $editingCourseId ? 'fa-edit' : 'fa-plus' }} mr-1"></i>
                                    {{ $editingCourseId ? 'Editar Curso' : 'Agregar Curso al Pénsum' }}
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">

                                    {{-- Curso --}}
                                    <div class="col-md-4 form-group mb-3">
                                        <label class="text-sm mb-1">Curso <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-book"></i></span>
                                            </div>
                                            <select wire:model.live="course_id"
                                                class="form-control @error('course_id') is-invalid @enderror">
                                                <option value="">-- Seleccione un curso --</option>
                                                @foreach ($courses as $course)
                                                    <option value="{{ $course->id }}">{{ $course->course_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('course_id')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Escenario --}}
                                    <div class="col-md-3 form-group mb-3">
                                        <label class="text-sm mb-1">Escenario <span
                                                class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-sitemap"></i></span>
                                            </div>
                                            <select wire:model.live="scenario"
                                                class="form-control @error('scenario') is-invalid @enderror">
                                                <option value="common">Completo (todas las unidades)</option>
                                                <option value="partial">Parcial (ciertas unidades)</option>
                                                <option value="main">Curso Principal (con sub cursos)</option>
                                            </select>
                                            @error('scenario')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-2 form-group mb-3">
                                        <label class="text-sm mb-1">Orden <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-sort-numeric-up"></i></span>
                                            </div>
                                            <input type="number" wire:model="courseOrdering"
                                                class="form-control @error('courseOrdering') is-invalid @enderror"
                                                placeholder="Ej. 1" min="0">
                                            @error('courseOrdering')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    {{-- Oficial --}}
                                    <div class="col-md-3 form-group mb-3">
                                        <div class="custom-control custom-switch">
                                            <input type="checkbox" wire:model="courseIsOfficial"
                                                class="custom-control-input" id="courseIsOfficial">
                                            <label class="custom-control-label" for="courseIsOfficial">
                                                Curso Oficial
                                                <small class="text-muted ml-1">(desmarca si no es un curso
                                                    del CNB)</small>
                                            </label>
                                        </div>
                                    </div>

                                    {{-- Escenario Parcial: seleccionar unidades --}}
                                    @if ($scenario === 'partial')
                                        <div class="col-12 form-group mb-3">
                                            <label class="text-sm mb-1">¿En qué unidades se imparte? <span
                                                    class="text-danger">*</span></label>
                                            <div class="d-flex flex-wrap gap-2">
                                                @for ($u = 1; $u <= $managingPensum->units; $u++)
                                                    <div class="custom-control custom-checkbox mr-3">
                                                        <input type="checkbox" wire:model="selectedUnits"
                                                            value="{{ $u }}" class="custom-control-input"
                                                            id="unit_{{ $u }}">
                                                        <label class="custom-control-label"
                                                            for="unit_{{ $u }}">
                                                            Unidad {{ $u }}
                                                        </label>
                                                    </div>
                                                @endfor
                                            </div>
                                            @error('selectedUnits')
                                                <span class="text-danger small">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endif

                                    {{-- Escenario Principal --}}
                                    @if ($scenario === 'main')

                                        {{-- Unidades del principal (opcional) --}}
                                        <div class="col-12 form-group mb-3">
                                            <label class="text-sm mb-1">
                                                ¿El curso principal también se imparte en unidades específicas?
                                                <small class="text-muted">(Opcional, dejar sin marcar si solo es
                                                    contenedor)</small>
                                            </label>
                                            <div class="d-flex flex-wrap">
                                                @for ($u = 1; $u <= $managingPensum->units; $u++)
                                                    <div class="custom-control custom-checkbox mr-3">
                                                        <input type="checkbox" wire:model="selectedUnits"
                                                            value="{{ $u }}" class="custom-control-input"
                                                            id="main_unit_{{ $u }}">
                                                        <label class="custom-control-label"
                                                            for="main_unit_{{ $u }}">
                                                            Unidad {{ $u }}
                                                        </label>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>

                                        {{-- Cantidad de sub cursos --}}
                                        <div class="col-md-4 form-group mb-3">
                                            <label class="text-sm mb-1">Cantidad de Sub Cursos <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-list-ol"></i></span>
                                                </div>
                                                <select wire:model.live="subCourseCount"
                                                    class="form-control @error('subCourseCount') is-invalid @enderror">
                                                    <option value="0">-- Seleccione --</option>
                                                    @for ($i = 1; $i <= $managingPensum->units; $i++)
                                                        <option value="{{ $i }}">{{ $i }} sub
                                                            curso(s)</option>
                                                    @endfor
                                                </select>
                                                @error('subCourseCount')
                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                @enderror
                                            </div>
                                        </div>

                                        {{-- Sub cursos dinámicos --}}
                                        @if ($subCourseCount > 0)
                                            <div class="col-12">
                                                <hr class="my-2">
                                                <label class="text-sm text-bold text-info mb-2">
                                                    <i class="fas fa-stream mr-1"></i> Configuración de Sub Cursos
                                                </label>
                                            </div>
                                            @foreach ($subCourses as $index => $sub)
                                                <div class="col-12 mb-3">
                                                    <div class="card border-info">
                                                        <div class="card-header p-2 bg-light">
                                                            <span class="text-sm text-bold text-info">Sub Curso
                                                                {{ $index + 1 }}</span>
                                                        </div>
                                                        <div class="card-body p-2">
                                                            <div class="row">
                                                                <div class="col-md-5 form-group mb-2">
                                                                    <label class="text-sm mb-1">Curso <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="input-group input-group-sm">
                                                                        <div class="input-group-prepend">
                                                                            <span class="input-group-text"><i
                                                                                    class="fas fa-book"></i></span>
                                                                        </div>
                                                                        <select
                                                                            wire:model="subCourses.{{ $index }}.course_id"
                                                                            class="form-control @error('subCourses.' . $index . '.course_id') is-invalid @enderror">
                                                                            <option value="">-- Seleccione --
                                                                            </option>
                                                                            @foreach ($courses as $course)
                                                                                <option value="{{ $course->id }}">
                                                                                    {{ $course->course_name }}</option>
                                                                            @endforeach
                                                                        </select>
                                                                        @error('subCourses.' . $index . '.course_id')
                                                                            <span
                                                                                class="invalid-feedback">{{ $message }}</span>
                                                                        @enderror
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-7 form-group mb-2">
                                                                    <label class="text-sm mb-1">¿En qué unidades se
                                                                        imparte? <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="d-flex flex-wrap">
                                                                        @for ($u = 1; $u <= $managingPensum->units; $u++)
                                                                            <div
                                                                                class="custom-control custom-checkbox mr-3">
                                                                                <input type="checkbox"
                                                                                    wire:model="subCourses.{{ $index }}.units"
                                                                                    value="{{ $u }}"
                                                                                    class="custom-control-input"
                                                                                    id="sub_{{ $index }}_unit_{{ $u }}">
                                                                                <label class="custom-control-label"
                                                                                    for="sub_{{ $index }}_unit_{{ $u }}">
                                                                                    U{{ $u }}
                                                                                </label>
                                                                            </div>
                                                                        @endfor
                                                                    </div>
                                                                    @error('subCourses.' . $index . '.units')
                                                                        <span
                                                                            class="text-danger small">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    @endif

                                </div>
                            </div>
                            <div class="card-footer text-right bg-white">
                                <button wire:click="resetCourseForm" class="btn btn-secondary btn-sm mr-2">
                                    <i class="fas fa-arrow-left"></i> Cancelar
                                </button>
                                <button wire:click.prevent="saveCourse" class="btn btn-success btn-sm"
                                    wire:loading.attr="disabled" wire:target="saveCourse">
                                    <span wire:loading.remove wire:target="saveCourse">
                                        <i class="fas fa-save"></i>
                                        {{ $editingCourseId ? 'Actualizar Curso' : 'Guardar Curso' }}
                                    </span>
                                    <span wire:loading wire:target="saveCourse">
                                        <i class="fas fa-spinner fa-pulse"></i> Guardando...
                                    </span>
                                </button>
                            </div>
                        </div>

                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark btn-sm" data-dismiss="modal"
                        wire:click="resetCourseForm">
                        Cerrar Panel
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Copiar Pénsum --}}
    <div wire:ignore.self class="modal fade" id="CopyPensumModal" tabindex="-1" role="dialog"
        data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-copy"></i> Copiar Pénsum
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border text-sm mb-3">
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        Se copiará la estructura completa del pénsum (cursos, sub cursos y unidades) al grado y año que
                        seleccione.
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Grado Destino <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                            </div>
                            <select wire:model="copy_grade_id"
                                class="form-control @error('copy_grade_id') is-invalid @enderror">
                                <option value="">-- Seleccione un grado --</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                                @endforeach
                            </select>
                            @error('copy_grade_id')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Año Destino <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="number" wire:model="copy_year"
                                class="form-control @error('copy_year') is-invalid @enderror" placeholder="Ej. 2027"
                                min="2000" max="2100">
                            @error('copy_year')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button wire:click.prevent="copyPensum" type="button" class="btn btn-info btn-sm"
                        wire:loading.attr="disabled" wire:target="copyPensum">
                        <span wire:loading.remove wire:target="copyPensum">
                            <i class="fas fa-copy"></i> Copiar Pénsum
                        </span>
                        <span wire:loading wire:target="copyPensum">
                            <i class="fas fa-spinner fa-pulse"></i> Copiando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Card principal --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#PensumModal"
                        wire:click="resetFields">
                        <i class="fas fa-plus-circle"></i> Nuevo Pénsum
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
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Buscar grado o año...">
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
            @if ($readyToLoad && count($pensums))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('id')">
                                # <i
                                    class="fas fa-sort{{ $sort === 'id' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Grado</th>
                            <th style="cursor:pointer" wire:click="order('year')">
                                Año <i
                                    class="fas fa-sort{{ $sort === 'year' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Unidades</th>
                            <th>Cursos</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pensums as $pensum)
                            <tr>
                                <td>{{ $pensum->id }}</td>
                                <td>{{ $pensum->grade->grade_name }}</td>
                                <td>{{ $pensum->year }}</td>
                                <td><span class="badge badge-info">{{ $pensum->units }}</span></td>
                                <td><span class="badge badge-secondary">{{ $pensum->mainCourses->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="manageCourses({{ $pensum->id }})" data-toggle="modal"
                                        data-target="#CoursesModal" class="btn btn-sm btn-success shadow-sm"
                                        title="Gestionar Cursos">
                                        <i class="fas fa-list-alt"></i>
                                    </button>
                                    <button wire:click="edit({{ $pensum->id }})" data-toggle="modal"
                                        data-target="#PensumModal" class="btn btn-sm btn-warning shadow-sm"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if ($pensum->has_gradebooks)
                                        <button class="btn btn-sm btn-secondary shadow-sm"
                                            style="cursor: not-allowed; opacity: 0.6;"
                                            title="No se puede eliminar porque ya tiene GradeBooks creados">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @else
                                        <button
                                            onclick="confirmDelete({{ $pensum->id }}, '¿Eliminar el pénsum de {{ addslashes($pensum->grade->grade_name) }} {{ $pensum->year }}?')"
                                            class="btn btn-sm btn-danger shadow-sm" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    @endif
                                    <button wire:click="openCopyModal({{ $pensum->id }})" data-toggle="modal"
                                        data-target="#CopyPensumModal" class="btn btn-sm btn-info shadow-sm"
                                        title="Copiar Pénsum">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando pénsum...
                    @else
                        <i class="fas fa-list-alt fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($pensums) && $pensums->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $pensums->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            function confirmDelete(id, mensaje) {
                Swal.fire({
                    title: mensaje || '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.delete(id);
                    }
                });
            }

            function confirmDeleteCourse(id, nombre) {
                Swal.fire({
                    title: '¿Eliminar ' + nombre + '?',
                    text: 'Se eliminarán también sus sub cursos si los tiene.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.deleteCourse(id);
                    }
                });
            }

            document.addEventListener('livewire:init', () => {
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
                        timer: 3000
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
            });
        </script>
    @endpush
</div>
