<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends ResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        $institutionName = env('APP_INSTITUTION_NAME', 'EduCheck');
        $expireMinutes   = config('auth.passwords.users.expire', 60);
        $firstName       = $notifiable->first_name ?? $notifiable->name;

        return (new MailMessage)
            ->subject('Restablecer Contrasena — ' . $institutionName)
            ->view('emails.reset-password', [
                'url'             => $url,
                'institutionName' => $institutionName,
                'firstName'       => $firstName,
                'expireMinutes'   => $expireMinutes,
            ]);
    }
}
