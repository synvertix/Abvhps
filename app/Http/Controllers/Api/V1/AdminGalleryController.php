<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminGalleryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $type = $request->input('type');

        $query = Gallery::query();

        if (!empty($type)) {
            $query->where('media_type', $type);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($g) {
            return [
                'id'         => $g->id,
                'media_type' => $g->media_type,
                'image_url'  => $g->image_path ? Storage::disk('public')->url($g->image_path) : null,
                'video_url'  => $g->video_url,
                'created_at' => $g->created_at,
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

    public function store(Request $request)
    {
        $request->validate([
            'media_type' => 'required|in:image,video',
            'image'      => 'required_if:media_type,image|nullable|image|mimes:jpeg,jpg,png|max:2048',
            'video_url'  => 'required_if:media_type,video|nullable|url|max:255',
        ]);

        $uploadedPath = null;
        if ($request->media_type === 'image' && $request->hasFile('image')) {
            $uploadedPath = $request->file('image')->store('gallery', 'public');
        }

        $g = Gallery::create([
            'media_type' => $request->media_type,
            'image_path' => $uploadedPath,
            'video_url'  => $request->media_type === 'video' ? $request->video_url : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Media item added to gallery successfully.',
            'data'    => [
                'id'         => $g->id,
                'media_type' => $g->media_type,
                'image_url'  => $g->image_path ? Storage::disk('public')->url($g->image_path) : null,
                'video_url'  => $g->video_url,
                'created_at' => $g->created_at,
            ],
        ], 201);
    }

    public function destroy($id)
    {
        $g = Gallery::find($id);
        if (!$g) {
            return response()->json(['success' => false, 'message' => 'Gallery item not found'], 404);
        }

        if ($g->image_path && Storage::disk('public')->exists($g->image_path)) {
            Storage::disk('public')->delete($g->image_path);
        }

        $g->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gallery item deleted successfully.',
        ]);
    }
}
