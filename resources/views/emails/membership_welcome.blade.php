<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to ABVHPS – Your Membership ID {{ $memberData['membership_id'] ?? '' }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 24px; color: #374151;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; padding: 28px;">

        <p style="font-size: 15px; font-weight: bold; color: #111827; margin-top: 0;">
            Namaste {{ $memberData['member_name'] ?? ($memberData['full_name'] ?? 'Member') }},
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Welcome to Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS).
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            We are pleased to confirm that your membership registration has been successfully completed.
        </p>

        <div style="margin: 20px 0; padding: 16px; background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #9a3412;">Your Membership Details</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Membership ID:</strong> {{ $memberData['membership_id'] ?? '' }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Name:</strong> {{ $memberData['member_name'] ?? ($memberData['full_name'] ?? '') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Membership Status:</strong> Active</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Registration Date:</strong> {{ $memberData['registration_date'] ?? now('Asia/Kolkata')->format('d-M-Y') }}</p>
        </div>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your official ABVHPS Membership ID Card is attached to this email as a PDF.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Please keep your Membership ID safe and quote it whenever you communicate with ABVHPS regarding your membership.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            We sincerely thank you for becoming a member of ABVHPS and for supporting our service and organizational activities.
        </p>

        <p style="font-size: 13px; color: #4b5563; margin-top: 16px;">
            <strong>Attachment:</strong><br>
            ABVHPS_Membership_ID_{{ $memberData['membership_id'] ?? '' }}.pdf
        </p>

        @include('emails.partials.footer')
    </div>
</body>
</html>
