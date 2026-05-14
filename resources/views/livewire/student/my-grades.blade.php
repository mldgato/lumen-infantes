<div>
    @if (!$classroom)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No tiene una inscripción activa para el año {{ date('Y') }}.
        </div>
    @elseif (!$pensum)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No existe un pénsum configurado para su grado en {{ date('Y') }}.
        </div>
    @elseif ($rows->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            No hay cursos oficiales registrados en su pénsum.
        </div>
    @else
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h5 class="m-0 text-bold">
                    <i class="fas fa-book-open mr-1"></i>
                    {{ $classroom->grade->grade_name }} — {{ $classroom->section->section_name }}
                    <small class="text-muted ml-2">Ciclo {{ $classroom->year }}</small>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th class="text-center" style="width:40px">No.</th>
                                <th>Curso</th>
                                @for ($u = 1; $u <= $pensum->units; $u++)
                                    <th class="text-center" style="width:70px">Unidad {{ $u }}</th>
                                @endfor
                                <th class="text-center" style="width:90px">Acumulado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $i => $row)
                                <tr>
                                    <td class="text-center text-muted">{{ $i + 1 }}</td>
                                    <td>{{ $row['course'] }}</td>
                                    @for ($u = 1; $u <= $pensum->units; $u++)
                                        <td class="text-center">
                                            @if ($row['unitScores'][$u] !== null)
                                                <span class="badge {{ $row['unitScores'][$u] < 60 ? 'badge-danger' : 'badge-success' }}">
                                                    {{ $row['unitScores'][$u] }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    @endfor
                                    <td class="text-center font-weight-bold">
                                        @if ($row['accumulated'] !== null)
                                            <span class="{{ $row['atRisk'] ? 'text-danger' : 'text-success' }}">
                                                {{ $row['accumulated'] }}
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
                Solo se muestran notas de cuadros aprobados. El acumulado es el promedio ponderado por unidad.
                <span class="ml-2">
                    <span class="badge badge-danger">Rojo</span> = nota menor a 60 &nbsp;
                    <span class="badge badge-success">Verde</span> = aprobado
                </span>
            </div>
        </div>
    @endif
</div>
