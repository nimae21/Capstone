<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Support\ActivitySubjectLoader;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:50'],
            'event' => ['nullable', 'string', 'max:50'],
            'user_id' => ['nullable', 'integer'],
            'search' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = ActivityLog::with('user');

        if (! empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('action', 'like', $filters['category'].'.%');
        }
        if (! empty($filters['event'])) {
            $query->where('action', 'like', '%.'.$filters['event']);
        }
        if (! empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }
        if (! empty($filters['from'])) {
            $query->where('created_at', '>=', $filters['from']);
        }
        if (! empty($filters['to'])) {
            $query->where('created_at', '<=', $filters['to']);
        }
        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', '%'.$search.'%')
                    ->orWhere('ip_address', 'like', '%'.$search.'%')
                    ->orWhereHas('user', fn ($u) => $u
                        ->where('email', 'like', '%'.$search.'%')
                        ->orWhere('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%'));
            });
        }

        $logs = $query->orderByDesc('activity_log_id')->paginate(25);

        // The subject is a morphTo: resolving it per row would add one query
        // per entry on a page of 25. Prime it in bulk instead.
        ActivitySubjectLoader::prime($logs->getCollection());

        return response()->json($logs->through(fn (ActivityLog $log) => $this->present($log)));
    }

    public function show(ActivityLog $log)
    {
        $log->load('user');
        ActivitySubjectLoader::prime([$log]);

        return response()->json($this->present($log));
    }

    /** Filter options for the mobile audit-log screen. */
    public function filters()
    {
        // Cached: this list only changes when a brand new action type is logged,
        // and each request otherwise costs several remote round trips.
        return response()->json(Cache::remember('mobile-log-filters', 300, function () {
            // Split in PHP: the distinct action list is tiny and this stays
            // portable between sqlite and the production Postgres database.
            $actions = ActivityLog::query()->distinct()->orderBy('action')->pluck('action');

            return [
                'categories' => $actions->map(fn ($action) => Str::before((string) $action, '.'))->filter()->unique()->values(),
                'events' => $actions->map(fn ($action) => Str::after((string) $action, '.'))->filter()->unique()->values(),
                'users' => User::whereIn('id', ActivityLog::query()->select('user_id')->whereNotNull('user_id')->distinct())
                    ->orderBy('first_name')->take(100)->get()
                    ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->full_name, 'role' => $user->role]),
            ];
        }));
    }

    private function present(ActivityLog $log): array
    {
        return [
            'id' => $log->activity_log_id,
            'action' => $log->action,
            'category' => $log->category,
            'event' => $log->event,
            'subject' => $log->subject_label,
            'subject_type' => $log->subject_type,
            'subject_id' => $log->subject_id,
            'user' => $log->user?->full_name ?? 'System',
            'user_id' => $log->user_id,
            'user_email' => $log->user?->email,
            'ip_address' => $log->ip_address,
            'changes' => $log->changes,
            'created_at' => $log->created_at?->toIso8601String(),
        ];
    }
}