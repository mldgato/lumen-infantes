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

        $institutionName = html_entity_decode(
            htmlspecialchars_decode(env('APP_INSTITUTION_NAME', 'Lumen'), ENT_QUOTES),
            ENT_QUOTES,
            'UTF-8'
        );

        $expireMinutes = config('auth.passwords.users.expire', 60);
        $firstName     = $notifiable->first_name ?? $notifiable->name;

        return (new MailMessage)
            ->subject('Restablecer Contraseña — ' . $institutionName)
            ->greeting('Hola ' . $firstName . ',')
            ->line('Recibiste este correo porque se solicitó un restablecimiento de contraseña para tu cuenta en **' . $institutionName . '**.')
            ->action('Restablecer Contraseña', $url)
            ->line('Este enlace expirará en **' . $expireMinutes . ' minutos**.')
            ->line('Si no solicitaste este cambio, puedes ignorar este correo, tu contraseña no será modificada.')
            ->salutation('Atentamente, ' . $institutionName);
    }
}
