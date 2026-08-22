<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ABVHPS Donation Confirmation & Receipt</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f9fafb; margin: 0; padding: 24px; color: #374151;">
    <div style="max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e5e7eb; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        
        <!-- Header -->
        <div style="background-color: #FF6600; padding: 24px; text-align: center; color: #ffffff;">
            <h1 style="margin: 0; font-size: 20px; font-weight: 800; letter-spacing: 0.5px; text-transform: uppercase;">
                Akhanda Bharatha Viswa Hindu Parirakshana Samiti
            </h1>
            <p style="margin: 6px 0 0 0; font-size: 12px; font-weight: 600; color: #fff3eb; text-transform: uppercase;">
                Official Sacred Contribution Confirmation
            </p>
        </div>

        <!-- Body Content -->
        <div style="padding: 24px 28px;">
            <p style="font-size: 15px; font-weight: bold; color: #111827; margin-top: 0;">
                Namaste {{ $donation->name }},
            </p>
            
            <p style="font-size: 13px; line-height: 1.6; color: #4b5563;">
                Thank you with all our hearts for your generous and holy contribution towards ABVHPS Sanatana Dharma preservation initiatives and community seva projects.
            </p>

            <!-- Details Table -->
            <table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px;">
                <tr style="background-color: #fff7ed;">
                    <td style="padding: 10px 14px; border: 1px solid #fed7aa; font-weight: bold; color: #9a3412;">Receipt Number:</td>
                    <td style="padding: 10px 14px; border: 1px solid #fed7aa; font-family: monospace; font-weight: bold; color: #ea580c;">{{ $donation->receipt_number ?? 'ABVHPS-TXN-' . str_pad($donation->id, 6, '0', STR_PAD_LEFT) }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-weight: bold; color: #374151;">Contribution Amount:</td>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-weight: 900; color: #059669; font-size: 15px;">₹{{ number_format((float)$donation->amount, 2) }}</td>
                </tr>
                @if($donation->campaign)
                <tr style="background-color: #f9fafb;">
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-weight: bold; color: #374151;">Dedicated Cause:</td>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-weight: bold; color: #1f2937; text-transform: uppercase;">{{ $donation->campaign->title }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-weight: bold; color: #374151;">Payment Channel:</td>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; color: #4b5563; text-transform: capitalize;">{{ $donation->payment_gateway }} Payments</td>
                </tr>
                <tr style="background-color: #f9fafb;">
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-weight: bold; color: #374151;">Transaction Reference:</td>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-family: monospace; color: #4b5563;">{{ $donation->gateway_payment_id ?? $donation->gateway_order_id ?? 'Confirmed' }}</td>
                </tr>
                <tr>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; font-weight: bold; color: #374151;">Date &amp; Time (IST):</td>
                    <td style="padding: 10px 14px; border: 1px solid #e5e7eb; color: #4b5563;">{{ $donation->paid_at ? \Carbon\Carbon::parse($donation->paid_at)->timezone('Asia/Kolkata')->format('d-M-Y H:i') . ' IST' : now('Asia/Kolkata')->format('d-M-Y H:i') . ' IST' }}</td>
                </tr>
            </table>

            <!-- Button -->
            <div style="text-align: center; margin: 28px 0;">
                <a href="{{ route('donations.receipt', !empty($receiptToken) ? ['id' => $donation->id, 'token' => $receiptToken] : $donation->id) }}" style="background-color: #FF6600; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-size: 13px; font-weight: bold; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px;">
                    View &amp; Download Official Receipt
                </a>
            </div>

            <p style="font-size: 12px; color: #6b7280; line-height: 1.5;">
                This contribution is dedicated towards temple renovations, goshala developments, youth welfare, and Hindu community empowerment initiatives.
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f3f4f6; padding: 16px 24px; text-align: center; font-size: 11px; color: #9ca3af; border-top: 1px solid #e5e7eb;">
            <p style="margin: 0;">Akhanda Bharatha Viswa Hindu Parirakshana Samiti (ABVHPS)</p>
            <p style="margin: 4px 0 0 0;">{{ \App\Models\SiteSetting::get('contact_address', 'Survey No:1826, Shanmukhapuram, Akkalareddy Palli Village and Post, Porumamilla Mandalam, Kadapa, A.P - 516193') }}</p>
        </div>
    </div>
</body>
</html>
