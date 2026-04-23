<div wire:init="loadData" style="display: contents;">
    @if ($readyToLoad)
        <div class="col-lg-8 col-md-12 mb-3 mb-lg-0">
            <div class="card card-outline card-warning shadow-sm h-100">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title m-0 text-bold flex-grow-1">
                        <i class="fas fa-birthday-cake mr-1 text-warning"></i>
                        Cumpleañeros de {{ ucfirst(\Carbon\Carbon::now()->locale('es')->isoFormat('MMMM')) }}
                        <span class="badge badge-warning ml-1">Estudiantes</span>
                    </h5>
                    <span class="badge badge-light text-muted">{{ count($birthdayStudents) }} este mes</span>
                </div>
                <div class="card-body p-2" style="overflow-x: auto;">
                    @if (empty($birthdayStudents))
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            <small>No hay estudiantes cumpleañeros este mes.</small>
                        </div>
                    @else
                        <div class="d-flex flex-wrap p-1" style="gap: 0.5rem;">
                            @foreach ($birthdayStudents as $student)
                                <div class="d-flex flex-column align-items-center text-center p-2 rounded"
                                    style="width: 88px; min-width: 80px; position: relative;
                                    background-color: {{ $student['is_today'] ? 'rgba(255,193,7,0.15)' : '#f8f9fa' }};
                                    border: 1px solid {{ $student['is_today'] ? '#ffc107' : '#dee2e6' }};">

                                    @if ($student['is_today'])
                                        <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%);
                                             background: #ffc107; color: #212529; font-size: 0.58rem;
                                             font-weight: 700; padding: 1px 5px; border-radius: 8px; white-space: nowrap;">
                                            🎂 ¡Hoy!
                                        </span>
                                    @endif

                                    <img src="{{ $student['image'] }}" alt="{{ $student['initials'] }}"
                                        class="img-circle"
                                        style="width: 48px; height: 48px; object-fit: cover; margin-bottom: 5px;
                                        border: 2px solid {{ $student['is_today'] ? '#ffc107' : '#ced4da' }};">

                                    <small class="font-weight-bold text-dark d-block"
                                        style="font-size: 0.68rem; line-height: 1.3; word-break: break-word;">
                                        {{ $student['name'] }}
                                    </small>

                                    <span class="badge mt-1"
                                        style="font-size: 0.62rem;
                                         background-color: {{ $student['is_today'] ? '#ffc107' : '#6c757d' }};
                                         color: {{ $student['is_today'] ? '#212529' : '#fff' }};">
                                        Día {{ $student['day'] }}
                                    </span>

                                    <small class="text-muted" style="font-size: 0.62rem;">
                                        {{ $student['age'] }} años
                                    </small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
