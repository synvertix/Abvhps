<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Razorpay Payment Gateway Service
 *
 * Implements PaymentGatewayInterface for the ABVHPS donation flow.
 * Uses Razorpay Orders API v1.
 *
 * SECURITY:
 *  - RAZORPAY_KEY_SECRET stays server-side at ALL times
 *  - Only RAZORPAY_KEY_ID is passed to the browser checkout
 *  - Webhook signature verified with RAZORPAY_WEBHOOK_SECRET (separate from key_secret)
 *  - Signature verification uses HMAC-SHA256 on raw webhook body
 *  - Payment verification uses HMAC-SHA256 on order_id|payment_id
 */
class RazorpayPaymentService implements PaymentGatewayInterface
{
    protected string $keyId;
    protected string $keySecret;
    protected string $webhookSecret;
    protected string $baseUrl = 'https://api.razorpay.com/v1';
    protected bool $isConfigured;

    public function __construct()
    {
        $this->keyId         = config('services.razorpay.key_id', '');
        $this->keySecret     = config('services.razorpay.key_secret', '');
        $this->webhookSecret = config('services.razorpay.webhook_secret', '');

        $this->isConfigured = !empty($this->keyId) && !empty($this->keySecret);
    }

    /**
     * Check if Razorpay credentials are configured.
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    /**
     * Get public Key ID (safe to pass to browser checkout).
     * Key Secret is NEVER returned.
     */
    public function getPublicKeyId(): string
    {
        return $this->keyId;
    }

    // =========================================================================
    // PaymentGatewayInterface: createOrder()
    // =========================================================================

    /**
     * Create a Razorpay order from a pending Donation record.
     *
     * Razorpay requires amounts in PAISE (integer).
     * ₹100.00 → 10000 paise
     *
     * Returns:
     *   ['success' => bool, 'order_id' => string, 'session_data' => [...], 'message' => ?string]
     *
     * session_data contains: razorpay_order_id, key_id (public only), amount_paise, currency
     */
    public function createOrder(Donation $donation, string $returnUrl): array
    {
        // Convert INR to paise using integer arithmetic — NEVER use floating point
        $amountPaise = (int) round((float) $donation->amount * 100);

        if ($amountPaise < 100) {
            return [
                'success' => false,
                'message' => 'Minimum donation amount is ₹1.',
            ];
        }

        if (!$this->isConfigured) {
            $mockOrderId = 'order_SIMULATION_' . strtoupper(uniqid());
            return [
                'success'      => true,
                'is_simulated' => true,
                'order_id'     => $mockOrderId,
                'session_data' => [
                    'razorpay_order_id' => $mockOrderId,
                    'key_id'            => 'rzp_test_simulation',  // public key only
                    'amount_paise'      => $amountPaise,
                    'currency'          => 'INR',
                    'donation_id'       => $donation->id,
                    'donor_name'        => $donation->name,
                    'donor_email'       => $donation->email ?? '',
                    'donor_phone'       => $donation->phone ?? '',
                    'return_url'        => $returnUrl,
                ],
                'message' => 'Razorpay Simulation Mode (configure RAZORPAY_KEY_ID & RAZORPAY_KEY_SECRET to go live).',
            ];
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/orders", [
                    'amount'          => $amountPaise,
                    'currency'        => 'INR',
                    'receipt'         => 'ABVHPS-DON-' . $donation->id,
                    'payment_capture' => 1,
                    'notes'           => [
                        'donation_id'   => $donation->id,
                        'donor_name'    => $donation->name,
                        'campaign_id'   => $donation->campaign_id,
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'      => true,
                    'is_simulated' => false,
                    'order_id'     => $data['id'],
                    'session_data' => [
                        'razorpay_order_id' => $data['id'],
                        'key_id'            => $this->keyId,  // public key only
                        'amount_paise'      => $amountPaise,
                        'currency'          => 'INR',
                        'donation_id'       => $donation->id,
                        'donor_name'        => $donation->name,
                        'donor_email'       => $donation->email ?? '',
                        'donor_phone'       => $donation->phone ?? '',
                        'return_url'        => $returnUrl,
                    ],
                ];
            }

            Log::error('Razorpay Order Creation Failed', [
                'donation_id' => $donation->id,
                'status'      => $response->status(),
                'error'       => $response->json('error', []),
            ]);

            return [
                'success' => false,
                'message' => $response->json('error.description') ?? 'Failed to initialize Razorpay payment. Please try again.',
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayPaymentService::createOrder exception', [
                'donation_id' => $donation->id,
                'error'       => $e->getMessage(),
            ]);
            return [
                'success' => false,
                'message' => 'Payment gateway communication failure. Please try again.',
            ];
        }
    }

    // =========================================================================
    // PaymentGatewayInterface: verifyPayment()
    // =========================================================================

    /**
     * Verify Razorpay payment signature from browser callback.
     *
     * After checkout, Razorpay POSTs back:
     *   razorpay_payment_id, razorpay_order_id, razorpay_signature
     *
     * Verification: HMAC-SHA256("{order_id}|{payment_id}", key_secret) == signature
     *
     * IMPORTANT: We use the order_id stored in the database (gateway_order_id),
     * not the one supplied by the browser, to prevent tampering.
     *
     * Returns:
     *   ['success' => bool, 'payment_id' => ?string, 'reference' => ?string, 'status' => string, 'message' => ?string]
     */
    public function verifyPayment(Request $request, Donation $donation): array
    {
        $razorpayPaymentId = $request->input('razorpay_payment_id', '');
        $razorpaySignature = $request->input('razorpay_signature', '');

        // Use the order ID from our DB record — never trust browser-supplied order_id
        $razorpayOrderId = $donation->gateway_order_id;

        if (empty($razorpayPaymentId) || empty($razorpaySignature) || empty($razorpayOrderId)) {
            return [
                'success' => false,
                'status'  => 'failed',
                'message' => 'Missing payment verification parameters.',
            ];
        }

        // Simulation mode
        if (!$this->isConfigured) {
            return [
                'success'    => true,
                'payment_id' => $razorpayPaymentId ?: 'SIM_PAY_' . strtoupper(uniqid()),
                'reference'  => $razorpayOrderId,
                'status'     => 'paid',
                'message'    => 'Simulated payment verified.',
            ];
        }

        // HMAC-SHA256 verification
        $expectedSignature = hash_hmac(
            'sha256',
            $razorpayOrderId . '|' . $razorpayPaymentId,
            $this->keySecret
        );

        if (!hash_equals($expectedSignature, $razorpaySignature)) {
            Log::warning('Razorpay payment signature verification failed', [
                'donation_id'      => $donation->id,
                'gateway_order_id' => $razorpayOrderId,
                'payment_id'       => $razorpayPaymentId,
                // Never log signature or key_secret
            ]);

            return [
                'success' => false,
                'status'  => 'failed',
                'message' => 'Payment signature verification failed.',
            ];
        }

        return [
            'success'    => true,
            'payment_id' => $razorpayPaymentId,
            'reference'  => $razorpayOrderId,
            'status'     => 'paid',
            'message'    => 'Payment verified successfully.',
        ];
    }

    // =========================================================================
    // PaymentGatewayInterface: verifyWebhookSignature()
    // =========================================================================

    /**
     * Verify Razorpay webhook signature using raw request body.
     *
     * Razorpay signature algorithm:
     *   HMAC-SHA256(rawBody, webhook_secret)
     *   Header: X-Razorpay-Signature
     *
     * CRITICAL: Use raw body ONLY. Never parse/re-serialize.
     * FAIL CLOSED: Always return false if webhook secret or signature is missing/invalid.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $signature = $request->header('X-Razorpay-Signature', '');
        $secret    = $this->webhookSecret;

        if (empty($secret) || empty($signature)) {
            Log::warning('Razorpay webhook signature verification rejected: missing signature header or webhook secret');
            return false;
        }

        $rawBody  = $request->getContent();
        $computed = hash_hmac('sha256', $rawBody, $secret);

        $valid = hash_equals($computed, $signature);

        if (!$valid) {
            Log::warning('Razorpay webhook signature verification failed');
        }

        return $valid;
    }

    // =========================================================================
    // PaymentGatewayInterface: handleWebhook()
    // =========================================================================

    /**
     * Parse and extract payment data from Razorpay webhook payload.
     *
     * Handles events:
     *  - payment.captured  → paid
     *  - payment.failed    → failed
     *  - order.paid        → paid
     *
     * Returns standardized array:
     *   ['success' => bool, 'event' => string, 'gateway_order_id' => ?string, 'payment_id' => ?string, 'reference' => ?string, 'status' => string]
     */
    public function handleWebhook(Request $request): array
    {
        $payload = $request->json()->all();
        $event   = $payload['event'] ?? 'unknown';

        Log::info('Razorpay webhook received', ['event' => $event]);

        $paymentEntity = $payload['payload']['payment']['entity'] ?? [];
        $orderEntity   = $payload['payload']['order']['entity'] ?? [];

        $razorpayPaymentId = $paymentEntity['id'] ?? null;
        $razorpayOrderId   = $paymentEntity['order_id'] ?? ($orderEntity['id'] ?? null);
        $paymentStatus     = strtolower($paymentEntity['status'] ?? 'unknown');

        $isPaid = in_array($event, ['payment.captured', 'order.paid'])
            || $paymentStatus === 'captured';

        $isFailed = $event === 'payment.failed'
            || $paymentStatus === 'failed';

        return [
            'success'          => true,
            'event'            => $event,
            'gateway_order_id' => $razorpayOrderId,
            'payment_id'       => $razorpayPaymentId,
            'reference'        => $razorpayOrderId,
            'status'           => $isPaid ? 'paid' : ($isFailed ? 'failed' : 'pending'),
        ];
    }

    // =========================================================================
    // PaymentGatewayInterface: getPaymentStatus()
    // =========================================================================

    /**
     * Fetch real-time payment status for a Razorpay order.
     *
     * Returns array with 'success', 'data' (raw order data), or 'message' on failure.
     */
    public function getPaymentStatus(string $gatewayOrderId): array
    {
        if (!$this->isConfigured) {
            return [
                'success'      => true,
                'is_simulated' => true,
                'data'         => ['status' => 'paid', 'amount' => 10000],
            ];
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/orders/{$gatewayOrderId}");

            if ($response->successful()) {
                return [
                    'success'      => true,
                    'is_simulated' => false,
                    'data'         => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch Razorpay order status.',
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayPaymentService::getPaymentStatus exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // MEMBERSHIP REAL PAYMENT FLOW
    // =========================================================================

    /**
     * Create a server-side Razorpay order for ABVHPS Membership Fee.
     * Hardcoded amount = 10000 paise (₹100.00). No amount parameter accepted.
     * FAIL CLOSED when credentials are missing (zero simulation).
     */
    public function createMembershipOrder(string $internalReference, string $phone): array
    {
        if (!$this->isConfigured) {
            Log::warning('Razorpay membership order creation failed: missing credentials');
            return [
                'success' => false,
                'message' => 'Razorpay gateway is not configured. Please contact administrator.',
            ];
        }

        try {
            $response = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->baseUrl}/orders", [
                    'amount'          => 10000, // ₹100.00 fixed
                    'currency'        => 'INR',
                    'receipt'         => $internalReference,
                    'payment_capture' => 1,
                    'notes'           => [
                        'phone' => $phone,
                        'type'  => 'membership',
                    ],
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'      => true,
                    'key_id'       => $this->keyId, // public key ID only
                    'order_id'     => $data['id'],
                    'amount_paise' => 10000,
                    'currency'     => 'INR',
                ];
            }

            Log::error('Razorpay Membership Order Creation Failed', [
                'status' => $response->status(),
                'error'  => $response->json('error', []),
            ]);

            return [
                'success' => false,
                'message' => $response->json('error.description') ?? 'Failed to initialize membership payment order.',
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayPaymentService::createMembershipOrder exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Payment gateway communication failure. Please try again.',
            ];
        }
    }

    /**
     * Verify Razorpay membership payment signature & server-to-server captured payment facts.
     *
     * 1. Validates HMAC-SHA256 signature using timing-safe hash_equals().
     * 2. Server-to-server HTTP GET /payments/{payment_id} -> requires status=captured, amount=10000, currency=INR, order_id=DB payment_order_id.
     * 3. Server-to-server HTTP GET /orders/{order_id} -> requires status=paid, id=DB payment_order_id, amount=10000, currency=INR.
     */
    public function verifyMembershipPayment(string $razorpayPaymentId, string $razorpaySignature, string $serverOrderId): array
    {
        if (!$this->isConfigured) {
            return [
                'success' => false,
                'message' => 'Razorpay payment gateway is not configured.',
            ];
        }

        if (empty($razorpayPaymentId) || empty($razorpaySignature) || empty($serverOrderId)) {
            return [
                'success' => false,
                'message' => 'Missing required payment verification parameters.',
            ];
        }

        // Step 1: Cryptographic HMAC-SHA256 signature verification
        $expectedSignature = hash_hmac(
            'sha256',
            $serverOrderId . '|' . $razorpayPaymentId,
            $this->keySecret
        );

        if (!hash_equals($expectedSignature, $razorpaySignature)) {
            Log::warning('Razorpay membership payment signature mismatch', [
                'server_order_id' => $serverOrderId,
                'payment_id'      => $razorpayPaymentId,
            ]);

            return [
                'success' => false,
                'message' => 'Payment signature verification failed.',
            ];
        }

        // Step 2: Server-to-server fetch of Payment details
        try {
            $paymentResponse = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/payments/{$razorpayPaymentId}");

            if (!$paymentResponse->successful()) {
                Log::error('Razorpay membership GET payment failed', [
                    'payment_id' => $razorpayPaymentId,
                    'status'     => $paymentResponse->status(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Unable to verify payment status with gateway provider.',
                ];
            }

            $paymentData = $paymentResponse->json();
            $paymentStatus = $paymentData['status'] ?? '';
            $paymentAmount = (int) ($paymentData['amount'] ?? 0);
            $paymentCurrency = $paymentData['currency'] ?? '';
            $paymentOrderId = $paymentData['order_id'] ?? '';

            if (
                $paymentStatus !== 'captured' ||
                $paymentAmount !== 10000 ||
                $paymentCurrency !== 'INR' ||
                $paymentOrderId !== $serverOrderId
            ) {
                Log::warning('Razorpay membership payment status/fact check failed', [
                    'expected_status'   => 'captured',
                    'actual_status'     => $paymentStatus,
                    'expected_amount'   => 10000,
                    'actual_amount'     => $paymentAmount,
                    'expected_currency' => 'INR',
                    'actual_currency'   => $paymentCurrency,
                    'expected_order_id' => $serverOrderId,
                    'actual_order_id'   => $paymentOrderId,
                ]);

                return [
                    'success' => false,
                    'message' => 'Payment verification failed: payment status or amount mismatch.',
                ];
            }

            // Step 3: Server-to-server fetch of Order details
            $orderResponse = Http::withBasicAuth($this->keyId, $this->keySecret)
                ->get("{$this->baseUrl}/orders/{$serverOrderId}");

            if (!$orderResponse->successful()) {
                Log::error('Razorpay membership GET order failed', [
                    'order_id' => $serverOrderId,
                    'status'   => $orderResponse->status(),
                ]);
                return [
                    'success' => false,
                    'message' => 'Unable to verify payment order status with gateway provider.',
                ];
            }

            $orderData = $orderResponse->json();
            $orderStatus = $orderData['status'] ?? '';
            $orderId = $orderData['id'] ?? '';
            $orderAmount = (int) ($orderData['amount'] ?? 0);
            $orderCurrency = $orderData['currency'] ?? '';

            if (
                $orderStatus !== 'paid' ||
                $orderId !== $serverOrderId ||
                $orderAmount !== 10000 ||
                $orderCurrency !== 'INR'
            ) {
                Log::warning('Razorpay membership order status/fact check failed', [
                    'expected_status'   => 'paid',
                    'actual_status'     => $orderStatus,
                    'expected_id'       => $serverOrderId,
                    'actual_id'         => $orderId,
                    'expected_amount'   => 10000,
                    'actual_amount'     => $orderAmount,
                    'expected_currency' => 'INR',
                    'actual_currency'   => $orderCurrency,
                ]);

                return [
                    'success' => false,
                    'message' => 'Payment verification failed: order status mismatch.',
                ];
            }

            return [
                'success'    => true,
                'payment_id' => $razorpayPaymentId,
                'order_id'   => $serverOrderId,
            ];
        } catch (\Exception $e) {
            Log::error('RazorpayPaymentService::verifyMembershipPayment exception', ['error' => $e->getMessage()]);
            return [
                'success' => false,
                'message' => 'Gateway verification communication failure.',
            ];
        }
    }
}
