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
