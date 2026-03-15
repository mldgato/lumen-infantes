<div>
    <div class="card card-danger card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-filter mr-1"></i> Generar Cuadro Vacío para llenar a mano
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-4 form-group mb-3">
                    <label class="text-sm mb-1">Aula <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-chalkboard"></i></span>
                        </div>
                        <select wire:model.live="filterClassroom"
                            class="form-control @error('filterClassroom') is-invalid @enderror">
                            <option value="">-- Seleccione un aula --</option>
                            @foreach ($classrooms as $classroom)
                                <option value="{{ $classroom->id }}">
                                    {{ $classroom->level->level_name }} —
                                    {{ $classroom->grade->grade_name }}
                                    {{ $classroom->section->section_name }}
                                    ({{ $classroom->year }})
                                </option>
                            @endforeach
                        </select>
                        @error('filterClassroom')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4 form-group mb-3">
                    <label class="text-sm mb-1">Curso <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-book"></i></span>
                        </div>
                        <select wire:model.live="filterPensumCourse"
                            class="form-control @error('filterPensumCourse') is-invalid @enderror"
                            {{ !$filterClassroom ? 'disabled' : '' }}>
                            <option value="">-- Seleccione un curso --</option>
                            @foreach ($courses as $pc)
                                <option value="{{ $pc->id }}">{{ $pc->course->course_name }}</option>
                            @endforeach
                        </select>
                        @error('filterPensumCourse')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <button wire:click.prevent="download" class="btn btn-danger btn-sm shadow-sm w-100"
                        wire:loading.attr="disabled" wire:target="download"
                        {{ !$filterPensumCourse ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="download">
                            <i class="fas fa-print"></i> Generar PDF
                        </span>
                        <span wire:loading wire:target="download">
                            <i class="fas fa-spinner fa-pulse"></i> Generando...
                        </span>
                    </button>
                </div>

            </div>

            <div class="alert alert-light border text-sm mb-0">
                <i class="fas fa-info-circle text-info mr-1"></i>
                Se generará un cuadro vacío con el listado de estudiantes del aula seleccionada.
                Si el cuadro ya tiene actividades configuradas se mostrarán las columnas, de lo contrario
                se generarán columnas en blanco. La unidad se deja en blanco para llenarla a mano.
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('downloadCuadroVacio', (event) => {
                    let payload = event[0] || event;
                    window.open(payload.url, '_blank');
                });
            });
        </script>
    @endpush
</div>
