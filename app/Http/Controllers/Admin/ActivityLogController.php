<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->query('category', 'all');
        $search   = trim((string) $request->query('search'));
        $from     = $request->query('from');
        $to       = $request->query('to');

        $query = ActivityLog::with(['user', 'subject'])->latest();

        if ($category !== 'all') {
            $query->where('action', 'like', $category . '.%');
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('action', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('email', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }

        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Category tabs, derived from the distinct action prefixes actually
        // present in the table - split_part() is Postgres-specific, which is
        // fine since Supabase is Postgres (matches the rest of the codebase's
        // avoidance of MySQL-only functions).
        //
        // Aliased as `category_name`, NOT `category` - the ActivityLog model
        // defines a getCategoryAttribute() accessor, and Eloquent always
        // routes $model->category through that accessor if it exists,
        // regardless of what a raw select aliased the column as. Using a
        // different alias avoids that collision entirely.
        $categories = ActivityLog::selectRaw("split_part(action, '.', 1) as category_name, COUNT(*) as total")
            ->groupBy('category_name')
            ->orderBy('category_name')
            ->get();

        return view('admin.logs.index', [
            'logs'           => $logs,
            'categories'     => $categories,
            'activeCategory' => $category,
            'search'         => $search,
            'from'           => $from,
            'to'             => $to,
        ]);
    }
}