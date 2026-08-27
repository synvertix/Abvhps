<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ABVHPS Volunteer Assignment Updated – Volunteer ID {{ $volunteerData['volunteer_id'] ?? '' }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 24px; color: #374151;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; padding: 28px;">
        
        <p style="font-size: 15px; font-weight: bold; color: #111827; margin-top: 0;">
            Namaste {{ $volunteerData['volunteer_name'] ?? ($volunteerData['full_name'] ?? 'Volunteer') }},
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your ABVHPS Volunteer organizational assignment has been updated by the Administration.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            <strong>Volunteer ID:</strong> {{ $volunteerData['volunteer_id'] ?? '' }}
        </p>

        <div style="margin: 20px 0; padding: 16px; background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #9a3412;">Updated Assignment</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Cadre:</strong> {{ $volunteerData['cadre_title'] ?? ($volunteerData['designation'] ?? 'Volunteer') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Jurisdiction:</strong> {{ $volunteerData['jurisdiction'] ?? ($volunteerData['locality'] ?? 'HQ') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Status:</strong> Approved &amp; Active</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Effective Date:</strong> {{ $volunteerData['effective_date'] ?? now('Asia/Kolkata')->format('d-M-Y') }}</p>
        </div>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            You can view your updated responsibilities and authorized jurisdiction by signing in to the ABVHPS Volunteer Portal.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            <strong>Volunteer Login Page:</strong><br>
            <a href="{{ $volunteerData['volunteer_login_url'] ?? route('volunteer.login') }}" style="color: #ea580c; text-decoration: underline; font-weight: bold;">
                {{ $volunteerData['volunteer_login_url'] ?? route('volunteer.login') }}
            </a>
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your existing Volunteer Login ID remains:
        </p>

        <p style="font-size: 15px; font-family: monospace; font-weight: bold; color: #ea580c; margin: 4px 0;">
            {{ $volunteerData['volunteer_login_id'] ?? ($volunteerData['volunteer_id'] ?? '') }}
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your password has not been changed as part of this assignment update.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Please keep your login credentials confidential.
        </p>

        @include('emails.partials.footer')
    </div>
</body>
</html>
