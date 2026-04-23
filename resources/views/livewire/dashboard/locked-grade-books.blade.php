<div wire:init="loadData">
    @if ($readyToLoad)
        <div class="col-lg-6 mb-3">
            <div class="card card-outline card-warning shadow-sm">
                <div class="card-header">
                    <h5 class="card-title m-0 text-bold">
                        <i class="fas fa-book-open mr-1 text-warning"></i> Cuadros Enviados a Revisión
                    </h5>
                    <div class="card-tools">
                        @can('admin.grade-books.index')
                            <a href="{{ route('admin.grade-books.index') }}" class="btn btn-sm btn-outline-warning">
                                Ver todos <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body p-0">
                    @if (count($recentLocked) > 0)
                        <table class="table table-sm table-hover mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Grado / Curso</th>
                                    <th>Profesor</th>
                                    <th>Hace</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentLocked as $gb)
                                    <tr>
                                        <td>
                                            <small>
                                                <span class="badge badge-secondary">
                                                    {{ $gb['grade'] }} {{ $gb['section'] }} U{{ $gb['unit'] }}
                                                </span>
                                                {{ $gb['course'] }}
                                            </small>
                                        </td>
                                        <td><small>{{ $gb['professor'] }}</small></td>
                                        <td><small class="text-muted">{{ $gb['updated_at'] }}</small></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 text-gray"></i><br>
                            No hay cuadros en espera de revisión.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
