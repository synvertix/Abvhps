<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VolunteerRejectedStatusMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $volunteerData;

    public function __construct(array $volunteerData)
    {
        $this->volunteerData = $volunteerData;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'ABVHPS Volunteer Application Status Update',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.volunteer_rejected_status',
            with: [
                'volunteerData' => $this->volunteerData,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
