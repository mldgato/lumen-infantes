<div>
    <div class="card card-success card-outline">
        <div class="card-header">
            <h5 class="m-0 text-bold">
                <i class="fas fa-filter mr-1"></i> Generar Reporte de Profesores y Cursos Asignados
            </h5>
        </div>
        <div class="card-body">
            <div class="row">

                <div class="col-md-4 form-group mb-3">
                    <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                        </div>
                        <select wire:model.live="filterYear"
                            class="form-control @error('filterYear') is-invalid @enderror">
                            <option value="">-- Año --</option>
                            @foreach ($years as $year)
                                <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach
                        </select>
                        @error('filterYear')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12 text-right">
                    <button wire:click.prevent="download" class="btn btn-success btn-sm shadow-sm"
                        wire:loading.attr="disabled" wire:target="download" {{ !$filterYear ? 'disabled' : '' }}>
                        <span wire:loading.remove wire:target="download">
                            <i class="fas fa-file-excel"></i> Generar Excel
                        </span>
                        <span wire:loading wire:target="download">
                            <i class="fas fa-spinner fa-pulse"></i> Generando...
                        </span>
                    </button>
                </div>

            </div>
        </div>
    </div>

    @push('js')
        <script>
            document.addEventListener('livewire:init', () => {
                Livewire.on('downloadProfessorCoursesExcel', (event) => {
                    let payload = event[0] || event;
                    window.location.href = payload.url;
                });
            });
        </script>
    @endpush
</div>
