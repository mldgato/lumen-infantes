<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoleRevoked extends Notification
{
    use Queueable;

    public function __construct(private readonly string $roleName) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'icon' => 'fas fa-user-times',
            'color' => 'warning',
            'title' => 'Rol removido',
            'message' => "Se ha removido el rol: {$this->roleName}",
            'url' => route('dashboard'),
        ];
    }
}
