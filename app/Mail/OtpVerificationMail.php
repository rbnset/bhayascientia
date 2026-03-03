<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $otpCode,
        public string $userName,
        public string $userEmail,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Kode Verifikasi Email – DABRAKA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
            with: [
                'otpCode'   => $this->otpCode,
                'userName'  => $this->userName,
                'userEmail' => $this->userEmail,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
