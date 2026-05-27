<li class="nav-item dropdown" wire:poll.30s="$refresh">
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
        <i class="far fa-bell"></i>
        @if ($unreadCount > 0)
            <span class="badge badge-danger navbar-badge">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
        @endif
    </a>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="overflow: hidden;">

        {{-- Header --}}
        <span class="dropdown-item dropdown-header">
            {{ $unreadCount > 0 ? $unreadCount . ' notificación(es) nueva(s)' : 'Sin notificaciones nuevas' }}
        </span>

        <div class="dropdown-divider"></div>

        {{-- Lista de notificaciones con scroll --}}
        <div style="max-height: 255px; overflow-y: auto; overflow-x: hidden;">
            @forelse ($notifications as $notification)
                @php
                    $data    = $notification->data;
                    $color   = $data['color'] ?? 'secondary';
                    $icon    = $data['icon']  ?? 'fas fa-bell';
                    $title   = $data['title'] ?? '';
                    $message = $data['message'] ?? '';
                @endphp
                <a href="#"
                   wire:click.prevent="markReadAndRedirect('{{ $notification->id }}')"
                   class="dropdown-item">
                    <i class="{{ $icon }} mr-2 text-{{ $color }}"></i>
                    <span class="d-inline-block" style="max-width:260px; white-space:normal; vertical-align:middle;">
                        <span class="d-block text-sm">{{ $title }}</span>
                        <span class="d-block text-xs text-muted">{{ $message }}</span>
                        <span class="d-block text-xs text-muted mt-1">
                            <i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                        </span>
                    </span>
                </a>
                <div class="dropdown-divider"></div>
            @empty
                <span class="dropdown-item text-center text-muted text-sm py-2">
                    <i class="far fa-bell-slash mr-1"></i> Sin notificaciones
                </span>
                <div class="dropdown-divider"></div>
            @endforelse
        </div>

        {{-- Footer --}}
        @if ($unreadCount > 0)
            <a href="#" wire:click.prevent="markAllRead" class="dropdown-item dropdown-footer text-center text-xs">
                <i class="fas fa-check-double mr-1"></i> Marcar todas como leídas
            </a>
        @endif

    </div>
</li>
