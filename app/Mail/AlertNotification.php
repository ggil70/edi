<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $mensaje;
    public $subjectLine;

    public function __construct($mensaje, $subjectLine)
    {
        $this->mensaje = $mensaje;
        $this->subjectLine = $subjectLine;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subjectLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alert', // Crearemos esta vista ahora
        );
    }
}