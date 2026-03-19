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

        $institutionName = htmlspecialchars(
            env('APP_INSTITUTION_NAME', 'Lumen'),
            ENT_QUOTES | ENT_HTML5,
            'UTF-8'
        );
        $expireMinutes   = config('auth.passwords.users.expire', 60);
        $firstName       = $notifiable->first_name ?? $notifiable->name;

        return (new MailMessage)
            ->subject('Restablecer Contrase&#241;a — ' . env('APP_INSTITUTION_NAME', 'Lumen'))
            ->view('emails.reset-password', [
                'url'             => $url,
                'institutionName' => $institutionName,
                'firstName'       => $firstName,
                'expireMinutes'   => $expireMinutes,
            ]);
    }
}
