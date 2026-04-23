<div wire:init="loadData" style="display: contents;">
    @if ($readyToLoad)
        <div class="col-lg-3 col-md-6 col-sm-6 mb-3">
            <div class="info-box shadow-sm">
                <span class="info-box-icon {{ $pendingCount > 0 ? 'bg-danger' : 'bg-secondary' }}">
                    <i class="fas fa-book-open"></i>
                </span>
                <div class="info-box-content">
                    <span class="info-box-text">Cuadros por Revisar</span>
                    <span class="info-box-number">
                        {{ number_format($pendingCount) }}
                        @if ($pendingCount > 0)
                            <span class="badge badge-danger ml-1" style="font-size:0.7rem">Pendientes</span>
                        @endif
                    </span>
                    <span class="progress-description text-muted text-sm">En espera de aprobación</span>
                </div>
            </div>
        </div>
    @endif
</div>
