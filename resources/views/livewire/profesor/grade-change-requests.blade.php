<div wire:init="loadRequests">

    @if ($view === 'list')

        {{-- LISTA DE CUADROS APROBADOS --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="row align-items-end">

                    <div class="col-md-3 form-group mb-2">
                        <label class="text-sm mb-1">Nivel</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                            </div>
                            <select wire:model.live="filterLevel" class="form-control">
                                <option value="">-- Todos los niveles --</option>
                                @foreach ($levels as $level)
                                    <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 form-group mb-2">
                        <label class="text-sm mb-1">Grado</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                            </div>
                            <select wire:model.live="filterGrade" class="form-control"
                                {{ !$filterLevel ? 'disabled' : '' }}>
                                <option value="">-- Todos --</option>
                                @foreach ($grades as $grade)
                                    <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2 form-group mb-2">
                        <label class="text-sm mb-1">Sección</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-door-open"></i></span>
                            </div>
                            <select wire:model.live="filterSection" class="form-control"
                                {{ !$filterGrade ? 'disabled' : '' }}>
                                <option value="">-- Todas --</option>
                                @foreach ($sections as $section)
                                    <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-1 form-group mb-2">
                        <label class="text-sm mb-1">Unidad</label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-bookmark"></i></span>
                            </div>
                            <select wire:model.live="filterUnit" class="form-control"
                                {{ !$filterSection ? 'disabled' : '' }}>
                                <option value="">--</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit }}">U{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-4 form-group mb-2">
                        <label class="text-sm mb-1">Buscar</label>
                        <div class="input-group input-group-sm">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                name="buscar" id="buscador" placeholder="Buscar grado, curso, sección..." autocomplete="search">
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
                @if ($readyToLoad && count($gradeBooks))
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th>Nivel</th>
                                <th>Grado</th>
                                <th>Sección</th>
                                <th>Curso</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gradeBooks as $gradeBook)
                                @php
                                    $hasPending = \App\Models\GradeChangeRequest::where('grade_book_id', $gradeBook->id)
                                        ->where('status', 'pending')
                                        ->exists();
                                @endphp
                                <tr>
                                    <td>{{ $gradeBook->assignment->classroom->level->level_name }}</td>
                                    <td>{{ $gradeBook->assignment->classroom->grade->grade_name }}</td>
                                    <td>{{ $gradeBook->assignment->classroom->section->section_name }}</td>
                                    <td>{{ $gradeBook->assignment->pensumCourse->course->course_name }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">U{{ $gradeBook->assignment->unit }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($hasPending)
                                            <span class="badge badge-warning">
                                                <i class="fas fa-clock mr-1"></i> Solicitud pendiente
                                            </span>
                                        @else
                                            <button wire:click="selectGradeBook({{ $gradeBook->id }})"
                                                class="btn btn-sm btn-primary shadow-sm">
                                                <i class="fas fa-edit"></i> Solicitar cambio
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-4 text-center text-muted">
                        @if (!$readyToLoad)
                            <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando cuadros...
                        @else
                            <i class="fas fa-book-open fa-3x mb-3 text-gray"></i><br>No hay cuadros aprobados
                            disponibles.
                        @endif
                    </div>
                @endif
            </div>
            @if ($readyToLoad && count($gradeBooks) && $gradeBooks->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">{{ $gradeBooks->links() }}</div>
                </div>
            @endif
        </div>

        {{-- MIS SOLICITUDES ENVIADAS --}}
        @if ($readyToLoad && count($myRequests))
            <div class="card card-secondary card-outline mt-3">
                <div class="card-header">
                    <h5 class="m-0 text-bold text-secondary">
                        <i class="fas fa-history mr-1"></i> Mis Solicitudes
                    </h5>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Curso</th>
                                <th>Grado</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-center">Estudiantes</th>
                                <th class="text-center">Estado</th>
                                <th>Motivo rechazo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($myRequests as $req)
                                <tr>
                                    <td>{{ $req->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $req->gradeBook->assignment->pensumCourse->course->course_name }}</td>
                                    <td>
                                        {{ $req->gradeBook->assignment->classroom->grade->grade_name }}
                                        {{ $req->gradeBook->assignment->classroom->section->section_name }}
                                    </td>
                                    <td class="text-center">
                                        <span
                                            class="badge badge-secondary">U{{ $req->gradeBook->assignment->unit }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            {{ $req->items->pluck('student_id')->unique()->count() }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($req->status === 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif ($req->status === 'approved')
                                            <span class="badge badge-success">Aprobada</span>
                                        @else
                                            <span class="badge badge-danger">Rechazada</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($req->isRejected())
                                            <small
                                                class="text-danger">{{ Str::limit($req->rejection_reason, 40) }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($myRequests->hasPages())
                    <div class="card-footer clearfix">
                        <div class="float-right">{{ $myRequests->links() }}</div>
                    </div>
                @endif
            </div>
        @endif
    @elseif ($view === 'select-students')
        {{-- STEP 2: SELECCIONAR ESTUDIANTES --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="m-0 text-bold">
                        <i class="fas fa-users mr-1"></i>
                        Seleccionar Estudiantes —
                        {{ $selectedGradeBook->assignment->pensumCourse->course->course_name }}
                        <span class="badge badge-secondary ml-1">U{{ $selectedGradeBook->assignment->unit }}</span>
                    </h5>
                    <button wire:click="cancelRequest" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </button>
                </div>
            </div>
            <div class="card-body">

                @error('selectedStudents')
                    <div class="alert alert-danger py-2">
                        <i class="fas fa-exclamation-triangle mr-1"></i> {{ $message }}
                    </div>
                @enderror

                <div class="alert alert-info py-2 text-sm mb-3">
                    <i class="fas fa-info-circle mr-1"></i>
                    Los estudiantes marcados en <span class="badge badge-warning">amarillo</span>
                    tienen una solicitud pendiente en este cuadro y no pueden ser seleccionados.
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px" class="text-center">
                                    <input type="checkbox" id="selectAll" onclick="toggleSelectableStudents(this)">
                                </th>
                                <th>#</th>
                                <th>Estudiante</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students as $idx => $student)
                                @php $blocked = in_array($student->id, $blockedIds); @endphp
                                <tr class="{{ $blocked ? 'table-warning' : '' }}">
                                    <td class="text-center">
                                        @if (!$blocked)
                                            <input type="checkbox" wire:model="selectedStudents"
                                                value="{{ $student->id }}" class="selectable-student">
                                        @else
                                            <i class="fas fa-lock text-warning" title="Solicitud pendiente"></i>
                                        @endif
                                    </td>
                                    <td>{{ $idx + 1 }}</td>
                                    <td>
                                        {{ $student->user->surname }}
                                        {{ $student->user->second_surname }},
                                        {{ $student->user->first_name }}
                                        {{ $student->user->middle_name }}
                                        @if ($blocked)
                                            <span class="badge badge-warning ml-1 text-sm">Pendiente</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-right bg-light">
                <button wire:click="cancelRequest" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button wire:click="confirmStudents" class="btn btn-primary btn-sm" wire:loading.attr="disabled"
                    wire:target="confirmStudents">
                    <span wire:loading.remove wire:target="confirmStudents">
                        <i class="fas fa-arrow-right"></i> Continuar
                        @if (count($selectedStudents) > 0)
                            <span class="badge badge-light ml-1">{{ count($selectedStudents) }}</span>
                        @endif
                    </span>
                    <span wire:loading wire:target="confirmStudents">
                        <i class="fas fa-spinner fa-pulse"></i>
                    </span>
                </button>
            </div>
        </div>
    @elseif ($view === 'edit-scores')
        {{-- STEP 3: EDITAR CALIFICACIONES --}}
        <div class="card card-warning card-outline">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="m-0 text-bold">
                        <i class="fas fa-pen mr-1"></i>
                        Modificar Calificaciones —
                        {{ $selectedGradeBook->assignment->pensumCourse->course->course_name }}
                        <span class="badge badge-secondary ml-1">U{{ $selectedGradeBook->assignment->unit }}</span>
                    </h5>
                    <button wire:click="backToStudents" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </button>
                </div>
            </div>
            <div class="card-body">

                <div class="alert alert-warning py-2 text-sm mb-3">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Modifica únicamente las calificaciones que deseas cambiar.
                    Solo se enviarán los cambios donde la nota sea diferente a la actual.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover mb-3">
                        <thead class="thead-light">
                            <tr>
                                <th style="min-width:200px">Estudiante</th>
                                @foreach ($activities as $activity)
                                    <th class="text-center {{ $activity->activityType->is_extra ? 'table-warning' : '' }}"
                                        style="min-width:130px">
                                        <div>{{ $activity->name }}</div>
                                        <small class="text-muted">Máx: {{ $activity->max_points }}</small>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($students->whereIn('id', $selectedStudents) as $student)
                                <tr>
                                    <td>
                                        {{ $student->user->surname }}
                                        {{ $student->user->second_surname }},
                                        {{ $student->user->first_name }}
                                        {{ $student->user->middle_name }}
                                    </td>
                                    @foreach ($activities as $activity)
                                        <td
                                            class="p-1 {{ $activity->activityType->is_extra ? 'table-warning' : '' }}">
                                            <div class="mb-1">
                                                <small class="text-muted d-block">Nota</small>
                                                <input type="number"
                                                    wire:model.live="scores.{{ $student->id }}.{{ $activity->id }}.score"
                                                    class="form-control form-control-sm text-center" min="0"
                                                    max="{{ $activity->max_points }}" step="0.01"
                                                    oninput="if(parseFloat(this.value) > {{ $activity->max_points }}) this.value = {{ $activity->max_points }}; if(parseFloat(this.value) < 0) this.value = 0;">
                                            </div>
                                            @if ($config && $config->improvement_type !== 'none')
                                                <div>
                                                    <small class="text-muted d-block">Mejora</small>
                                                    <input type="number"
                                                        wire:model.live="scores.{{ $student->id }}.{{ $activity->id }}.improvement_score"
                                                        class="form-control form-control-sm text-center"
                                                        min="0" max="{{ $activity->max_points }}"
                                                        step="0.01" placeholder="—">
                                                </div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Motivo --}}
                <div class="form-group mb-0">
                    <label class="text-sm mb-1 text-bold">
                        Motivo del cambio <span class="text-danger">*</span>
                    </label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-comment-alt"></i></span>
                        </div>
                        <textarea wire:model="reason" class="form-control @error('reason') is-invalid @enderror" rows="3"
                            placeholder="Describa el motivo por el que solicita este cambio de calificaciones..."></textarea>
                        @error('reason')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

            </div>
            <div class="card-footer text-right bg-light">
                <button wire:click="cancelRequest" class="btn btn-secondary btn-sm mr-2">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button wire:click.prevent="submitRequest" class="btn btn-warning btn-sm"
                    wire:loading.attr="disabled" wire:target="submitRequest">
                    <span wire:loading.remove wire:target="submitRequest">
                        <i class="fas fa-paper-plane"></i> Enviar Solicitud
                    </span>
                    <span wire:loading wire:target="submitRequest">
                        <i class="fas fa-spinner fa-pulse"></i> Enviando...
                    </span>
                </button>
            </div>
        </div>

    @endif

    @push('js')
        <script>
            function toggleSelectableStudents(source) {
                document.querySelectorAll('.selectable-student').forEach(cb => {
                    cb.checked = source.checked;
                    cb.dispatchEvent(new Event('change'));
                });
            }

            document.addEventListener('livewire:init', () => {
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
            });
        </script>
    @endpush
</div>
