<?php

namespace App\Mail;

use App\Models\OtpCode;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpVerificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public OtpCode $otpCode
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Kode Verifikasi DABRAKA – ' . $this->otpCode->code,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp-verification',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
