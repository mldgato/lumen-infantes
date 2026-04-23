<div wire:init="loadData">
    @if ($readyToLoad)
        <div class="col-lg-4 col-md-12">
            <div class="card card-outline card-info shadow-sm h-100">
                <div class="card-header">
                    <h5 class="card-title m-0 text-bold">
                        <i class="fas fa-bell mr-1 text-info"></i> Próximos Cumpleaños
                        <span class="badge badge-info ml-1">Personal</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    @if (empty($upcomingBirthdays))
                        <div class="text-center py-4 text-muted">
                            <i class="fas fa-calendar-times fa-2x mb-2 d-block"></i>
                            <small>Sin información disponible.</small>
                        </div>
                    @else
                        <ul class="list-unstyled m-0">
                            @foreach ($upcomingBirthdays as $person)
                                <li class="d-flex align-items-center px-3 py-2 {{ !$loop->last ? 'border-bottom' : '' }}"
                                    style="{{ $person['is_today'] ? 'background-color: rgba(23,162,184,0.08);' : '' }}">

                                    <img src="{{ $person['image'] }}" alt="{{ $person['initials'] }}"
                                        class="img-circle mr-2 flex-shrink-0"
                                        style="width: 40px; height: 40px; object-fit: cover;
                                        border: 2px solid {{ $person['is_today'] ? '#17a2b8' : '#dee2e6' }};">

                                    <div class="flex-grow-1" style="min-width: 0;">
                                        <div class="font-weight-bold text-dark text-truncate" style="font-size: 0.82rem;">
                                            {{ $person['name'] }}
                                        </div>
                                        <div class="text-muted" style="font-size: 0.68rem;">
                                            <i class="fas fa-tag mr-1"></i>{{ $person['role'] }}
                                        </div>
                                    </div>

                                    <div class="text-right ml-2 flex-shrink-0">
                                        @if ($person['is_today'])
                                            <span class="badge badge-info">🎂 ¡Hoy!</span>
                                        @else
                                            <div class="font-weight-bold text-dark" style="font-size: 0.78rem;">
                                                {{ $person['day'] }} {{ $person['month'] }}
                                            </div>
                                            <div class="text-muted" style="font-size: 0.65rem;">
                                                en {{ $person['days_until'] }}d
                                            </div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>
