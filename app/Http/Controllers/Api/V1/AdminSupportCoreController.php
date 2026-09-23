<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\OurSupport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminSupportCoreController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = (int) $request->input('per_page', 15);

        $query = OurSupport::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('short_info', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('sort_order', 'asc')->orderBy('id', 'asc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($s) {
            return [
                'id'         => $s->id,
                'name'       => $s->name,
                'sort_order' => (int) $s->sort_order,
                'short_info' => $s->short_info,
                'status'     => $s->status ?? 'show',
                'image_url'  => $s->image_path ? Storage::disk('public')->url($s->image_path) : null,
                'created_at' => $s->created_at,
            ];
        });

        return response()->json([
            'success' => true,
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
        $s = OurSupport::find($id);
        if (!$s) {
            return response()->json(['success' => false, 'message' => 'Support project not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'         => $s->id,
                'name'       => $s->name,
                'sort_order' => (int) $s->sort_order,
                'short_info' => $s->short_info,
                'status'     => $s->status ?? 'show',
                'image_url'  => $s->image_path ? Storage::disk('public')->url($s->image_path) : null,
                'created_at' => $s->created_at,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'required|integer|min:1',
            'short_info' => 'required|string',
            'status'     => 'required|in:show,hide',
            'image'      => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $uploadedPath = null;
        if ($request->hasFile('image')) {
            $uploadedPath = $request->file('image')->store('supports', 'public');
        }

        $s = OurSupport::create([
            'name'       => $request->name,
            'sort_order' => $request->sort_order,
            'short_info' => $request->short_info,
            'status'     => $request->status,
            'image_path' => $uploadedPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Support project created successfully.',
            'data'    => $s,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $s = OurSupport::find($id);
        if (!$s) {
            return response()->json(['success' => false, 'message' => 'Support project not found'], 404);
        }

        $request->validate([
            'name'       => 'required|string|max:255',
            'sort_order' => 'required|integer|min:1',
            'short_info' => 'required|string',
            'status'     => 'required|in:show,hide',
            'image'      => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        if ($request->hasFile('image')) {
            if ($s->image_path && Storage::disk('public')->exists($s->image_path)) {
                Storage::disk('public')->delete($s->image_path);
            }
            $s->image_path = $request->file('image')->store('supports', 'public');
        }

        $s->name       = $request->name;
        $s->sort_order = $request->sort_order;
        $s->short_info = $request->short_info;
        $s->status     = $request->status;
        $s->save();

        return response()->json([
            'success' => true,
            'message' => 'Support project updated successfully.',
            'data'    => $s,
        ]);
    }

    public function destroy($id)
    {
        $s = OurSupport::find($id);
        if (!$s) {
            return response()->json(['success' => false, 'message' => 'Support project not found'], 404);
        }

        if ($s->image_path && Storage::disk('public')->exists($s->image_path)) {
            Storage::disk('public')->delete($s->image_path);
        }

        $s->delete();

        return response()->json([
            'success' => true,
            'message' => 'Support project deleted successfully.',
        ]);
    }
}
