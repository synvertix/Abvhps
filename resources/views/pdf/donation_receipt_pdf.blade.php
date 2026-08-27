<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Official Donation Receipt</title>
    <style>
        @page {
            size: a4 portrait;
            margin: 20pt 30pt;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #374151;
            background-color: #ffffff;
            margin: 0;
            padding: 0;
        }
        .receipt-box {
            border: 3px double #ea580c;
            border-radius: 8px;
            padding: 24px;
            background: #ffffff;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #ea580c;
            padding-bottom: 16px;
            margin-bottom: 20px;
        }
        .title {
            color: #ea580c;
            font-size: 16pt;
            font-weight: 900;
            margin: 6px 0 2px 0;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .subtitle {
            color: #6b7280;
            font-size: 8pt;
            font-weight: bold;
            margin: 0;
            text-transform: uppercase;
        }
        .badge {
            display: inline-block;
            background-color: #fef3c7;
            color: #d97706;
            font-size: 9pt;
            font-weight: 900;
            padding: 4px 16px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid #fde68a;
            margin: 12px 0 18px 0;
        }
        .details-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-bottom: 20px;
        }
        .details-table td {
            padding: 8pt 10pt;
            border: 1px solid #e5e7eb;
        }
        .label {
            width: 35%;
            font-weight: bold;
            background-color: #f9fafb;
            color: #4b5563;
        }
        .value {
            width: 65%;
            color: #111827;
        }
        .highlight-row {
            background-color: #fff7ed !important;
        }
        .highlight-label {
            color: #c2410c !important;
            font-weight: 900 !important;
        }
        .highlight-amount {
            color: #ea580c !important;
            font-size: 13pt !important;
            font-weight: 900 !important;
        }
        .receipt-number {
            font-family: monospace;
            font-weight: 900;
            color: #ea580c;
            font-size: 11pt;
        }
        .status-badge {
            display: inline-block;
            background-color: #059669;
            color: #ffffff;
            font-size: 8pt;
            font-weight: 900;
            padding: 3px 12px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .footer {
            text-align: center;
            border-top: 1px solid #e5e7eb;
            padding-top: 14px;
            margin-top: 20px;
            font-size: 8pt;
            color: #9ca3af;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>
    <div class="receipt-box">
        <div class="header">
            @if(file_exists(public_path('images/ABVHPS_LOGO.jpg')))
                <img src="{{ public_path('images/ABVHPS_LOGO.jpg') }}" style="width: 48px; height: 48px; border-radius: 50%; border: 2px solid #ea580c;" alt="ABVHPS">
            @endif
            <h1 class="title">Akhanda Bharatha Viswa Hindu Parirakshana Samiti</h1>
            <p class="subtitle">{{ \App\Models\SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193') }}</p>
            <div style="text-align: center;">
                <span class="badge">{{ !empty($donationData['fundraiser_name']) ? 'Official Fundraiser Contribution Receipt' : 'Official Donation Receipt' }}</span>
            </div>
        </div>

        <table class="details-table">
            <tr class="highlight-row">
                <td class="label highlight-label">Receipt Number:</td>
                <td class="value receipt-number">{{ $donationData['receipt_number'] ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Date:</td>
                <td class="value">{{ $donationData['date'] ?? now('Asia/Kolkata')->format('d-M-Y') }}</td>
            </tr>
            <tr>
                <td class="label">{{ !empty($donationData['fundraiser_name']) ? 'Contributor Name:' : 'Donor Name:' }}</td>
                <td class="value" style="font-weight: bold; text-transform: uppercase;">{{ $donationData['donor_name'] ?? 'N/A' }}</td>
            </tr>
            @if(!empty($donationData['fundraiser_name']))
            <tr>
                <td class="label">Fundraiser Campaign:</td>
                <td class="value" style="font-weight: bold; color: #ea580c; text-transform: uppercase;">{{ $donationData['fundraiser_name'] }}</td>
            </tr>
            @elseif(!empty($donationData['purpose']))
            <tr>
                <td class="label">Purpose / Seva:</td>
                <td class="value" style="font-weight: bold;">{{ $donationData['purpose'] }}</td>
            </tr>
            @endif
            <tr class="highlight-row">
                <td class="label highlight-label">Contribution Amount:</td>
                <td class="value highlight-amount">₹{{ number_format((float)($donationData['amount'] ?? 0), 2) }}</td>
            </tr>
            <tr>
                <td class="label">Payment Status:</td>
                <td class="value"><span class="status-badge">Successful &amp; Verified</span></td>
            </tr>
        </table>

        <div class="footer">
            Thank you for your sacred support toward ABVHPS Sanatana Dharma service initiatives and community seva activities.
            <div style="margin-top: 4px; font-weight: bold; color: #6b7280;">Website: https://abvhps.org &bull; Email: info@abvhps.org</div>
        </div>
    </div>
</body>
</html>
