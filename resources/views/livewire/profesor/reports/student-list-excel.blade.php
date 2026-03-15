<div>
    <div class="card card-success card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-filter mr-1"></i> Generar Listado de Estudiantes en Excel
            </h5>
        </div>
        <div class="card-body">
            <div class="row align-items-end">

                <div class="col-md-6 form-group mb-3">
                    <label class="text-sm mb-1">Aula <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-chalkboard"></i></span>
                        </div>
                        <select wire:model.live="selectedClassroom"
                            class="form-control @error('selectedClassroom') is-invalid @enderror">
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
                        @error('selectedClassroom')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-3 form-group mb-3">
                    <button wire:click.prevent="download" class="btn btn-success btn-sm shadow-sm w-100"
                        wire:loading.attr="disabled" wire:target="download" {{ !$selectedClassroom ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="download">
                            <i class="fas fa-file-excel"></i> Generar Excel
                        </span>
                        <span wire:loading wire:target="download">
                            <i class="fas fa-spinner fa-pulse"></i> Generando...
                        </span>
                    </button>
                </div>

            </div>

            <div class="alert alert-light border text-sm mb-0">
                <i class="fas fa-info-circle text-info mr-1"></i>
                Se generará un listado Excel con todos los estudiantes activos del aula seleccionada,
                con una columna de observaciones para llenar.
            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('downloadStudentListExcel', (event) => {
                    let payload = event[0] || event;
                    window.location.href = payload.url;
                });
            });
        </script>
    @endpush
</div>
