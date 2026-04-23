<div wire:init="loadData">
    @if (!$readyToLoad)
        <div class="col-lg-8 mb-3">
            <div class="card shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    @else
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
    @endif

    @script
        <script>
            $wire.watch('readyToLoad', (value) => {
                if (!value) return;

                const rawData = $wire.gradeBookStatusByClassroom;
                if (!rawData || !rawData.length) return;

                new Chart(document.getElementById('gradeBookByClassroomChart'), {
                    type: 'bar',
                    data: {
                        labels: rawData.map(d => d.label),
                        datasets: [
                            { label: 'Abiertos',    data: rawData.map(d => d.open),     backgroundColor: '#6c757d', borderRadius: 3 },
                            { label: 'En revisión', data: rawData.map(d => d.locked),   backgroundColor: '#ffc107', borderRadius: 3 },
                            { label: 'Aprobados',   data: rawData.map(d => d.approved), backgroundColor: '#28a745', borderRadius: 3 },
                            { label: 'Rechazados',  data: rawData.map(d => d.rejected), backgroundColor: '#dc3545', borderRadius: 3 },
                        ]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            x: { stacked: true, grid: { display: false } },
                            y: {
                                stacked: true,
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0 },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            }
                        },
                        plugins: {
                            legend: { position: 'bottom', labels: { usePointStyle: true, padding: 15 } }
                        }
                    }
                });
            });
        </script>
    @endscript
</div>
