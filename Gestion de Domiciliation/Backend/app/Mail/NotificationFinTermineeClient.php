<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Domiciliation;
use App\Models\Societe;
use SoapClient;

class NotificationFinTermineeClient extends Mailable
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
            subject: 'Fin de votre contrat de domiciliation - Action requise',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.client.fin',
            with: [
                'domiciliation' => $this->domiciliation,
                'client' => $this->domiciliation->client,
                'societe' => $this->societe,
                'dateFinFormatted' => $this->domiciliation->date_fin ? 
                    \Carbon\Carbon::parse($this->domiciliation->date_fin)->format('d/m/Y') : 
                    'Non définie'
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