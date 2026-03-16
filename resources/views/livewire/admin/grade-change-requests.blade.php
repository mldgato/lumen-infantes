<div wire:init="loadRequests">

    {{-- Modal Rechazo --}}
    <div wire:ignore.self class="modal fade" id="RejectModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-times-circle"></i> Rechazar Solicitud
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-0">
                        <label class="text-sm mb-1">Motivo del Rechazo <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-comment-alt"></i></span>
                            </div>
                            <textarea wire:model="rejectionReason" class="form-control @error('rejectionReason') is-invalid @enderror"
                                rows="3" placeholder="Describa el motivo del rechazo..."></textarea>
                            @error('rejectionReason')
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

    @if (!$viewingRequest)

        {{-- TABLA DE SOLICITUDES --}}
        <div class="card card-primary card-outline">
            <div class="card-header">
                <div class="row align-items-center">
                    <div class="col-md-4 d-flex" style="gap:8px">
                        @foreach (['pending' => ['Pendientes', 'warning'], 'approved' => ['Aprobadas', 'success'], 'rejected' => ['Rechazadas', 'danger'], '' => ['Todas', 'secondary']] as $val => $cfg)
                            <button wire:click="$set('filterStatus', '{{ $val }}')"
                                class="btn btn-sm btn-{{ $filterStatus === $val ? $cfg[1] : 'outline-' . $cfg[1] }}">
                                {{ $cfg[0] }}
                            </button>
                        @endforeach
                    </div>
                    <div class="col-md-4 d-flex justify-content-end align-items-center">
                        <span class="mr-2 text-sm">Mostrar</span>
                        <select wire:model.live="cant" class="form-control form-control-sm w-auto mr-3">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group input-group-sm">
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control"
                                placeholder="Buscar profesor, curso, grado...">
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
                @if ($readyToLoad && count($requests))
                    <table class="table table-hover table-striped text-nowrap">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Profesor</th>
                                <th>Grado</th>
                                <th>Curso</th>
                                <th class="text-center">Unidad</th>
                                <th class="text-center">Cambios</th>
                                <th class="text-center">Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($requests as $request)
                                <tr>
                                    <td>{{ $request->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ $request->professor->user->name }}</td>
                                    <td>
                                        {{ $request->gradeBook->assignment->classroom->grade->grade_name }}
                                        {{ $request->gradeBook->assignment->classroom->section->section_name }}
                                    </td>
                                    <td>{{ $request->gradeBook->assignment->pensumCourse->course->course_name }}</td>
                                    <td class="text-center">
                                        <span class="badge badge-secondary">
                                            U{{ $request->gradeBook->assignment->unit }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">
                                            {{ $request->items->count() }} cambio(s)
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if ($request->status === 'pending')
                                            <span class="badge badge-warning">Pendiente</span>
                                        @elseif ($request->status === 'approved')
                                            <span class="badge badge-success">Aprobada</span>
                                        @else
                                            <span class="badge badge-danger">Rechazada</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button wire:click="openRequest({{ $request->id }})"
                                            class="btn btn-sm btn-info shadow-sm">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        @if ($request->status === 'pending')
                                            <button onclick="confirmApproveRequest({{ $request->id }})"
                                                class="btn btn-sm btn-success shadow-sm ml-1">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            <button wire:click="openRejectModal({{ $request->id }})"
                                                data-toggle="modal" data-target="#RejectModal"
                                                class="btn btn-sm btn-danger shadow-sm ml-1">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="p-4 text-center text-muted">
                        @if (!$readyToLoad)
                            <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando solicitudes...
                        @else
                            <i class="fas fa-check-circle fa-3x mb-3 text-gray"></i><br>No hay solicitudes.
                        @endif
                    </div>
                @endif
            </div>

            @if ($readyToLoad && count($requests) && $requests->hasPages())
                <div class="card-footer clearfix">
                    <div class="float-right">{{ $requests->links() }}</div>
                </div>
            @endif
        </div>
    @else
        {{-- VISTA DETALLE --}}
        @php
            $gb = $viewingRequest->gradeBook;
            $itemsByStudent = $viewingRequest->items->groupBy('student_id');
        @endphp

        <div class="card card-outline card-info">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button wire:click="closeRequest" class="btn btn-sm btn-secondary mr-2">
                            <i class="fas fa-arrow-left"></i> Volver
                        </button>
                        <strong>
                            {{ $gb->assignment->classroom->grade->grade_name }}
                            {{ $gb->assignment->classroom->section->section_name }} —
                            {{ $gb->assignment->pensumCourse->course->course_name }}
                            <span class="badge badge-secondary ml-1">U{{ $gb->assignment->unit }}</span>
                        </strong>
                        <span class="text-muted text-sm ml-2">
                            Solicitado por <strong>{{ $viewingRequest->professor->user->name }}</strong>
                            el {{ $viewingRequest->created_at->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    <div class="d-flex align-items-center" style="gap:6px">
                        @if ($viewingRequest->isPending())
                            <span class="badge badge-warning mr-2">Pendiente</span>
                            <button onclick="confirmApproveRequest({{ $viewingRequest->id }})"
                                class="btn btn-sm btn-success shadow-sm">
                                <i class="fas fa-check"></i> Aprobar
                            </button>
                            <button wire:click="openRejectModal({{ $viewingRequest->id }})" data-toggle="modal"
                                data-target="#RejectModal" class="btn btn-sm btn-danger shadow-sm">
                                <i class="fas fa-times"></i> Rechazar
                            </button>
                        @elseif ($viewingRequest->isApproved())
                            <span class="badge badge-success">Aprobada</span>
                            <small class="text-muted ml-2">
                                por {{ $viewingRequest->reviewer?->name }} —
                                {{ $viewingRequest->reviewed_at?->format('d/m/Y H:i') }}
                            </small>
                        @else
                            <span class="badge badge-danger">Rechazada</span>
                            <small class="text-muted ml-2">
                                por {{ $viewingRequest->reviewer?->name }} —
                                {{ $viewingRequest->reviewed_at?->format('d/m/Y H:i') }}
                            </small>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card-body">

                {{-- Motivo de la solicitud --}}
                <div class="alert alert-light border mb-3">
                    <strong><i class="fas fa-comment-alt mr-1"></i> Motivo de la solicitud:</strong>
                    {{ $viewingRequest->reason }}
                </div>

                {{-- Motivo de rechazo --}}
                @if ($viewingRequest->isRejected() && $viewingRequest->rejection_reason)
                    <div class="alert alert-danger mb-3">
                        <strong><i class="fas fa-times-circle mr-1"></i> Motivo del rechazo:</strong>
                        {{ $viewingRequest->rejection_reason }}
                    </div>
                @endif

                {{-- Detalle de cambios por estudiante --}}
                @foreach ($itemsByStudent as $studentId => $items)
                    @php
                        $student = $students->firstWhere('id', $studentId);
                    @endphp
                    <div class="card card-outline card-secondary mb-3">
                        <div class="card-header py-2 bg-light">
                            <h6 class="m-0 text-bold">
                                <i class="fas fa-user-graduate mr-1"></i>
                                {{ $student?->user->surname }}
                                {{ $student?->user->second_surname }},
                                {{ $student?->user->first_name }}
                                {{ $student?->user->middle_name }}
                            </h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Actividad</th>
                                        <th class="text-center">Nota Anterior</th>
                                        <th class="text-center">Nota Nueva</th>
                                        <th class="text-center">Mejora Anterior</th>
                                        <th class="text-center">Mejora Nueva</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        @php
                                            $scoreChanged = round($item->old_score, 2) !== round($item->new_score, 2);
                                            $improvChanged =
                                                round((float) $item->old_improvement_score, 2) !==
                                                round((float) $item->new_improvement_score, 2);
                                        @endphp
                                        <tr>
                                            <td>
                                                {{ $item->activity->name }}
                                                <small
                                                    class="text-muted">({{ $item->activity->activityType->name }})</small>
                                            </td>
                                            <td class="text-center {{ $scoreChanged ? 'table-warning' : '' }}">
                                                {{ number_format($item->old_score, 2) }}
                                            </td>
                                            <td
                                                class="text-center {{ $scoreChanged ? 'table-success font-weight-bold' : '' }}">
                                                {{ number_format($item->new_score, 2) }}
                                                @if ($scoreChanged)
                                                    @php $diff = $item->new_score - $item->old_score; @endphp
                                                    <small class="{{ $diff > 0 ? 'text-success' : 'text-danger' }}">
                                                        ({{ $diff > 0 ? '+' : '' }}{{ number_format($diff, 2) }})
                                                    </small>
                                                @endif
                                            </td>
                                            <td class="text-center {{ $improvChanged ? 'table-warning' : '' }}">
                                                {{ is_null($item->old_improvement_score) ? '—' : number_format($item->old_improvement_score, 2) }}
                                            </td>
                                            <td
                                                class="text-center {{ $improvChanged ? 'table-success font-weight-bold' : '' }}">
                                                {{ is_null($item->new_improvement_score) ? '—' : number_format($item->new_improvement_score, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach

            </div>
        </div>

    @endif

    @push('js')
        <script>
            function confirmApproveRequest(id) {
                Swal.fire({
                    title: '¿Aprobar esta solicitud?',
                    text: 'Se aplicarán los cambios de calificaciones y se recalcularán los totales.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, aprobar',
                    cancelButtonText: 'Cancelar',
                }).then(result => {
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
                        timer: 3000,
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
                        timer: 3500,
                    });
                });
            });
        </script>
    @endpush
</div>
