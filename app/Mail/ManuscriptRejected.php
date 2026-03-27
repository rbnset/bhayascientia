<?php

namespace App\Mail;

use App\Models\Publication;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManuscriptRejected extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Publication $publication,
        public readonly Review $review,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '📋 Hasil Review Naskah — ' . str($this->publication->title)->limit(60),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manuscript-rejected',
            with: [
                'publication'    => $this->publication,
                'review'         => $this->review,
                'authorName'     => $this->publication->creator?->name ?? 'Penulis',
                'title'          => $this->publication->title,
                'overallComment' => $this->review->overall_comment,
                'editUrl'        => route('filament.admin.resources.publications.edit', [
                    'record' => $this->publication->id,
                ]),
            ],
        );
    }
}
