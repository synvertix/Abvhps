<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\Donation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Cashfree Payment Gateway Service
 *
 * Implements PaymentGatewayInterface for the ABVHPS donation flow.
 * Uses the Cashfree PG Orders API v2023-08-01.
 *
 * SECURITY: Secret key is NEVER passed to frontend.
 * Webhook signature is verified on raw request body.
 */
class CashfreePaymentService implements PaymentGatewayInterface
{
    protected string $appId;
    protected string $secretKey;
    protected string $apiVersion;
    protected string $baseUrl;
    protected bool $isConfigured;

    public function __construct()
    {
        $this->appId      = config('services.cashfree.app_id', '');
        $this->secretKey  = config('services.cashfree.secret_key', '');
        $this->apiVersion = config('services.cashfree.api_version', '2023-08-01');

        $environment  = config('services.cashfree.environment', 'sandbox');
        $this->baseUrl = $environment === 'production'
            ? 'https://api.cashfree.com/pg'
            : 'https://sandbox.cashfree.com/pg';

        $this->isConfigured = !empty($this->appId) && !empty($this->secretKey);
    }

    /**
     * Check if Cashfree live/sandbox credentials are fully configured.
     */
    public function isConfigured(): bool
    {
        return $this->isConfigured;
    }

    // =========================================================================
    // PaymentGatewayInterface: createOrder()
    // =========================================================================

    /**
     * Create a Cashfree order from a pending Donation record or legacy parameter list.
     *
     * Amount is stored in INR and sent as INR decimal to Cashfree.
     * We use paise-safe integer arithmetic: multiply to paise, then back to INR decimal.
     *
     * Returns:
     *   ['success' => bool, 'order_id' => string, 'session_data' => [...], 'message' => ?string]
     */
    public function createOrder(Donation|string $donation, float|string $returnUrlOrAmount = '', array $customerDetails = [], string $legacyReturnUrl = ''): array
    {
        if (is_string($donation)) {
            // Legacy signature: createOrder(string $orderId, float $amount, array $customerDetails, string $returnUrl)
            return $this->createOrderLegacy($donation, (float) $returnUrlOrAmount, $customerDetails, $legacyReturnUrl);
        }

        $returnUrl = (string) $returnUrlOrAmount;

        // Convert INR to paise (integer) then back to INR decimal to eliminate float rounding
        $amountPaise = (int) round((float) $donation->amount * 100);
        $amountInr   = number_format($amountPaise / 100, 2, '.', '');

        $orderId  = 'ABVHPS-DON-' . $donation->id . '-' . time();

        if (!$this->isConfigured) {
            return [
                'success'      => true,
                'is_simulated' => true,
                'order_id'     => $orderId,
                'session_data' => [
                    'payment_session_id' => 'session_mock_' . strtoupper(uniqid()),
                    'order_id'           => $orderId,
                    'order_status'       => 'ACTIVE',
                    'cf_order_id'        => 'mock_' . time(),
                ],
                'message' => 'Cashfree Simulation Mode (configure CASHFREE_APP_ID & CASHFREE_SECRET_KEY to go live).',
            ];
        }

        try {
            $response = Http::withHeaders([
                'x-client-id'     => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version'   => $this->apiVersion,
                'Content-Type'    => 'application/json',
            ])->post("{$this->baseUrl}/orders", [
                'order_id'         => $orderId,
                'order_amount'     => (float) $amountInr,
                'order_currency'   => 'INR',
                'customer_details' => [
                    'customer_id'    => 'ABVHPS_DONOR_' . $donation->id,
                    'customer_name'  => $donation->name,
                    'customer_email' => $donation->email ?? 'donor@abvhps.org',
                    'customer_phone' => $donation->phone ?? '9999999999',
                ],
                'order_meta' => [
                    'return_url' => $returnUrl . '?order_id={order_id}&donation_id=' . $donation->id,
                    'notify_url' => url('/webhook/cashfree'),
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'      => true,
                    'is_simulated' => false,
                    'order_id'     => $data['order_id'] ?? $orderId,
                    'session_data' => [
                        'payment_session_id' => $data['payment_session_id'] ?? null,
                        'order_id'           => $data['order_id'] ?? $orderId,
                        'order_status'       => $data['order_status'] ?? 'ACTIVE',
                        'cf_order_id'        => $data['cf_order_id'] ?? null,
                    ],
                ];
            }

            Log::error('Cashfree Order Creation Failed', [
                'donation_id' => $donation->id,
                'status'      => $response->status(),
                'response'    => $response->json(),
            ]);

            return [
                'success' => false,
                'message' => $response->json('message') ?? 'Failed to initialize Cashfree payment. Please try again.',
            ];
        } catch (\Exception $e) {
            Log::error('CashfreePaymentService::createOrder exception', [
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
     * Verify Cashfree payment from browser return.
     *
     * Cashfree redirects back with ?order_id=... after payment.
     * We fetch the order status server-side — NEVER trust frontend-supplied status.
     *
     * Returns:
     *   ['success' => bool, 'payment_id' => ?string, 'reference' => ?string, 'status' => string, 'message' => ?string]
     */
    public function verifyPayment(Request $request, Donation $donation): array
    {
        $gatewayOrderId = $donation->gateway_order_id;

        if (empty($gatewayOrderId)) {
            return [
                'success' => false,
                'status'  => 'failed',
                'message' => 'Order reference not found.',
            ];
        }

        $statusResult = $this->getPaymentStatus($gatewayOrderId);

        if (!$statusResult['success']) {
            return [
                'success' => false,
                'status'  => 'failed',
                'message' => $statusResult['message'] ?? 'Could not verify payment status.',
            ];
        }

        // In simulation mode, auto-return as PAID
        if ($statusResult['is_simulated'] ?? false) {
            return [
                'success'    => true,
                'payment_id' => 'SIM_PAY_' . strtoupper(uniqid()),
                'reference'  => 'SIM_REF_' . $donation->id,
                'status'     => 'paid',
                'message'    => 'Simulated payment verified.',
            ];
        }

        $data         = $statusResult['data'] ?? [];
        $orderStatus  = strtoupper($data['order_status'] ?? 'UNKNOWN');
        $cfPaymentId  = $data['cf_payment_id'] ?? null;
        $paymentRef   = $data['payment_message'] ?? $gatewayOrderId;

        return match ($orderStatus) {
            'PAID'       => ['success' => true,  'payment_id' => (string) $cfPaymentId, 'reference' => $paymentRef, 'status' => 'paid',       'message' => 'Payment confirmed.'],
            'ACTIVE'     => ['success' => false, 'payment_id' => null,                   'reference' => null,        'status' => 'pending',    'message' => 'Payment is pending.'],
            'CANCELLED'  => ['success' => false, 'payment_id' => null,                   'reference' => null,        'status' => 'cancelled',  'message' => 'Payment was cancelled.'],
            'EXPIRED'    => ['success' => false, 'payment_id' => null,                   'reference' => null,        'status' => 'failed',     'message' => 'Payment session expired.'],
            default      => ['success' => false, 'payment_id' => null,                   'reference' => null,        'status' => 'failed',     'message' => 'Payment could not be confirmed.'],
        };
    }

    // =========================================================================
    // PaymentGatewayInterface: verifyWebhookSignature()
    // =========================================================================

    /**
     * Verify Cashfree webhook signature using raw request body.
     *
     * Cashfree signature algorithm:
     *   HMAC-SHA256( "{timestamp}{rawBody}", CASHFREE_SECRET_KEY )
     *   Header: x-webhook-signature / x-cashfree-signature (base64)
     *   Header: x-webhook-timestamp / x-cashfree-timestamp
     *
     * CRITICAL: Use raw body ONLY. Never re-serialize JSON.
     * FAIL CLOSED: Always return false if timestamp, signature, or secret is missing/invalid.
     */
    public function verifyWebhookSignature(Request $request): bool
    {
        $timestamp = $request->header('x-webhook-timestamp') ?? $request->header('x-cashfree-timestamp') ?? '';
        $signature = $request->header('x-webhook-signature') ?? $request->header('x-cashfree-signature') ?? '';
        $secret    = $this->secretKey;

        if (empty($timestamp) || empty($signature) || empty($secret)) {
            Log::warning('Cashfree webhook signature verification rejected: missing timestamp, signature header, or secret key');
            return false;
        }

        $rawBody  = $request->getContent();
        $computed = base64_encode(hash_hmac('sha256', $timestamp . $rawBody, $secret, true));

        $valid = hash_equals($computed, $signature);

        if (!$valid) {
            Log::warning('Cashfree webhook signature verification failed', [
                'timestamp' => $timestamp,
            ]);
        }

        return $valid;
    }

    // =========================================================================
    // PaymentGatewayInterface: handleWebhook()
    // =========================================================================

    /**
     * Parse and extract payment data from Cashfree webhook payload.
     *
     * Returns standardized array:
     *   ['success' => bool, 'gateway_order_id' => ?string, 'payment_id' => ?string, 'reference' => ?string, 'status' => string]
     */
    public function handleWebhook(Request $request): array
    {
        $payload = $request->json()->all();

        $event     = $payload['type'] ?? ($payload['event'] ?? 'unknown');
        $orderData = $payload['data']['order'] ?? $payload['order'] ?? [];
        $payData   = $payload['data']['payment'] ?? $payload['payment'] ?? [];

        $gatewayOrderId = $orderData['order_id'] ?? ($payload['order_id'] ?? null);
        $cfPaymentId    = $payData['cf_payment_id'] ?? ($payload['cf_payment_id'] ?? null);
        $paymentStatus  = strtoupper($payData['payment_status'] ?? ($orderData['order_status'] ?? 'UNKNOWN'));

        Log::info('Cashfree webhook received', [
            'event'           => $event,
            'gateway_order_id'=> $gatewayOrderId,
            'payment_status'  => $paymentStatus,
        ]);

        $isPaid = in_array($paymentStatus, ['SUCCESS', 'PAID']);

        return [
            'success'          => true,
            'event'            => $event,
            'gateway_order_id' => $gatewayOrderId,
            'payment_id'       => $cfPaymentId ? (string) $cfPaymentId : null,
            'reference'        => $gatewayOrderId,
            'status'           => $isPaid ? 'paid' : strtolower($paymentStatus),
        ];
    }

    // =========================================================================
    // PaymentGatewayInterface: getPaymentStatus()
    // =========================================================================

    /**
     * Fetch real-time order status from Cashfree API.
     */
    public function getPaymentStatus(string $gatewayOrderId): array
    {
        if (!$this->isConfigured) {
            return [
                'success'      => true,
                'is_simulated' => true,
                'data'         => ['order_status' => 'PAID', 'order_amount' => 100.00],
            ];
        }

        try {
            $response = Http::withHeaders([
                'x-client-id'     => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version'   => $this->apiVersion,
            ])->get("{$this->baseUrl}/orders/{$gatewayOrderId}");

            if ($response->successful()) {
                return [
                    'success'      => true,
                    'is_simulated' => false,
                    'data'         => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to fetch order status from Cashfree.',
            ];
        } catch (\Exception $e) {
            Log::error('CashfreePaymentService::getPaymentStatus exception', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // =========================================================================
    // Legacy compat: createOrder(string, float, array, string) for membership
    // =========================================================================

    /**
     * Legacy order creation for membership/exam flows (non-Donation-model calls).
     * Preserves backward compatibility with MembershipController usage.
     */
    public function createOrderLegacy(string $orderId, float $amount, array $customerDetails, string $returnUrl): array
    {
        $amountPaise = (int) round($amount * 100);
        $amountInr   = number_format($amountPaise / 100, 2, '.', '');

        if (!$this->isConfigured) {
            return [
                'success'            => true,
                'is_simulated'       => true,
                'order_id'           => $orderId,
                'payment_session_id' => 'session_mock_' . strtoupper(uniqid()),
                'order_status'       => 'ACTIVE',
                'message'            => 'Cashfree Simulation Mode Active.',
            ];
        }

        try {
            $response = Http::withHeaders([
                'x-client-id'     => $this->appId,
                'x-client-secret' => $this->secretKey,
                'x-api-version'   => $this->apiVersion,
                'Content-Type'    => 'application/json',
            ])->post("{$this->baseUrl}/orders", [
                'order_id'         => $orderId,
                'order_amount'     => (float) $amountInr,
                'order_currency'   => 'INR',
                'customer_details' => [
                    'customer_id'    => $customerDetails['customer_id'] ?? ('CUST_' . time()),
                    'customer_name'  => $customerDetails['customer_name'] ?? 'ABVHPS Member',
                    'customer_email' => $customerDetails['customer_email'] ?? 'support@abvhps.org',
                    'customer_phone' => $customerDetails['customer_phone'] ?? '9999999999',
                ],
                'order_meta' => [
                    'return_url' => $returnUrl . '?order_id={order_id}',
                    'notify_url' => url('/api/cashfree/webhook'),
                ],
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success'            => true,
                    'is_simulated'       => false,
                    'order_id'           => $data['order_id'] ?? $orderId,
                    'payment_session_id' => $data['payment_session_id'] ?? null,
                    'order_status'       => $data['order_status'] ?? 'ACTIVE',
                ];
            }

            Log::error('Cashfree Legacy Order Creation Failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'success'      => false,
                'is_simulated' => false,
                'message'      => $response->json('message') ?? 'Failed to initialize Cashfree payment gateway.',
            ];
        } catch (\Exception $e) {
            Log::error('Cashfree Service Exception: ' . $e->getMessage());
            return [
                'success'      => false,
                'is_simulated' => false,
                'message'      => 'Payment gateway communication failure.',
            ];
        }
    }

    /**
     * Legacy getOrderStatus — preserved for MembershipController backward compatibility.
     */
    public function getOrderStatus(string $orderId): array
    {
        return $this->getPaymentStatus($orderId);
    }
}
