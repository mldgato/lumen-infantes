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

        $rawName = env('APP_INSTITUTION_NAME', 'Lumen');

        // Convierte caracteres no-ASCII a entidades HTML para usar en la vista HTML
        $institutionName = preg_replace_callback('/[\x{0080}-\x{FFFF}]/u', function ($match) {
            return '&#' . mb_ord($match[0]) . ';';
        }, $rawName);

        $expireMinutes = config('auth.passwords.users.expire', 60);
        $firstName     = $notifiable->first_name ?? $notifiable->name;

        return (new MailMessage)
            ->subject('Restablecer Contrasena — ' . $rawName)
            ->view('emails.reset-password', [
                'url'             => $url,
                'institutionName' => $institutionName,
                'firstName'       => $firstName,
                'expireMinutes'   => $expireMinutes,
            ]);
    }
}
