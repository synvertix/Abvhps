<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ABVHPS Volunteer Application Received</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 24px; color: #374151;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; padding: 28px;">
        
        <p style="font-size: 15px; font-weight: bold; color: #111827; margin-top: 0;">
            Namaste {{ $volunteerData['volunteer_name'] ?? ($volunteerData['full_name'] ?? 'Volunteer') }},
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Thank you for submitting your application to serve as a Volunteer with Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS).
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your Volunteer application has been successfully received.
        </p>

        <div style="margin: 20px 0; padding: 16px; background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #9a3412;">Application Details</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Name:</strong> {{ $volunteerData['volunteer_name'] ?? ($volunteerData['full_name'] ?? '') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Membership ID:</strong> {{ $volunteerData['membership_id'] ?? '' }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Application Date:</strong> {{ $volunteerData['application_date'] ?? now('Asia/Kolkata')->format('d-M-Y') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Application Status:</strong> Pending Review</p>
        </div>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your application will now be reviewed by the ABVHPS Administration.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            You will receive another email once a decision or status update has been made.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Submitting a Volunteer application does not itself grant Volunteer login access or organizational authority. Access will be provided only after approval by the Administration.
        </p>

        @include('emails.partials.footer')
    </div>
</body>
</html>
