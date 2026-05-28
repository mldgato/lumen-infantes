<div>
    {{-- ============================================================
         CONTROLES
         ============================================================ --}}
    <div class="card card-success card-outline">
        <div class="card-header d-flex align-items-center flex-wrap" style="gap:.5rem">
            <h5 class="m-0 text-bold flex-grow-1">
                <i class="fas fa-user-friends mr-1"></i> Guardianes / Tutores
            </h5>

            <div class="input-group input-group-sm" style="width:260px">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control" placeholder="Nombre, CUI, teléfono..." autocomplete="new-password">
            </div>
        </div>

        <div class="card-body p-0" wire:init="loadGuardians">
            @if ($readyToLoad)
                <table class="table table-hover table-striped mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width:30px">#</th>
                            <th>Nombre completo</th>
                            <th>CUI</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th class="text-center">Estudiantes</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($guardians as $guardian)
                            <tr>
                                <td class="text-muted">{{ $guardians->firstItem() + $loop->index }}</td>
                                <td>{{ $guardian->last_name }}, {{ $guardian->first_name }}</td>
                                <td>{{ $guardian->cui ?: '—' }}</td>
                                <td>{{ $guardian->phone ?: '—' }}</td>
                                <td>{{ $guardian->email ?: '—' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $guardian->students_count > 0 ? 'primary' : 'secondary' }}">
                                        {{ $guardian->students_count }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="openDetail({{ $guardian->id }})"
                                        class="btn btn-sm btn-outline-info"
                                        title="Ver estudiantes relacionados">
                                        <i class="fas fa-users"></i>
                                    </button>
                                    @can('admin.guardians.edit')
                                        <button wire:click="openEdit({{ $guardian->id }})"
                                            class="btn btn-sm btn-outline-primary ml-1"
                                            title="Editar guardián">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-user-friends fa-2x mb-2"></i><br>
                                    No se encontraron guardianes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="px-3 py-2">{{ $guardians->links() }}</div>
            @else
                <div class="p-4 text-center text-muted">
                    <i class="fas fa-spinner fa-pulse mr-1"></i> Cargando...
                </div>
            @endif
        </div>
    </div>

    {{-- ============================================================
         MODAL: ESTUDIANTES RELACIONADOS
         ============================================================ --}}
    @if ($detailGuardianId && $detailGuardian)
        <div class="modal fade show" style="display:block; background:rgba(0,0,0,.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-users mr-2"></i>
                            {{ $detailGuardian->last_name }}, {{ $detailGuardian->first_name }}
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeDetail">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-0">
                        @if ($detailStudents->isEmpty())
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-users fa-2x mb-2"></i><br>
                                Este guardián no tiene estudiantes relacionados.
                            </div>
                        @else
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Estudiante</th>
                                        <th>Relación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($detailStudents as $i => $student)
                                        <tr>
                                            <td class="text-muted">{{ $i + 1 }}</td>
                                            <td>{{ $student->user->full_full_name }}</td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    {{ $student->pivot->relationship_type ?: '—' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeDetail">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- ============================================================
         MODAL: EDITAR GUARDIÁN
         ============================================================ --}}
    @if ($editingGuardianId)
        <div class="modal fade show" style="display:block; background:rgba(0,0,0,.5);" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-edit mr-2"></i> Editar Guardián
                        </h5>
                        <button type="button" class="close text-white" wire:click="closeEdit">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Nombres <span class="text-danger">*</span></label>
                                <input type="text" wire:model="guardianForm.first_name"
                                    class="form-control form-control-sm @error('guardianForm.first_name') is-invalid @enderror">
                                @error('guardianForm.first_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Apellidos <span class="text-danger">*</span></label>
                                <input type="text" wire:model="guardianForm.last_name"
                                    class="form-control form-control-sm @error('guardianForm.last_name') is-invalid @enderror">
                                @error('guardianForm.last_name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">CUI <span class="text-danger">*</span></label>
                                <input type="text" wire:model="guardianForm.cui"
                                    class="form-control form-control-sm @error('guardianForm.cui') is-invalid @enderror">
                                @error('guardianForm.cui')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Extendido en</label>
                                <input type="text" wire:model="guardianForm.cui_extended_in"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Teléfono</label>
                                <input type="text" wire:model="guardianForm.phone"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Correo electrónico</label>
                                <input type="email" wire:model="guardianForm.email"
                                    class="form-control form-control-sm @error('guardianForm.email') is-invalid @enderror">
                                @error('guardianForm.email')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Profesión</label>
                                <input type="text" wire:model="guardianForm.profession"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Nacionalidad</label>
                                <input type="text" wire:model="guardianForm.nationality"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Fecha de nacimiento</label>
                                <input type="date" wire:model="guardianForm.birthdate"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="text-sm mb-1">Lugar de nacimiento</label>
                                <input type="text" wire:model="guardianForm.birthplace"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-12 form-group">
                                <label class="text-sm mb-1">Dirección de residencia</label>
                                <input type="text" wire:model="guardianForm.residence_address"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="text-sm mb-1">Empresa</label>
                                <input type="text" wire:model="guardianForm.company_name"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="text-sm mb-1">Dirección de empresa</label>
                                <input type="text" wire:model="guardianForm.company_address"
                                    class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4 form-group">
                                <label class="text-sm mb-1">Teléfono de empresa</label>
                                <input type="text" wire:model="guardianForm.company_phone"
                                    class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light justify-content-between">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="closeEdit">
                            Cancelar
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" wire:click="save"
                            wire:loading.attr="disabled" wire:target="save">
                            <span wire:loading.remove wire:target="save">
                                <i class="fas fa-save mr-1"></i> Guardar
                            </span>
                            <span wire:loading wire:target="save">
                                <i class="fas fa-spinner fa-pulse"></i> Guardando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
