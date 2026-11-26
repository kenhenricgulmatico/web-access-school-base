<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Models\RequestType;
use App\Models\Resource;
use App\Models\User;
use App\Notifications\ProgramHeadRequestNotification;
use App\Notifications\RequestStatusNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Student / faculty request  ──"Forward to Admin"──▶  Program Head's Request to Admin
 *
 *   Admin APPROVES the forwarded request → the student's request is approved automatically
 *                                          (the items the Admin released go to the student).
 *   Admin REJECTS  the forwarded request → the student's request is rejected too.
 *
 * The link is requests.forwarded_from_request_id on the Program Head's request.
 */
class ForwardedRequests
{
    /** The Program Head's request that forwarded this student request (latest), if any. */
    public static function forwardOf(ResourceRequest $student): ?ResourceRequest
    {
        return ResourceRequest::where('forwarded_from_request_id', $student->id)->latest('id')->first();
    }

    /** Forwarded and the Admin has not decided yet. */
    public static function isWaitingForAdmin(ResourceRequest $student): bool
    {
        return $student->status === 'pending'
            && ResourceRequest::where('forwarded_from_request_id', $student->id)->where('status', 'pending')->exists();
    }

    // =================================================================
    // ✅ ONE CLICK: Program Head forwards a student / faculty request to the Admin
    // =================================================================
    /**
     * Creates the Program Head's Request to Admin with the same facility / items / return date,
     * linked to the student's request. Returns [ok (bool), message (string)].
     */
    public static function forward(ResourceRequest $student, User $programHead): array
    {
        $student->loadMissing(['user', 'items.resource']);

        if ($student->status !== 'pending') {
            return [false, 'This request was already approved or rejected.'];
        }

        if (static::isWaitingForAdmin($student)) {
            return [false, 'This request was already forwarded and is waiting for the Admin.'];
        }

        $facility  = $student->items->firstWhere('resource_id', null);
        $materials = $student->items->whereNotNull('resource_id');

        // Only items the Admin still has in stock can be forwarded
        $adminStock = Resource::whereIn('id', $materials->pluck('resource_id'))->pluck('quantity_available', 'id');
        $sendable   = $materials->filter(fn ($i) => (int) ($adminStock[$i->resource_id] ?? 0) > 0);
        $notAtAdmin = $materials->diff($sendable)->pluck('item_name')->unique();

        if (! $facility && $sendable->isEmpty()) {
            return [false, 'The Admin has none left of: ' . $notAtAdmin->implode(', ') . '. Please reject this request instead.'];
        }

        $studentName = $student->user->name ?? 'a faculty member';

        $forward = DB::transaction(function () use ($student, $programHead, $facility, $sendable, $studentName) {
            $forward = ResourceRequest::create([
                'user_id'                          => $programHead->id,
                'department_id'                    => $programHead->department_id ?? $student->department_id,
                'request_type_id'                  => $facility ? RequestType::FACILITY : RequestType::MATERIAL,
                // ✅ The faculty member's own purpose, as they wrote it (no "For <name>:" in front)
                'purpose'                          => \Illuminate\Support\Str::limit((string) $student->purpose, 500, ''),
                'status'                           => 'pending',
                'current_responsibility_center_id' => $programHead->responsibility_center_id ?? null,
            ]);
            $forward->forceFill(['forwarded_from_request_id' => $student->id])->save();

            if ($facility) {
                RequestItem::create([
                    'request_id'   => $forward->id,
                    'resource_id'  => null,
                    'item_name'    => $facility->item_name,
                    'quantity'     => 1,
                    'request_date' => $facility->request_date,
                    'start_time'   => $facility->start_time,
                    'end_time'     => $facility->end_time,
                ]);
            }

            foreach ($sendable as $item) {
                RequestItem::create([
                    'request_id'   => $forward->id,
                    'resource_id'  => $item->resource_id,
                    'item_name'    => $item->item_name,
                    'quantity'     => $item->quantity,
                    'request_date' => $facility ? $facility->request_date : max(today()->toDateString(), Carbon::parse($item->request_date)->toDateString()),
                    'start_time'   => $facility?->start_time,
                    'end_time'     => $facility?->end_time,
                    // Pcs items: same return date the student chose (the Admin can still change it)
                    'due_back_at'  => $item->resource?->is_returnable ? $item->due_back_at : null,
                ]);
            }

            return $forward;
        });

        // Tell the Admins (bell + e-mail) and the student
        $kind    = $facility ? 'Facility Reservation' : 'Material Request';
        $details = "Forwarded from {$studentName}'s request: " . static::details($forward->load('items'));

        foreach (User::role('admin')->get() as $admin) {
            Notification::create([
                'user_id'    => $admin->id,
                'request_id' => $forward->id,
                'message'    => "{$programHead->name} (Program Head) forwarded {$studentName}'s {$kind}.",
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);

            rescue(fn () => $admin->notify(new ProgramHeadRequestNotification($programHead->name, $kind, $details)));
        }

        Notification::create([
            'user_id'    => $student->user_id,
            'request_id' => $student->id,
            'message'    => "{$programHead->name} (Program Head) forwarded your request to the Admin. It will be approved automatically once the Admin approves it.",
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);

        $message = "Forwarded to the Admin. {$studentName}'s request will be approved automatically when the Admin approves it.";
        if ($notAtAdmin->isNotEmpty()) {
            $message .= ' Not sent (the Admin has none left): ' . $notAtAdmin->implode(', ') . '.';
        }

        return [true, $message];
    }

    // =================================================================
    // ADMIN APPROVED → approve the student's request automatically
    // =================================================================
    /** Returns a short note for the Admin's success message (or null when not forwarded). */
    public static function onAdminApproved(ResourceRequest $forward): ?string
    {
        if (! $forward->forwarded_from_request_id) {
            return null;
        }

        $student = ResourceRequest::with(['user', 'items.resource'])->find($forward->forwarded_from_request_id);

        if (! $student || $student->status !== 'pending') {
            return null;
        }

        $programHead = User::find($forward->user_id);

        // A facility booking must still be free (the Program Head's own forwarded booking does not count)
        $facility = $student->items->firstWhere('resource_id', null);

        if ($facility && $facility->start_time && DB::table('request_items as ri')
            ->join('requests as req', 'req.id', '=', 'ri.request_id')
            ->where('req.status', 'approved')
            ->whereNotIn('req.id', [$student->id, $forward->id])
            ->whereNull('ri.resource_id')
            ->where('ri.item_name', $facility->item_name)
            ->whereDate('ri.request_date', $facility->request_date)
            ->where('ri.start_time', '<', $facility->end_time)
            ->where('ri.end_time', '>', $facility->start_time)
            ->exists()) {
            static::tellProgramHead($forward, "{$facility->item_name} is already booked at that time, so {$student->user->name}'s request was NOT approved automatically. Please check it.");
            return "{$student->user->name}'s request could not be approved automatically (the facility is already booked).";
        }

        $materials = $student->items->whereNotNull('resource_id');

        try {
            DB::transaction(function () use ($student, $materials, $programHead, $forward) {
                // The Admin just added these items to the department — hand them to the student
                foreach ($materials as $item) {
                    $allocation = DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $student->department_id)
                        ->lockForUpdate()
                        ->first();

                    if (! $allocation || $allocation->allocated_quantity < $item->quantity) {
                        throw new \RuntimeException("Not enough \"{$item->item_name}\" in the department");
                    }
                }

                foreach ($materials as $item) {
                    DB::table('resource_all_locations')
                        ->where('resource_id', $item->resource_id)
                        ->where('department_id', $student->department_id)
                        ->decrement('allocated_quantity', $item->quantity);
                }

                $student->update(['status' => 'approved']);

                // Borrowed items keep the return date the student chose
                ReturnTracking::assignDueDates($student);

                DB::table('request_approvals')->updateOrInsert(
                    ['request_id' => $student->id, 'approver_id' => $programHead?->id ?? $forward->user_id],
                    [
                        'status'      => 'approved',
                        'remarks'     => "Approved automatically when the Admin approved the forwarded request",
                        'approved_at' => now(),
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ]
                );
            });
        } catch (\RuntimeException $e) {
            static::tellProgramHead($forward, "{$student->user->name}'s request was NOT approved automatically: {$e->getMessage()}. Please check it.");
            return "{$student->user->name}'s request could not be approved automatically ({$e->getMessage()}).";
        }

        $details = static::details($student);
        $by      = ($programHead?->name ?? 'Your Program Head') . ' (Program Head)';

        Notification::create([
            'user_id'    => $student->user_id,
            'request_id' => $student->id,
            'message'    => "Your request was approved: {$details}.",
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);

        rescue(fn () => $student->user?->notify(new RequestStatusNotification(
            status:       'approved',
            approverName: $by,
            requestKind:  $facility ? 'Facility Reservation' : 'Material Request',
            details:      $details,
            requestId:    $student->id,
        )));

        static::tellProgramHead($forward, "The Admin approved your request, so {$student->user->name}'s request was approved automatically.");

        return "{$student->user->name}'s request was approved automatically.";
    }

    // =================================================================
    // ADMIN REJECTED → reject the student's request too
    // =================================================================
    // =================================================================
    // ✅ THE FACULTY MEMBER CANCELLED their own pending request
    // =================================================================
    /**
     * Cancels the request, withdraws the Program Head's endorsed copy if the Admin hasn't
     * decided yet (so the Admin can't approve a booking nobody needs), and tells the
     * Program Head(s) — and the Admins when an endorsed copy was withdrawn.
     * Returns a short note for the faculty member's message.
     */
    public static function onStudentCancelled(ResourceRequest $student, User $by): ?string
    {
        $kind = (int) $student->request_type_id === RequestType::FACILITY ? 'facility reservation' : 'material request';
        $what = static::details($student->loadMissing('items'));

        $withdrawn = DB::transaction(function () use ($student) {
            $student->update(['status' => 'cancelled']);

            $forward = ResourceRequest::where('forwarded_from_request_id', $student->id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            $forward?->update(['status' => 'cancelled']);

            return $forward;
        });

        // The Program Head(s) of the department
        foreach (User::role('program head')->where('department_id', $student->department_id)->get() as $programHead) {
            Notification::create([
                'user_id'    => $programHead->id,
                'request_id' => $student->id,
                'message'    => "{$by->name} cancelled their {$kind}: {$what}."
                              . ($withdrawn ? ' Your endorsed request to the Admin was withdrawn too.' : ''),
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }

        if (! $withdrawn) {
            return null;
        }

        // The Admins: the endorsed copy they were about to review is gone
        foreach (User::role('admin')->get() as $admin) {
            Notification::create([
                'user_id'    => $admin->id,
                'request_id' => $withdrawn->id,
                'message'    => "{$by->name} cancelled their {$kind}, so the Program Head's endorsed request was withdrawn — no need to review it.",
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }

        return 'It had been endorsed to the Admin — that request was withdrawn too.';
    }

    public static function onAdminRejected(ResourceRequest $forward, ?string $reason, User $admin): ?string
    {
        if (! $forward->forwarded_from_request_id) {
            return null;
        }

        $student = ResourceRequest::with(['user', 'items'])->find($forward->forwarded_from_request_id);

        if (! $student || $student->status !== 'pending') {
            return null;
        }

        $student->update(['status' => 'rejected']);

        DB::table('request_approvals')->updateOrInsert(
            ['request_id' => $student->id, 'approver_id' => $admin->id],
            [
                'status'      => 'rejected',
                'remarks'     => $reason ?: 'The Admin could not release the items.',
                'approved_at' => now(),
                'updated_at'  => now(),
                'created_at'  => now(),
            ]
        );

        $details = static::details($student);
        $message = "Your request was rejected because the Admin could not release the items: {$details}.";
        if ($reason) {
            $message .= ' Reason: ' . $reason;
        }

        Notification::create([
            'user_id'    => $student->user_id,
            'request_id' => $student->id,
            'message'    => $message,
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);

        rescue(fn () => $student->user?->notify(new RequestStatusNotification(
            status:       'rejected',
            approverName: "{$admin->name} (Admin)",
            requestKind:  $student->items->firstWhere('resource_id', null) ? 'Facility Reservation' : 'Material Request',
            details:      $details,
            reason:       $reason,
            requestId:    $student->id,
        )));

        return "{$student->user->name}'s request was rejected too.";
    }

    // =================================================================
    protected static function details(ResourceRequest $request): string
    {
        return $request->items
            ->map(fn ($i) => $i->resource_id
                ? "{$i->item_name} x{$i->quantity}"
                : "{$i->item_name} on " . Carbon::parse($i->request_date)->format('M d, Y')
                    . ($i->start_time ? ' (' . Carbon::parse($i->start_time)->format('g:i A') . ' - ' . Carbon::parse($i->end_time)->format('g:i A') . ')' : ''))
            ->implode(', ') ?: (string) $request->purpose;
    }

    protected static function tellProgramHead(ResourceRequest $forward, string $message): void
    {
        Notification::create([
            'user_id'    => $forward->user_id,
            'request_id' => $forward->forwarded_from_request_id,
            'message'    => $message,
            'type'       => 'Gmail',
            'status'     => 'pending',
        ]);
    }
}
