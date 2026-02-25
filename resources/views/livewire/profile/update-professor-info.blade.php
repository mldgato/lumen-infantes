<div class="card card-info card-outline mb-3">
    <div class="card-header">
        <h3 class="card-title">Información Profesional (Docente)</h3>
    </div>
    <form wire:submit="updateProfessor">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label class="text-sm">Fecha de Contratación <span class="text-danger">*</span></label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-calendar-alt"></i></span></div>
                        <input type="date" wire:model="hire_date"
                            class="form-control @error('hire_date') is-invalid @enderror">
                    </div>
                    @error('hire_date')
                        <span class="text-danger text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-sm">NIT</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-id-badge"></i></span></div>
                        <input type="text" wire:model="nit" class="form-control">
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-sm">Afiliación IGSS</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-hospital-user"></i></span></div>
                        <input type="text" wire:model="igss_affiliation" class="form-control">
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-sm">Cédula Docente</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-address-card"></i></span></div>
                        <input type="text" wire:model="teaching_cedula" class="form-control">
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-sm">Título Principal</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-graduation-cap"></i></span></div>
                        <input type="text" wire:model="title" class="form-control">
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-sm">Licenciatura</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-university"></i></span></div>
                        <input type="text" wire:model="bachelor_degree" class="form-control">
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-sm">Nombre del Cónyuge</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-user-friends"></i></span></div>
                        <input type="text" wire:model="spouse_name" class="form-control">
                    </div>
                </div>
                <div class="col-md-6 form-group">
                    <label class="text-sm">Teléfono del Cónyuge</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-phone"></i></span></div>
                        <input type="text" wire:model="spouse_phone" class="form-control">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-info btn-sm" wire:loading.attr="disabled"
                wire:target="updateProfessor">
                <span wire:loading.remove wire:target="updateProfessor">
                    Guardar <i class="fas fa-save ml-1"></i>
                </span>
                <span wire:loading wire:target="updateProfessor">
                    Guardando <i class="fas fa-spinner fa-pulse ml-1"></i>
                </span>
            </button>
        </div>
    </form>
</div>
