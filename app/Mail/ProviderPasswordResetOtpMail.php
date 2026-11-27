<?php

namespace App\Mail;

use App\Models\ServiceProvider;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProviderPasswordResetOtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ServiceProvider $provider,
        public string $otpCode,
        public int $expiresInMinutes = 10
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'رمز التحقق (OTP) لاستعادة كلمة المرور — بوابة مقدمي الخدمات',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.provider-password-otp',
        );
    }
}
