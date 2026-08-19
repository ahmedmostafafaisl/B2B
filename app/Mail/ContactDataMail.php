<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactDataMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly array $contactData
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Contact Details - ' . ($this->contactData['name'] ?? 'N/A'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-data',
            with: ['contact' => $this->contactData],
        );
    }
}
