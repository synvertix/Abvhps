<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Welcome to ABVHPS</title>
    <style>
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: #f8fafc;
            margin: 0;
            padding: 24px;
            color: #334155;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #1e293b;
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 4px solid #ea580c;
        }
        .header h1 {
            color: #ea580c;
            margin: 0;
            font-size: 18px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .header p {
            color: #cbd5e1;
            margin: 5px 0 0;
            font-size: 12px;
        }
        .content {
            padding: 30px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .details-box {
            background-color: #fff7ed;
            border: 2px solid #fed7aa;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .details-box h2 {
            margin: 0 0 12px;
            font-size: 14px;
            color: #c2410c;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px dashed #fdba74;
            font-size: 13px;
        }
        .detail-row:last-child {
            border-bottom: none;
        }
        .detail-label {
            color: #7c2d12;
            font-weight: 600;
        }
        .detail-value {
            color: #0f172a;
            font-weight: bold;
            font-family: 'Consolas', 'Courier New', monospace;
        }
        .btn-action {
            display: inline-block;
            background-color: #ea580c;
            color: #ffffff !important;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 15px;
        }
        .footer {
            background-color: #f1f5f9;
            padding: 20px;
            text-align: center;
            font-size: 11px;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <img src="{{ asset('images/ABVHPS_LOGO.jpg') }}" width="56" height="56" style="border-radius: 50%; object-fit: cover; display: inline-block; margin-bottom: 6px; border: 2px solid #ffffff;" alt="ABVHPS Logo">
            <h1>AKHANDA BHARATA VISWA HINDU PARIRAKSHANA SAMITI</h1>
            <p>Official Membership Confirmation &amp; Welcome Desk</p>
        </div>

        <!-- Content -->
        <div class="content">
            <div class="greeting">
                Namaste {{ $memberData['full_name'] ?? 'Member' }},
            </div>

            <p>
                Hearty congratulations and a warm divine welcome! Your membership registration application with <strong>Akhanda Bharata Viswa Hindu Parirakshana Samiti</strong> has been successfully verified and completed.
            </p>

            <!-- Membership Details Box -->
            <div class="details-box">
                <h2>📜 Your Official Membership Summary</h2>
                <div class="detail-row">
                    <span class="detail-label">Member Name:</span>
                    <span class="detail-value" style="color: #0f172a; font-family: 'Segoe UI', Arial, sans-serif;">{{ $memberData['full_name'] ?? 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Membership ID:</span>
                    <span class="detail-value" style="color: #ea580c; font-size: 16px;">{{ $memberData['formatted_id'] ?? ($memberData['membership_id'] ?? 'N/A') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Membership Status:</span>
                    <span class="detail-value" style="color: #047857;">{{ $memberData['status'] ?? 'Active / Completed' }}</span>
                </div>
            </div>

            <p style="font-size: 13px; color: #475569;">
                🪪 <strong>Membership Card:</strong> You can view and download your official Digital Membership PVC Card anytime by logging into the membership portal using your verified mobile number.
            </p>

            <div style="text-align: center; margin: 25px 0 10px;">
                <a href="{{ url('/membership') }}" class="btn-action">
                    Access Membership Portal &rarr;
                </a>
            </div>

            <p style="font-size: 12px; color: #64748b; margin-top: 25px; line-height: 1.5;">
                Thank you for joining hands with ABVHPS to protect Sanatana Dharma, restore ancient temples, preserve goshalas, and uplift our community.
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; 2026 Akhanda Bharata Viswa Hindu Parirakshana Samiti.<br>
            {{ \App\Models\SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193') }}<br>
            Dedicated to the preservation and protection of Sanatana Dharma.
        </div>
    </div>
</body>
</html>
