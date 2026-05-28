<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RoleAssigned extends Notification
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
            'icon' => 'fas fa-user-check',
            'color' => 'success',
            'title' => 'Rol asignado',
            'message' => "Se te ha asignado el rol: {$this->roleName}",
            'url' => route('dashboard'),
        ];
    }
}
