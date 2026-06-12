<div>
    {{-- ════════════════════════════════════════
         CABECERA + BOTÓN NUEVO
    ════════════════════════════════════════ --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="row w-100">
            <div class="col-sm-12 col-md-5">
                <input type="text" wire:model.live.debounce.300ms="search"
                    class="form-control" placeholder="Buscar materia..."
                    autocomplete="new-password">
            </div>
            <div class="col-sm-12 col-md-3">
                <select wire:model.live="cant" class="form-control">
                    <option value="10">10 por página</option>
                    <option value="15">15 por página</option>
                    <option value="25">25 por página</option>
                    <option value="50">50 por página</option>
                </select>
            </div>
            <div class="col-sm-12 col-md-4 text-md-right mt-2 mt-md-0">
                <button wire:click="openCreate" class="btn btn-primary">
                    <i class="fas fa-plus mr-1"></i> Nueva Materia
                </button>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         TABLA
    ════════════════════════════════════════ --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-sm mb-0">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:60px;" class="text-center">Orden</th>
                            <th>Nombre</th>
                            <th style="width:100px;" class="text-center">Punteos</th>
                            <th style="width:100px;" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->courses as $course)
                            <tr>
                                <td class="text-center text-muted">{{ $course->ordering }}</td>
                                <td><strong>{{ $course->name }}</strong></td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $course->scores_count ?? $course->scores()->count() }}</span>
                                </td>
                                <td class="text-center">
                                    <button wire:click="openEdit({{ $course->id }})"
                                        class="btn btn-xs btn-warning" title="Editar">
                                        <i class="fas fa-pencil-alt"></i>
                                    </button>
                                    <button
                                        @click="Swal.fire({
                                            title: '¿Eliminar materia?',
                                            text: '{{ addslashes($course->name) }}',
                                            icon: 'warning',
                                            showCancelButton: true,
                                            confirmButtonText: 'Sí, eliminar',
                                            cancelButtonText: 'Cancelar',
                                            confirmButtonColor: '#dc3545'
                                        }).then(r => r.isConfirmed && $wire.delete({{ $course->id }}))"
                                        class="btn btn-xs btn-danger" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                    No se encontraron materias.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if ($this->courses->hasPages())
            <div class="card-footer">
                {{ $this->courses->links() }}
            </div>
        @endif
    </div>

    {{-- ════════════════════════════════════════
         MODAL — Crear / Editar
    ════════════════════════════════════════ --}}
    <div class="modal fade" id="courseModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-sm">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-book mr-2"></i>
                        {{ $editId ? 'Editar Materia' : 'Nueva Materia' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre <span class="text-danger">*</span></label>
                        <input type="text" wire:model="editName"
                            class="form-control @error('editName') is-invalid @enderror"
                            placeholder="Ej: Matemáticas">
                        @error('editName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label>Orden <span class="text-danger">*</span></label>
                        <input type="number" wire:model="editOrdering" min="0"
                            class="form-control @error('editOrdering') is-invalid @enderror">
                        @error('editOrdering')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button wire:click="save"
                        wire:loading.attr="disabled" wire:target="save"
                        class="btn btn-primary btn-sm">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save mr-1"></i> Guardar
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-spin"></i>
                        </span>
                    </button>
                    <button type="button" class="btn btn-default btn-sm" data-dismiss="modal">
                        <i class="fas fa-times mr-1"></i> Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('openCourseModal', () => {
        $('#courseModal').modal('show');
    });

    $wire.on('closeCourseModal', () => {
        $('#courseModal').modal('hide');
    });

    $wire.on('toastMessage', (data) => {
        let p = Array.isArray(data) ? (data[0] || {}) : (data || {});
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
        });
        Toast.fire({ icon: p.type || 'info', title: p.message || p.title });
    });
</script>
@endscript
