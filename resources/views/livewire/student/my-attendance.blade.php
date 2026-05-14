<div>
    @if (!$classroom)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No tiene una inscripción activa para el año {{ date('Y') }}.
        </div>
    @elseif ($rows->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Aún no hay registros de asistencia para sus cursos.
        </div>
    @else
        <div class="card card-outline card-success">
            <div class="card-header">
                <h5 class="m-0 text-bold">
                    <i class="fas fa-user-check mr-1"></i>
                    Historial de Asistencia — {{ date('Y') }}
                    <small class="text-muted ml-2">
                        {{ $classroom->grade->grade_name }} {{ $classroom->section->section_name }}
                    </small>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Curso</th>
                                <th class="text-center" style="width:80px">Clases</th>
                                <th class="text-center" style="width:80px">Presente</th>
                                <th class="text-center" style="width:80px">Ausente</th>
                                <th class="text-center" style="width:110px">% Asistencia</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                                <tr>
                                    <td>{{ $row['course'] }}</td>
                                    <td class="text-center">{{ $row['total'] }}</td>
                                    <td class="text-center text-success font-weight-bold">{{ $row['present'] }}</td>
                                    <td class="text-center text-danger font-weight-bold">{{ $row['absent'] }}</td>
                                    <td class="text-center">
                                        @if ($row['percentage'] !== null)
                                            <span class="badge {{ $row['atRisk'] ? 'badge-danger' : 'badge-success' }} px-2">
                                                {{ $row['percentage'] }}%
                                            </span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer text-muted text-sm">
                <i class="fas fa-info-circle mr-1"></i>
                <span class="badge badge-danger">Rojo</span> = asistencia menor al 80%
            </div>
        </div>
    @endif
</div>
