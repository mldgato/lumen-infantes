<div wire:init="loadData">
    @if (!$readyToLoad)
        <div class="col-lg-4 mb-3">
            <div class="card shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    @else
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
                            <strong>{{ $gradeBookStatuses['open'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-sm mb-1">
                            <span><i class="fas fa-circle text-warning mr-1"></i> En revisión</span>
                            <strong>{{ $gradeBookStatuses['locked'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-sm mb-1">
                            <span><i class="fas fa-circle text-success mr-1"></i> Aprobados</span>
                            <strong>{{ $gradeBookStatuses['approved'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between text-sm">
                            <span><i class="fas fa-circle text-danger mr-1"></i> Rechazados</span>
                            <strong>{{ $gradeBookStatuses['rejected'] }}</strong>
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

                const s = $wire.gradeBookStatuses;
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
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ` ${ctx.label}: ${ctx.parsed}` } }
                        }
                    }
                });
            });
        </script>
    @endscript
</div>
