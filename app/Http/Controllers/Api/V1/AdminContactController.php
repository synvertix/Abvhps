<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class AdminContactController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $search = $request->input('search');
        $perPage = (int) $request->input('per_page', 15);

        $query = ContactMessage::query();

        if (!empty($status)) {
            $query->where('status', $status);
        }

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('subject', 'LIKE', '%' . $search . '%')
                  ->orWhere('message', 'LIKE', '%' . $search . '%');
            });
        }

        $paginator = $query->orderBy('id', 'desc')->paginate($perPage);

        $items = collect($paginator->items())->map(function ($c) {
            return [
                'id'          => $c->id,
                'name'        => $c->name,
                'email'       => $c->email,
                'phone'       => $c->phone,
                'subject'     => $c->subject ?? 'General Inquiry',
                'message'     => $c->message,
                'status'      => $c->status ?? 'unread',
                'admin_notes' => $c->admin_notes,
                'created_at'  => $c->created_at,
            ];
        });

        $stats = [
            'total'          => ContactMessage::count(),
            'unread_count'   => ContactMessage::whereIn('status', ['new', 'unread', 'pending'])->count(),
            'reviewed_count' => ContactMessage::whereIn('status', ['read', 'reviewed', 'resolved'])->count(),
            'spam_count'     => ContactMessage::where('status', 'spam')->count(),
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
        $c = ContactMessage::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Contact message not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'          => $c->id,
                'name'        => $c->name,
                'email'       => $c->email,
                'phone'       => $c->phone,
                'subject'     => $c->subject ?? 'General Inquiry',
                'message'     => $c->message,
                'status'      => $c->status ?? 'new',
                'admin_notes' => $c->admin_notes,
                'created_at'  => $c->created_at,
            ],
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $c = ContactMessage::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Contact message not found'], 404);
        }

        $request->validate([
            'status'      => 'required|string',
            'admin_notes' => 'nullable|string|max:1000',
        ]);

        $c->status = $request->status;
        if ($request->has('admin_notes')) {
            $c->admin_notes = $request->admin_notes;
        }
        $c->save();

        return response()->json([
            'success' => true,
            'message' => 'Contact status updated.',
            'data'    => $c,
        ]);
    }

    public function destroy($id)
    {
        $c = ContactMessage::find($id);
        if (!$c) {
            return response()->json(['success' => false, 'message' => 'Contact message not found'], 404);
        }

        $c->delete();

        return response()->json([
            'success' => true,
            'message' => 'Contact message deleted.',
        ]);
    }
}
