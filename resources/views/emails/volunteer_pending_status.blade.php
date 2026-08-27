<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ABVHPS Volunteer Application Status Update – Pending Review</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 24px; color: #374151;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; padding: 28px;">
        
        <p style="font-size: 15px; font-weight: bold; color: #111827; margin-top: 0;">
            Namaste {{ $volunteerData['volunteer_name'] ?? ($volunteerData['full_name'] ?? 'Volunteer') }},
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            This is an update regarding your ABVHPS Volunteer application.
        </p>

        <div style="margin: 20px 0; padding: 16px; background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #9a3412;">Application Details</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Membership ID:</strong> {{ $volunteerData['membership_id'] ?? '' }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Current Status:</strong> Pending Review</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Updated On:</strong> {{ $volunteerData['status_updated_at'] ?? now('Asia/Kolkata')->format('d-M-Y') }}</p>
        </div>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your application is currently under review by the ABVHPS Administration.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            No action is required from you at this time unless an authorized ABVHPS representative contacts you requesting additional information.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            You will receive another automated email when your application status changes.
        </p>

        @include('emails.partials.footer')
    </div>
</body>
</html>
