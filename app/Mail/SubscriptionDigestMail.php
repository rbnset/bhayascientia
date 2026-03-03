<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class SubscriptionDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Subscription  $subscription,
        public Collection    $publications,
        public string        $digestType, // instant|daily|weekly_new|weekly_popular|monthly_popular
        public string        $periodLabel // "Hari Ini", "Minggu Ini", dst
    ) {}

    public function envelope(): Envelope
    {
        $subjects = [
            'instant'          => '🔔 Publikasi Baru di DABRAKA',
            'daily'            => '🌅 Ringkasan Harian DABRAKA – ' . now()->setTimezone('Asia/Jakarta')->format('d M Y'),
            'weekly_new'       => '📅 Publikasi Terbaru Minggu Ini – DABRAKA',
            'weekly_popular'   => '🔥 Publikasi Terpopuler Minggu Ini – DABRAKA',
            'monthly_popular'  => '⭐ Publikasi Terbaik Bulan Ini – DABRAKA',
        ];

        return new Envelope(
            subject: $subjects[$this->digestType] ?? '📬 Update Terbaru dari DABRAKA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.subscription-digest',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
