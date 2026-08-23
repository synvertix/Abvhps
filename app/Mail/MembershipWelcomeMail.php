<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembershipWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $memberData;

    /**
     * Create a new message instance.
     */
    public function __construct(array $memberData)
    {
        $this->memberData = $memberData;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $id = $this->memberData['formatted_id'] ?? ($this->memberData['membership_id'] ?? 'MEMBER');

        return new Envelope(
            subject: "🙏 Welcome to ABVHPS — Your Membership ID: {$id}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.membership_welcome',
            with: [
                'memberData' => $this->memberData,
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
