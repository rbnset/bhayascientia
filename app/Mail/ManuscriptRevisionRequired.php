<?php

namespace App\Mail;

use App\Models\Publication;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ManuscriptRevisionRequired extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly Publication $publication,
        public readonly Review $review,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '✏️ Revisi Diperlukan — ' . str($this->publication->title)->limit(60),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.manuscript-revision-required',
            with: [
                'publication'       => $this->publication,
                'review'            => $this->review,
                'authorName'        => $this->publication->creator?->name ?? 'Penulis',
                'title'             => $this->publication->title,
                'revisionDeadline'  => $this->review->revision_deadline,
                'overallComment'    => $this->review->overall_comment,
                'editUrl'           => route('filament.admin.resources.publications.edit', [
                    'record' => $this->publication->id,
                ]),
            ],
        );
    }
}
