<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome to ABVHPS Volunteer Service – Volunteer ID {{ $volunteerData['volunteer_id'] ?? '' }}</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 24px; color: #374151;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 8px; overflow: hidden; border: 1px solid #e5e7eb; padding: 28px;">

        <p style="font-size: 15px; font-weight: bold; color: #111827; margin-top: 0;">
            Namaste {{ $volunteerData['volunteer_name'] ?? ($volunteerData['full_name'] ?? 'Volunteer') }},
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Congratulations.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your application to serve as a Volunteer with Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS) has been approved.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Welcome to the ABVHPS Volunteer Service.
        </p>

        <div style="margin: 20px 0; padding: 16px; background-color: #fff7ed; border-left: 4px solid #ea580c; border-radius: 4px;">
            <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #9a3412;">Your Official Volunteer Details</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Volunteer ID:</strong> {{ $volunteerData['volunteer_id'] ?? '' }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Membership ID:</strong> {{ $volunteerData['membership_id'] ?? '' }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Name:</strong> {{ $volunteerData['volunteer_name'] ?? ($volunteerData['full_name'] ?? '') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Assigned Cadre:</strong> {{ $volunteerData['cadre_title'] ?? ($volunteerData['designation'] ?? 'Volunteer') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Assigned Jurisdiction:</strong> {{ $volunteerData['jurisdiction'] ?? ($volunteerData['locality'] ?? 'HQ') }}</p>
            <p style="margin: 4px 0; font-size: 13px; color: #374151;"><strong>Status:</strong> Approved &amp; Active</p>
        </div>

        <div style="margin: 20px 0; padding: 16px; background-color: #f8fafc; border: 1px solid #cbd5e1; border-radius: 6px;">
            <p style="margin: 0 0 10px 0; font-size: 14px; font-weight: bold; color: #1e293b; text-transform: uppercase;">VOLUNTEER LOGIN DETAILS</p>
            <p style="margin: 6px 0; font-size: 13px; color: #374151;">
                <strong>Login ID:</strong><br>
                <span style="font-family: monospace; font-size: 14px; font-weight: bold; color: #ea580c;">{{ $volunteerData['volunteer_login_id'] ?? '' }}</span>
            </p>
            @if(!empty($volunteerData['temporary_password']) || !empty($volunteerData['plainPassword']))
            <p style="margin: 6px 0; font-size: 13px; color: #374151;">
                <strong>Temporary Password:</strong><br>
                <span style="font-family: monospace; font-size: 14px; font-weight: bold; color: #ea580c;">{{ $volunteerData['temporary_password'] ?? ($volunteerData['plainPassword'] ?? '') }}</span>
            </p>
            @endif
            <p style="margin: 6px 0; font-size: 13px; color: #374151;">
                <strong>Volunteer Login Page:</strong><br>
                <a href="{{ $volunteerData['volunteer_login_url'] ?? route('volunteer.login') }}" style="color: #ea580c; text-decoration: underline; font-weight: bold;">
                    {{ $volunteerData['volunteer_login_url'] ?? route('volunteer.login') }}
                </a>
            </p>
        </div>

        <div style="margin: 20px 0; padding: 14px 16px; background-color: #fef2f2; border-left: 4px solid #ef4444; border-radius: 4px;">
            <p style="margin: 0 0 6px 0; font-size: 13px; font-weight: bold; color: #991b1b; text-transform: uppercase;">IMPORTANT SECURITY INSTRUCTION</p>
            <p style="margin: 4px 0; font-size: 12.5px; color: #7f1d1d; line-height: 1.5;">
                The password provided above is a temporary first-login password.
            </p>
            <p style="margin: 4px 0; font-size: 12.5px; color: #7f1d1d; line-height: 1.5; font-weight: bold;">
                Please log in to the ABVHPS Volunteer Portal and CHANGE YOUR PASSWORD IMMEDIATELY after your first successful login.
            </p>
            <p style="margin: 4px 0; font-size: 12.5px; color: #7f1d1d; line-height: 1.5;">
                Do not share your Volunteer Login ID or password with anyone.
            </p>
            <p style="margin: 4px 0; font-size: 12.5px; color: #7f1d1d; line-height: 1.5;">
                ABVHPS representatives will never ask you to send your password by email, telephone, WhatsApp, or any other communication channel.
            </p>
        </div>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your Volunteer Portal provides access according to the cadre and geographic jurisdiction officially assigned to you by the ABVHPS Administration.
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            Your official ABVHPS Volunteer ID Card is attached to this email as a PDF.
        </p>

        <p style="font-size: 13px; color: #4b5563; margin-top: 14px;">
            <strong>Attachment:</strong><br>
            ABVHPS_Volunteer_ID_{{ $volunteerData['volunteer_id'] ?? '' }}.pdf
        </p>

        <p style="font-size: 14px; line-height: 1.6; color: #374151;">
            We thank you for accepting the responsibility to serve through ABVHPS and wish you success in your assigned service responsibilities.
        </p>

        @include('emails.partials.footer')
    </div>
</body>
</html>
