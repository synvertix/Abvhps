<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VolunteerAssignmentUpdatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $volunteerData;

    public function __construct(array $volunteerData)
    {
        $this->volunteerData = $volunteerData;
    }

    public function envelope(): Envelope
    {
        $id = $this->volunteerData['volunteer_id'] ?? ($this->volunteerData['formatted_volunteer_id'] ?? '');

        return new Envelope(
            subject: "ABVHPS Volunteer Assignment Updated – Volunteer ID {$id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.volunteer_assignment_updated',
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
