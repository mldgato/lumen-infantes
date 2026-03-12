<div wire:init="loadGradeBooks">

    {{-- Modal Rechazo --}}
    <div wire:ignore.self class="modal fade" id="RejectModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-times-circle"></i> Rechazar Cuadro
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Motivo del Rechazo <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-comment-alt"></i></span>
                            </div>
                            <textarea wire:model="rejection_reason" class="form-control @error('rejection_reason') is-invalid @enderror"
                                rows="3" placeholder="Describa el motivo del rechazo..."></textarea>
                            @error('rejection_reason')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button wire:click.prevent="reject" type="button" class="btn btn-danger btn-sm"
                        wire:loading.attr="disabled" wire:target="reject">
                        <span wire:loading.remove wire:target="reject">
                            <i class="fas fa-times-circle"></i> Rechazar
                        </span>
                        <span wire:loading wire:target="reject">
                            <i class="fas fa-spinner fa-pulse"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Card principal --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <select wire:model.live="filterYear" class="form-control form-control-sm">
                        <option value="">-- Todos los años --</option>
                        @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select wire:model.live="filterStatus" class="form-control form-control-sm">
                        <option value="">-- Todos los estados --</option>
                        <option value="open">Abierto</option>
                        <option value="locked">Bloqueado</option>
                        <option value="rejected">Rechazado</option>
                        <option value="approved">Aprobado</option>
                    </select>
                </div>
                <div class="col-md-6 d-flex justify-content-end align-items-center">
                    <span class="mr-2 text-sm">Mostrar</span>
                    <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                            placeholder="Buscar...">
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
            @if ($readyToLoad && count($gradeBooks))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th>Profesor</th>
                            <th>Nivel</th>
                            <th>Grado</th>
                            <th>Sección</th>
                            <th>Curso</th>
                            <th class="text-center">Unidad</th>
                            <th class="text-center">Año</th>
                            <th class="text-center">Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($gradeBooks as $gradeBook)
                            <tr>
                                <td>{{ $gradeBook->assignment->professor->user->name }}</td>
                                <td>{{ $gradeBook->assignment->classroom->level->level_name }}</td>
                                <td>{{ $gradeBook->assignment->classroom->grade->grade_name }}</td>
                                <td>{{ $gradeBook->assignment->classroom->section->section_name }}</td>
                                <td>{{ $gradeBook->assignment->pensumCourse->course->course_name }}</td>
                                <td class="text-center">
                                    <span class="badge badge-secondary">U{{ $gradeBook->assignment->unit }}</span>
                                </td>
                                <td class="text-center">{{ $gradeBook->assignment->classroom->year }}</td>
                                <td class="text-center">
                                    @if ($gradeBook->status === 'open')
                                        <span class="badge badge-success">Abierto</span>
                                    @elseif ($gradeBook->status === 'locked')
                                        <span class="badge badge-secondary">Bloqueado</span>
                                    @elseif ($gradeBook->status === 'rejected')
                                        <span class="badge badge-danger">Rechazado</span>
                                    @elseif ($gradeBook->status === 'approved')
                                        <span class="badge badge-primary">Aprobado</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if ($gradeBook->status === 'locked')
                                        <button onclick="confirmApprove({{ $gradeBook->id }})"
                                            class="btn btn-sm btn-primary shadow-sm" title="Aprobar">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <button wire:click="openRejectModal({{ $gradeBook->id }})" data-toggle="modal"
                                            data-target="#RejectModal" class="btn btn-sm btn-danger shadow-sm"
                                            title="Rechazar">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @elseif ($gradeBook->status === 'rejected')
                                        <span class="text-muted text-sm" title="{{ $gradeBook->rejection_reason }}">
                                            <i class="fas fa-info-circle"></i>
                                            {{ Str::limit($gradeBook->rejection_reason, 30) }}
                                        </span>
                                    @else
                                        <span class="text-muted text-sm">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="p-4 text-center text-muted">
                    @if (!$readyToLoad)
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando cuadros...
                    @else
                        <i class="fas fa-book-open fa-3x mb-3 text-gray"></i><br>No se encontraron cuadros.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($gradeBooks) && $gradeBooks->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $gradeBooks->links() }}</div>
            </div>
        @endif
    </div>

    @push('js')
        <script>
            function confirmApprove(id) {
                Swal.fire({
                    title: '¿Aprobar este cuadro?',
                    text: 'Una vez aprobado no podrá ser modificado.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#007bff',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, aprobar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.approve(id);
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
