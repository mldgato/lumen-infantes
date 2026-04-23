<div wire:init="loadData" style="display: contents;">
    @if ($readyToLoad && count($actionableGradeBooks) > 0)
        <div class="col-12 mb-3">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h5 class="card-title m-0 text-bold">
                        <i class="fas fa-exclamation-triangle mr-1 text-warning"></i>
                        Cuadros que Requieren Atención
                    </h5>
                    <div class="card-tools">
                        @can('profesor.grade-books.index')
                            <a href="{{ route('profesor.grade-books.index') }}" class="btn btn-sm btn-outline-warning">
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
                                            <small class="text-danger">{{ Str::limit($gb['reason'], 50) }}</small>
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
    @endif
</div>
