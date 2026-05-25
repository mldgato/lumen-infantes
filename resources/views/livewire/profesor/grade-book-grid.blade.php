<div>
    {{-- ── INFO DEL CUADRO ──────────────────────────────────────── --}}
    <div class="card card-primary card-outline mb-3">
        <div class="card-header d-flex justify-content-between align-items-center py-2">
            <h5 class="m-0 text-bold">
                <i class="fas fa-th mr-1"></i>
                {{ $gradeBook->assignment->pensumCourse->course->course_name }}
                <small class="text-muted font-weight-normal ml-2">
                    Unidad {{ $gradeBook->assignment->unit }}
                    &mdash;
                    {{ $gradeBook->assignment->classroom->grade->grade_name }}
                    {{ $gradeBook->assignment->classroom->section->section_name }}
                    ({{ $gradeBook->assignment->classroom->year }})
                </small>
            </h5>
            <div class="d-flex align-items-center">
                @php
                    [$statusLabel, $statusColor] = match ($gradeBook->status) {
                        'open'     => ['Abierto',   'success'],
                        'locked'   => ['Bloqueado', 'secondary'],
                        'approved' => ['Aprobado',  'primary'],
                        'rejected' => ['Rechazado', 'danger'],
                        default    => ['—',         'light'],
                    };
                @endphp
                <span class="badge badge-{{ $statusColor }} px-3 py-2 mr-2">{{ $statusLabel }}</span>
                <a href="{{ route('profesor.grade-books.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Volver
                </a>
            </div>
        </div>
    </div>

    @if ($gradeBook->status !== 'open')
        <div class="alert alert-warning">
            <i class="fas fa-lock mr-1"></i>
            Este cuadro está <strong>{{ $statusLabel }}</strong> — las notas son de solo lectura.
        </div>
    @endif

    @if ($activities->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Este cuadro no tiene actividades registradas. Créalas primero desde la vista principal de cuadros.
        </div>
    @else
        <div
            x-data="gradeGrid(
                {{ Js::from($initialGrid) }},
                {{ Js::from($config->improvement_type) }},
                {{ Js::from((float) ($config->improvement_percentage ?? 0)) }},
                {{ Js::from($activitiesMeta) }}
            )"
            x-cloak>

            {{-- ── TOOLBAR ──────────────────────────────────────────── --}}
            @if ($gradeBook->status === 'open')
                <div class="d-flex align-items-center mb-2 flex-wrap">
                    <button @click="saveGrid()" class="btn btn-success btn-sm shadow-sm mr-2" :disabled="saving">
                        <i class="fas fa-save mr-1"></i>
                        <span x-show="!saving">Guardar todo</span>
                        <span x-show="saving" x-cloak><i class="fas fa-spinner fa-pulse"></i> Guardando…</span>
                    </button>
                    <small class="text-white">
                        <i class="fas fa-info-circle mr-1"></i>
                        Las celdas vacías no se guardan — se permite guardado parcial.
                        @if ($hasImprovement)
                            &nbsp;|&nbsp; Mejoramiento: <strong>{{ $config->improvement_type }}</strong>
                        @endif
                    </small>
                </div>
            @endif

            {{-- ── TABLA ────────────────────────────────────────────── --}}
            <div class="card card-outline card-secondary">
                <div class="card-body p-0" style="overflow-x: auto;">
                    <table class="table table-bordered table-sm mb-0 grid-table" style="white-space: nowrap;">
                        <thead class="thead-dark">
                            {{-- Fila 1: nombres de actividades --}}
                            <tr>
                                <th class="text-center grid-sticky-no"
                                    rowspan="{{ $hasImprovement ? 2 : 1 }}"
                                    style="width:38px;">#</th>
                                <th class="grid-sticky-name"
                                    rowspan="{{ $hasImprovement ? 2 : 1 }}">Alumno</th>
                                @foreach ($activities as $activity)
                                    <th class="text-center" colspan="{{ $hasImprovement ? 2 : 1 }}"
                                        style="min-width:{{ $hasImprovement ? 160 : 85 }}px;">
                                        <div style="font-size:.8rem;">{{ $activity->name }}</div>
                                        <div style="font-size:.7rem;font-weight:normal;color:#adb5bd;">
                                            {{ $activity->activityType->name }}
                                            &nbsp;|&nbsp; Máx:&nbsp;{{ number_format($activity->max_points, 2) }}
                                        </div>
                                    </th>
                                @endforeach
                                <th class="text-center" rowspan="{{ $hasImprovement ? 2 : 1 }}"
                                    style="min-width:65px;">Total</th>
                            </tr>
                            {{-- Fila 2 de sub-encabezados (solo con mejoramiento) --}}
                            @if ($hasImprovement)
                                <tr>
                                    @foreach ($activities as $activity)
                                        <th class="text-center" style="font-size:.75rem;background:#495057;min-width:80px;">Nota</th>
                                        <th class="text-center" style="font-size:.75rem;background:#495057;min-width:80px;">Mejora</th>
                                    @endforeach
                                </tr>
                            @endif
                        </thead>
                        <tbody>
                            @foreach ($students as $idx => $student)
                                @php $isEven = $idx % 2 === 0; @endphp
                                <tr style="{{ $isEven ? '' : 'background:#f8f9fa;' }}">
                                    {{-- No. --}}
                                    <td class="text-center align-middle text-muted grid-sticky-no"
                                        style="{{ $isEven ? 'background:#fff;' : 'background:#f8f9fa;' }}">
                                        <small>{{ $idx + 1 }}</small>
                                    </td>
                                    {{-- Nombre --}}
                                    <td class="align-middle grid-sticky-name"
                                        style="{{ $isEven ? 'background:#fff;' : 'background:#f8f9fa;' }}">
                                        <small>{{ $student->user->full_full_name }}</small>
                                    </td>
                                    {{-- Actividades --}}
                                    @foreach ($activities as $activity)
                                        {{-- Nota --}}
                                        <td class="p-1 text-center align-middle">
                                            <input
                                                type="text"
                                                inputmode="decimal"
                                                x-model="grid[{{ $student->id }}][{{ $activity->id }}].score"
                                                @blur="clampScore($event, 0, {{ $activity->max_points }})"
                                                @keydown.enter.prevent="navigateDown($event)"
                                                @focus="$el.select()"
                                                {{ $gradeBook->status !== 'open' ? 'disabled' : '' }}
                                                class="form-control form-control-sm text-right"
                                                style="width:78px;"
                                                placeholder="—"
                                            />
                                        </td>
                                        {{-- Mejora --}}
                                        @if ($hasImprovement)
                                            <td class="p-1 text-center align-middle">
                                                <input
                                                    type="text"
                                                    inputmode="decimal"
                                                    x-model="grid[{{ $student->id }}][{{ $activity->id }}].improvement"
                                                    @blur="clampImprovement($event, {{ $student->id }}, {{ $activity->id }}, {{ $activity->max_points }})"
                                                    @keydown.enter.prevent="navigateDown($event)"
                                                    @focus="$el.select()"
                                                    {{ $gradeBook->status !== 'open' ? 'disabled' : '' }}
                                                    class="form-control form-control-sm text-right"
                                                    style="width:78px;background:#fffde7;"
                                                    placeholder="—"
                                                />
                                            </td>
                                        @endif
                                    @endforeach
                                    {{-- Total en tiempo real --}}
                                    <td class="text-center align-middle">
                                        <strong class="text-primary" x-text="getTotal({{ $student->id }})"></strong>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Botón guardar inferior --}}
            @if ($gradeBook->status === 'open')
                <div class="mt-2 mb-4">
                    <button @click="saveGrid()" class="btn btn-success btn-sm shadow-sm" :disabled="saving">
                        <i class="fas fa-save mr-1"></i>
                        <span x-show="!saving">Guardar todo</span>
                        <span x-show="saving" x-cloak><i class="fas fa-spinner fa-pulse"></i> Guardando…</span>
                    </button>
                </div>
            @endif
        </div>
    @endif
</div>

@push('css')
<style>
    .grid-table thead th { vertical-align: middle; }
    .grid-sticky-no   { position: sticky; left: 0;    z-index: 2; background: inherit; }
    .grid-sticky-name { position: sticky; left: 38px; z-index: 2; background: inherit; min-width: 200px; }
    .grid-table thead .grid-sticky-no,
    .grid-table thead .grid-sticky-name { z-index: 3; background: #343a40; }
    [x-cloak] { display: none !important; }
</style>
@endpush

@push('js')
<script>
document.addEventListener('livewire:initialized', () => {
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
            title: payload.message,
        });
    });
});

function gradeGrid(initialGrid, improvementType, improvementPercentage, activities) {
    return {
        grid: initialGrid,
        saving: false,

        effectiveScore(score, improvement, maxPoints) {
            score      = parseFloat(score)      || 0;
            improvement = parseFloat(improvement) || 0;
            if (improvementType === 'none' || improvement <= 0) return score;
            if (improvementType === 'full' || improvementType === 'percentage') {
                return Math.max(score, improvement);
            }
            if (improvementType === 'additive') {
                return Math.min(score + improvement, maxPoints);
            }
            return score;
        },

        getMaxImprovement(studentId, activityId, maxPoints) {
            const score = parseFloat(this.grid[studentId]?.[activityId]?.score) || 0;
            if (improvementType === 'none')       return 0;
            if (improvementType === 'full')       return maxPoints;
            if (improvementType === 'percentage') {
                return Math.round(maxPoints * improvementPercentage / 100 * 100) / 100;
            }
            if (improvementType === 'additive') {
                return Math.max(0, maxPoints - score);
            }
            return maxPoints;
        },

        clampScore(event, min, max) {
            const val = parseFloat(event.target.value);
            if (isNaN(val)) return;
            if (val < min) event.target.value = min;
            if (val > max) event.target.value = max;
        },

        clampImprovement(event, studentId, activityId, maxPoints) {
            const val = parseFloat(event.target.value);
            if (isNaN(val)) return;
            const max = this.getMaxImprovement(studentId, activityId, maxPoints);
            if (val < 0)   event.target.value = 0;
            if (val > max) event.target.value = max;
        },

        navigateDown(event) {
            const td      = event.target.closest('td');
            const tr      = td.closest('tr');
            const nextTr  = tr.nextElementSibling;
            if (!nextTr) return;
            const colIndex = Array.from(tr.children).indexOf(td);
            const nextTd   = nextTr.children[colIndex];
            if (!nextTd) return;
            const nextInput = nextTd.querySelector('input');
            if (nextInput && !nextInput.disabled) {
                nextInput.focus();
                nextInput.select();
            }
        },

        getTotal(studentId) {
            let normal = 0;
            let extra  = 0;
            for (const activity of activities) {
                const entry     = this.grid[studentId]?.[activity.id] || {};
                const effective = this.effectiveScore(entry.score, entry.improvement, activity.maxPoints);
                if (activity.isExtra) {
                    extra  += effective;
                } else {
                    normal += effective;
                }
            }
            return Math.ceil(Math.round((normal + extra) * 100) / 100);
        },

        async saveGrid() {
            this.saving = true;
            try {
                await this.$wire.saveGrid(this.grid);
            } finally {
                this.saving = false;
            }
        },
    };
}
</script>
@endpush
