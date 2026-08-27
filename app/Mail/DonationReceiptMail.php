<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class DonationReceiptMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $donationData;
    protected ?string $pdfContent;

    /**
     * Create a new message instance.
     */
    public function __construct(array $donationData, ?string $pdfContent = null)
    {
        $this->donationData = $donationData;
        $this->pdfContent   = $pdfContent;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $receipt = $this->donationData['receipt_number'] ?? 'RECEIPT';
        $fundraiser = $this->donationData['fundraiser_name'] ?? null;

        if (!empty($fundraiser)) {
            return new Envelope(
                subject: "Thank You for Supporting {$fundraiser} – ABVHPS Receipt {$receipt}",
            );
        }

        return new Envelope(
            subject: "Thank You for Your Donation to ABVHPS – Receipt {$receipt}",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.donation_confirmation',
            with: [
                'donationData' => $this->donationData,
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
            $receipt = $this->donationData['receipt_number'] ?? 'RECEIPT';
            $cleanReceipt = preg_replace('/[^A-Za-z0-9_\-]/', '_', $receipt);
            $prefix = !empty($this->donationData['fundraiser_name']) ? 'ABVHPS_Fundraiser_Receipt_' : 'ABVHPS_Donation_Receipt_';

            $attachments[] = Attachment::fromData(
                fn () => $this->pdfContent,
                $prefix . $cleanReceipt . '.pdf'
            )->withMime('application/pdf');
        }

        return $attachments;
    }
}
