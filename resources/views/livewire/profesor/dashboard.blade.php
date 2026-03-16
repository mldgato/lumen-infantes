<div wire:init="loadData">

    @if (!$readyToLoad)
        <div class="text-center py-5">
            <i class="fas fa-spinner fa-spin fa-3x text-primary"></i>
            <p class="mt-3 text-muted">Cargando tu panel...</p>
        </div>
    @else
        {{-- KPI CARDS --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-info"><i class="fas fa-chalkboard"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mis Aulas</span>
                        <span class="info-box-number">{{ $totalClassrooms }}</span>
                        <span class="progress-description text-muted text-sm">Año {{ date('Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Cuadros Aprobados</span>
                        <span class="info-box-number">{{ $approvedGradeBooks }}</span>
                        <span class="progress-description text-muted text-sm">Este año</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon {{ $pendingGradeBooks > 0 ? 'bg-warning' : 'bg-secondary' }}">
                        <i class="fas fa-clock"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">En Revisión</span>
                        <span class="info-box-number">{{ $pendingGradeBooks }}</span>
                        <span class="progress-description text-muted text-sm">Esperando aprobación</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
                <div class="info-box shadow-sm">
                    <span class="info-box-icon {{ $pendingChangeRequests > 0 ? 'bg-danger' : 'bg-secondary' }}">
                        <i class="fas fa-edit"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Cambios Pendientes</span>
                        <span class="info-box-number">{{ $pendingChangeRequests }}</span>
                        <span class="progress-description text-muted text-sm">Solicitudes enviadas</span>
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
                            <i class="fas fa-chart-bar mr-1 text-primary"></i> Estado de Cuadros por Aula
                        </h5>
                    </div>
                    <div class="card-body">
                        @if (count($gradeBookStatusByClassroom) > 0)
                            <canvas id="gradeBookByClassroomChart" height="120"></canvas>
                        @else
                            <div class="text-center text-muted py-4">
                                No hay cuadros registrados aún.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-3">
                <div class="card card-outline card-success shadow-sm h-100">
                    <div class="card-header">
                        <h5 class="card-title m-0 text-bold">
                            <i class="fas fa-info-circle mr-1 text-success"></i> Resumen
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $totalOpen = collect($gradeBookStatusByClassroom)->sum('open');
                            $totalLocked = collect($gradeBookStatusByClassroom)->sum('locked');
                            $totalApproved = collect($gradeBookStatusByClassroom)->sum('approved');
                            $totalRejected = collect($gradeBookStatusByClassroom)->sum('rejected');
                            $grandTotal = $totalOpen + $totalLocked + $totalApproved + $totalRejected;
                        @endphp

                        @foreach ([['Abiertos', $totalOpen, 'secondary', 'folder-open'], ['En revisión', $totalLocked, 'warning', 'clock'], ['Aprobados', $totalApproved, 'success', 'check-circle'], ['Rechazados', $totalRejected, 'danger', 'times-circle']] as [$label, $count, $color, $icon])
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge badge-{{ $color }} mr-2 p-2">
                                        <i class="fas fa-{{ $icon }}"></i>
                                    </span>
                                    <span class="text-sm">{{ $label }}</span>
                                </div>
                                <div class="d-flex align-items-center">
                                    <strong class="mr-2">{{ $count }}</strong>
                                    @if ($grandTotal > 0)
                                        <div class="progress" style="width:60px;height:6px;background:#eee;">
                                            <div class="progress-bar bg-{{ $color }}"
                                                style="width:{{ round(($count / $grandTotal) * 100) }}%"></div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <hr>
                        <div class="d-flex justify-content-between">
                            <span class="text-sm text-muted">Total de cuadros</span>
                            <strong>{{ $grandTotal }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ACTIONABLE GRADE BOOKS --}}
        @if (count($actionableGradeBooks) > 0)
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card card-outline card-warning shadow-sm">
                        <div class="card-header">
                            <h5 class="card-title m-0 text-bold">
                                <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                                Cuadros que Requieren Atención
                            </h5>
                            <div class="card-tools">
                                @can('profesor.grade-books.index')
                                    <a href="{{ route('profesor.grade-books.index') }}"
                                        class="btn btn-sm btn-outline-warning">
                                        Ver mis cuadros <i class="fas fa-arrow-right ml-1"></i>
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Grado</th>
                                        <th>Sección</th>
                                        <th>Curso</th>
                                        <th class="text-center">Unidad</th>
                                        <th class="text-center">Estado</th>
                                        <th>Motivo rechazo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($actionableGradeBooks as $gb)
                                        <tr>
                                            <td><small>{{ $gb['grade'] }}</small></td>
                                            <td><small>{{ $gb['section'] }}</small></td>
                                            <td><small>{{ $gb['course'] }}</small></td>
                                            <td class="text-center">
                                                <span class="badge badge-secondary">U{{ $gb['unit'] }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($gb['status'] === 'open')
                                                    <span class="badge badge-secondary">Abierto</span>
                                                @else
                                                    <span class="badge badge-danger">Rechazado</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($gb['reason'])
                                                    <small
                                                        class="text-danger">{{ Str::limit($gb['reason'], 50) }}</small>
                                                @else
                                                    <small class="text-muted">—</small>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    @endif

    @script
        <script>
            $wire.watch('readyToLoad', (value) => {
                if (!value) return;

                const rawData = $wire.gradeBookStatusByClassroom;
                if (!rawData || !rawData.length) return;

                const labels = rawData.map(d => d.label);
                const open = rawData.map(d => d.open);
                const locked = rawData.map(d => d.locked);
                const approved = rawData.map(d => d.approved);
                const rejected = rawData.map(d => d.rejected);

                new Chart(document.getElementById('gradeBookByClassroomChart'), {
                    type: 'bar',
                    data: {
                        labels,
                        datasets: [{
                                label: 'Abiertos',
                                data: open,
                                backgroundColor: '#6c757d',
                                borderRadius: 3
                            },
                            {
                                label: 'En revisión',
                                data: locked,
                                backgroundColor: '#ffc107',
                                borderRadius: 3
                            },
                            {
                                label: 'Aprobados',
                                data: approved,
                                backgroundColor: '#28a745',
                                borderRadius: 3
                            },
                            {
                                label: 'Rechazados',
                                data: rejected,
                                backgroundColor: '#dc3545',
                                borderRadius: 3
                            },
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: {
                                stacked: true,
                                grid: {
                                    display: false
                                }
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1,
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0,0,0,0.05)'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    usePointStyle: true,
                                    padding: 15
                                }
                            }
                        }
                    }
                });
            });
        </script>
    @endscript

</div>
