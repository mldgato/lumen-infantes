<div class="card card-warning card-outline mb-3">
    <div class="card-header">
        <h3 class="card-title">Ficha Médica</h3>
    </div>
    <form wire:submit="updateMedical">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label class="text-sm">Tipo de Sangre</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-tint text-danger"></i></span></div>
                        <select wire:model="blood_type" class="form-control">
                            <option value="">Seleccione...</option>
                            <option value="O+">O Positivo (O+)</option>
                            <option value="O-">O Negativo (O-)</option>
                            <option value="A+">A Positivo (A+)</option>
                            <option value="A-">A Negativo (A-)</option>
                            <option value="B+">B Positivo (B+)</option>
                            <option value="B-">B Negativo (B-)</option>
                            <option value="AB+">AB Positivo (AB+)</option>
                            <option value="AB-">AB Negativo (AB-)</option>
                        </select>
                    </div>
                    @error('blood_type')
                        <span class="text-danger text-xs">{{ $message }}</span>
                    @enderror
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-sm">Peso (lb)</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-weight"></i></span></div>
                        <input type="number" step="0.01" wire:model="weight" class="form-control">
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <label class="text-sm">Estatura (m)</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-ruler-vertical"></i></span></div>
                        <input type="number" step="0.01" wire:model="height" class="form-control">
                    </div>
                </div>

                <div class="col-12">
                    <hr>
                </div>

                <div class="col-md-4 form-group">
                    <div class="custom-control custom-switch mt-1">
                        <input type="checkbox" wire:model.live="takes_medication" class="custom-control-input"
                            id="takes_medication">
                        <label class="custom-control-label text-sm" for="takes_medication">¿Toma medicamentos?</label>
                    </div>
                </div>
                <div class="col-md-8 form-group">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-pills"></i></span></div>
                        <input type="text" wire:model="medication_description" class="form-control"
                            placeholder="Especifique cuáles..." @if (!$takes_medication) disabled @endif>
                    </div>
                </div>

                <div class="col-md-4 form-group">
                    <div class="custom-control custom-switch mt-1">
                        <input type="checkbox" wire:model.live="has_disease" class="custom-control-input"
                            id="has_disease">
                        <label class="custom-control-label text-sm" for="has_disease">¿Padece alguna enfermedad?</label>
                    </div>
                </div>
                <div class="col-md-8 form-group">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text"><i class="fas fa-hospital"></i></span>
                        </div>
                        <input type="text" wire:model="disease_description" class="form-control"
                            placeholder="Especifique cuál..." @if (!$has_disease) disabled @endif>
                    </div>
                    @error('disease_description')
                        <span class="text-danger text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="col-md-4 form-group">
                    <div class="custom-control custom-switch mt-1">
                        <input type="checkbox" wire:model.live="has_allergies" class="custom-control-input"
                            id="has_allergies">
                        <label class="custom-control-label text-sm" for="has_allergies">¿Tiene alergias?</label>
                    </div>
                </div>
                <div class="col-md-8 form-group">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-allergies"></i></span></div>
                        <input type="text" wire:model="allergies_description" class="form-control"
                            placeholder="Especifique cuáles..." @if (!$has_allergies) disabled @endif>
                    </div>
                </div>

                <div class="col-md-4 form-group">
                    <div class="custom-control custom-switch mt-1">
                        <input type="checkbox" wire:model.live="had_surgery" class="custom-control-input"
                            id="had_surgery">
                        <label class="custom-control-label text-sm" for="had_surgery">¿Tuvo cirugía previa?</label>
                    </div>
                </div>
                <div class="col-md-8 form-group">
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend"><span class="input-group-text"><i
                                    class="fas fa-procedures"></i></span></div>
                        <input type="text" wire:model="surgery_description" class="form-control"
                            placeholder="Especifique cuáles..." @if (!$had_surgery) disabled @endif>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-warning btn-sm" wire:loading.attr="disabled"
                wire:target="updateMedical">
                <span wire:loading.remove wire:target="updateMedical">
                    Guardar <i class="fas fa-save ml-1"></i>
                </span>
                <span wire:loading wire:target="updateMedical">
                    Guardando <i class="fas fa-spinner fa-pulse ml-1"></i>
                </span>
            </button>
        </div>
    </form>
</div>
