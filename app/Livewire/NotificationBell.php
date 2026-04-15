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

    public function markRead(string $id): void
    {
        Auth::user()->notifications()->find($id)?->markAsRead();
    }

    public function render()
    {
        $user = Auth::user();
        $notifications = $user->notifications()->latest()->take(10)->get();
        $unreadCount = $user->unreadNotifications()->count();

        return view('livewire.notification-bell', compact('notifications', 'unreadCount'));
    }
}
