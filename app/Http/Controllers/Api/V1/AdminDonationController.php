<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use Illuminate\Http\Request;

class AdminDonationController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $gateway = $request->input('gateway');
        $status = $request->input('status');
        $perPage = (int) $request->input('per_page', 15);

        $query = Donation::with('campaign')->orderBy('id', 'desc');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('contact', 'LIKE', '%' . $search . '%')
                  ->orWhere('pan_number', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('gateway_order_id', 'LIKE', '%' . $search . '%')
                  ->orWhere('gateway_payment_id', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($gateway)) {
            $query->where('payment_gateway', $gateway);
        }

        if (!empty($status)) {
            $query->where('payment_status', $status);
        }

        $paginator = $query->paginate($perPage);

        $items = collect($paginator->items())->map(function ($d) {
            return [
                'id'                 => $d->id,
                'name'               => $d->name,
                'contact'            => $d->contact,
                'email'              => $d->email,
                'pan_number'         => $d->pan_number,
                'amount'             => (float) $d->amount,
                'payment_gateway'    => $d->payment_gateway,
                'gateway_order_id'   => $d->gateway_order_id,
                'gateway_payment_id' => $d->gateway_payment_id,
                'payment_status'     => $d->payment_status,
                'cause'              => $d->campaign ? $d->campaign->title : 'General Fund',
                'receipt_url'        => route('donations.receipt', $d->id),
                'created_at'         => $d->created_at,
            ];
        });

        $stats = [
            'total_recorded' => Donation::count(),
            'total_raised'   => (float) Donation::where('payment_status', 'PAID')->sum('amount'),
            'paid_count'     => Donation::where('payment_status', 'PAID')->count(),
            'pending_count'  => Donation::where('payment_status', 'PENDING')->count(),
        ];

        return response()->json([
            'success' => true,
            'stats'   => $stats,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginator->currentPage(),
                'last_page'    => $paginator->lastPage(),
                'per_page'     => $paginator->perPage(),
                'total'        => $paginator->total(),
            ],
        ]);
    }

    public function show($id)
    {
        $d = Donation::with('campaign')->find($id);
        if (!$d) {
            return response()->json(['success' => false, 'message' => 'Donation record not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $d->id,
                'name'               => $d->name,
                'contact'            => $d->contact,
                'email'              => $d->email,
                'pan_number'         => $d->pan_number,
                'amount'             => (float) $d->amount,
                'payment_gateway'    => $d->payment_gateway,
                'gateway_order_id'   => $d->gateway_order_id,
                'gateway_payment_id' => $d->gateway_payment_id,
                'payment_status'     => $d->payment_status,
                'cause'              => $d->campaign ? $d->campaign->title : 'General Fund',
                'receipt_url'        => route('donations.receipt', $d->id),
                'created_at'         => $d->created_at,
            ],
        ]);
    }
}
