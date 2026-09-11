<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use App\Models\User;
use App\Services\AccountStatusService;
use App\Services\ApprovalService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GovernanceController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['per_page' => 'nullable|in:25,50,100', 'status' => 'nullable|in:pending,approved,rejected']);
        $requests = ApprovalRequest::with(['requester', 'reviewer'])->where('status', $data['status'] ?? 'pending')
            ->orderByDesc('id')->paginate((int) ($data['per_page'] ?? 25))->withQueryString();

        return view('admin.governance.queue', compact('requests'));
    }

    public function review(Request $request, ApprovalService $service)
    {
        $data = $request->validate(['ids' => 'required|array|min:1|max:100', 'ids.*' => 'required|integer|distinct|exists:approval_requests,id',
            'decision' => 'required|in:approved,rejected', 'reason' => 'nullable|required_if:decision,rejected|string|max:2000']);
        $done = 0;
        $failed = [];
        foreach (array_chunk($data['ids'], 25) as $chunk) {
            foreach ($chunk as $id) {
                try {
                    $service->review((int) $id, $request->user(), $data['decision'], $data['reason'] ?? null);
                    $done++;
                } catch (ValidationException $e) {
                    $failed[] = '#'.$id.': '.implode(' ', $e->validator->errors()->all());
                } catch (QueryException $e) {
                    report($e);
                    $failed[] = '#'.$id.': The database could not apply this request. It remains pending; refresh and retry.';
                }
            }
        }

        return back()->with('success', "{$done} request(s) reviewed.")->with('review_errors', $failed);
    }

    public function accounts(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:255']);
        $users = User::when($request->filled('search'), fn ($q) => $q->where('email', 'like', '%'.$request->search.'%'))->orderBy('id')->paginate(25)->withQueryString();

        return view('admin.governance.accounts', compact('users'));
    }

    public function status(Request $request, User $user, AccountStatusService $statuses)
    {
        $data = $request->validate(['is_active' => 'required|boolean']);
        $statuses->setActive($user, $request->user(), (bool) $data['is_active']);

        return back()->with('success', 'Account status updated.');
    }
}