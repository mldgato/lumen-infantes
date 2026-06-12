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
                            <th class="text-center">Psicométrica</th>
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
                                    @if ($app->psychometric)
                                        <span class="badge badge-success" title="{{ $app->psychometric->result }}">
                                            <i class="fas fa-check mr-1"></i> {{ $app->psychometric->result }}
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($app->isBilled())
                                        <button wire:click="openModal({{ $app->id }})"
                                            class="btn btn-xs btn-info" title="Ver / Registrar evaluación">
                                            <i class="fas fa-brain"></i>
                                        </button>
                                    @else
                                        <button class="btn btn-xs btn-secondary" disabled
                                            title="Solo disponible en estado 'Facturado'">
                                            <i class="fas fa-brain"></i>
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
         MODAL — Detalle + Psicométrica
    ════════════════════════════════════════ --}}
    <div class="modal fade" id="psychometricDetailModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                @if ($viewing)
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-brain mr-2"></i>
                            {{ $viewing->student_first_name }} {{ $viewing->student_first_surname }}
                            — Ciclo {{ $viewing->year }}
                            <span class="badge badge-{{ $viewing->statusColor() }} ml-2">
                                {{ $viewing->statusLabel() }}
                            </span>
                        </h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>

                    <div class="modal-body p-0"
                        x-data="{ activeTab: 'tab-alumno' }">

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
                            <li class="nav-item">
                                <a class="nav-link" :class="{ 'active': activeTab === 'tab-psicometrica' }"
                                    @click.prevent="activeTab = 'tab-psicometrica'; $nextTick(() => window.initPsychometricQuill && window.initPsychometricQuill())"
                                    href="#" role="tab">
                                    <i class="fas fa-brain mr-1"></i>
                                    Psicométrica
                                    @if ($viewing->psychometric)
                                        <span class="badge badge-success badge-sm ml-1">✓</span>
                                    @endif
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-3">

                            {{-- ── TAB 1: Alumno & Grado ─────────────────── --}}
                            <div role="tabpanel" x-show="activeTab === 'tab-alumno'">
                                <div class="row">
                                    <div class="col-sm-12 col-md-6">
                                        <fieldset class="psyco-section mb-3">
                                            <legend><i class="fas fa-user mr-1"></i> Datos del alumno</legend>
                                            <dl class="row mb-0 small">
                                                <dt class="col-sm-5">Primer nombre</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_first_name }}</dd>
                                                <dt class="col-sm-5">Segundo nombre</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_second_name ?? '—' }}</dd>
                                                <dt class="col-sm-5">Primer apellido</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_first_surname }}</dd>
                                                <dt class="col-sm-5">Segundo apellido</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_second_surname ?? '—' }}</dd>
                                                <dt class="col-sm-5">Fecha de nacimiento</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_birthdate?->format('d/m/Y') ?? '—' }}</dd>
                                                <dt class="col-sm-5">Religión</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_religion ?? '—' }}</dd>
                                                <dt class="col-sm-5">Colegio anterior</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_previous_school ?? '—' }}</dd>
                                                <dt class="col-sm-5">Dirección</dt>
                                                <dd class="col-sm-7">{{ $viewing->student_address }}</dd>
                                            </dl>
                                        </fieldset>
                                    </div>
                                    <div class="col-sm-12 col-md-6">
                                        <fieldset class="psyco-section mb-3">
                                            <legend><i class="fas fa-school mr-1"></i> Ciclo y grado solicitado</legend>
                                            <dl class="row mb-0 small">
                                                <dt class="col-sm-5">Ciclo escolar</dt>
                                                <dd class="col-sm-7">{{ $viewing->year }}</dd>
                                                <dt class="col-sm-5">Nivel</dt>
                                                <dd class="col-sm-7">{{ $viewing->level?->level_name ?? '—' }}</dd>
                                                <dt class="col-sm-5">Grado</dt>
                                                <dd class="col-sm-7">{{ $viewing->grade?->grade_name ?? '—' }}</dd>
                                            </dl>
                                        </fieldset>
                                        <fieldset class="psyco-section mb-0">
                                            <legend><i class="fas fa-star mr-1"></i> ¿Cómo nos conoció?</legend>
                                            <p class="mb-0 small">{{ $viewing->referral_source ?? 'No especificado' }}</p>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>

                            {{-- ── TAB 2: Padres & Encargado ────────────── --}}
                            <div role="tabpanel" x-show="activeTab === 'tab-padres'">
                                <div class="row">
                                    {{-- Padre --}}
                                    <div class="col-sm-12 col-md-6 mb-3">
                                        <fieldset class="psyco-section h-100">
                                            <legend><i class="fas fa-male mr-1"></i> Padre</legend>
                                            @if ($viewing->father_first_name)
                                                <dl class="row mb-0 small">
                                                    <dt class="col-sm-5">Nombres</dt>
                                                    <dd class="col-sm-7">{{ $viewing->father_first_name }}</dd>
                                                    <dt class="col-sm-5">Apellidos</dt>
                                                    <dd class="col-sm-7">{{ $viewing->father_last_name }}</dd>
                                                    <dt class="col-sm-5">Teléfono</dt>
                                                    <dd class="col-sm-7">{{ $viewing->father_phone }}</dd>
                                                    <dt class="col-sm-5">Lugar de trabajo</dt>
                                                    <dd class="col-sm-7">{{ $viewing->father_workplace ?? '—' }}</dd>
                                                    <dt class="col-sm-5">NIT</dt>
                                                    <dd class="col-sm-7">{{ $viewing->father_nit ?? '—' }}</dd>
                                                    <dt class="col-sm-5">Profesión</dt>
                                                    <dd class="col-sm-7">{{ $viewing->father_profession ?? '—' }}</dd>
                                                </dl>
                                            @else
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-info-circle mr-1"></i> No registrado.
                                                </p>
                                            @endif
                                        </fieldset>
                                    </div>
                                    {{-- Madre --}}
                                    <div class="col-sm-12 col-md-6 mb-3">
                                        <fieldset class="psyco-section h-100">
                                            <legend><i class="fas fa-female mr-1"></i> Madre</legend>
                                            @if ($viewing->mother_first_name)
                                                <dl class="row mb-0 small">
                                                    <dt class="col-sm-5">Nombres</dt>
                                                    <dd class="col-sm-7">{{ $viewing->mother_first_name }}</dd>
                                                    <dt class="col-sm-5">Apellidos</dt>
                                                    <dd class="col-sm-7">{{ $viewing->mother_last_name }}</dd>
                                                    <dt class="col-sm-5">Teléfono</dt>
                                                    <dd class="col-sm-7">{{ $viewing->mother_phone }}</dd>
                                                    <dt class="col-sm-5">Lugar de trabajo</dt>
                                                    <dd class="col-sm-7">{{ $viewing->mother_workplace ?? '—' }}</dd>
                                                    <dt class="col-sm-5">NIT</dt>
                                                    <dd class="col-sm-7">{{ $viewing->mother_nit ?? '—' }}</dd>
                                                    <dt class="col-sm-5">Profesión</dt>
                                                    <dd class="col-sm-7">{{ $viewing->mother_profession ?? '—' }}</dd>
                                                </dl>
                                            @else
                                                <p class="text-muted small mb-0">
                                                    <i class="fas fa-info-circle mr-1"></i> No registrada.
                                                </p>
                                            @endif
                                        </fieldset>
                                    </div>
                                    {{-- Encargado --}}
                                    <div class="col-sm-12 col-md-6 mb-3">
                                        <fieldset class="psyco-section">
                                            <legend><i class="fas fa-user-shield mr-1"></i> Encargado</legend>
                                            <dl class="row mb-0 small">
                                                <dt class="col-sm-5">Tipo</dt>
                                                <dd class="col-sm-7">{{ $viewing->guardianTypeLabel() }}</dd>
                                                <dt class="col-sm-5">Nombre</dt>
                                                <dd class="col-sm-7">{{ $viewing->guardian_name }}</dd>
                                                <dt class="col-sm-5">Teléfono</dt>
                                                <dd class="col-sm-7">{{ $viewing->guardian_phone }}</dd>
                                                <dt class="col-sm-5">Correo</dt>
                                                <dd class="col-sm-7">{{ $viewing->guardian_email }}</dd>
                                            </dl>
                                        </fieldset>
                                    </div>
                                    {{-- Familia --}}
                                    <div class="col-sm-12 col-md-6 mb-3">
                                        <fieldset class="psyco-section">
                                            <legend><i class="fas fa-users mr-1"></i> Información Familiar</legend>
                                            <dl class="row mb-0 small">
                                                <dt class="col-sm-6">Hijos (varones)</dt>
                                                <dd class="col-sm-6">{{ $viewing->sons_count ?? 0 }}
                                                    @if ($viewing->sons_ages) <small class="text-muted">({{ $viewing->sons_ages }})</small> @endif
                                                </dd>
                                                <dt class="col-sm-6">Hijas (mujeres)</dt>
                                                <dd class="col-sm-6">{{ $viewing->daughters_count ?? 0 }}
                                                    @if ($viewing->daughters_ages) <small class="text-muted">({{ $viewing->daughters_ages }})</small> @endif
                                                </dd>
                                            </dl>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>

                            {{-- ── TAB 3: Papelería ─────────────────────── --}}
                            <div role="tabpanel" x-show="activeTab === 'tab-papeleria'">
                                <fieldset class="psyco-section mb-3">
                                    <legend>
                                        <i class="fas fa-link mr-1"></i> Enlace de Documentos
                                    </legend>
                                    @if ($viewing->url_documents)
                                        <a href="{{ $viewing->url_documents }}" target="_blank"
                                            class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-external-link-alt mr-1"></i> Ver carpeta de papelería
                                        </a>
                                    @else
                                        <p class="text-muted small mb-0">
                                            <i class="fas fa-exclamation-circle mr-1"></i>
                                            No hay enlace de papelería registrado.
                                        </p>
                                    @endif
                                </fieldset>

                                <fieldset class="psyco-section mb-0">
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
                                    <div class="row">
                                        @foreach (\App\Models\AdmissionApplicationDocument::fields() as $field => $label)
                                            <div class="col-sm-12 col-md-6 mb-2">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox"
                                                        class="custom-control-input"
                                                        id="psyco_doc_{{ $field }}"
                                                        @checked($viewing->documents?->$field)
                                                        disabled>
                                                    <label class="custom-control-label text-muted"
                                                        for="psyco_doc_{{ $field }}">
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
                                </fieldset>
                            </div>

                            {{-- ── TAB 4: Historial ─────────────────────── --}}
                            <div role="tabpanel" x-show="activeTab === 'tab-historial'">
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

                            {{-- ── TAB 5: Psicométrica ──────────────────── --}}
                            <div role="tabpanel" x-show="activeTab === 'tab-psicometrica'">
                                @php $isLocked = $viewing->isPsychometric() || $viewing->isAccepted() || $viewing->isRejected(); @endphp

                                @if ($viewing->psychometric)
                                    <div class="alert alert-success py-2 mb-3">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Evaluación {{ $viewing->psychometric->created_at === $viewing->psychometric->updated_at ? 'registrada' : 'actualizada' }}
                                        por <strong>{{ $viewing->psychometric->user?->first_name }} {{ $viewing->psychometric->user?->first_surname }}</strong>
                                        el {{ $viewing->psychometric->updated_at->format('d/m/Y H:i') }}.
                                    </div>
                                @endif

                                @if ($isLocked)
                                    {{-- Solo lectura --}}
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label class="font-weight-bold">Resultado Psicométrico</label>
                                                <p class="mb-0">
                                                    <span class="badge badge-info" style="font-size: .95rem; padding: .4em .75em;">
                                                        {{ $viewing->psychometric?->result ?? '—' }}
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label class="font-weight-bold">Anotaciones</label>
                                        @if ($viewing->psychometric?->notes)
                                            <div class="border rounded p-3 bg-light" style="min-height: 80px; font-size: .9rem;">
                                                {!! $viewing->psychometric->notes !!}
                                            </div>
                                        @else
                                            <p class="text-muted small mb-0">No hay anotaciones registradas.</p>
                                        @endif
                                    </div>
                                @else
                                    {{-- Formulario editable --}}
                                    <div class="row">
                                        <div class="col-sm-12 col-md-6">
                                            <div class="form-group">
                                                <label>
                                                    Resultado Psicométrico
                                                    <span class="text-danger">*</span>
                                                </label>
                                                <input type="text"
                                                    wire:model="psychometricResult"
                                                    list="psychometric-result-datalist"
                                                    class="form-control @error('psychometricResult') is-invalid @enderror"
                                                    placeholder="Seleccione o escriba el resultado..."
                                                    autocomplete="off">
                                                <datalist id="psychometric-result-datalist">
                                                    <option value="Arriba del Promedio">
                                                    <option value="Promedio">
                                                    <option value="Debajo del promedio">
                                                    <option value="Alto">
                                                    <option value="Bajo">
                                                </datalist>
                                                @error('psychometricResult')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group mb-0">
                                        <label>Anotaciones</label>
                                        <div wire:ignore>
                                            <div id="psychometric-quill-editor"></div>
                                        </div>
                                    </div>

                                    <div class="text-right mt-3">
                                        <button type="button" class="btn btn-primary btn-sm"
                                            @click="$wire.savePsychometric(
                                                window.psychometricQuill
                                                    ? (window.psychometricQuill.root.innerHTML === '<p><br></p>' ? '' : window.psychometricQuill.root.innerHTML)
                                                    : ''
                                            )"
                                            wire:loading.attr="disabled"
                                            wire:target="savePsychometric">
                                            <span wire:loading.remove wire:target="savePsychometric">
                                                <i class="fas fa-save mr-1"></i> Guardar evaluación
                                            </span>
                                            <span wire:loading wire:target="savePsychometric">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> Guardando...
                                            </span>
                                        </button>
                                    </div>
                                @endif
                            </div>

                        </div>{{-- /tab-content --}}
                    </div>{{-- /modal-body --}}

                    <div class="modal-footer">
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
    window.psychometricQuill = null;

    window.initPsychometricQuill = function () {
        if (!document.getElementById('psychometric-quill-editor')) {
            return;
        }
        if (!window.psychometricQuill) {
            window.psychometricQuill = new Quill('#psychometric-quill-editor', {
                theme: 'snow',
                placeholder: 'Ingrese sus anotaciones...',
                modules: {
                    toolbar: [
                        [{ header: [1, 2, 3, false] }],
                        ['bold', 'italic', 'underline'],
                        [{ color: [] }],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        [{ align: [] }],
                        ['clean']
                    ]
                }
            });
        }
        window.psychometricQuill.root.innerHTML = $wire.psychometricNotes || '';
    };

    $wire.on('openPsychometricDetailModal', () => {
        if (window.psychometricQuill) {
            window.psychometricQuill.root.innerHTML = $wire.psychometricNotes || '';
        }
        $('#psychometricDetailModal').modal('show');
    });

    $wire.on('closePsychometricModal', () => {
        $('#psychometricDetailModal').modal('hide');
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
    fieldset.psyco-section {
        border: 1px solid #dee2e6;
        border-radius: 6px;
        padding: 12px 16px 10px;
    }
    fieldset.psyco-section legend {
        font-size: .88rem;
        font-weight: 600;
        color: #2c3e50;
        width: auto;
        padding: 0 8px;
    }
    fieldset.psyco-section dl.row dt,
    fieldset.psyco-section dl.row dd {
        margin-bottom: .35rem;
    }
    #psychometric-quill-editor .ql-editor {
        min-height: 320px !important;
        font-size: .9rem;
    }
</style>
@endpush
