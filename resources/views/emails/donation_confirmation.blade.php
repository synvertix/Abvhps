<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        @if(!empty($donationData['fundraiser_name']))
            Thank You for Supporting {{ $donationData['fundraiser_name'] }} – ABVHPS Receipt {{ $donationData['receipt_number'] ?? '' }}
        @else
            Thank You for Your Donation to ABVHPS – Receipt {{ $donationData['receipt_number'] ?? '' }}
        @endif
    </title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 24px; color: #374151;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; padding: 28px;">
        
        <p style="font-size: 15px; font-weight: bold; color: #111827; margin-top: 0;">
            Namaste {{ $donationData['donor_name'] ?? 'Devotee' }},
        </p>

        @if(!empty($donationData['fundraiser_name']))
            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                Thank you for supporting the ABVHPS fundraiser:
            </p>

            <p style="font-size: 15px; font-weight: bold; color: #ea580c; margin: 8px 0;">
                {{ $donationData['fundraiser_name'] }}
            </p>

            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                Your contribution has been successfully received.
            </p>

            <div style="margin: 20px 0; padding: 16px; background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 4px;">
                <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #9a3412;">Contribution Details</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Receipt Number:</strong> {{ $donationData['receipt_number'] ?? '' }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Contributor Name:</strong> {{ $donationData['donor_name'] ?? '' }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Fundraiser:</strong> {{ $donationData['fundraiser_name'] }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Amount:</strong> ₹{{ number_format((float)($donationData['amount'] ?? 0), 2) }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Contribution Date:</strong> {{ $donationData['contribution_date'] ?? ($donationData['donation_date'] ?? now('Asia/Kolkata')->format('d-M-Y')) }}</p>
            </div>

            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                Your official contribution receipt is attached to this email as a PDF.
            </p>

            <p style="font-size: 13px; color: #4b5563; margin-top: 14px;">
                <strong>Attachment:</strong><br>
                ABVHPS_Fundraiser_Receipt_{{ $donationData['receipt_number'] ?? '' }}.pdf
            </p>

            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                Your support helps ABVHPS carry forward its service initiatives and organizational activities.
            </p>

            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                We sincerely thank you for your contribution.
            </p>
        @else
            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                Thank you for your generous contribution to Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS).
            </p>

            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                Your donation has been successfully received.
            </p>

            <div style="margin: 20px 0; padding: 16px; background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 4px;">
                <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #9a3412;">Donation Details</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Receipt Number:</strong> {{ $donationData['receipt_number'] ?? '' }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Donor Name:</strong> {{ $donationData['donor_name'] ?? '' }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Amount:</strong> ₹{{ number_format((float)($donationData['amount'] ?? 0), 2) }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Date:</strong> {{ $donationData['donation_date'] ?? now('Asia/Kolkata')->format('d-M-Y') }}</p>
                <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Purpose:</strong> {{ $donationData['donation_purpose'] ?? ($donationData['purpose'] ?? 'General Contribution Fund') }}</p>
            </div>

            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                Your official donation receipt is attached to this email as a PDF.
            </p>

            <p style="font-size: 13px; color: #4b5563; margin-top: 14px;">
                <strong>Attachment:</strong><br>
                ABVHPS_Donation_Receipt_{{ $donationData['receipt_number'] ?? '' }}.pdf
            </p>

            <p style="font-size: 14px; line-height: 1.6; color: #374151;">
                We sincerely appreciate your support and contribution toward the service activities and objectives of ABVHPS.
            </p>
        @endif

        @include('emails.partials.footer')
    </div>
</body>
</html>
