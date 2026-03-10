<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AdminEmailVerificationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private string $otp
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $locale = app()->getLocale();
        if ($locale === 'ar') {
            return (new MailMessage)
                ->subject(__('api.auth.email_verification_subject'))
                ->line(__('api.auth.email_verification_line'))
                ->line('رمز التحقق: **' . $this->otp . '**')
                ->line(__('api.auth.email_verification_expiry'));
        }
        return (new MailMessage)
            ->subject(__('api.auth.email_verification_subject'))
            ->line(__('api.auth.email_verification_line'))
            ->line('Your verification code: **' . $this->otp . '**')
            ->line(__('api.auth.email_verification_expiry'));
    }
}
