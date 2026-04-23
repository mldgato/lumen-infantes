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
    @endif
</div>
