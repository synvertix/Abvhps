<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBannerController extends Controller
{
    public function index(Request $request)
    {
        $page = $request->input('page_key');
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $query = Banner::query();

        if (!empty($page) && $page !== 'all') {
            $query->where('page_key', $page);
        }

        if (!empty($status) && $status !== 'all') {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('page_key', 'LIKE', "%{$search}%")
                  ->orWhere('title', 'LIKE', "%{$search}%")
                  ->orWhere('subtitle', 'LIKE', "%{$search}%");
            });
        }

        $paginator = $query->orderBy('sort_order', 'asc')->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($b) {
            return [
                'id'                 => $b->id,
                'page_key'           => $b->page_key,
                'title'              => $b->title,
                'subtitle'           => $b->subtitle,
                'desktop_image_url'  => $b->desktop_image_path ? Storage::disk('public')->url($b->desktop_image_path) : null,
                'mobile_image_url'   => $b->mobile_image_path ? Storage::disk('public')->url($b->mobile_image_path) : null,
                'status'             => $b->status ?? 'show',
                'sort_order'         => (int) ($b->sort_order ?? 0),
                'created_at'         => $b->created_at,
            ];
        });

        $stats = [
            'total_banners'  => Banner::count(),
            'active_banners' => Banner::where('status', 'show')->count(),
            'hidden_banners' => Banner::where('status', 'hide')->count(),
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
        $b = Banner::find($id);
        if (!$b) {
            return response()->json(['success' => false, 'message' => 'Banner not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $b->id,
                'page_key'           => $b->page_key,
                'title'              => $b->title,
                'subtitle'           => $b->subtitle,
                'desktop_image_url'  => $b->desktop_image_path ? Storage::disk('public')->url($b->desktop_image_path) : null,
                'mobile_image_url'   => $b->mobile_image_path ? Storage::disk('public')->url($b->mobile_image_path) : null,
                'status'             => $b->status ?? 'show',
                'sort_order'         => (int) ($b->sort_order ?? 0),
                'created_at'         => $b->created_at,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'page_key'      => 'required|string|max:100',
            'title'         => 'nullable|string|max:255',
            'subtitle'      => 'nullable|string|max:255',
            'status'        => 'required|in:show,hide',
            'sort_order'    => 'nullable|integer',
            'desktop_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'mobile_image'  => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        $deskPath = null;
        $mobPath = null;

        if ($request->hasFile('desktop_image')) {
            $deskPath = $request->file('desktop_image')->store('banners/desktop', 'public');
        }

        if ($request->hasFile('mobile_image')) {
            $mobPath = $request->file('mobile_image')->store('banners/mobile', 'public');
        }

        $b = Banner::create([
            'page_key'           => $request->page_key,
            'title'              => $request->title,
            'subtitle'           => $request->subtitle,
            'status'             => $request->status,
            'sort_order'         => $request->sort_order ?? 0,
            'desktop_image_path' => $deskPath,
            'mobile_image_path'  => $mobPath,
            'desktop_banner'     => $deskPath ?? 'banners/desktop/default.jpg',
            'mobile_banner'      => $mobPath ?? 'banners/mobile/default.jpg',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Banner created successfully.',
            'data'    => $b,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $b = Banner::find($id);
        if (!$b) {
            return response()->json(['success' => false, 'message' => 'Banner not found'], 404);
        }

        $request->validate([
            'page_key'      => 'required|string|max:100',
            'title'         => 'nullable|string|max:255',
            'subtitle'      => 'nullable|string|max:255',
            'status'        => 'required|in:show,hide',
            'sort_order'    => 'nullable|integer',
            'desktop_image' => 'nullable|image|mimes:jpeg,jpg,png,webp|max:4096',
            'mobile_image'  => 'nullable|image|mimes:jpeg,jpg,png,webp|max:2048',
        ]);

        if ($request->hasFile('desktop_image')) {
            if ($b->desktop_image_path && Storage::disk('public')->exists($b->desktop_image_path)) {
                Storage::disk('public')->delete($b->desktop_image_path);
            }
            $b->desktop_image_path = $request->file('desktop_image')->store('banners/desktop', 'public');
        }

        if ($request->hasFile('mobile_image')) {
            if ($b->mobile_image_path && Storage::disk('public')->exists($b->mobile_image_path)) {
                Storage::disk('public')->delete($b->mobile_image_path);
            }
            $b->mobile_image_path = $request->file('mobile_image')->store('banners/mobile', 'public');
        }

        $b->page_key   = $request->page_key;
        $b->title      = $request->title;
        $b->subtitle   = $request->subtitle;
        $b->status     = $request->status;
        $b->sort_order = $request->sort_order ?? $b->sort_order;
        $b->save();

        return response()->json([
            'success' => true,
            'message' => 'Banner updated successfully.',
            'data'    => $b,
        ]);
    }

    public function toggleStatus($id)
    {
        $b = Banner::find($id);
        if (!$b) {
            return response()->json(['success' => false, 'message' => 'Banner not found'], 404);
        }

        $b->status = ($b->status === 'show') ? 'hide' : 'show';
        $b->save();

        return response()->json([
            'success' => true,
            'message' => "Banner status toggled to {$b->status}.",
            'data'    => $b,
        ]);
    }

    public function destroy($id)
    {
        $b = Banner::find($id);
        if (!$b) {
            return response()->json(['success' => false, 'message' => 'Banner not found'], 404);
        }

        if ($b->desktop_image_path && Storage::disk('public')->exists($b->desktop_image_path)) {
            Storage::disk('public')->delete($b->desktop_image_path);
        }
        if ($b->mobile_image_path && Storage::disk('public')->exists($b->mobile_image_path)) {
            Storage::disk('public')->delete($b->mobile_image_path);
        }

        $b->delete();

        return response()->json([
            'success' => true,
            'message' => 'Banner deleted successfully.',
        ]);
    }
}
