<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\FundraisingCampaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminCampaignController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $query = FundraisingCampaign::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where('title', 'LIKE', '%' . $search . '%');
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($c) {
            $raised = (float) Donation::where('campaign_id', $c->id)->where('payment_status', 'PAID')->sum('amount');
            $target = (float) ($c->target_amount ?? 0);
            $progress = $target > 0 ? min(100, round(($raised / $target) * 100, 1)) : 0;

            return [
                'id'            => $c->id,
                'title'         => $c->title,
                'description'   => $c->description,
                'target_amount' => $target,
                'raised_amount' => $raised,
                'progress_pct'  => $progress,
                'status'        => $c->status ?? 'active',
                'end_date'      => $c->end_date,
                'image_url'     => $c->image_path ? Storage::disk('public')->url($c->image_path) : null,
                'created_at'    => $c->created_at,
            ];
        });

        $totalTarget = (float) FundraisingCampaign::sum('target_amount');
        $totalRaised = (float) Donation::where('payment_status', 'PAID')->sum('amount');

        $stats = [
            'total_campaigns' => FundraisingCampaign::count(),
            'active_count'    => FundraisingCampaign::where('status', 'active')->count(),
            'total_target'    => $totalTarget,
            'total_raised'    => $totalRaised,
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
        $c = FundraisingCampaign::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        $raised = (float) Donation::where('campaign_id', $c->id)->where('payment_status', 'PAID')->sum('amount');
        $target = (float) ($c->target_amount ?? 0);
        $progress = $target > 0 ? min(100, round(($raised / $target) * 100, 1)) : 0;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $c->id,
                'title'         => $c->title,
                'description'   => $c->description,
                'target_amount' => $target,
                'raised_amount' => $raised,
                'progress_pct'  => $progress,
                'status'        => $c->status ?? 'active',
                'end_date'      => $c->end_date,
                'image_url'     => $c->image_path ? Storage::disk('public')->url($c->image_path) : null,
                'created_at'    => $c->created_at,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'target_amount' => 'required|numeric|min:1',
            'status'        => 'required|in:active,expired,disabled',
            'end_date'      => 'nullable|date',
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('campaigns', 'public');
        }

        $c = FundraisingCampaign::create([
            'title'         => $request->title,
            'description'   => $request->description,
            'target_amount' => $request->target_amount,
            'status'        => $request->status,
            'end_date'      => $request->end_date ?? now()->addMonth(),
            'image_path'    => $imagePath,
            'cover_image'   => $imagePath ?? 'campaigns/default.jpg',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Campaign created successfully.',
            'data'    => $c,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $c = FundraisingCampaign::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'target_amount' => 'required|numeric|min:1',
            'status'        => 'required|in:active,expired,disabled',
            'end_date'      => 'nullable|date',
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($c->image_path && Storage::disk('public')->exists($c->image_path)) {
                Storage::disk('public')->delete($c->image_path);
            }
            $c->image_path = $request->file('image')->store('campaigns', 'public');
        }

        $c->title         = $request->title;
        $c->description   = $request->description;
        $c->target_amount = $request->target_amount;
        $c->status        = $request->status;
        $c->end_date      = $request->end_date;
        $c->save();

        return response()->json([
            'success' => true,
            'message' => 'Campaign updated successfully.',
            'data'    => $c,
        ]);
    }

    public function toggleStatus($id)
    {
        $c = FundraisingCampaign::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        $c->status = ($c->status === 'active') ? 'disabled' : 'active';
        $c->save();

        return response()->json([
            'success' => true,
            'message' => "Campaign status toggled to {$c->status}.",
            'data'    => $c,
        ]);
    }

    public function destroy($id)
    {
        $c = FundraisingCampaign::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Campaign not found'], 404);
        }

        if ($c->image_path && Storage::disk('public')->exists($c->image_path)) {
            Storage::disk('public')->delete($c->image_path);
        }

        $c->delete();

        return response()->json([
            'success' => true,
            'message' => 'Campaign deleted successfully.',
        ]);
    }
}
