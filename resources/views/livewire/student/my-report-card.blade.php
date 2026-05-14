<div>
    @if (!$enrollment)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle mr-1"></i>
            No tiene una inscripción activa para el año {{ date('Y') }}.
        </div>
    @elseif ($availableUnits->isEmpty())
        <div class="alert alert-info">
            <i class="fas fa-info-circle mr-1"></i>
            Aún no hay unidades con cuadros aprobados disponibles para imprimir.
        </div>
    @else
        <div class="card card-outline card-danger" style="max-width: 480px; margin: 0 auto;">
            <div class="card-header">
                <h5 class="m-0 text-bold">
                    <i class="fas fa-file-pdf mr-1"></i>
                    Imprimir Boleta de Calificaciones
                </h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="text-sm mb-1">Seleccione la unidad <span class="text-danger">*</span></label>
                    <select wire:model.live="selectedUnit" class="form-control">
                        <option value="">-- Seleccione una unidad --</option>
                        @foreach ($availableUnits as $unit)
                            <option value="{{ $unit }}">Unidad {{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-footer">
                @if ($selectedUnit && $classroomId)
                    <a href="{{ route('student.report-card.print', ['unit' => $selectedUnit]) }}"
                        target="_blank"
                        class="btn btn-danger btn-sm shadow-sm">
                        <i class="fas fa-print mr-1"></i> Ver boleta en PDF
                    </a>
                @else
                    <button class="btn btn-danger btn-sm shadow-sm" disabled>
                        <i class="fas fa-print mr-1"></i> Ver boleta en PDF
                    </button>
                @endif
            </div>
        </div>
    @endif
</div>
