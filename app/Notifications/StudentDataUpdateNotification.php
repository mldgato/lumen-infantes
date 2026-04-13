<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentDataUpdateNotification extends Notification
{
    public function __construct(
        public readonly string $token,
        public readonly string $firstName,
    ) {}

    /** @return list<string> */
    public function via(mixed $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(mixed $notifiable): MailMessage
    {
        $url = url(route('student.data.verify', ['token' => $this->token], false));
        $institutionName = config('app.institution_name', 'EduCheck');

        return (new MailMessage)
            ->subject('Actualización de Datos — '.$institutionName)
            ->view('emails.student-data-update', [
                'url' => $url,
                'institutionName' => $institutionName,
                'firstName' => $this->firstName,
            ]);
    }
}
