<div wire:init="loadData" style="display: contents;">
    @if (!$readyToLoad)
        <div class="col-12">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            </div>
        </div>
    @else
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-info"><i class="fas fa-book"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Mis Cursos</span>
                    <span class="info-box-number">{{ $totalCourses ?? '—' }}</span>
                    <span class="progress-description text-muted text-sm">Año {{ date('Y') }}</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="info-box shadow-sm">
                <span class="info-box-icon {{ ($coursesAtRisk ?? 0) > 0 ? 'bg-danger' : 'bg-success' }}">
                    <i class="fas fa-exclamation-triangle"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Cursos en Riesgo</span>
                    <span class="info-box-number">{{ $coursesAtRisk ?? '—' }}</span>
                    <span class="progress-description text-muted text-sm">Nota acumulada &lt; 60</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="info-box shadow-sm">
                @php
                    $attColor = $attendancePercentage === null ? 'bg-secondary'
                        : ($attendancePercentage < 80 ? 'bg-danger' : 'bg-success');
                @endphp
                <span class="info-box-icon {{ $attColor }}"><i class="fas fa-user-check"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">% Asistencia</span>
                    <span class="info-box-number">
                        {{ $attendancePercentage !== null ? $attendancePercentage . '%' : '—' }}
                    </span>
                    <span class="progress-description text-muted text-sm">Global este año</span>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="info-box shadow-sm">
                <span class="info-box-icon bg-warning"><i class="fas fa-check-double"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">Unidades Publicadas</span>
                    <span class="info-box-number">{{ $approvedUnits ?? '—' }}</span>
                    <span class="progress-description text-muted text-sm">Cuadros aprobados</span>
                </div>
            </div>
        </div>
    @endif
</div>
