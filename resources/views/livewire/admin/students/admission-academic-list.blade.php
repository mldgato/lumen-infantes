<div>
    {{-- ════════════════════════════════════════
         FILTROS
    ════════════════════════════════════════ --}}
    <div class="card card-outline card-primary">
        <div class="card-body pb-2">
            <div class="row">
                <div class="col-sm-12 col-md-2">
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
                <div class="col-sm-12 col-md-2">
                    <div class="form-group">
                        <label class="control-label">Nivel</label>
                        <select wire:model.live="filterLevel" class="form-control">
                            <option value="">— Todos —</option>
                            @foreach ($this->allLevels as $level)
                                <option value="{{ $level->id }}">{{ $level->level_name }}</option>
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
                            <option value="billed">Facturado</option>
                            <option value="psychometric">Psicométrica registrada</option>
                            <option value="academic">Evaluaciones académicas</option>
                            <option value="accepted">Aceptado</option>
                            <option value="rejected">Rechazado</option>
                        </select>
                    </div>
                </div>
                <div class="col-sm-12 col-md-3">
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
                            <th>Estado</th>
                            <th class="text-center">Materias</th>
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
                                <td>
                                    <span class="badge badge-{{ $app->statusColor() }}">
                                        {{ $app->statusLabel() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if ($app->academicScores->isNotEmpty())
                                        <span class="badge badge-teal">
                                            <i class="fas fa-check mr-1"></i> {{ $app->academicScores->count() }} materia(s)
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @php
                                        $academicEditable = in_array($app->current_status, ['reviewed', 'billed', 'psychometric']);
                                        $academicReadable = $app->academicScores->isNotEmpty() || in_array($app->current_status, ['academic', 'accepted', 'rejected']);
                                    @endphp
                                    @if ($app->academic_unlocked)
                                        <button wire:click="openModal({{ $app->id }})"
                                            class="btn btn-xs btn-warning"
                                            title="Corregir evaluaciones académicas (desbloqueadas)">
                                            <i class="fas fa-unlock"></i>
                                        </button>
                                    @elseif ($academicEditable)
                                        <button wire:click="openModal({{ $app->id }})"
                                            class="btn btn-xs btn-info"
                                            title="Registrar evaluaciones académicas">
                                            <i class="fas fa-graduation-cap"></i>
                                        </button>
                                    @elseif ($academicReadable)
                                        <button wire:click="openModal({{ $app->id }})"
                                            class="btn btn-xs btn-secondary"
                                            title="Ver evaluaciones académicas">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-xs btn-light" disabled
                                            title="Disponible a partir de 'Documentación completa'">
                                            <i class="fas fa-graduation-cap"></i>
                                        </button>
                                    @endif
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
         MODAL — Evaluaciones Académicas
    ════════════════════════════════════════ --}}
    <div class="modal fade" id="academicModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                @if ($viewing)
                    @php $isEditable = in_array($viewing->current_status, ['reviewed', 'billed', 'psychometric']) || $viewing->academic_unlocked; @endphp

                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            {{ $viewing->student_first_name }} {{ $viewing->student_first_surname }}
                            — Ciclo {{ $viewing->year }}
                            <span class="badge badge-{{ $viewing->statusColor() }} ml-2">
                                {{ $viewing->statusLabel() }}
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body">

                        @if ($viewing->academic_unlocked)
                            <div class="alert alert-warning py-2 mb-3">
                                <i class="fas fa-unlock mr-1"></i>
                                <strong>Modo corrección.</strong> Agregue, elimine materias y presione "Finalizar" para guardar los cambios.
                            </div>
                        @endif

                        {{-- ── Información del alumno ────────────────── --}}
                        <fieldset class="edit-section mb-3">
                            <legend><i class="fas fa-user-graduate mr-1"></i> Datos del Alumno</legend>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><small class="text-muted">Nombre completo</small></p>
                                    <p class="mb-2">
                                        <strong>
                                            {{ $viewing->student_first_name }}
                                            {{ $viewing->student_second_name }}
                                            {{ $viewing->student_first_surname }}
                                            {{ $viewing->student_second_surname }}
                                        </strong>
                                    </p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">Nivel</small></p>
                                    <p class="mb-2">{{ $viewing->level?->level_name ?? '—' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">Grado</small></p>
                                    <p class="mb-2">{{ $viewing->grade?->grade_name ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <p class="mb-1"><small class="text-muted">Encargado</small></p>
                                    <p class="mb-2">{{ $viewing->guardian_name }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">Teléfono</small></p>
                                    <p class="mb-2">{{ $viewing->guardian_phone ?: '—' }}</p>
                                </div>
                                <div class="col-md-3">
                                    <p class="mb-1"><small class="text-muted">Correo</small></p>
                                    <p class="mb-2 text-truncate" title="{{ $viewing->guardian_email }}">
                                        {{ $viewing->guardian_email ?: '—' }}
                                    </p>
                                </div>
                            </div>
                        </fieldset>

                        {{-- ── Punteos registrados ───────────────────── --}}
                        @if ($viewing->academicScores->isNotEmpty())
                            <fieldset class="edit-section mb-3">
                                <legend><i class="fas fa-list-ol mr-1"></i> Punteos por Materia</legend>
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>Materia</th>
                                            <th class="text-center" style="width:120px;">Punteo</th>
                                            @if ($isEditable)
                                                <th class="text-center" style="width:50px;"></th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($viewing->academicScores->sortBy('course.ordering') as $item)
                                            <tr>
                                                <td>{{ $item->course?->name ?? '—' }}</td>
                                                <td class="text-center">
                                                    <span class="badge badge-{{ $item->score >= 60 ? 'success' : 'danger' }} px-2">
                                                        {{ number_format($item->score, 2) }}
                                                    </span>
                                                </td>
                                                @if ($isEditable)
                                                    <td class="text-center">
                                                        <button
                                                            @click="Swal.fire({ title: '¿Eliminar materia?', icon: 'warning', showCancelButton: true, confirmButtonText: 'Sí', cancelButtonText: 'No', confirmButtonColor: '#dc3545' }).then(r => r.isConfirmed && $wire.removeScore({{ $item->id }}))"
                                                            class="btn btn-xs btn-outline-danger" title="Eliminar">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                @endif
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="thead-light">
                                        <tr>
                                            <th>Promedio</th>
                                            <th class="text-center">
                                                <span class="badge badge-{{ $viewing->academicScores->avg('score') >= 60 ? 'success' : 'danger' }} px-2">
                                                    {{ number_format($viewing->academicScores->avg('score'), 2) }}
                                                </span>
                                            </th>
                                            @if ($isEditable)
                                                <th></th>
                                            @endif
                                        </tr>
                                    </tfoot>
                                </table>
                            </fieldset>
                        @else
                            <div class="alert alert-light border py-2 mb-3">
                                <i class="fas fa-info-circle mr-1 text-muted"></i>
                                <span class="text-muted">Aún no se han registrado materias.</span>
                            </div>
                        @endif

                        {{-- ── Formulario agregar materia (solo editable) ── --}}
                        @if ($isEditable)
                            @if ($this->availableCourses->isNotEmpty())
                                <fieldset class="edit-section mb-0">
                                    <legend><i class="fas fa-plus-circle mr-1"></i> Agregar Materia</legend>
                                    <div class="row align-items-end">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group mb-0">
                                                <label>Materia <span class="text-danger">*</span></label>
                                                <select wire:model="selectedCourseId"
                                                    class="form-control @error('selectedCourseId') is-invalid @enderror">
                                                    <option value="">— Seleccione —</option>
                                                    @foreach ($this->availableCourses as $course)
                                                        <option value="{{ $course->id }}">{{ $course->name }}</option>
                                                    @endforeach
                                                </select>
                                                @error('selectedCourseId')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-4">
                                            <div class="form-group mb-0">
                                                <label>Punteo (0–100) <span class="text-danger">*</span></label>
                                                <input type="number" wire:model="score" min="0" max="100" step="0.01"
                                                    class="form-control @error('score') is-invalid @enderror"
                                                    placeholder="Ej: 85.50">
                                                @error('score')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="col-sm-12 col-md-2 mt-2 mt-md-0">
                                            <button wire:click="addScore"
                                                wire:loading.attr="disabled" wire:target="addScore"
                                                class="btn btn-primary btn-block">
                                                <span wire:loading.remove wire:target="addScore">
                                                    <i class="fas fa-plus mr-1"></i> Agregar
                                                </span>
                                                <span wire:loading wire:target="addScore">
                                                    <i class="fas fa-spinner fa-spin"></i>
                                                </span>
                                            </button>
                                        </div>
                                    </div>
                                </fieldset>
                            @else
                                <div class="alert alert-info py-2 mb-0">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Todas las materias del catálogo han sido registradas.
                                </div>
                            @endif
                        @endif

                    </div>

                    <div class="modal-footer justify-content-between">
                        @if ($isEditable)
                            <button type="button"
                                @click="Swal.fire({
                                    title: '¿Finalizar evaluaciones académicas?',
                                    text: 'Una vez finalizado no podrá agregar más materias.',
                                    icon: 'warning',
                                    showCancelButton: true,
                                    confirmButtonText: 'Sí, finalizar',
                                    cancelButtonText: 'Cancelar',
                                    confirmButtonColor: '#28a745'
                                }).then(r => r.isConfirmed && $wire.finalizeEvaluation())"
                                wire:loading.attr="disabled" wire:target="finalizeEvaluation"
                                class="btn btn-success btn-sm"
                                @if ($viewing->academicScores->isEmpty()) disabled @endif>
                                <i class="fas fa-flag-checkered mr-1"></i> Finalizar evaluación
                            </button>
                        @else
                            <span></span>
                        @endif
                        <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                            <i class="fas fa-times mr-1"></i> Cerrar
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('openAcademicModal', () => {
        $('#academicModal').modal('show');
    });

    $wire.on('closeAcademicModal', () => {
        $('#academicModal').modal('hide');
    });

    $wire.on('showAlert', (data) => {
        let p = Array.isArray(data) ? (data[0] || {}) : (data || {});
        Swal.fire({
            position: 'top-end',
            icon: p.type || 'info',
            title: p.title,
            showConfirmButton: false,
            timer: 3500
        });
    });

    $wire.on('toastMessage', (data) => {
        let p = Array.isArray(data) ? (data[0] || {}) : (data || {});
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({ icon: p.type || 'info', title: p.message || p.title });
    });
</script>
@endscript

@push('css')
<style>
    fieldset.edit-section { border: 1px solid #dee2e6; border-radius: .25rem; padding: .75rem 1rem; }
    fieldset.edit-section legend { width: auto; font-size: .85rem; font-weight: 600; color: #6c757d; padding: 0 .4rem; margin-bottom: .5rem; }
</style>
@endpush
