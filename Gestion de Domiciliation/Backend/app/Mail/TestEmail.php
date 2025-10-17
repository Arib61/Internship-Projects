<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\Societe;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $clientName;
    public $messagePro;
    public $societe;

    public function __construct($clientName, $messagePro)
    {
        $this->clientName = $clientName;
        $this->messagePro = $messagePro;
        $this->societe = Societe::first(); // Récupère la société (tu peux adapter)
    }

    public function build()
    {
        return $this->subject('Notification Professionnelle')
                    ->view('emails.test')
                    ->with([
                        'clientName' => $this->clientName,
                        'messagePro' => $this->messagePro,
                        'societe' => $this->societe,
                    ]);
    }
}
