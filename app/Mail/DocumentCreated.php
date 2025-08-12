<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DocumentCreated extends Mailable
{
    use Queueable, SerializesModels;
    public $documento;

    /**
     * Create a new message instance.
     */
    public function __construct($documento)
    {
        $this->documento = $documento;
    }

    public function build()
    {
        return $this->markdown('emails.create')->replyTo(Auth::user()->email, Auth::user()->name);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Criação de Documento',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.create',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
