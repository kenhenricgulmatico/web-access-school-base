<?php

use App\Models\Request as ResourceRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component
{
    #[Url]
    public $request_id;

    public ?ResourceRequest $printRequest = null;
    public ?object $approval = null;
    public bool $notFound = false;

    public function mount()
    {
        if (!$this->request_id) {
            $this->notFound = true;
            return;
        }

        $request = ResourceRequest::find($this->request_id);

        if (!$request || $request->status !== 'approved') {
            $this->notFound = true;
            return;
        }

        // ✅ Who may print this receipt:
        //   - the person who made the request
        //   - an admin
        //   - the program head of the request's department
        // Students/faculty can NOT open other people's receipts.
        $user = Auth::user();
        abort_unless($user, 403);

        $isOwner        = (int) $request->user_id === (int) $user->id;
        $isAdmin        = $user->hasRole('admin');
        $isDeptHead     = $user->hasRole('program head')
                          && (int) $user->department_id === (int) $request->department_id;

        if (! $isOwner && ! $isAdmin && ! $isDeptHead) {
            abort(403);
        }

        $request->load(['user.department', 'items.resource', 'requestType']);

        $this->approval = DB::table('request_approvals')
            ->join('users', 'users.id', '=', 'request_approvals.approver_id')
            ->where('request_approvals.request_id', $request->id)
            ->where('request_approvals.status', 'approved')
            ->select('users.name as approver_name', 'request_approvals.approved_at')
            ->first();

        $this->printRequest = $request;
    }
};
