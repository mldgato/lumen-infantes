<div wire:init="loadData">
    @if (!$readyToLoad)
        <div class="col-12">
            <div class="text-center py-4">
                <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            </div>
        </div>
    @else
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
    @endif
</div>
