<div>
    {{-- ============================================================
         BÚSQUEDA DE ESTUDIANTE
         ============================================================ --}}
    <div class="card card-info card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-history mr-1"></i> Historial de Calificaciones por Estudiante
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group mb-0">
                    <label class="text-sm mb-1">Buscar estudiante (mínimo 3 caracteres)</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-search"></i></span>
                        </div>
                        <input type="text" wire:model.live.debounce.400ms="search"
                            class="form-control" placeholder="Nombre, apellido o CUI..."
                            autocomplete="new-password">
                    </div>
                </div>
            </div>

            @if (strlen($search) >= 3 && $students->isNotEmpty())
                <div class="mt-3">
                    <label class="text-sm text-muted">Resultados ({{ $students->count() }}):</label>
                    <div class="list-group list-group-flush border rounded">
                        @foreach ($students as $student)
                            <button type="button"
                                class="list-group-item list-group-item-action py-2 {{ $selectedStudentId === $student->id ? 'active' : '' }}"
                                wire:click="selectStudent({{ $student->id }})">
                                <i class="fas fa-user mr-1"></i>
                                {{ $student->user->full_full_name }}
                                <small class="text-muted ml-2">{{ $student->user->cui }}</small>
                            </button>
                        @endforeach
                    </div>
                </div>
            @elseif (strlen($search) >= 3)
                <p class="text-muted text-sm mt-3 mb-0">
                    <i class="fas fa-info-circle mr-1"></i> No se encontraron estudiantes.
                </p>
            @endif
        </div>
    </div>

    {{-- ============================================================
         HISTORIAL
         ============================================================ --}}
    @if ($selectedStudent && $readyToLoad)
        <div class="d-flex justify-content-between align-items-center mb-2">
            <strong><i class="fas fa-user mr-1"></i> {{ $selectedStudent->user->full_full_name }}</strong>
            <span class="badge badge-info badge-pill">{{ $history->count() }} ciclo(s)</span>
        </div>

        @if ($history->isEmpty())
            <div class="alert alert-warning">
                <i class="fas fa-info-circle mr-1"></i>
                No se encontró historial de calificaciones para este estudiante.
            </div>
        @else
            @foreach ($history as $cycle)
                <div class="card card-outline card-info mb-3">
                    <div class="card-header py-2 d-flex justify-content-between align-items-center">
                        <span class="font-weight-bold">
                            {{ $cycle['year'] }} —
                            {{ $cycle['level'] }}
                            {{ $cycle['grade'] }}
                            {{ $cycle['section'] }}
                        </span>
                        <div>
                            <span class="badge badge-secondary mr-1">{{ $cycle['status'] }}</span>
                            @if ($cycle['average'] !== null)
                                <span class="badge badge-{{ $cycle['average'] >= 60 ? 'success' : 'danger' }}">
                                    Promedio: {{ $cycle['average'] }}
                                </span>
                            @else
                                <span class="badge badge-secondary">Sin calificaciones</span>
                            @endif
                        </div>
                    </div>
                    @if ($cycle['courses']->isNotEmpty())
                        <div class="card-body p-0">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Curso</th>
                                        <th class="text-center" style="width:120px">Promedio pond.</th>
                                        <th class="text-center" style="width:100px">Avance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cycle['courses'] as $course)
                                        <tr>
                                            <td>{{ $course['course'] }}</td>
                                            <td class="text-center font-weight-bold {{ $course['weighted'] >= 60 ? 'text-success' : 'text-danger' }}">
                                                {{ $course['weighted'] }}
                                            </td>
                                            <td class="text-center text-muted text-sm">{{ $course['covered'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            @endforeach
        @endif
    @endif
</div>
