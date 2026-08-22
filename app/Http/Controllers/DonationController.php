<?php

namespace App\Http\Controllers;

use App\Contracts\PaymentGatewayInterface;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\FundraisingCampaign;
use App\Models\NotificationLog;
use App\Services\CashfreePaymentService;
use App\Services\RazorpayPaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class DonationController extends Controller
{
    // =========================================================================
    // CONSTANTS — Amount Limits (in INR)
    // =========================================================================

    /** Minimum donation in INR. Any lower will be rejected server-side. */
    private const MIN_AMOUNT = 1;

    /** Maximum donation in INR (₹5,00,000 = 5 Lakh). */
    private const MAX_AMOUNT = 500000;

    /** Preset amounts allowed without custom flag. Custom amounts are also allowed within range. */
    private const PRESET_AMOUNTS = [100, 500, 1000, 2500, 5000, 10000];

    // =========================================================================
    // ADMIN — Donation Ledger
    // =========================================================================

    /**
     * Display the Admin Donation Ledger with search, gateway, and status filters.
     */
    public function index(Request $request)
    {
        $searchToken   = $request->input('search');
        $gatewayFilter = $request->input('gateway');
        $statusFilter  = $request->input('status');

        $query = Donation::with('campaign')->orderBy('id', 'desc');

        if (!empty($searchToken)) {
            $query->where(function ($q) use ($searchToken) {
                $q->where('name',          'LIKE', '%' . $searchToken . '%')
                  ->orWhere('contact',      'LIKE', '%' . $searchToken . '%')
                  ->orWhere('pan_number',   'LIKE', '%' . $searchToken . '%')
                  ->orWhere('email',        'LIKE', '%' . $searchToken . '%')
                  ->orWhere('gateway_order_id', 'LIKE', '%' . $searchToken . '%')
                  ->orWhere('gateway_payment_id', 'LIKE', '%' . $searchToken . '%');
            });
        }

        if (!empty($gatewayFilter)) {
            $query->where('payment_gateway', $gatewayFilter);
        }

        if (!empty($statusFilter)) {
            $query->where('payment_status', $statusFilter);
        }

        $donations = $query->get();

        return view('admin.donation.index', compact('donations', 'searchToken', 'gatewayFilter', 'statusFilter'));
    }

    // =========================================================================
    // ADMIN — Download Receipt
    // =========================================================================

    /**
     * Generate and return the ABVHPS official donation receipt (HTML).
     * Enhanced to include gateway details, campaign, and transaction reference.
     */
    public function downloadReceipt(Request $request, $id)
    {
        $donation = Donation::with('campaign')->findOrFail($id);

        if (!$this->isAuthorizedForDonation($request, $donation)) {
            abort(404);
        }

        $logoAsset  = asset('images/ABVHPS_LOGO.jpg');
        $address    = 'Survey No:1035, Sasirekhapuram, Akkalareddy Palli, Porumamilla, Kadapa, A.P - 516193';
        $receiptNum = $donation->receipt_number ?? ('ABVHPS-TXN-' . str_pad($donation->id, 6, '0', STR_PAD_LEFT));

        $paidAt     = $donation->paid_at
            ? Carbon::parse($donation->paid_at)->timezone('Asia/Kolkata')->format('d-M-Y H:i') . ' IST'
            : Carbon::parse($donation->created_at)->timezone('Asia/Kolkata')->format('d-M-Y H:i') . ' IST';

        $gatewayLabel = match (strtolower($donation->payment_gateway ?? 'manual')) {
            'cashfree' => 'Cashfree',
            'razorpay' => 'Razorpay',
            default    => 'Manual / Cash',
        };

        $campaignRow = '';
        if ($donation->campaign) {
            $campaignRow = "
                <tr style='background-color: #F9FAFB;'>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Campaign:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold; color: #EA580C; text-transform: uppercase;'>" . e($donation->campaign->title) . "</td>
                </tr>";
        }

        $gatewayRows = '';
        if ($donation->payment_gateway !== 'manual') {
            $gatewayRows = "
                <tr>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Payment Gateway:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold; color: #1F2937;'>" . e($gatewayLabel) . "</td>
                </tr>
                <tr style='background-color: #F9FAFB;'>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Transaction Reference:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-family: monospace; font-size: 11px; color: #374151;'>" . e($donation->gateway_payment_id ?? $donation->gateway_order_id ?? 'N/A') . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Payment Status:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold; color: #059669; text-transform: uppercase;'>" . e(strtoupper($donation->payment_status ?? 'PAID')) . "</td>
                </tr>";
        }

        $statusBadgeColor = match (strtolower($donation->payment_status ?? 'paid')) {
            'paid'      => '#059669',
            'pending'   => '#D97706',
            'failed'    => '#DC2626',
            'cancelled' => '#6B7280',
            default     => '#374151',
        };

        $htmlOutput = "
        <div style='max-width: 640px; margin: 20px auto; font-family: sans-serif; padding: 30px; border: 6px double #EA580C; border-radius: 8px; background-color: #FFF;'>
            <div style='text-align: center; border-bottom: 2px solid #EA580C; padding-bottom: 15px; margin-bottom: 20px;'>
                <div style='margin-bottom: 8px;'><img src='{$logoAsset}' style='width: 56px; height: 56px; border-radius: 50%; object-fit: cover; border: 2px solid #EA580C; display: inline-block;' alt='ABVHPS Logo'></div>
                <h2 style='color: #EA580C; margin: 0; font-size: 18px; font-weight: 900; letter-spacing: 1px;'>AKHANDA BHARATA VISWA HINDU PARIRAKSHANA SAMITI</h2>
                <p style='color: #6B7280; font-size: 10px; font-weight: 700; margin: 5px 0 0 0; text-transform: uppercase; letter-spacing: 0.5px;'>{$address}</p>
            </div>

            <div style='text-align: center; margin-bottom: 25px;'>
                <span style='background-color: #FEF3C7; color: #D97706; font-size: 11px; font-weight: 900; padding: 5px 20px; border-radius: 4px; text-transform: uppercase; letter-spacing: 1px; border: 1px solid #FDE68A;'>Official Donation Receipt</span>
            </div>

            <table style='width: 100%; border-collapse: collapse; font-size: 13px; color: #374151; margin-bottom: 30px;'>
                <tr style='background-color: #F9FAFB;'>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Receipt Number:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-family: monospace; color: #EA580C; font-weight: bold;'>" . e($receiptNum) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Date &amp; Time:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB;'>" . e($paidAt) . "</td>
                </tr>
                <tr style='background-color: #F9FAFB;'>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Donor Name:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; text-transform: uppercase; font-weight: bold;'>" . e($donation->name) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Guardian Name:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; text-transform: uppercase;'>" . e($donation->guardian ?? 'N/A') . "</td>
                </tr>
                <tr style='background-color: #F9FAFB;'>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Contact:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-family: monospace;'>" . e($donation->contact) . "</td>
                </tr>
                <tr>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>PAN Card:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-family: monospace; text-transform: uppercase;'>" . e($donation->pan_number ?? 'N/A') . "</td>
                </tr>
                {$campaignRow}
                <tr style='background-color: #FEF3C7;'>
                    <td style='padding: 12px; border: 1px solid #FDE68A; font-weight: bold; color: #B45309;'>Donation Amount:</td>
                    <td style='padding: 12px; border: 1px solid #FDE68A; font-size: 18px; font-weight: 900; color: #B45309;'>₹" . number_format((float)$donation->amount, 2) . "</td>
                </tr>
                <tr style='background-color: #F9FAFB;'>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold;'>Seva / Purpose:</td>
                    <td style='padding: 10px; border: 1px solid #E5E7EB; font-weight: bold; color: #1F2937;'>" . e($donation->about ?? 'General Contribution Fund') . "</td>
                </tr>
                {$gatewayRows}
            </table>

            <div style='text-align: center; margin-bottom: 15px;'>
                <span style='background: " . $statusBadgeColor . "; color: white; font-size: 11px; font-weight: 900; padding: 6px 20px; border-radius: 20px; text-transform: uppercase; letter-spacing: 1px;'>
                    " . e(strtoupper($donation->payment_status ?? 'PAID')) . "
                </span>
            </div>

            <div style='margin-top: 40px; border-top: 1px dashed #E5E7EB; padding-top: 20px; text-align: right;'>
                <div style='display: inline-block; text-align: center;'>
                    <div style='font-size: 14px; font-weight: bold; color: #111827; margin-bottom: 45px;'>Authorized Signatory</div>
                    <div style='font-size: 10px; font-weight: 700; color: #9CA3AF; text-transform: uppercase; letter-spacing: 0.5px;'>Central Administration Node Desk</div>
                </div>
            </div>

            <div style='text-align: center; margin-top: 30px; border-top: 1px solid #E5E7EB; padding-top: 15px;'>
                <p style='color: #9CA3AF; font-size: 9px; font-weight: 700; text-transform: uppercase; margin: 0; letter-spacing: 1px;'>Thank you for your sacred contribution towards Sanatana Dharma Protection.</p>
            </div>
        </div>
        ";

        return response($htmlOutput)->header('Content-Type', 'text/html');
    }

    // =========================================================================
    // PUBLIC — Initiate Cashfree Payment
    // =========================================================================

    /**
     * Validate donor form, create pending donation, create Cashfree order.
     *
     * Returns JSON:
     *  { success: true, gateway: 'cashfree', session_data: { payment_session_id, order_id }, donation_id }
     */
    public function initiateCashfreePayment(Request $request)
    {
        $validated = $this->validateDonationRequest($request);
        $donation  = $this->createPendingDonation($validated, 'cashfree');
        $this->authorizeDonationInSession($request, (int) $donation->id);

        $cashfree   = app(CashfreePaymentService::class);
        $returnUrl  = route('donations.cashfree_return');
        $orderResult = $cashfree->createOrder($donation, $returnUrl);

        if (!$orderResult['success']) {
            $donation->update(['payment_status' => 'failed']);
            return response()->json([
                'success' => false,
                'message' => $orderResult['message'] ?? 'Failed to initialize payment.',
            ], 422);
        }

        // Store gateway order ID
        $donation->update(['gateway_order_id' => $orderResult['order_id']]);

        return response()->json([
            'success'      => true,
            'gateway'      => 'cashfree',
            'donation_id'  => $donation->id,
            'session_data' => $orderResult['session_data'],
            'is_simulated' => $orderResult['is_simulated'] ?? false,
        ]);
    }

    // =========================================================================
    // PUBLIC — Initiate Razorpay Payment
    // =========================================================================

    /**
     * Validate donor form, create pending donation, create Razorpay order.
     *
     * Returns JSON:
     *  { success: true, gateway: 'razorpay', session_data: { razorpay_order_id, key_id, amount_paise, ... }, donation_id }
     *
     * NOTE: key_id (public) is returned. key_secret is NEVER in the response.
     */
    public function initiateRazorpayPayment(Request $request)
    {
        $validated = $this->validateDonationRequest($request);
        $donation  = $this->createPendingDonation($validated, 'razorpay');
        $this->authorizeDonationInSession($request, (int) $donation->id);

        $razorpay   = app(RazorpayPaymentService::class);
        $returnUrl  = route('donations.razorpay_return');
        $orderResult = $razorpay->createOrder($donation, $returnUrl);

        if (!$orderResult['success']) {
            $donation->update(['payment_status' => 'failed']);
            return response()->json([
                'success' => false,
                'message' => $orderResult['message'] ?? 'Failed to initialize payment.',
            ], 422);
        }

        // Store gateway order ID
        $donation->update(['gateway_order_id' => $orderResult['order_id']]);

        return response()->json([
            'success'      => true,
            'gateway'      => 'razorpay',
            'donation_id'  => $donation->id,
            'session_data' => $orderResult['session_data'],  // contains only key_id (public)
            'is_simulated' => $orderResult['is_simulated'] ?? false,
        ]);
    }

    // =========================================================================
    // PUBLIC — Verify Razorpay Payment (browser callback)
    // =========================================================================

    /**
     * Server-side signature verification after Razorpay browser callback.
     *
     * Called via POST after Razorpay checkout handler returns.
     * Uses donation_id to fetch the DB record and verify against stored gateway_order_id.
     *
     * Never trusts the razorpay_order_id supplied by the browser.
     */
    public function verifyRazorpayPayment(Request $request)
    {
        $request->validate([
            'donation_id'         => 'required|integer|exists:donations,id',
            'razorpay_payment_id' => 'required|string|max:100',
            'razorpay_signature'  => 'required|string|max:200',
        ]);

        $donation = Donation::findOrFail($request->integer('donation_id'));

        // Only verify pending/processing donations
        if ($donation->payment_status === 'paid') {
            return response()->json([
                'success'     => true,
                'status'      => 'paid',
                'receipt_url' => route('donations.receipt', $donation->id),
                'message'     => 'Payment already confirmed.',
            ]);
        }

        $razorpay = app(RazorpayPaymentService::class);
        $result   = $razorpay->verifyPayment($request, $donation);

        if ($result['success'] && $result['status'] === 'paid') {
            $this->markDonationPaid($donation, $result['payment_id'] ?? '', $result['reference'] ?? '');
            $this->authorizeDonationInSession($request, (int) $donation->id);
            return response()->json([
                'success'     => true,
                'status'      => 'paid',
                'receipt_url' => route('donations.receipt', $donation->id),
                'message'     => 'Payment verified and recorded successfully.',
            ]);
        }

        $donation->update(['payment_status' => $result['status'] ?? 'failed']);

        return response()->json([
            'success' => false,
            'status'  => $result['status'] ?? 'failed',
            'message' => $result['message'] ?? 'Payment verification failed.',
        ], 422);
    }

    // =========================================================================
    // PUBLIC — Cashfree Return (redirect after payment)
    // =========================================================================

    /**
     * Handle Cashfree redirect after payment.
     *
     * Cashfree redirects to this URL with ?order_id={order_id}&donation_id={id}
     * We fetch the status server-side — never trust URL parameters as payment proof.
     */
    public function cashfreeReturn(Request $request)
    {
        $donationId  = $request->input('donation_id');
        $gatewayOrderId = $request->input('order_id');

        if (!$donationId) {
            return redirect()->route('donations.grid')->with('error', 'Invalid payment return.');
        }

        $donation = Donation::find($donationId);

        if (!$donation) {
            return redirect()->route('donations.grid')->with('error', 'Donation record not found.');
        }

        // Already paid — show receipt
        if ($donation->payment_status === 'paid') {
            $this->authorizeDonationInSession($request, (int) $donation->id);
            return redirect()->route('donations.status', $donation->id);
        }

        // Verify payment server-side
        $cashfree = app(CashfreePaymentService::class);
        $result   = $cashfree->verifyPayment($request, $donation);

        if ($result['success'] && $result['status'] === 'paid') {
            $this->markDonationPaid($donation, $result['payment_id'] ?? '', $result['reference'] ?? '');
            $this->authorizeDonationInSession($request, (int) $donation->id);
            return redirect()->route('donations.status', $donation->id);
        }

        // Update status but don't mark as paid
        $status = $result['status'] ?? 'pending';
        $donation->update(['payment_status' => $status]);
        $this->authorizeDonationInSession($request, (int) $donation->id);

        return redirect()->route('donations.status', $donation->id);
    }

    // =========================================================================
    // PUBLIC — Razorpay Return Page
    // =========================================================================

    /**
     * Show Razorpay return page (Razorpay redirects here after checkout).
     * Status is determined by JS verifyRazorpayPayment POST call, then status page.
     */
    public function razorpayReturn(Request $request)
    {
        $donationId = $request->input('donation_id');

        if (!$donationId) {
            return redirect()->route('donations.grid');
        }

        $this->authorizeDonationInSession($request, (int) $donationId);
        return redirect()->route('donations.status', $donationId);
    }

    // =========================================================================
    // PUBLIC — Payment Status Page
    // =========================================================================

    /**
     * Show payment status for a donation.
     * Status is fetched from the DB — never from URL parameters.
     */
    public function paymentStatus(Request $request, $id)
    {
        $donation = Donation::with('campaign')->findOrFail($id);

        if (!$this->isAuthorizedForDonation($request, $donation)) {
            abort(404);
        }

        return view('donations.payment_status', compact('donation'));
    }

    // =========================================================================
    // WEBHOOK — Cashfree
    // =========================================================================

    /**
     * Handle Cashfree webhook.
     *
     * Signature is verified against raw request body (HMAC-SHA256).
     * Processing is idempotent — markDonationPaid() is safe to call multiple times.
     */
    public function handleCashfreeWebhook(Request $request)
    {
        $cashfree = app(CashfreePaymentService::class);

        // Verify signature using raw body
        if (!$cashfree->verifyWebhookSignature($request)) {
            Log::warning('Cashfree webhook: invalid signature');
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $webhookData = $cashfree->handleWebhook($request);

        if (!$webhookData['success']) {
            return response()->json(['status' => 'error'], 400);
        }

        $gatewayOrderId = $webhookData['gateway_order_id'];
        $status         = $webhookData['status'];

        if (empty($gatewayOrderId)) {
            Log::warning('Cashfree webhook: missing gateway_order_id');
            return response()->json(['status' => 'ok']);
        }

        // Find the donation by gateway_order_id
        $donation = Donation::where('gateway_order_id', $gatewayOrderId)->first();

        if (!$donation) {
            Log::info('Cashfree webhook: donation not found for order', ['gateway_order_id' => $gatewayOrderId]);
            return response()->json(['status' => 'ok']);
        }

        if ($status === 'paid') {
            $this->markDonationPaid(
                $donation,
                $webhookData['payment_id'] ?? '',
                $webhookData['reference'] ?? $gatewayOrderId
            );
        } elseif (in_array($status, ['failed', 'cancelled', 'expired'])) {
            // Only update if not already paid (idempotent)
            if ($donation->payment_status !== 'paid') {
                $donation->update(['payment_status' => $status]);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // WEBHOOK — Razorpay
    // =========================================================================

    /**
     * Handle Razorpay webhook.
     *
     * Signature is verified against raw request body (HMAC-SHA256 with webhook_secret).
     * Processing is idempotent — markDonationPaid() is safe to call multiple times.
     */
    public function handleRazorpayWebhook(Request $request)
    {
        $razorpay = app(RazorpayPaymentService::class);

        // Verify signature using raw body
        if (!$razorpay->verifyWebhookSignature($request)) {
            Log::warning('Razorpay webhook: invalid signature');
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $webhookData = $razorpay->handleWebhook($request);

        if (!$webhookData['success']) {
            return response()->json(['status' => 'error'], 400);
        }

        $gatewayOrderId = $webhookData['gateway_order_id'];
        $status         = $webhookData['status'];

        if (empty($gatewayOrderId)) {
            Log::warning('Razorpay webhook: missing gateway_order_id');
            return response()->json(['status' => 'ok']);
        }

        $donation = Donation::where('gateway_order_id', $gatewayOrderId)->first();

        if (!$donation) {
            Log::info('Razorpay webhook: donation not found for order', ['gateway_order_id' => $gatewayOrderId]);
            return response()->json(['status' => 'ok']);
        }

        if ($status === 'paid') {
            $this->markDonationPaid(
                $donation,
                $webhookData['payment_id'] ?? '',
                $webhookData['reference'] ?? $gatewayOrderId
            );
        } elseif ($status === 'failed') {
            if ($donation->payment_status !== 'paid') {
                $donation->update(['payment_status' => 'failed']);
            }
        }

        return response()->json(['status' => 'ok']);
    }

    // =========================================================================
    // PRIVATE — markDonationPaid() — IDEMPOTENT PAYMENT FINALIZATION
    // =========================================================================

    /**
     * Atomically mark a donation as paid and update campaign raised_amount.
     *
     * IDEMPOTENCY GUARANTEE:
     *  - If donation is already 'paid', this method exits without making any changes.
     *  - This prevents duplicate receipt generation, duplicate emails, duplicate campaign increments.
     *  - Uses DB transaction with SELECT FOR UPDATE to prevent race conditions.
     *
     * CAMPAIGN ACCOUNTING:
     *  - Increments raised_amount exactly once per confirmed payment.
     *  - Protected by row-level lock.
     */
    private function markDonationPaid(Donation $donation, string $paymentId, string $reference): void
    {
        DB::transaction(function () use ($donation, $paymentId, $reference) {
            // Lock the row to prevent concurrent updates
            $locked = Donation::lockForUpdate()->find($donation->id);

            if (!$locked || $locked->payment_status === 'paid') {
                // Already paid — idempotent exit
                return;
            }

            $receiptNumber = Donation::generateReceiptNumber($locked->id);
            $now           = Carbon::now('Asia/Kolkata');

            $locked->update([
                'payment_status'     => 'paid',
                'gateway_payment_id' => $paymentId ?: $locked->gateway_payment_id,
                'payment_reference'  => $reference ?: $locked->payment_reference,
                'receipt_number'     => $receiptNumber,
                'paid_at'            => $now,
            ]);

            // Increment campaign raised_amount exactly once (with row lock)
            if ($locked->campaign_id) {
                FundraisingCampaign::lockForUpdate()
                    ->where('id', $locked->campaign_id)
                    ->update([
                        'raised_amount' => DB::raw('raised_amount + ' . (float) $locked->amount),
                    ]);
            }

            // Send confirmation notification (idempotent — NotificationLog prevents duplicates)
            $this->sendDonationConfirmation($locked);
        });
    }

    // =========================================================================
    // PRIVATE — sendDonationConfirmation() — IDEMPOTENT
    // =========================================================================

    /**
     * Send donation confirmation email exactly once.
     *
     * Uses NotificationLog::alreadySent() to ensure idempotency.
     * Failure to send email does NOT fail the payment — errors are logged only.
     */
    private function sendDonationConfirmation(Donation $donation): void
    {
        $eventType = 'donation_paid';
        $channel   = 'email';

        if (empty($donation->email)) {
            return;
        }

        // Idempotency check — skip if already sent
        if (NotificationLog::alreadySent(Donation::class, $donation->id, $channel, $eventType)) {
            return;
        }

        $receiptToken = hash_hmac('sha256', $donation->id . '|' . $donation->created_at . '|' . $donation->phone, config('app.key'));

        try {
            Mail::send(
                'emails.donation_confirmation',
                ['donation' => $donation, 'receiptToken' => $receiptToken],
                function ($message) use ($donation) {
                    $message->to($donation->email, $donation->name)
                            ->subject('🙏 ABVHPS — Donation Receipt & Confirmation');
                }
            );

            NotificationLog::record([
                'event_type'      => $eventType,
                'notifiable_type' => Donation::class,
                'notifiable_id'   => $donation->id,
                'channel'         => $channel,
                'recipient_email' => $donation->email,
                'subject'         => 'ABVHPS Donation Receipt',
                'message'         => 'Donation confirmation sent for receipt: ' . $donation->receipt_number,
                'status'          => 'sent',
                'sent_at'         => now(),
            ]);
        } catch (\Exception $e) {
            Log::error('DonationController: email notification failed', [
                'donation_id' => $donation->id,
                'error'       => $e->getMessage(),
                // Never log email credentials or secrets
            ]);
        }
    }

    // =========================================================================
    // PRIVATE — validateDonationRequest() — SERVER-SIDE AMOUNT VALIDATION
    // =========================================================================

    /**
     * Validate the donation form request.
     *
     * SECURITY: Amount is validated server-side. Frontend values are NEVER trusted.
     * Amount must be within MIN_AMOUNT and MAX_AMOUNT.
     */
    private function validateDonationRequest(Request $request): array
    {
        $rules = [
            'donor_name'  => 'required|string|min:2|max:100',
            'email'       => 'required|email|max:150',
            'phone'       => 'required|digits_between:10,13',
            'amount'      => 'required|numeric|min:' . self::MIN_AMOUNT . '|max:' . self::MAX_AMOUNT,
            'campaign_id' => 'nullable|integer|exists:fundraising_campaigns,id',
            'message'     => 'nullable|string|max:500',
            'pan_number'  => 'nullable|regex:/^[A-Z]{5}[0-9]{4}[A-Z]{1}$/i|max:10',
            'guardian'    => 'nullable|string|max:100',
        ];

        return $request->validate($rules, [
            'amount.min' => 'Minimum donation amount is ₹' . number_format(self::MIN_AMOUNT) . '.',
            'amount.max' => 'Maximum donation amount is ₹' . number_format(self::MAX_AMOUNT) . '.',
            'pan_number.regex' => 'Please enter a valid 10-character PAN number.',
        ]);
    }

    // =========================================================================
    // PRIVATE — createPendingDonation()
    // =========================================================================

    /**
     * Create a new Donation record in 'pending' status.
     *
     * Amount is converted via paise-safe arithmetic before storing.
     */
    private function createPendingDonation(array $validated, string $gateway): Donation
    {
        // Paise-safe: round to 2 decimal places
        $amountPaise = (int) round((float) $validated['amount'] * 100);
        $amountInr   = $amountPaise / 100;

        return Donation::create([
            'name'            => strtoupper(trim($validated['donor_name'])),
            'email'           => strtolower(trim($validated['email'])),
            'phone'           => trim($validated['phone']),
            'contact'         => trim($validated['phone']),
            'guardian'        => !empty($validated['guardian']) ? strtoupper(trim($validated['guardian'])) : null,
            'amount'          => $amountInr,
            'pan_number'      => !empty($validated['pan_number']) ? strtoupper(trim($validated['pan_number'])) : null,
            'campaign_id'     => $validated['campaign_id'] ?? null,
            'about'           => $validated['message'] ?? null,
            'payment_gateway' => $gateway,
            'payment_status'  => 'pending',
        ]);
    }

    // =========================================================================
    // AUTHORIZATION & ANTI-IDOR SECURITY HELPERS
    // =========================================================================

    /**
     * Store authorized donation ID in session.
     */
    private function authorizeDonationInSession(Request $request, int $donationId): void
    {
        if ($request->hasSession()) {
            $existing = (array) $request->session()->get('authorized_donation_ids', []);
            $updated  = array_values(array_unique(array_merge($existing, [$donationId])));
            $request->session()->put('authorized_donation_ids', $updated);
        }
    }

    /**
     * Check if requester is authorized to view this donation.
     */
    private function isAuthorizedForDonation(Request $request, Donation $donation): bool
    {
        // 1. Authenticated administrators or admin routes can view all donations
        if (auth()->guard('web')->check() || $request->is('admin/*')) {
            return true;
        }

        // 2. Session-based authorization (donor in current session)
        if ($request->hasSession()) {
            $authorizedIds = (array) $request->session()->get('authorized_donation_ids', []);
            if (in_array((int) $donation->id, array_map('intval', $authorizedIds), true)) {
                return true;
            }
        }

        // 3. Secure token verification (for email receipts)
        $token = $request->query('token');
        if ($token) {
            $expectedToken = hash_hmac('sha256', $donation->id . '|' . $donation->created_at . '|' . $donation->phone, config('app.key'));
            if (hash_equals($expectedToken, (string) $token)) {
                $this->authorizeDonationInSession($request, (int) $donation->id);
                return true;
            }
        }

        return false;
    }
}
