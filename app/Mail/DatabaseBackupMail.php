<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DatabaseBackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $zipPath,
        protected string $zipFilename,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Penny Post database backup — '.now()->format('j F Y'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.database-backup',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->zipPath)
                ->as($this->zipFilename)
                ->withMime('application/zip'),
        ];
    }
}

