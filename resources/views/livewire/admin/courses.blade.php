<div wire:init="loadCourses">
    <div wire:ignore.self class="modal fade" id="CourseModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $form->course ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title">
                        <i class="fas {{ $form->course ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                        {{ $form->course ? 'Editar Curso: ' . $form->course->course_name : 'Nuevo Curso' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Nombre del Curso <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-book"></i></span>
                            </div>
                            <input type="text" wire:model="form.course_name"
                                class="form-control @error('form.course_name') is-invalid @enderror"
                                placeholder="Ej. Matemática">
                            @error('form.course_name')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">
                        Cancelar
                    </button>
                    <button wire:click.prevent="save" type="button"
                        class="btn btn-sm {{ $form->course ? 'btn-warning' : 'btn-primary' }}"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save"></i>
                            {{ $form->course ? 'Actualizar' : 'Guardar' }}
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
                    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#CourseModal"
                        wire:click="resetFields">
                        <i class="fas fa-plus-circle"></i> Nuevo Curso
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
                            placeholder="Buscar curso...">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body table-responsive p-0">
            @if ($readyToLoad && count($courses))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('id')">
                                # <i
                                    class="fas fa-sort{{ $sort === 'id' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th style="cursor:pointer" wire:click="order('course_name')">
                                Nombre <i
                                    class="fas fa-sort{{ $sort === 'course_name' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($courses as $course)
                            <tr>
                                <td>{{ $course->id }}</td>
                                <td>{{ $course->course_name }}</td>
                                <td class="text-center">
                                    <button wire:click="edit({{ $course->id }})" data-toggle="modal"
                                        data-target="#CourseModal" class="btn btn-sm btn-warning shadow-sm">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        onclick="confirmDelete({{ $course->id }}, '¿Eliminar el curso {{ addslashes($course->course_name) }}?')"
                                        class="btn btn-sm btn-danger shadow-sm">
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
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando cursos...
                    @else
                        <i class="fas fa-book fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($courses) && $courses->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $courses->links() }}</div>
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
