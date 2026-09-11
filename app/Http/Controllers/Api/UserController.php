<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AdminInvitation;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\AccountStatusService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Customer and Admin accounts. Suspension reuses AccountStatusService, so the
 * mobile app cannot suspend a Super Admin or the caller's own account.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'role' => ['nullable', Rule::in(['user', 'admin', 'all'])],
            'status' => ['nullable', Rule::in(['active', 'suspended', 'all'])],
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = User::query()->withCount('orders');

        $role = $filters['role'] ?? 'user';
        if ($role === 'user') {
            $query->where('role', 'user');
        } elseif ($role === 'admin') {
            $query->where('role', 'admin');
        } else {
            $query->whereIn('role', ['user', 'admin']);
        }

        $status = $filters['status'] ?? 'all';
        if ($status === 'active') {
            $query->where('is_active', true);
        } elseif ($status === 'suspended') {
            $query->where('is_active', false);
        }

        if (! empty($filters['search'])) {
            $term = trim($filters['search']);
            $query->where(fn ($q) => $q->where('email', 'like', '%'.$term.'%')
                ->orWhere('first_name', 'like', '%'.$term.'%')
                ->orWhere('last_name', 'like', '%'.$term.'%'));
        }

        return response()->json($query->orderBy('id')->paginate(25)
            ->through(fn (User $user) => $this->present($user)));
    }

    public function counts()
    {
        $base = fn () => User::query();

        return response()->json([
            'users' => [
                'total' => $base()->where('role', 'user')->count(),
                'active' => $base()->where('role', 'user')->where('is_active', true)->count(),
                'suspended' => $base()->where('role', 'user')->where('is_active', false)->count(),
            ],
            'admins' => [
                'total' => $base()->where('role', 'admin')->count(),
                'active' => $base()->where('role', 'admin')->where('is_active', true)->count(),
                'suspended' => $base()->where('role', 'admin')->where('is_active', false)->count(),
            ],
            'invitations_pending' => AdminInvitation::whereNull('accepted_at')->where('expires_at', '>', now())->count(),
            'approvals_pending' => ApprovalRequest::where('status', 'pending')->count(),
        ]);
    }

    public function show(Request $request, User $user)
    {
        abort_if($user->role === 'super_admin' && $user->id !== $request->user()->id, 404);

        $user->loadCount('orders');
        $isAdmin = $user->role === 'admin';

        $activity = ActivityLog::with('user')
            ->where(fn ($q) => $q->where('user_id', $user->id)
                ->orWhere(fn ($q2) => $q2->where('subject_type', User::class)->where('subject_id', $user->id)))
            ->orderByDesc('activity_log_id')->take(10)->get()
            ->map(fn ($log) => [
                'id' => $log->activity_log_id,
                'action' => $log->action,
                'event' => $log->event,
                'subject' => $log->subject_label,
                'created_at' => $log->created_at?->toIso8601String(),
            ])->values();

        return response()->json(array_merge($this->present($user), [
            'phone' => null,
            'orders_count' => $user->orders_count,
            'approvals_submitted' => $isAdmin ? ApprovalRequest::where('requester_id', $user->id)->count() : 0,
            'approvals_pending' => $isAdmin ? ApprovalRequest::where('requester_id', $user->id)->where('status', 'pending')->count() : 0,
            'invited_by' => $isAdmin ? AdminInvitation::where('accepted_user_id', $user->id)->with('inviter')->first()?->inviter?->full_name : null,
            'can_suspend' => $this->canSuspend($request->user(), $user),
            'recent_activity' => $activity,
        ]));
    }

    public function status(Request $request, User $user, AccountStatusService $statuses)
    {
        $data = $request->validate(['is_active' => ['required', 'boolean']]);
        $statuses->setActive($user, $request->user(), (bool) $data['is_active']);

        return response()->json([
            'message' => $data['is_active'] ? 'Account reactivated.' : 'Account suspended.',
            'user' => $this->present($user->fresh()),
        ]);
    }

    private function canSuspend(User $actor, User $target): bool
    {
        return $target->id !== $actor->id && in_array($target->role, ['user', 'admin'], true);
    }

    private function present(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->full_name,
            'email' => $user->email,
            'initials' => $user->initials,
            'role' => $user->role,
            'role_label' => $user->role === 'admin' ? 'Admin' : ($user->role === 'super_admin' ? 'Super Admin' : 'Customer'),
            'is_active' => (bool) $user->is_active,
            'status' => $user->is_active ? 'Active' : 'Suspended',
            'email_verified' => $user->email_verified_at !== null,
            'created_at' => $user->created_at?->toIso8601String(),
            'orders_count' => $user->orders_count ?? null,
        ];
    }
}