<div wire:init="loadData">

    {{-- Señuelo anti-autocompletado --}}
    <div style="position:fixed;top:-200px;left:-200px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
        <input type="text" autocomplete="username" tabindex="-1">
        <input type="password" autocomplete="current-password" tabindex="-1">
    </div>

    {{-- =====================================================
         MODAL: Actualización de Notas
         ===================================================== --}}
    <div wire:ignore.self class="modal fade" id="ScoreUpdateModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <div class="modal-header bg-warning">
                    <h5 class="modal-title">
                        <i class="fas fa-edit mr-1"></i> Actualización de Notas
                        @if ($modalStep === 2)
                            <small class="text-dark font-weight-normal ml-2">— Paso 2: Mapeo de actividades</small>
                        @endif
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                {{-- ── PASO 1: selección de cursos y unidades ── --}}
                @if ($modalStep === 1)

                    <div class="modal-body">

                        @if ($classroom)
                            <div class="alert alert-light border mb-3 py-2">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>{{ $classroom->grade->grade_name }}</strong>
                                    {{ $classroom->section->section_name }} —
                                    {{ $classroom->year }} —
                                    Alumnos seleccionados:
                                    <span class="badge badge-primary">{{ count($selected) }}</span>
                                </small>
                            </div>
                        @endif

                        <div class="row">

                            {{-- ORIGEN --}}
                            <div class="col-md-6">
                                <div class="card card-outline card-success mb-0">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0 text-success">
                                            <i class="fas fa-sign-out-alt mr-1"></i> Origen
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">

                                        <div class="form-group mb-3">
                                            <label class="text-sm mb-1">
                                                Curso Origen <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model.live="originCourseId"
                                                class="form-control form-control-sm @error('originCourseId') is-invalid @enderror">
                                                <option value="">-- Seleccione un curso --</option>
                                                @foreach ($modalCourses['standalone'] as $course)
                                                    <option value="{{ $course['id'] }}">
                                                        [{{ $course['badge'] }}] {{ $course['label'] }}
                                                    </option>
                                                @endforeach
                                                @foreach ($modalCourses['groups'] as $group)
                                                    <optgroup label="{{ $group['label'] }}">
                                                        @foreach ($group['courses'] as $course)
                                                            <option value="{{ $course['id'] }}">
                                                                @if ($course['badge'] === 'Sub')
                                                                    ↳ {{ $course['label'] }}
                                                                @else
                                                                    {{ $course['label'] }} [{{ $course['badge'] }}]
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            @error('originCourseId')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="text-sm mb-1">
                                                Unidad Origen <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model.live="originUnit"
                                                class="form-control form-control-sm @error('originUnit') is-invalid @enderror"
                                                {{ ! $originCourseId ? 'disabled' : '' }}>
                                                <option value="">-- Seleccione una unidad --</option>
                                                @foreach ($originUnits as $unit)
                                                    <option value="{{ $unit }}">Unidad {{ $unit }}</option>
                                                @endforeach
                                            </select>
                                            @error('originUnit')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            @if (! $originCourseId)
                                                <small class="text-muted">Seleccione primero el curso origen.</small>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- DESTINO --}}
                            <div class="col-md-6">
                                <div class="card card-outline card-info mb-0">
                                    <div class="card-header py-2">
                                        <h6 class="mb-0 text-info">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Destino
                                        </h6>
                                    </div>
                                    <div class="card-body p-3">

                                        <div class="form-group mb-3">
                                            <label class="text-sm mb-1">
                                                Curso Destino <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model.live="destinationCourseId"
                                                class="form-control form-control-sm @error('destinationCourseId') is-invalid @enderror">
                                                <option value="">-- Seleccione un curso --</option>
                                                @foreach ($modalCourses['standalone'] as $course)
                                                    <option value="{{ $course['id'] }}">
                                                        [{{ $course['badge'] }}] {{ $course['label'] }}
                                                    </option>
                                                @endforeach
                                                @foreach ($modalCourses['groups'] as $group)
                                                    <optgroup label="{{ $group['label'] }}">
                                                        @foreach ($group['courses'] as $course)
                                                            <option value="{{ $course['id'] }}">
                                                                @if ($course['badge'] === 'Sub')
                                                                    ↳ {{ $course['label'] }}
                                                                @else
                                                                    {{ $course['label'] }} [{{ $course['badge'] }}]
                                                                @endif
                                                            </option>
                                                        @endforeach
                                                    </optgroup>
                                                @endforeach
                                            </select>
                                            @error('destinationCourseId')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="form-group mb-0">
                                            <label class="text-sm mb-1">
                                                Unidad Destino <span class="text-danger">*</span>
                                            </label>
                                            <select wire:model.live="destinationUnit"
                                                class="form-control form-control-sm @error('destinationUnit') is-invalid @enderror"
                                                {{ ! $destinationCourseId ? 'disabled' : '' }}>
                                                <option value="">-- Seleccione una unidad --</option>
                                                @foreach ($destinationUnits as $unit)
                                                    <option value="{{ $unit }}">Unidad {{ $unit }}</option>
                                                @endforeach
                                            </select>
                                            @error('destinationUnit')
                                                <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                            @if (! $destinationCourseId)
                                                <small class="text-muted">Seleccione primero el curso destino.</small>
                                            @endif
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                            Cancelar
                        </button>
                        <button wire:click="continuar" type="button" class="btn btn-warning btn-sm"
                            {{ (! $originCourseId || ! $originUnit || ! $destinationCourseId || ! $destinationUnit) ? 'disabled' : '' }}
                            wire:loading.attr="disabled" wire:target="continuar">
                            <span wire:loading.remove wire:target="continuar">
                                <i class="fas fa-arrow-right mr-1"></i> Continuar
                            </span>
                            <span wire:loading wire:target="continuar">
                                <i class="fas fa-spinner fa-pulse mr-1"></i> Verificando...
                            </span>
                        </button>
                    </div>

                {{-- ── PASO 2: mapeo de actividades ── --}}
                @elseif ($modalStep === 2)

                    <div class="modal-body">

                        <div class="alert alert-info py-2 mb-3">
                            <small>
                                <i class="fas fa-info-circle mr-1"></i>
                                Para cada <strong class="text-info">actividad del destino</strong> seleccione
                                cuál <strong class="text-success">actividad del origen</strong> desea copiar.
                                Las actividades sin asignación <strong>no serán modificadas</strong> para los alumnos seleccionados.
                            </small>
                        </div>

                        @if (! empty($destinationActivities))
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-info" style="width: 40%">
                                                <i class="fas fa-sign-in-alt mr-1"></i> Actividad Destino
                                            </th>
                                            <th class="text-center text-muted" style="width: 10%">Pts.</th>
                                            <th class="text-success" style="width: 50%">
                                                <i class="fas fa-sign-out-alt mr-1"></i> Actividad Origen a Copiar
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($destinationActivities as $dest)
                                            <tr>
                                                <td class="text-sm align-middle">
                                                    @if ($dest['is_extra'])
                                                        <span class="badge badge-warning mr-1">Extra</span>
                                                    @endif
                                                    {{ $dest['name'] }}
                                                </td>
                                                <td class="text-center text-sm text-muted align-middle">
                                                    {{ number_format($dest['max_points'], 2) }}
                                                </td>
                                                <td class="align-middle">
                                                    <select wire:model.live="activityMapping.{{ $dest['id'] }}"
                                                        class="form-control form-control-sm">
                                                        <option value="">— No copiar —</option>
                                                        @foreach ($originActivities as $orig)
                                                            <option value="{{ $orig['id'] }}">
                                                                @if ($orig['is_extra'])
                                                                    [Extra]
                                                                @endif
                                                                {{ $orig['name'] }}
                                                                ({{ number_format($orig['max_points'], 2) }} pts.)
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            @error('activityMapping')
                                <div class="text-danger text-sm mt-2">
                                    <i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}
                                </div>
                            @enderror
                        @endif

                    </div>

                    <div class="modal-footer bg-light">
                        <button wire:click="volverPaso1" type="button" class="btn btn-secondary btn-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Volver
                        </button>
                        <button wire:click="ejecutarConMapeo" type="button" class="btn btn-warning btn-sm"
                            wire:loading.attr="disabled" wire:target="ejecutarConMapeo">
                            <span wire:loading.remove wire:target="ejecutarConMapeo">
                                <i class="fas fa-check mr-1"></i> Aplicar Actualización
                            </span>
                            <span wire:loading wire:target="ejecutarConMapeo">
                                <i class="fas fa-spinner fa-pulse mr-1"></i> Procesando...
                            </span>
                        </button>
                    </div>

                @endif

            </div>
        </div>
    </div>

    {{-- =====================================================
         TARJETA PRINCIPAL
         ===================================================== --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row g-2">

                {{-- Filtros en cascada --}}
                <div class="col-md-2">
                    <select wire:model.live="filterYear" class="form-control form-control-sm">
                        <option value="">-- Año --</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filterLevel" class="form-control form-control-sm"
                        {{ ! $filterYear ? 'disabled' : '' }}>
                        <option value="">-- Nivel --</option>
                        @foreach ($levels as $level)
                            <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filterGrade" class="form-control form-control-sm"
                        {{ ! $filterLevel ? 'disabled' : '' }}>
                        <option value="">-- Grado --</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select wire:model.live="filterSection" class="form-control form-control-sm"
                        {{ ! $filterGrade ? 'disabled' : '' }}>
                        <option value="">-- Sección --</option>
                        @foreach ($sections as $section)
                            <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-center">
                    @if (count($selected) > 0)
                        <span class="badge badge-primary">
                            <i class="fas fa-check-square mr-1"></i>
                            {{ count($selected) }} seleccionado(s)
                        </span>
                    @endif
                </div>

                {{-- Buscador --}}
                @if ($classroom)
                    <div class="col-md-12 mt-2">
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                            </div>
                            <input type="text" wire:model.live.debounce.300ms="search"
                                class="form-control"
                                placeholder="Buscar por carné, código personal o nombre..."
                                autocomplete="new-password">
                            @if ($search)
                                <div class="input-group-append">
                                    <button wire:click="$set('search', '')" type="button"
                                        class="btn btn-outline-secondary btn-sm">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

            </div>
        </div>

        <div class="card-body p-0">

            @if (! $classroom)
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-filter fa-3x mb-3 text-gray"></i>
                    <br>Seleccione año, nivel, grado y sección para ver los estudiantes.
                </div>
            @elseif (! $readyToLoad)
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i>
                    <br>Cargando estudiantes...
                </div>
            @elseif ($students->isEmpty())
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-user-slash fa-3x mb-3 text-gray"></i>
                    <br>No se encontraron estudiantes
                    @if ($search)
                        con el término <strong>"{{ $search }}"</strong>
                    @else
                        inscritos en esta sección.
                    @endif
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-striped table-sm mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width: 40px;" class="text-center">
                                    <input type="checkbox" wire:model.live="selectAll" title="Seleccionar todos">
                                </th>
                                <th>Carné</th>
                                <th>Cód. Personal</th>
                                <th>Nombre completo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $student)
                                <tr class="{{ in_array((string) $student->id, $selected) ? 'table-primary' : '' }}">
                                    <td class="text-center">
                                        <input type="checkbox" wire:model.live="selected" value="{{ $student->id }}">
                                    </td>
                                    <td class="text-sm">{{ $student->carne ?? '—' }}</td>
                                    <td class="text-sm">{{ $student->personal_code ?? '—' }}</td>
                                    <td>
                                        <span class="font-weight-bold">
                                            {{ $student->user->surname }}
                                            {{ $student->user->second_surname }}
                                        </span>
                                        {{ $student->user->first_name }}
                                        {{ $student->user->middle_name }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

        </div>

        @if ($classroom && $readyToLoad)
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted text-sm">
                    @if ($students->isNotEmpty())
                        <i class="fas fa-users mr-1"></i>
                        {{ $students->count() }} estudiante(s)
                        @if ($search)
                            encontrado(s)
                        @else
                            activo(s) en
                            {{ $classroom->grade->grade_name }}
                            {{ $classroom->section->section_name }}
                            — {{ $classroom->year }}
                        @endif
                    @else
                        <i class="fas fa-info-circle mr-1"></i>
                        No hay estudiantes activos en esta sección.
                    @endif
                </span>

                <button wire:click="openScoreModal"
                    class="btn btn-sm btn-warning"
                    {{ count($selected) === 0 ? 'disabled' : '' }}
                    title="{{ count($selected) === 0 ? 'Seleccione al menos un alumno' : 'Actualizar notas de los alumnos seleccionados' }}">
                    <i class="fas fa-edit mr-1"></i> Actualizar Notas
                    @if (count($selected) > 0)
                        <span class="badge badge-dark ml-1">{{ count($selected) }}</span>
                    @endif
                </button>
            </div>
        @endif

    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {

                Livewire.on('openScoreModal', () => {
                    $('#ScoreUpdateModal').modal('show');
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
                        timer: 3500,
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
                        timer: 4000,
                    });
                });

                Livewire.on('confirmScoreCopy', (event) => {
                    let payload = event[0] || event;
                    let message = payload.warnReplace
                        ? 'El cuadro de destino tiene actividades existentes. Se reemplazarán completamente con las del origen y los punteos de TODOS los alumnos serán actualizados.'
                        : 'Las calificaciones del origen se copiarán al destino para los alumnos seleccionados.';

                    Swal.fire({
                        title: '¿Confirmar actualización?',
                        text: message,
                        icon: payload.warnReplace ? 'warning' : 'question',
                        showCancelButton: true,
                        confirmButtonColor: payload.warnReplace ? '#e0a800' : '#007bff',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Sí, actualizar',
                        cancelButtonText: 'Cancelar',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            @this.ejecutarCopia();
                        }
                    });
                });

            });
        </script>
    @endpush

</div>
