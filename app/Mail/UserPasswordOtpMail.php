<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserPasswordOtpMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $otp,
        protected User $user
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('api.auth.otp_sent_generic')
        );
    }

    public function build(): static
    {
        return $this->html(
            '<p>'.$this->user->name.'</p><p>'.$this->otp.'</p>'
        );
    }
}
