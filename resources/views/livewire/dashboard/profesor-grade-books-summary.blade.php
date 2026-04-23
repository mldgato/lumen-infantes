<div wire:init="loadData">
    @if ($readyToLoad)
        @php
            $grandTotal = $totalOpen + $totalLocked + $totalApproved + $totalRejected;
        @endphp
        <div class="col-lg-4 mb-3">
            <div class="card card-outline card-success shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title m-0 text-bold">
                        <i class="fas fa-info-circle mr-1 text-success"></i> Resumen
                    </h5>
                </div>
                <div class="card-body">
                    @foreach ([
                        ['Abiertos',    $totalOpen,     'secondary', 'folder-open'],
                        ['En revisión', $totalLocked,   'warning',   'clock'],
                        ['Aprobados',   $totalApproved, 'success',   'check-circle'],
                        ['Rechazados',  $totalRejected, 'danger',    'times-circle'],
                    ] as [$label, $count, $color, $icon])
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
    @endif
</div>
