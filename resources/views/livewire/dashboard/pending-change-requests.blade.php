<div wire:init="loadData" style="display: contents;">
    @if ($readyToLoad)
        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-danger shadow-sm">
                <div class="card-header">
                    <h5 class="card-title m-0 text-bold">
                        <i class="fas fa-edit mr-1 text-danger"></i> Solicitudes de Cambio Pendientes
                    </h5>
                    <div class="card-tools">
                        @can('admin.grade-change-requests.index')
                            <a href="{{ route('admin.grade-change-requests.index') }}"
                                class="btn btn-sm btn-outline-danger">
                                Ver todas <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0">
                    @if (count($recentPendingRequests) > 0)
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Profesor</th>
                                    <th>Grado / Curso</th>
                                    <th>Hace</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPendingRequests as $req)
                                    <tr>
                                        <td><small>{{ $req['professor'] }}</small></td>
                                        <td>
                                            <small>
                                                <span class="badge badge-secondary">
                                                    {{ $req['grade'] }} {{ $req['section'] }}
                                                </span>
                                                {{ $req['course'] }}
                                            </small>
                                        </td>
                                        <td><small class="text-muted">{{ $req['created_at'] }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>
                            No hay solicitudes pendientes.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
