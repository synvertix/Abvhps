<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class MembershipWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $memberData;
    protected ?string $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(array $memberData, ?string $pdfContent = null)
    {
        $this->memberData = $memberData;
        $this->pdfContent = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $id = $this->memberData['membership_id'] ?? ($this->memberData['formatted_id'] ?? 'MEMBER');

        return new Envelope(
            subject: "Welcome to ABVHPS – Your Membership ID {$id}",
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
        $attachments = [];

        if (!empty($this->pdfContent)) {
            $id = $this->memberData['membership_id'] ?? ($this->memberData['formatted_id'] ?? 'MEMBER');
            $cleanId = str_replace(' ', '', $id);
            $attachments[] = Attachment::fromData(
                fn () => $this->pdfContent,
                'ABVHPS_Membership_ID_' . $cleanId . '.pdf'
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
