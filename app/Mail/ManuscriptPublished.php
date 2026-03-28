<?php

namespace App\Mail;

use App\Models\Publication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManuscriptPublished extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Publication $publication,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🎉 Karya Anda Telah Diterbitkan — ' . str($this->publication->title)->limit(60),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manuscript-published',
            with: [
                'publication' => $this->publication,
                'title'       => $this->publication->title,
                'type'        => $this->publication->publicationType?->name ?? 'Publikasi',
                'publishedAt' => $this->publication->published_at
                    ?->timezone('Asia/Jakarta')
                    ->locale('id')
                    ->isoFormat('D MMMM YYYY, HH:mm'),
                'publicUrl'   => route('publikasi.show', ['slug' => $this->publication->slug]),
            ],
        );
    }
}
