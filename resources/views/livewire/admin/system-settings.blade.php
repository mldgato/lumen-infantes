<div>
    <div class="row">
        <div class="col-lg-8">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-plus mr-2"></i> Módulo de Inscripciones
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        Seleccione cómo se gestionará la inscripción de <strong>estudiantes nuevos</strong> en el sistema.
                    </p>

                    <div class="form-group mb-0">
                        <div class="custom-control custom-radio mb-4">
                            <input type="radio" id="mode_direct" wire:model.live="enrollmentMode"
                                value="direct" class="custom-control-input">
                            <label class="custom-control-label" for="mode_direct">
                                <strong>Inscripción Directa</strong>
                                <div class="text-muted small mt-1">
                                    El personal del instituto inscribe manualmente a cada estudiante nuevo desde el módulo de Inscripciones.
                                    El formulario público de admisiones permanece desactivado.
                                </div>
                            </label>
                        </div>

                        <div class="custom-control custom-radio">
                            <input type="radio" id="mode_admissions" wire:model.live="enrollmentMode"
                                value="admissions" class="custom-control-input">
                            <label class="custom-control-label" for="mode_admissions">
                                <strong>Módulo de Admisiones</strong>
                                <div class="text-muted small mt-1">
                                    Los padres de familia completan el formulario público de admisiones en línea.
                                    El personal revisa las solicitudes desde el módulo de Admisiones y procesa la inscripción.
                                </div>
                            </label>
                        </div>
                    </div>

                    @if ($enrollmentMode === 'admissions')
                        <div class="alert alert-info mt-4 mb-0">
                            <i class="fas fa-info-circle mr-1"></i>
                            El formulario público de admisiones está <strong>activo</strong>. Enlace público:
                            <a href="{{ route('admissions.form') }}" target="_blank">
                                {{ route('admissions.form') }}
                            </a>
                        </div>
                    @endif

                    @error('enrollmentMode')
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>
                <div class="card-footer">
                    <button wire:click="save" wire:loading.attr="disabled" class="btn btn-primary">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save mr-1"></i> Guardar configuración
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin mr-1"></i> Guardando...
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card card-secondary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-question-circle mr-2"></i> ¿Cuál elegir?</h3>
                </div>
                <div class="card-body">
                    <p class="text-sm mb-3">
                        <i class="fas fa-arrow-right text-primary mr-1"></i>
                        <strong>Inscripción Directa</strong> es ideal cuando el proceso de inscripción
                        es presencial y el personal registra los datos directamente en el sistema.
                    </p>
                    <p class="text-sm mb-0">
                        <i class="fas fa-arrow-right text-success mr-1"></i>
                        <strong>Módulo de Admisiones</strong> es ideal cuando los padres pueden
                        registrar a sus hijos de forma remota antes de ser admitidos formalmente.
                        Las solicitudes quedan pendientes hasta que el personal las revise y apruebe.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
