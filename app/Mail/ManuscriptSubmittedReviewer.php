<?php

namespace App\Mail;

use App\Models\Publication;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManuscriptSubmittedReviewer extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Publication $publication,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 Publikasi Baru Menunggu Review — ' . str($this->publication->title)->limit(60),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manuscript-submitted-reviewer',
            with: [
                'publication'  => $this->publication,
                'title'        => $this->publication->title,
                'type'         => $this->publication->publicationType?->name ?? 'Publikasi',
                'authorNames'  => $this->publication->authors->pluck('name')->join(', ') ?: 'Tidak diketahui',
                'reviewUrl'    => route('filament.admin.resources.publications.edit', ['record' => $this->publication->id]),
            ],
        );
    }
}
