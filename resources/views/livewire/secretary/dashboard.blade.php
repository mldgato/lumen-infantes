<div wire:init="loadData">

    @if (!$readyToLoad)
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3 text-muted">Cargando información del panel...</p>
        </div>
    @else
        {{-- KPI CARDS --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info"><i class="fas fa-user-graduate"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Estudiantes Activos</span>
                        <span class="info-box-number">{{ number_format($totalStudents) }}</span>
                        <span class="progress-description text-muted text-sm">Año {{ date('Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success"><i class="fas fa-chalkboard-teacher"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Profesores Activos</span>
                        <span class="info-box-number">{{ number_format($totalProfessors) }}</span>
                        <span class="progress-description text-muted text-sm">Con asignaciones</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-warning"><i class="fas fa-chalkboard"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Aulas Activas</span>
                        <span class="info-box-number">{{ number_format($totalClassrooms) }}</span>
                        <span class="progress-description text-muted text-sm">Año {{ date('Y') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CUMPLEAÑOS --}}
        <div class="row mb-3">

            {{-- Estudiantes cumpleañeros del mes --}}
            <div class="col-lg-8 col-md-12 mb-3 mb-lg-0">
                <div class="card card-outline card-warning shadow-sm h-100">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title m-0 text-bold flex-grow-1">
                            <i class="fas fa-birthday-cake mr-1 text-warning"></i>
                            Cumpleañeros de {{ ucfirst(\Carbon\Carbon::now()->locale('es')->isoFormat('MMMM')) }}
                            <span class="badge badge-warning ml-1">Estudiantes</span>
                        </h5>
                        <span class="badge badge-light text-muted">{{ count($birthdayStudents) }} este mes</span>
                    </div>
                    <div class="card-body p-2" style="overflow-x: auto;">
                        @if (empty($birthdayStudents))
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                <small>No hay estudiantes cumpleañeros este mes.</small>
                            </div>
                        @else
                            <div class="d-flex flex-wrap p-1" style="gap: 0.5rem;">
                                @foreach ($birthdayStudents as $student)
                                    <div class="d-flex flex-column align-items-center text-center p-2 rounded"
                                        style="width: 88px; min-width: 80px; position: relative;
                                        background-color: {{ $student['is_today'] ? 'rgba(255,193,7,0.15)' : '#f8f9fa' }};
                                        border: 1px solid {{ $student['is_today'] ? '#ffc107' : '#dee2e6' }};">

                                        @if ($student['is_today'])
                                            <span
                                                style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
                                                 background: #ffc107; color: #212529; font-size: 0.58rem;
                                                 font-weight: 700; padding: 1px 5px; border-radius: 8px; white-space: nowrap;">
                                                🎂 ¡Hoy!
                                            </span>
                                        @endif

                                        <img src="{{ $student['image'] }}" alt="{{ $student['initials'] }}"
                                            class="img-circle"
                                            style="width: 48px; height: 48px; object-fit: cover; margin-bottom: 5px;
                                            border: 2px solid {{ $student['is_today'] ? '#ffc107' : '#ced4da' }};">

                                        <small class="font-weight-bold text-dark d-block"
                                            style="font-size: 0.68rem; line-height: 1.3; word-break: break-word;">
                                            {{ $student['name'] }}
                                        </small>

                                        <span class="badge mt-1"
                                            style="font-size: 0.62rem;
                                             background-color: {{ $student['is_today'] ? '#ffc107' : '#6c757d' }};
                                             color: {{ $student['is_today'] ? '#212529' : '#fff' }};">
                                            Día {{ $student['day'] }}
                                        </span>

                                        <small class="text-muted" style="font-size: 0.62rem;">
                                            {{ $student['age'] }} años
                                        </small>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Próximos cumpleaños del personal --}}
            <div class="col-lg-4 col-md-12">
                <div class="card card-outline card-info shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0 text-bold">
                            <i class="fas fa-bell mr-1 text-info"></i> Próximos Cumpleaños
                            <span class="badge badge-info ml-1">Personal</span>
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        @if (empty($upcomingBirthdays))
                            <div class="text-center py-4 text-muted">
                                <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                                <small>Sin información disponible.</small>
                            </div>
                        @else
                            <ul class="list-unstyled m-0">
                                @foreach ($upcomingBirthdays as $person)
                                    <li class="d-flex align-items-center px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                                        style="{{ $person['is_today'] ? 'background-color: rgba(23,162,184,0.08);' : '' }}">

                                        <img src="{{ $person['image'] }}" alt="{{ $person['initials'] }}"
                                            class="img-circle mr-2 flex-shrink-0"
                                            style="width: 40px; height: 40px; object-fit: cover;
                                            border: 2px solid {{ $person['is_today'] ? '#17a2b8' : '#dee2e6' }};">

                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <div class="font-weight-bold text-dark text-truncate"
                                                style="font-size: 0.82rem;">
                                                {{ $person['name'] }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.68rem;">
                                                <i class="fas fa-tag mr-1"></i>{{ $person['role'] }}
                                            </div>
                                        </div>

                                        <div class="text-right ml-2 flex-shrink-0">
                                            @if ($person['is_today'])
                                                <span class="badge badge-info">🎂 ¡Hoy!</span>
                                            @else
                                                <div class="font-weight-bold text-dark" style="font-size: 0.78rem;">
                                                    {{ $person['day'] }} {{ $person['month'] }}
                                                </div>
                                                <div class="text-muted" style="font-size: 0.65rem;">
                                                    en {{ $person['days_until'] }}d
                                                </div>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

        </div>

        {{-- CHARTS --}}
        <div class="row">
            <div class="col-lg-12 mb-3">
                <div class="card card-outline card-primary shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0 text-bold">
                            <i class="fas fa-chart-bar mr-1 text-primary"></i> Estudiantes por Grado
                        </h5>
                    </div>
                    <div class="card-body">
                        <canvas id="studentsByGradeChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @script
        <script>
            $wire.watch('readyToLoad', (value) => {
                if (!value) return;

                const gradeLabels = Object.keys($wire.studentsByGrade);
                const gradeData = Object.values($wire.studentsByGrade);

                new Chart(document.getElementById('studentsByGradeChart'), {
                    type: 'bar',
                    data: {
                        labels: gradeLabels,
                        datasets: [{
                            label: 'Estudiantes',
                            data: gradeData,
                            backgroundColor: 'rgba(54, 116, 181, 0.75)',
                            borderColor: 'rgba(54, 116, 181, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.parsed.y} estudiantes`
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                const s = $wire.gradeBookStatusChart;
                new Chart(document.getElementById('gradeBookStatusChart'), {
                    type: 'doughnut',
                    data: {
                        labels: ['Abiertos', 'En revisión', 'Aprobados', 'Rechazados'],
                        datasets: [{
                            data: [s.open, s.locked, s.approved, s.rejected],
                            backgroundColor: ['#6c757d', '#ffc107', '#28a745', '#dc3545'],
                            borderWidth: 2,
                            borderColor: '#fff',
                        }]
                    },
                    options: {
                        responsive: true,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                callbacks: {
                                    label: ctx => ` ${ctx.label}: ${ctx.parsed}`
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endscript

</div>
