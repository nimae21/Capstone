<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Optional category filter e.g. ?category=order
        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('action', 'like', $request->category . '.%');
        }

        // Optional search by action or user name/email
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($u) => $u
                      ->where('email', 'like', "%{$search}%")
                      ->orWhere('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                  );
            });
        }

        $logs = $query->paginate(20)->through(fn($log) => [
            'id'          => $log->activity_log_id,
            'action'      => $log->action,
            'category'    => $log->category,   // getCategoryAttribute()
            'event'       => $log->event,       // getEventAttribute()
            'subject'     => $log->subject_label, // getSubjectLabelAttribute()
            'user'        => $log->user?->full_name ?? 'System',
            'ip_address'  => $log->ip_address,
            'created_at'  => $log->created_at->diffForHumans(),
        ]);

        return response()->json($logs);
    }
}