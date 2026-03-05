<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProviderPasswordResetNotification extends Notification implements ShouldQueue
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
                ->subject('رمز إعادة تعيين كلمة المرور')
                ->line('أنت تتلقى هذا البريد الإلكتروني لأننا تلقينا طلب إعادة تعيين كلمة المرور لحسابك.')
                ->line('رمز التحقق الخاص بك هو: **' . $this->otp . '**')
                ->line('سينتهي صلاحية هذا الرمز خلال 10 دقائق.')
                ->line('إذا لم تطلب إعادة تعيين كلمة المرور، فلا حاجة لاتخاذ أي إجراء.');
        }

        return (new MailMessage)
            ->subject('Provider Password Reset')
            ->line('You are receiving this email because we received a password reset request for your account.')
            ->line('Your verification code is: **' . $this->otp . '**')
            ->line('This code will expire in 10 minutes.')
            ->line('If you did not request a password reset, no further action is required.');
    }
}
