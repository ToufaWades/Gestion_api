<?php

namespace App\Mail;

use App\Models\Compte;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CompteCreationConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public Compte $compte;

    /**
     * Create a new message instance.
     */
    public function __construct(Compte $compte)
    {
        $this->compte = $compte;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Confirmation de création de compte',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.compte-creation-confirmation',
            with: [
                'compte' => $this->compte,
                'client' => $this->compte->client,
            ],
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