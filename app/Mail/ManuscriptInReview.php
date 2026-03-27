<?php

namespace App\Mail;

use App\Models\Publication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManuscriptInReview extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Publication $publication,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔍 Naskah Anda Sedang Direview — ' . str($this->publication->title)->limit(60),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manuscript-in-review',
            with: [
                'publication' => $this->publication,
                'authorName'  => $this->publication->creator?->name ?? 'Penulis',
                'title'       => $this->publication->title,
                'editUrl'     => route('filament.admin.resources.publications.edit', ['record' => $this->publication->id]),
            ],
        );
    }
}
