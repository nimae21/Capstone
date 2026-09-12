<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'filter' => ['nullable', 'in:all,unread,read'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = $request->user()->notifications();

        $filter = $filters['filter'] ?? 'all';
        if ($filter === 'unread') {
            $query->whereNull('read_at');
        } elseif ($filter === 'read') {
            $query->whereNotNull('read_at');
        }

        $notifications = $query->orderByDesc('created_at')->paginate(20)
            ->through(fn (DatabaseNotification $n) => $this->present($n))
            ->toArray();

        // The unread badge ships with the list so the screen needs one request.
        return response()->json($notifications + [
            'counts' => ['unread' => $request->user()->unreadNotifications()->count()],
        ]);
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markRead(Request $request, string $notification)
    {
        $record = $request->user()->notifications()->whereKey($notification)->firstOrFail();
        if ($record->read_at === null) {
            $record->markAsRead();
        }

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $this->present($record->fresh()),
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function markAllRead(Request $request)
    {
        // One UPDATE for the whole inbox: the Eloquent collection helper saves
        // each row individually, which used to cost one remote round trip per
        // unread notification on a phone that may have hundreds of them.
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return response()->json([
            'message' => 'All notifications marked as read.',
            'unread' => 0,
        ]);
    }

    private function present(DatabaseNotification $n): array
    {
        $data = $n->data ?? [];

        return [
            'id' => $n->id,
            'type' => $data['type'] ?? 'alert',
            'title' => $data['title'] ?? 'Achilles',
            'body' => $data['body'] ?? '',
            'route' => $data['route'] ?? null,
            'meta' => $data['meta'] ?? [],
            'read' => $n->read_at !== null,
            'created_at' => $n->created_at?->toIso8601String(),
        ];
    }
}
