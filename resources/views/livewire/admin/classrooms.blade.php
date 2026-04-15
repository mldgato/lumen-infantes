<div wire:init="loadClassrooms">
    <div wire:ignore.self class="modal fade" id="ClassroomModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $form->classroom ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title">
                        <i class="fas {{ $form->classroom ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                        {{ $form->classroom ? 'Editar Aula' : 'Nueva Aula' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Nivel <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-layer-group"></i></span>
                                </div>
                                <select wire:model="form.level_id"
                                    class="form-control @error('form.level_id') is-invalid @enderror">
                                    <option value="">-- Seleccione un nivel --</option>
                                    @foreach ($levels as $level)
                                        <option value="{{ $level->id }}">{{ $level->level_name }}</option>
                                    @endforeach
                                </select>
                                @error('form.level_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Grado <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-graduation-cap"></i></span>
                                </div>
                                <select wire:model="form.grade_id"
                                    class="form-control @error('form.grade_id') is-invalid @enderror">
                                    <option value="">-- Seleccione un grado --</option>
                                    @foreach ($grades as $grade)
                                        <option value="{{ $grade->id }}">{{ $grade->grade_name }}</option>
                                    @endforeach
                                </select>
                                @error('form.grade_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Sección <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-tags"></i></span>
                                </div>
                                <select wire:model="form.section_id"
                                    class="form-control @error('form.section_id') is-invalid @enderror">
                                    <option value="">-- Seleccione una sección --</option>
                                    @foreach ($sections as $section)
                                        <option value="{{ $section->id }}">{{ $section->section_name }}</option>
                                    @endforeach
                                </select>
                                @error('form.section_id')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6 form-group mb-3">
                            <label class="text-sm mb-1">Año Escolar <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                </div>
                                <input type="number" wire:model="form.year"
                                    class="form-control @error('form.year') is-invalid @enderror" placeholder="Ej. 2026"
                                    min="2000" max="2100">
                                @error('form.year')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">
                        Cancelar
                    </button>
                    <button wire:click.prevent="save" type="button"
                        class="btn btn-sm {{ $form->classroom ? 'btn-warning' : 'btn-primary' }}"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save"></i>
                            {{ $form->classroom ? 'Actualizar' : 'Guardar' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-pulse"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-4">
                    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#ClassroomModal"
                        wire:click="resetFields">
                        <i class="fas fa-plus-circle"></i> Nueva Aula
                    </button>
                </div>
                <div class="col-md-8 d-flex justify-content-end align-items-center">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            name="buscar" id="buscador" placeholder="Buscar nivel, grado, sección, año..." autocomplete="search">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default"><i class="fas fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            @if ($readyToLoad && count($classrooms))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('id')">
                                # <i
                                    class="fas fa-sort{{ $sort === 'id' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Nivel</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th style="cursor:pointer" wire:click="order('year')">
                                Año <i
                                    class="fas fa-sort{{ $sort === 'year' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($classrooms as $classroom)
                            <tr>
                                <td>{{ $classroom->id }}</td>
                                <td>{{ $classroom->level->level_name }}</td>
                                <td>{{ $classroom->grade->grade_name }}</td>
                                <td>{{ $classroom->section->section_name }}</td>
                                <td>{{ $classroom->year }}</td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $classroom->id }})" data-toggle="modal"
                                        data-target="#ClassroomModal" class="btn btn-sm btn-warning shadow-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button onclick="confirmDelete({{ $classroom->id }}, '¿Eliminar esta aula?')"
                                        class="btn btn-sm btn-danger shadow-sm" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando aulas...
                    @else
                        <i class="fas fa-school fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($classrooms) && $classrooms->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $classrooms->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            function confirmDelete(id, mensaje) {
                Swal.fire({
                    title: mensaje || '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.delete(id);
                    }
                });
            }

            document.addEventListener('livewire:init', () => {
                Livewire.on('closeModalMessaje', (event) => {
                    let payload = event[0] || event;
                    if (payload.modalId) {
                        $('#' + payload.modalId).modal('hide');
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                    }
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3000
                    });
                });

                Livewire.on('showAlert', (event) => {
                    let payload = event[0] || event;
                    Swal.fire({
                        position: 'top-end',
                        icon: payload.type,
                        title: payload.title,
                        text: payload.message,
                        showConfirmButton: false,
                        timer: 3500
                    });
                });
            });
        </script>
    @endpush
</div>
