<div>
    @if (! $this->isAdmissionsOpen)
        <div class="admission-card">
            <div class="admission-header">
                <h1><i class="fas fa-door-closed mr-2"></i> Admisiones no disponibles</h1>
                <p>{{ config('app.institution_name', 'EduCheck') }}</p>
            </div>
            <div class="admission-body text-center py-5">
                <i class="fas fa-calendar-times fa-4x text-secondary mb-4"></i>
                <h5 class="text-dark mb-3">El proceso de admisiones no está activo en este momento.</h5>
                <p class="text-muted">
                    Comuníquese directamente con el instituto para obtener información sobre el proceso de inscripción.
                </p>
            </div>
            <div class="admission-footer">
                &copy; {{ date('Y') }} {{ config('app.institution_name', 'EduCheck') }}
            </div>
        </div>
    @else
        <div class="admission-card">
            {{-- Encabezado --}}
            <div class="admission-header">
                <img src="{{ asset(config('app.institution_logo_img', 'vendor/adminlte/dist/img/Escudo.png')) }}"
                    alt="Logo" class="admission-logo mb-3">
                <h1>Proceso de Admisiones {{ now()->month <= 6 ? now()->year : now()->addYear()->year }}</h1>
                <p>{{ config('app.institution_name', 'EduCheck') }}</p>
            </div>

            <div class="admission-body">
                <p class="text-justify mb-4">
                    <strong>Estimado Padre de Familia:</strong> Bienvenido. Complete el siguiente formulario para iniciar
                    el proceso de admisión. Toda comunicación oficial se realizará al correo electrónico del encargado indicado.
                </p>

                {{-- ═══════════════════════════════════════
                     SECCIÓN 1 — Datos del Alumno
                ════════════════════════════════════════ --}}
                <fieldset class="form-section mb-4">
                    <legend><i class="fas fa-user-graduate mr-1"></i> Datos del Alumno</legend>

                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Primer Nombre <span class="req">*</span></label>
                                <input type="text" wire:model="studentFirstName"
                                    class="form-control @error('studentFirstName') is-invalid @enderror">
                                @error('studentFirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Segundo Nombre</label>
                                <input type="text" wire:model="studentSecondName"
                                    class="form-control @error('studentSecondName') is-invalid @enderror">
                                @error('studentSecondName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Primer Apellido <span class="req">*</span></label>
                                <input type="text" wire:model="studentFirstSurname"
                                    class="form-control @error('studentFirstSurname') is-invalid @enderror">
                                @error('studentFirstSurname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group">
                                <label>Segundo Apellido</label>
                                <input type="text" wire:model="studentSecondSurname"
                                    class="form-control @error('studentSecondSurname') is-invalid @enderror">
                                @error('studentSecondSurname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group">
                                <label>Fecha de Nacimiento <span class="req">*</span></label>
                                <input type="date" wire:model="studentBirthdate"
                                    class="form-control @error('studentBirthdate') is-invalid @enderror">
                                @error('studentBirthdate') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group">
                                <label>Religión</label>
                                <input type="text" wire:model="studentReligion"
                                    class="form-control @error('studentReligion') is-invalid @enderror"
                                    placeholder="Ej. Católica">
                                @error('studentReligion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group">
                                <label>Colegio o Instituto Anterior</label>
                                <input type="text" wire:model="studentPreviousSchool"
                                    class="form-control @error('studentPreviousSchool') is-invalid @enderror"
                                    placeholder="Nombre del establecimiento">
                                @error('studentPreviousSchool') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12">
                            <div class="form-group mb-0">
                                <label>Dirección <span class="req">*</span></label>
                                <input type="text" wire:model="studentAddress"
                                    class="form-control @error('studentAddress') is-invalid @enderror"
                                    placeholder="Dirección completa de residencia">
                                @error('studentAddress') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- ═══════════════════════════════════════
                     SECCIÓN 2 — Grado al que aplica
                ════════════════════════════════════════ --}}
                <fieldset class="form-section mb-4">
                    <legend><i class="fas fa-graduation-cap mr-1"></i> Grado al que Aplica</legend>

                    <div class="row">
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group">
                                <label>Ciclo Escolar <span class="req">*</span></label>
                                <select wire:model="year"
                                    class="form-control @error('year') is-invalid @enderror">
                                    @foreach ($this->availableYears as $yr)
                                        <option value="{{ $yr }}">{{ $yr }}</option>
                                    @endforeach
                                </select>
                                @error('year') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group">
                                <label>Nivel <span class="req">*</span></label>
                                <select wire:model.live="levelId"
                                    class="form-control @error('levelId') is-invalid @enderror">
                                    <option value="">— Seleccione —</option>
                                    @foreach ($this->levels as $level)
                                        <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                                    @endforeach
                                </select>
                                @error('levelId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group mb-0">
                                <label>Grado <span class="req">*</span></label>
                                <select wire:model="gradeId"
                                    class="form-control @error('gradeId') is-invalid @enderror"
                                    @if(! $levelId) disabled @endif>
                                    <option value="">— Seleccione nivel primero —</option>
                                    @foreach ($this->grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                                    @endforeach
                                </select>
                                @error('gradeId') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- ═══════════════════════════════════════
                     SECCIÓN 3 — Información del Padre
                ════════════════════════════════════════ --}}
                <fieldset class="form-section mb-4">
                    <legend>
                        <i class="fas fa-male mr-1"></i> Información del Padre
                        <div class="custom-control custom-switch d-inline-block ml-3">
                            <input type="checkbox" class="custom-control-input" id="fatherToggle"
                                wire:model.live="fatherEnabled">
                            <label class="custom-control-label font-weight-normal" for="fatherToggle">
                                {{ $fatherEnabled ? 'Incluido' : 'No aplica' }}
                            </label>
                        </div>
                    </legend>

                    @if ($fatherEnabled)
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Nombres <span class="req">*</span></label>
                                    <input type="text" wire:model="fatherFirstName"
                                        class="form-control @error('fatherFirstName') is-invalid @enderror">
                                    @error('fatherFirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Apellidos <span class="req">*</span></label>
                                    <input type="text" wire:model="fatherLastName"
                                        class="form-control @error('fatherLastName') is-invalid @enderror">
                                    @error('fatherLastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Teléfono <span class="req">*</span></label>
                                    <input type="text" wire:model="fatherPhone"
                                        class="form-control @error('fatherPhone') is-invalid @enderror">
                                    @error('fatherPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group mb-0">
                                    <label>Lugar de Trabajo</label>
                                    <input type="text" wire:model="fatherWorkplace"
                                        class="form-control @error('fatherWorkplace') is-invalid @enderror"
                                        placeholder="Empresa u organización">
                                    @error('fatherWorkplace') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group mb-0">
                                    <label>NIT</label>
                                    <input type="text" wire:model="fatherNit"
                                        class="form-control @error('fatherNit') is-invalid @enderror"
                                        placeholder="NIT del padre">
                                    @error('fatherNit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group mb-0">
                                    <label>Profesión u Oficio</label>
                                    <input type="text" wire:model="fatherProfession"
                                        class="form-control @error('fatherProfession') is-invalid @enderror"
                                        placeholder="Ej. Ingeniero, Comerciante">
                                    @error('fatherProfession') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los datos del padre no serán registrados. Active el interruptor si desea incluirlos.
                        </p>
                    @endif
                </fieldset>

                {{-- ═══════════════════════════════════════
                     SECCIÓN 4 — Información de la Madre
                ════════════════════════════════════════ --}}
                <fieldset class="form-section mb-4">
                    <legend>
                        <i class="fas fa-female mr-1"></i> Información de la Madre
                        <div class="custom-control custom-switch d-inline-block ml-3">
                            <input type="checkbox" class="custom-control-input" id="motherToggle"
                                wire:model.live="motherEnabled">
                            <label class="custom-control-label font-weight-normal" for="motherToggle">
                                {{ $motherEnabled ? 'Incluida' : 'No aplica' }}
                            </label>
                        </div>
                    </legend>

                    @if ($motherEnabled)
                        <div class="row">
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Nombres <span class="req">*</span></label>
                                    <input type="text" wire:model="motherFirstName"
                                        class="form-control @error('motherFirstName') is-invalid @enderror">
                                    @error('motherFirstName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Apellidos <span class="req">*</span></label>
                                    <input type="text" wire:model="motherLastName"
                                        class="form-control @error('motherLastName') is-invalid @enderror">
                                    @error('motherLastName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group">
                                    <label>Teléfono <span class="req">*</span></label>
                                    <input type="text" wire:model="motherPhone"
                                        class="form-control @error('motherPhone') is-invalid @enderror">
                                    @error('motherPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group mb-0">
                                    <label>Lugar de Trabajo</label>
                                    <input type="text" wire:model="motherWorkplace"
                                        class="form-control @error('motherWorkplace') is-invalid @enderror"
                                        placeholder="Empresa u organización">
                                    @error('motherWorkplace') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group mb-0">
                                    <label>NIT</label>
                                    <input type="text" wire:model="motherNit"
                                        class="form-control @error('motherNit') is-invalid @enderror"
                                        placeholder="NIT de la madre">
                                    @error('motherNit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-4">
                                <div class="form-group mb-0">
                                    <label>Profesión u Oficio</label>
                                    <input type="text" wire:model="motherProfession"
                                        class="form-control @error('motherProfession') is-invalid @enderror"
                                        placeholder="Ej. Maestra, Enfermera">
                                    @error('motherProfession') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0 small">
                            <i class="fas fa-info-circle mr-1"></i>
                            Los datos de la madre no serán registrados. Active el interruptor si desea incluirlos.
                        </p>
                    @endif
                </fieldset>

                {{-- ═══════════════════════════════════════
                     SECCIÓN 5 — Encargado del Alumno
                ════════════════════════════════════════ --}}
                <fieldset class="form-section mb-4">
                    <legend><i class="fas fa-user-shield mr-1"></i> Encargado del Alumno</legend>

                    <div class="row">
                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label>Encargado es <span class="req">*</span></label>
                                <select wire:model.live="guardianType"
                                    class="form-control @error('guardianType') is-invalid @enderror">
                                    <option value="">— Seleccione —</option>
                                    @if ($fatherEnabled)
                                        <option value="father">Padre</option>
                                    @endif
                                    @if ($motherEnabled)
                                        <option value="mother">Madre</option>
                                    @endif
                                    <option value="other">Otro encargado</option>
                                </select>
                                @error('guardianType') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label>Nombre del Encargado</label>
                                <input type="text" wire:model="guardianName"
                                    class="form-control @error('guardianName') is-invalid @enderror"
                                    placeholder="Se llena automáticamente"
                                    @if($guardianType !== 'other') readonly @endif>
                                @error('guardianName') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label>Teléfono del Encargado <span class="req">*</span></label>
                                <input type="text" wire:model="guardianPhone"
                                    class="form-control @error('guardianPhone') is-invalid @enderror"
                                    placeholder="{{ $guardianType !== 'other' ? 'Se llena automáticamente' : 'Teléfono' }}"
                                    @if($guardianType !== 'other') readonly @endif>
                                @error('guardianPhone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @if ($guardianType === 'other')
                        <div class="col-sm-12 col-md-3">
                            <div class="form-group">
                                <label>NIT del Encargado</label>
                                <input type="text" wire:model="guardianNit"
                                    class="form-control @error('guardianNit') is-invalid @enderror"
                                    placeholder="NIT del encargado">
                                @error('guardianNit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        @endif
                        <div class="col-sm-12 col-md-3">
                            <div class="form-group mb-0">
                                <label>Correo Electrónico <span class="req">*</span></label>
                                <input type="email" wire:model="guardianEmail"
                                    class="form-control @error('guardianEmail') is-invalid @enderror"
                                    placeholder="correo@ejemplo.com">
                                <small class="text-muted">Toda comunicación será enviada aquí.</small>
                                @error('guardianEmail') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- ═══════════════════════════════════════
                     SECCIÓN 6 — Información Familiar
                ════════════════════════════════════════ --}}
                <fieldset class="form-section mb-4">
                    <legend><i class="fas fa-users mr-1"></i> Información Familiar</legend>

                    <div class="row">
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group mb-0">
                                <label>Número de Hijos (varones)</label>
                                <input type="number" wire:model="sonsCount" min="0"
                                    class="form-control @error('sonsCount') is-invalid @enderror"
                                    placeholder="0">
                                @error('sonsCount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-8">
                            <div class="form-group mb-0">
                                <label>Edades de los Hijos</label>
                                <input type="text" wire:model="sonsAges"
                                    class="form-control @error('sonsAges') is-invalid @enderror"
                                    placeholder="Ej: 5, 3, 1  (separadas por coma)">
                                @error('sonsAges') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-sm-12 col-md-4">
                            <div class="form-group mb-0">
                                <label>Número de Hijas (mujeres)</label>
                                <input type="number" wire:model="daughtersCount" min="0"
                                    class="form-control @error('daughtersCount') is-invalid @enderror"
                                    placeholder="0">
                                @error('daughtersCount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-8">
                            <div class="form-group mb-0">
                                <label>Edades de las Hijas</label>
                                <input type="text" wire:model="daughtersAges"
                                    class="form-control @error('daughtersAges') is-invalid @enderror"
                                    placeholder="Ej: 8, 4  (separadas por coma)">
                                @error('daughtersAges') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- ═══════════════════════════════════════
                     SECCIÓN 7 — ¿Cómo nos conoció?
                ════════════════════════════════════════ --}}
                <fieldset class="form-section mb-4">
                    <legend><i class="fas fa-star mr-1"></i> ¿Cómo supo de nosotros?</legend>

                    <div class="row">
                        <div class="col-sm-12 col-md-6">
                            <div class="form-group mb-0">
                                <label>Medio por el que conoció el instituto</label>
                                <select wire:model="referralSource"
                                    class="form-control @error('referralSource') is-invalid @enderror">
                                    <option value="">— Seleccione (opcional) —</option>
                                    <option value="Instagram">Instagram</option>
                                    <option value="Facebook">Facebook</option>
                                    <option value="Página Web">Página Web</option>
                                    <option value="Publicidad">Publicidad (volante, afiche, etc.)</option>
                                    <option value="Cercanía">Cercanía (ubicación geográfica)</option>
                                    <option value="Exalumno">Soy exalumno del instituto</option>
                                    <option value="Referido por exalumno">Referido por un exalumno</option>
                                    <option value="Referido por familia vecina">Referido por una familia vecina</option>
                                    <option value="Referido por familiar">Referido por un familiar</option>
                                </select>
                                @error('referralSource') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                {{-- Botón de envío --}}
                <div class="text-center mt-4"
                    x-data="{
                        confirm() {
                            const email = document.querySelector('[wire\\:model=\'guardianEmail\']')?.value ?? '';
                            Swal.fire({
                                title: '¿Enviar solicitud?',
                                html: 'La información será enviada para revisión.' + (email ? '<br><br>Correo de contacto:<br><strong>' + email + '<\/strong>' : ''),
                                icon: 'question',
                                showCancelButton: true,
                                confirmButtonColor: '#28a745',
                                cancelButtonColor: '#6c757d',
                                confirmButtonText: 'Sí, enviar',
                                cancelButtonText: 'Cancelar',
                            }).then(r => { if (r.isConfirmed) $wire.submit() });
                        }
                    }">
                    <button type="button" x-on:click="confirm()"
                        wire:loading.attr="disabled" wire:target="submit"
                        class="btn btn-success btn-lg px-5">
                        <span wire:loading.remove wire:target="submit">
                            <i class="fas fa-paper-plane mr-2"></i> Enviar Solicitud
                        </span>
                        <span wire:loading wire:target="submit">
                            <i class="fas fa-spinner fa-spin mr-2"></i> Enviando...
                        </span>
                    </button>
                </div>
            </div>

            <div class="admission-footer">
                &copy; {{ date('Y') }} {{ config('app.institution_name', 'EduCheck') }}
            </div>
        </div>
    @endif
</div>

