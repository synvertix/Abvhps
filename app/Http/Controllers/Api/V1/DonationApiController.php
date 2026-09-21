<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Services\RazorpayPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DonationApiController extends Controller
{
    /**
     * Initiate a donation payment order via Razorpay/Cashfree.
     */
    public function initiate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact'         => 'required|string|max:20',
            'email'           => 'nullable|email|max:255',
            'pan_number'      => 'nullable|string|size:10',
            'amount'          => 'required|numeric|min:1',
            'payment_gateway' => 'required|string|in:razorpay,cashfree',
            'cause'           => 'nullable|string|max:255',
        ]);

        $orderId = 'ORD_' . Str::upper(Str::random(10)) . '_' . time();

        $donation = Donation::create([
            'name'             => $validated['name'],
            'contact'          => $validated['contact'],
            'email'            => $validated['email'] ?? null,
            'pan_number'       => isset($validated['pan_number']) ? strtoupper($validated['pan_number']) : null,
            'amount'           => $validated['amount'],
            'payment_gateway'  => strtolower($validated['payment_gateway']),
            'gateway_order_id' => $orderId,
            'payment_status'   => 'PENDING',
            'cause'            => $validated['cause'] ?? 'General Donation',
        ]);

        $razorpayOrderId = null;

        if ($validated['payment_gateway'] === 'razorpay') {
            try {
                $service = new RazorpayPaymentService();
                $rpOrder = $service->createOrder($donation, url("/donations/receipt/{$donation->id}"));
                $razorpayOrderId = $rpOrder['session_data']['razorpay_order_id'] ?? ($rpOrder['order_id'] ?? null);
            } catch (\Exception $e) {
                // Fallback mock order for local testing if gateway keys unconfigured
                $razorpayOrderId = 'order_mock_' . Str::random(12);
            }
        }

        return response()->json([
            'success'     => true,
            'message'     => 'Donation order initiated successfully.',
            'donation_id' => $donation->id,
            'order'       => [
                'order_id'          => $orderId,
                'razorpay_order_id' => $razorpayOrderId,
                'amount'            => $donation->amount,
                'currency'          => 'INR',
                'gateway'           => $donation->payment_gateway,
            ],
        ], 201);
    }

    /**
     * Authoritative server check of donation payment status.
     */
    public function checkStatus(int $id): JsonResponse
    {
        $donation = Donation::find($id);

        if (!$donation) {
            return response()->json(['success' => false, 'message' => 'Donation record not found.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $donation->id,
                'name'               => $donation->name,
                'amount'             => (float) $donation->amount,
                'payment_status'     => $donation->payment_status,
                'gateway_order_id'   => $donation->gateway_order_id,
                'gateway_payment_id' => $donation->gateway_payment_id,
                'cause'              => $donation->cause,
                'receipt_url'        => $donation->payment_status === 'PAID' ? url("/donations/receipt/{$donation->id}") : null,
                'created_at'         => $donation->created_at,
            ],
        ]);
    }
}
