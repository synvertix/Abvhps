<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminBlogController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $perPage = (int) $request->input('per_page', 15);

        $query = Blog::query();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', '%' . $search . '%')
                  ->orWhere('content', 'LIKE', '%' . $search . '%');
            });
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($b) {
            return [
                'id'            => $b->id,
                'title'         => $b->title,
                'content'       => $b->content,
                'status'        => $b->status ?? 'active',
                'image_url'     => $b->image_path ? Storage::disk('public')->url($b->image_path) : null,
                'thumbnail_url' => $b->thumbnail_path ? Storage::disk('public')->url($b->thumbnail_path) : null,
                'created_at'    => $b->created_at,
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
        $b = Blog::find($id);
        if (!$b) {
            return response()->json(['success' => false, 'message' => 'Blog post not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $b->id,
                'title'         => $b->title,
                'content'       => $b->content,
                'status'        => $b->status ?? 'active',
                'image_url'     => $b->image_path ? Storage::disk('public')->url($b->image_path) : null,
                'thumbnail_url' => $b->thumbnail_path ? Storage::disk('public')->url($b->thumbnail_path) : null,
                'created_at'    => $b->created_at,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'status'    => 'required|in:active,draft',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png|max:1048',
        ]);

        $mainImagePath = null;
        $thumbnailPath = null;

        if ($request->hasFile('image')) {
            $mainImagePath = $request->file('image')->store('blogs/main', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('blogs/thumb', 'public');
        }

        $b = Blog::create([
            'title'          => $request->title,
            'content'        => $request->content,
            'status'         => $request->status,
            'image_path'     => $mainImagePath,
            'thumbnail_path' => $thumbnailPath,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Blog article published successfully.',
            'data'    => $b,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $b = Blog::find($id);
        if (!$b) {
            return response()->json(['success' => false, 'message' => 'Blog post not found'], 404);
        }

        $request->validate([
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
            'status'    => 'required|in:active,draft',
            'image'     => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
            'thumbnail' => 'nullable|image|mimes:jpeg,jpg,png|max:1048',
        ]);

        if ($request->hasFile('image')) {
            if ($b->image_path && Storage::disk('public')->exists($b->image_path)) {
                Storage::disk('public')->delete($b->image_path);
            }
            $b->image_path = $request->file('image')->store('blogs/main', 'public');
        }

        if ($request->hasFile('thumbnail')) {
            if ($b->thumbnail_path && Storage::disk('public')->exists($b->thumbnail_path)) {
                Storage::disk('public')->delete($b->thumbnail_path);
            }
            $b->thumbnail_path = $request->file('thumbnail')->store('blogs/thumb', 'public');
        }

        $b->title   = $request->title;
        $b->content = $request->content;
        $b->status  = $request->status;
        $b->save();

        return response()->json([
            'success' => true,
            'message' => 'Blog article updated successfully.',
            'data'    => $b,
        ]);
    }

    public function destroy($id)
    {
        $b = Blog::find($id);
        if (!$b) {
            return response()->json(['success' => false, 'message' => 'Blog post not found'], 404);
        }

        if ($b->image_path && Storage::disk('public')->exists($b->image_path)) {
            Storage::disk('public')->delete($b->image_path);
        }
        if ($b->thumbnail_path && Storage::disk('public')->exists($b->thumbnail_path)) {
            Storage::disk('public')->delete($b->thumbnail_path);
        }

        $b->delete();

        return response()->json([
            'success' => true,
            'message' => 'Blog article deleted successfully.',
        ]);
    }
}
