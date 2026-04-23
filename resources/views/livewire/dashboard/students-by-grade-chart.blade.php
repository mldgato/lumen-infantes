<div wire:init="loadData">
    @if (!$readyToLoad)
        <div class="col-lg-8 col-12 mb-3">
            <div class="card shadow-sm">
                <div class="card-body text-center py-4">
                    <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
                </div>
            </div>
        </div>
    @else
        <div class="col-lg-8 col-12 mb-3">
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
    @endif

    @script
        <script>
            $wire.watch('readyToLoad', (value) => {
                if (!value) return;

                new Chart(document.getElementById('studentsByGradeChart'), {
                    type: 'bar',
                    data: {
                        labels: Object.keys($wire.studentsByGrade),
                        datasets: [{
                            label: 'Estudiantes',
                            data: Object.values($wire.studentsByGrade),
                            backgroundColor: 'rgba(54, 116, 181, 0.75)',
                            borderColor: 'rgba(54, 116, 181, 1)',
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: { display: false },
                            tooltip: { callbacks: { label: ctx => ` ${ctx.parsed.y} estudiantes` } }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: { stepSize: 1, precision: 0 },
                                grid: { color: 'rgba(0,0,0,0.05)' }
                            },
                            x: { grid: { display: false } }
                        }
                    }
                });
            });
        </script>
    @endscript
</div>
