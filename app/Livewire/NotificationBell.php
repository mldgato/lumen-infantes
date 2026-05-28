<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationBell extends Component
{
    public function markAllRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();
    }

    public function markReadAndRedirect(string $id): void
    {
        $notification = Auth::user()->notifications()->find($id);

        if (! $notification) {
            return;
        }

        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        if ($url && $url !== '#') {
            $this->redirect($url);
        }
    }

    public function render()
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications()->latest()->take(10)->get();
        $unreadCount = $notifications->count();

        return view('livewire.notification-bell', compact('notifications', 'unreadCount'));
    }
}
