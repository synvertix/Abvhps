<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Support\ApiMediaHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogController extends Controller
{
    /**
     * List all published active blogs with pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 12);
        if ($perPage < 1 || $perPage > 50) {
            $perPage = 12;
        }

        $paginated = Blog::where('status', 'active')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $items = collect($paginated->items())->map(function ($b) {
            $cleanContent = strip_tags($b->content ?? '');
            return [
                'id'            => $b->id,
                'title'         => $b->title,
                'excerpt'       => Str::limit($cleanContent, 160),
                'thumbnail_url' => ApiMediaHelper::resolveUrl($b->thumbnail_path ?: $b->image_path),
                'image_url'     => ApiMediaHelper::resolveUrl($b->image_path),
                'published_at'  => $b->created_at ? $b->created_at->format('d M Y') : null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
            ],
            'message' => null,
        ]);
    }

    /**
     * Show single published active blog detail.
     */
    public function show(int $id): JsonResponse
    {
        $blog = Blog::where('id', $id)
            ->where('status', 'active')
            ->first();

        if (!$blog) {
            return response()->json([
                'success' => false,
                'message' => 'Article not found or has been unpublished.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'            => $blog->id,
                'title'         => $blog->title,
                'excerpt'       => Str::limit(strip_tags($blog->content ?? ''), 160),
                'content'       => $blog->content,
                'thumbnail_url' => ApiMediaHelper::resolveUrl($blog->thumbnail_path ?: $blog->image_path),
                'image_url'     => ApiMediaHelper::resolveUrl($blog->image_path),
                'published_at'  => $blog->created_at ? $blog->created_at->format('d M Y') : null,
            ],
            'message' => null,
        ]);
    }
}
