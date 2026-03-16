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
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon {{ $pendingGradeBooks > 0 ? 'bg-danger' : 'bg-secondary' }}">
                        <i class="fas fa-book-open"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Cuadros por Revisar</span>
                        <span class="info-box-number">
                            {{ number_format($pendingGradeBooks) }}
                            @if ($pendingGradeBooks > 0)
                                <span class="badge badge-danger ml-1" style="font-size:0.7rem">Pendientes</span>
                            @endif
                        </span>
                        <span class="progress-description text-muted text-sm">En espera de aprobación</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- CHARTS --}}
        <div class="row">
            <div class="col-lg-8 mb-3">
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

            <div class="col-lg-4 mb-3">
                <div class="card card-outline card-success shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0 text-bold">
                            <i class="fas fa-chart-pie mr-1 text-success"></i> Estado de Cuadros
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column justify-content-center">
                        <canvas id="gradeBookStatusChart" height="180"></canvas>
                        <div class="mt-3">
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span><i class="fas fa-circle text-secondary mr-1"></i> Abiertos</span>
                                <strong>{{ $gradeBookStatusChart['open'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span><i class="fas fa-circle text-warning mr-1"></i> En revisión</span>
                                <strong>{{ $gradeBookStatusChart['locked'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm mb-1">
                                <span><i class="fas fa-circle text-success mr-1"></i> Aprobados</span>
                                <strong>{{ $gradeBookStatusChart['approved'] }}</strong>
                            </div>
                            <div class="d-flex justify-content-between text-sm">
                                <span><i class="fas fa-circle text-danger mr-1"></i> Rechazados</span>
                                <strong>{{ $gradeBookStatusChart['rejected'] }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- RECENT ACTIVITY --}}
        <div class="row">
            <div class="col-lg-6 mb-3">
                <div class="card card-outline card-danger shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0 text-bold">
                            <i class="fas fa-edit mr-1 text-danger"></i> Solicitudes de Cambio Pendientes
                        </h5>
                        <div class="card-tools">
                            @can('admin.grade-change-requests.index')
                                <a href="{{ route('admin.grade-change-requests.index') }}"
                                    class="btn btn-sm btn-outline-danger">
                                    Ver todas <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if (count($recentPendingRequests) > 0)
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Profesor</th>
                                        <th>Grado / Curso</th>
                                        <th>Hace</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentPendingRequests as $req)
                                        <tr>
                                            <td><small>{{ $req['professor'] }}</small></td>
                                            <td>
                                                <small>
                                                    <span class="badge badge-secondary">{{ $req['grade'] }}
                                                        {{ $req['section'] }}</span>
                                                    {{ $req['course'] }}
                                                </small>
                                            </td>
                                            <td><small class="text-muted">{{ $req['created_at'] }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                                No hay solicitudes pendientes.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-6 mb-3">
                <div class="card card-outline card-warning shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title m-0 text-bold">
                            <i class="fas fa-book-open mr-1 text-warning"></i> Cuadros Enviados a Revisión
                        </h5>
                        <div class="card-tools">
                            @can('admin.grade-books.index')
                                <a href="{{ route('admin.grade-books.index') }}" class="btn btn-sm btn-outline-warning">
                                    Ver todos <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if (count($recentGradeBooks) > 0)
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Grado / Curso</th>
                                        <th>Profesor</th>
                                        <th>Hace</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentGradeBooks as $gb)
                                        <tr>
                                            <td>
                                                <small>
                                                    <span class="badge badge-secondary">{{ $gb['grade'] }}
                                                        {{ $gb['section'] }} U{{ $gb['unit'] }}</span>
                                                    {{ $gb['course'] }}
                                                </small>
                                            </td>
                                            <td><small>{{ $gb['professor'] }}</small></td>
                                            <td><small class="text-muted">{{ $gb['updated_at'] }}</small></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 text-gray"></i><br>
                                No hay cuadros en espera de revisión.
                            </div>
                        @endif
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
