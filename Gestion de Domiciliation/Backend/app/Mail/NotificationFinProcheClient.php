<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Domiciliation;
use App\Models\Societe;

class NotificationFinProcheClient extends Mailable
{
    use Queueable, SerializesModels;

    public $domiciliation;
    public $societe;

    /**
     * Create a new message instance.
     */
    public function __construct(Domiciliation $domiciliation, Societe $societe)
    {
        $this->domiciliation = $domiciliation;
        $this->societe = $societe;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Rappel: Votre domiciliation expire dans 15 jours',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.client.fin-proche',
            with: [
                'domiciliation' => $this->domiciliation,
                'client' => $this->domiciliation->client,
                'daysRemaining' => 15,
                'societe' => $this->societe
            ]
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