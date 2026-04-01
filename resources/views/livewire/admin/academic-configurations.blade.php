<div wire:init="loadConfigurations">

    {{-- Modal Configuración --}}
    <div wire:ignore.self class="modal fade" id="ConfigurationModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header {{ $form->configuration ? 'bg-warning' : 'bg-primary' }}">
                    <h5 class="modal-title">
                        <i class="fas {{ $form->configuration ? 'fa-edit' : 'fa-plus-circle' }}"></i>
                        {{ $form->configuration ? 'Editar Configuración' : 'Nueva Configuración Académica' }}
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" wire:click="resetFields">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Año <span class="text-danger">*</span></label>
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
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Modo de Trabajo <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-sliders-h"></i></span>
                            </div>
                            <select wire:model="form.mode"
                                class="form-control @error('form.mode') is-invalid @enderror">
                                <option value="free">Libre — Cada profesor define sus actividades</option>
                                <option value="assigned">Asignada — La institución define las actividades</option>
                            </select>
                            @error('form.mode')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    {{-- Tipo de Mejora --}}
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Tipo de Proceso de Mejora <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-chart-line"></i></span>
                            </div>
                            <select wire:model.live="form.improvement_type"
                                class="form-control @error('form.improvement_type') is-invalid @enderror">
                                <option value="">Selecione el tipo de mejora</option>
                                <option value="none">Ninguno — Sin proceso de mejora por actividades</option>
                                <option value="full">100% — Puede obtener el total de la actividad</option>
                                <option value="percentage">Porcentaje — Solo puede obtener un % del total</option>
                                <option value="additive">Suma — Se suma a la nota original sin sobrepasar el total
                                </option>
                            </select>
                            @error('form.improvement_type')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Porcentaje (solo si el tipo es percentage) --}}
                    @if ($form->improvement_type === 'percentage')
                        <div class="form-group mb-3">
                            <label class="text-sm mb-1">Porcentaje de Mejora <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-percent"></i></span>
                                </div>
                                <input type="number" wire:model="form.improvement_percentage"
                                    class="form-control @error('form.improvement_percentage') is-invalid @enderror"
                                    placeholder="Ej. 60" min="1" max="100" step="0.01">
                                <div class="input-group-append">
                                    <span class="input-group-text">%</span>
                                </div>
                                @error('form.improvement_percentage')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <small class="text-muted">
                                Ejemplo: si la actividad vale 10 pts y el porcentaje es 60%, el máximo en mejora es 6
                                pts.
                            </small>
                        </div>
                    @endif
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"
                        wire:click="resetFields">
                        Cancelar
                    </button>
                    <button wire:click.prevent="save" type="button"
                        class="btn btn-sm {{ $form->configuration ? 'btn-warning' : 'btn-primary' }}"
                        wire:loading.attr="disabled" wire:target="save">
                        <span wire:loading.remove wire:target="save">
                            <i class="fas fa-save"></i>
                            {{ $form->configuration ? 'Actualizar' : 'Guardar' }}
                        </span>
                        <span wire:loading wire:target="save">
                            <i class="fas fa-spinner fa-pulse"></i> Procesando...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Gestión de Actividades --}}
    <div wire:ignore.self class="modal fade" id="ActivitiesModal" tabindex="-1" role="dialog" data-backdrop="static">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-tasks"></i>
                        Actividades —
                        {{ $managingConfiguration ? 'Año ' . $managingConfiguration->year : '' }}
                        @if ($managingConfiguration)
                            <span
                                class="badge {{ $managingConfiguration->mode === 'free' ? 'badge-light' : 'badge-warning' }} ml-1">
                                {{ $managingConfiguration->mode === 'free' ? 'Libre' : 'Asignada' }}
                            </span>
                        @endif
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal"
                        wire:click="resetActivityForm">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body bg-light">

                    @if ($managingConfiguration)
                        @if ($managingConfiguration->mode === 'free')
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-1"></i>
                                Esta configuración es de modo <strong>Libre</strong>. Cada profesor definirá sus propias
                                actividades al momento de calificar.
                            </div>
                        @else
                            @if (!$showActivityForm)
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="m-0 text-bold text-secondary">Actividades Asignadas</h6>
                                    <button wire:click="openActivityForm" class="btn btn-sm btn-primary shadow-sm">
                                        <i class="fas fa-plus"></i> Agregar Actividad
                                    </button>
                                </div>

                                @if ($managingConfiguration->activities->count() > 0)
                                    @php
                                        $totalPoints = $managingConfiguration->activities->sum(
                                            fn($a) => $a->quantity * $a->points_each,
                                        );
                                        $normalPoints = $managingConfiguration->activities
                                            ->filter(fn($a) => !$a->activityType->is_extra)
                                            ->sum(fn($a) => $a->quantity * $a->points_each);
                                        $extraPoints = $managingConfiguration->activities
                                            ->filter(fn($a) => $a->activityType->is_extra)
                                            ->sum(fn($a) => $a->quantity * $a->points_each);
                                    @endphp

                                    {{-- Resumen de puntos --}}
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <div class="info-box mb-0">
                                                <span class="info-box-icon bg-success"><i
                                                        class="fas fa-star"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-sm">Puntos Normales</span>
                                                    <span
                                                        class="info-box-number">{{ number_format($normalPoints, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box mb-0">
                                                <span class="info-box-icon bg-warning"><i
                                                        class="fas fa-plus-circle"></i></span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-sm">Puntos Extra</span>
                                                    <span
                                                        class="info-box-number">{{ number_format($extraPoints, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="info-box mb-0">
                                                <span
                                                    class="info-box-icon {{ $normalPoints == 100 ? 'bg-success' : 'bg-danger' }}">
                                                    <i class="fas fa-calculator"></i>
                                                </span>
                                                <div class="info-box-content">
                                                    <span class="info-box-text text-sm">Total</span>
                                                    <span
                                                        class="info-box-number">{{ number_format($totalPoints, 2) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($normalPoints != 100)
                                        <div class="alert alert-warning py-2">
                                            <i class="fas fa-exclamation-triangle mr-1"></i>
                                            Los puntos normales deben sumar exactamente <strong>100</strong>.
                                            Actualmente suman <strong>{{ number_format($normalPoints, 2) }}</strong>.
                                        </div>
                                    @endif

                                    <table class="table table-hover table-striped mb-0 bg-white">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Tipo de Actividad</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-center">Pts. c/u</th>
                                                <th class="text-center">Total</th>
                                                <th class="text-center">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($managingConfiguration->activities as $activity)
                                                <tr
                                                    class="{{ $activity->activityType->is_extra ? 'table-warning' : '' }}">
                                                    <td>
                                                        {{ $activity->activityType->name }}
                                                        @if ($activity->activityType->is_extra)
                                                            <span class="badge badge-warning ml-1">Extra</span>
                                                        @endif
                                                    </td>
                                                    <td class="text-center">{{ $activity->quantity }}</td>
                                                    <td class="text-center">
                                                        {{ number_format($activity->points_each, 2) }}</td>
                                                    <td class="text-center">
                                                        <strong>{{ number_format($activity->quantity * $activity->points_each, 2) }}</strong>
                                                    </td>
                                                    <td class="text-center">
                                                        <button wire:click="editActivity({{ $activity->id }})"
                                                            class="btn btn-xs btn-warning px-2">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button
                                                            onclick="confirmDeleteActivity({{ $activity->id }}, '{{ addslashes($activity->activityType->name) }}')"
                                                            class="btn btn-xs btn-danger px-2 ml-1">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="alert alert-light border text-center text-muted">
                                        <i class="fas fa-tasks fa-3x mb-2"></i><br>
                                        No hay actividades configuradas aún.
                                    </div>
                                @endif
                            @else
                                {{-- Formulario de actividad --}}
                                <div class="card shadow-sm border-success mb-0">
                                    <div class="card-header bg-white">
                                        <h6 class="card-title text-bold text-success m-0">
                                            <i class="fas {{ $editingActivityId ? 'fa-edit' : 'fa-plus' }} mr-1"></i>
                                            {{ $editingActivityId ? 'Editar Actividad' : 'Agregar Actividad' }}
                                        </h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 form-group mb-3">
                                                <label class="text-sm mb-1">Tipo de Actividad <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-tag"></i></span>
                                                    </div>
                                                    <select wire:model="activity_type_id"
                                                        class="form-control @error('activity_type_id') is-invalid @enderror">
                                                        <option value="">-- Seleccione --</option>
                                                        @foreach ($activityTypes as $type)
                                                            <option value="{{ $type->id }}">
                                                                {{ $type->name }}
                                                                {{ $type->is_extra ? '(Extra)' : '' }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('activity_type_id')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3 form-group mb-3">
                                                <label class="text-sm mb-1">Cantidad <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-list-ol"></i></span>
                                                    </div>
                                                    <input type="number" wire:model="quantity"
                                                        class="form-control @error('quantity') is-invalid @enderror"
                                                        placeholder="Ej. 4" min="1">
                                                    @error('quantity')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                            <div class="col-md-3 form-group mb-3">
                                                <label class="text-sm mb-1">Puntos c/u <span
                                                        class="text-danger">*</span></label>
                                                <div class="input-group input-group-sm">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text"><i
                                                                class="fas fa-star"></i></span>
                                                    </div>
                                                    <input type="number" wire:model="points_each"
                                                        class="form-control @error('points_each') is-invalid @enderror"
                                                        placeholder="Ej. 10" min="0.01" step="0.01">
                                                    @error('points_each')
                                                        <span class="invalid-feedback">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-footer text-right bg-white">
                                        <button wire:click="resetActivityForm" class="btn btn-secondary btn-sm mr-2">
                                            <i class="fas fa-arrow-left"></i> Cancelar
                                        </button>
                                        <button wire:click.prevent="saveActivity" class="btn btn-success btn-sm"
                                            wire:loading.attr="disabled" wire:target="saveActivity">
                                            <span wire:loading.remove wire:target="saveActivity">
                                                <i class="fas fa-save"></i>
                                                {{ $editingActivityId ? 'Actualizar' : 'Guardar' }}
                                            </span>
                                            <span wire:loading wire:target="saveActivity">
                                                <i class="fas fa-spinner fa-pulse"></i> Guardando...
                                            </span>
                                        </button>
                                    </div>
                                </div>

                            @endif
                        @endif
                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark btn-sm" data-dismiss="modal"
                        wire:click="resetActivityForm">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Copiar Configuración --}}
    <div wire:ignore.self class="modal fade" id="CopyConfigurationModal" tabindex="-1" role="dialog"
        data-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title text-white">
                        <i class="fas fa-copy"></i> Copiar Configuración
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border text-sm mb-3">
                        <i class="fas fa-info-circle text-info mr-1"></i>
                        Se copiará el modo y todas las actividades al año que indique.
                    </div>
                    <div class="form-group mb-3">
                        <label class="text-sm mb-1">Año Destino <span class="text-danger">*</span></label>
                        <div class="input-group input-group-sm">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                            </div>
                            <input type="number" wire:model="copy_year"
                                class="form-control @error('copy_year') is-invalid @enderror" placeholder="Ej. 2027"
                                min="2000" max="2100">
                            @error('copy_year')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button wire:click.prevent="copyConfiguration" type="button" class="btn btn-info btn-sm"
                        wire:loading.attr="disabled" wire:target="copyConfiguration">
                        <span wire:loading.remove wire:target="copyConfiguration">
                            <i class="fas fa-copy"></i> Copiar
                        </span>
                        <span wire:loading wire:target="copyConfiguration">
                            <i class="fas fa-spinner fa-pulse"></i> Copiando...
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
                <div class="col-md-4">
                    <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal"
                        data-target="#ConfigurationModal" wire:click="resetFields">
                        <i class="fas fa-plus-circle"></i> Nueva Configuración
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
                            placeholder="Buscar año...">
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
            @if ($readyToLoad && count($configurations))
                <table class="table table-hover table-striped text-nowrap">
                    <thead>
                        <tr>
                            <th style="cursor:pointer" wire:click="order('id')">#
                                <i
                                    class="fas fa-sort{{ $sort === 'id' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th style="cursor:pointer" wire:click="order('year')">Año
                                <i
                                    class="fas fa-sort{{ $sort === 'year' ? '-' . ($direction === 'asc' ? 'up' : 'down') : ' text-muted' }}"></i>
                            </th>
                            <th>Modo</th>
                            <th>Mejora</th>
                            <th>Actividades</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($configurations as $configuration)
                            <tr>
                                <td>{{ $configuration->id }}</td>
                                <td>{{ $configuration->year }}</td>
                                <td>
                                    @if ($configuration->mode === 'free')
                                        <span class="badge badge-secondary">Libre</span>
                                    @else
                                        <span class="badge badge-primary">Asignada</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($configuration->improvement_type === 'full')
                                        <span class="badge badge-success">100%</span>
                                    @elseif ($configuration->improvement_type === 'percentage')
                                        <span
                                            class="badge badge-warning">{{ $configuration->improvement_percentage }}%</span>
                                    @elseif ($configuration->improvement_type === 'additive')
                                        <span class="badge badge-info">Suma</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($configuration->mode === 'assigned')
                                        <span class="badge badge-info">{{ $configuration->activities_count }}</span>
                                    @else
                                        <span class="text-muted text-sm">N/A</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button wire:click="manageActivities({{ $configuration->id }})"
                                        data-toggle="modal" data-target="#ActivitiesModal"
                                        class="btn btn-sm btn-success shadow-sm" title="Gestionar Actividades">
                                        <i class="fas fa-tasks"></i>
                                    </button>
                                    <button wire:click="openCopyModal({{ $configuration->id }})" data-toggle="modal"
                                        data-target="#CopyConfigurationModal" class="btn btn-sm btn-info shadow-sm"
                                        title="Copiar">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                    <button wire:click="edit({{ $configuration->id }})" data-toggle="modal"
                                        data-target="#ConfigurationModal" class="btn btn-sm btn-warning shadow-sm"
                                        title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <button
                                        onclick="confirmDelete({{ $configuration->id }}, '¿Eliminar la configuración del año {{ $configuration->year }}?')"
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
                        <i class="fas fa-spinner fa-spin fa-3x mb-3 text-primary"></i><br>Cargando configuraciones...
                    @else
                        <i class="fas fa-cogs fa-3x mb-3 text-gray"></i><br>No se encontraron registros.
                    @endif
                </div>
            @endif
        </div>

        @if ($readyToLoad && count($configurations) && $configurations->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">{{ $configurations->links() }}</div>
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

            function confirmDeleteActivity(id, nombre) {
                Swal.fire({
                    title: '¿Eliminar ' + nombre + '?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        @this.deleteActivity(id);
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

                Livewire.on('toastMessage', (event) => {
                    let payload = event[0] || event;
                    const Toast = Swal.mixin({
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true,
                    });
                    Toast.fire({
                        icon: payload.type,
                        title: payload.message
                    });
                });
            });
        </script>
    @endpush
</div>
