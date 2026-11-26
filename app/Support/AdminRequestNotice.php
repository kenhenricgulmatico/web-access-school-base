<?php

namespace App\Support;

use App\Models\Notification;
use App\Models\Request as ResourceRequest;
use App\Models\User;

/**
 * Bell notifications to the Admins about a Program Head's request,
 * always linked to the request so the Admin's bell opens the right page.
 */
class AdminRequestNotice
{
    /** "<name> (Program Head) updated their request" — after editing a pending request. */
    public static function updated(ResourceRequest $request, User $programHead): void
    {
        $kind = (int) $request->request_type_id === \App\Models\RequestType::FACILITY ? 'Facility Reservation' : 'Material Request';

        foreach (User::role('admin')->get() as $admin) {
            Notification::create([
                'user_id'    => $admin->id,
                'request_id' => $request->id,
                'message'    => "{$programHead->name} (Program Head) updated their {$kind} — please review the changes.",
                'type'       => 'Gmail',
                'status'     => 'pending',
            ]);
        }
    }

    /**
     * Where the Admin handles this request:
     *  - a Program Head's own borrowing of Pcs items (chairs…) → Borrowed Items
     *  - everything else → that request's View page (Approve / Reject there)
     */
    public static function adminUrl(int $requestId): ?string
    {
        $request = ResourceRequest::with('items.resource')->find($requestId);

        if (! $request) {
            return null;
        }

        $isPcsBorrowing = ! $request->forwarded_from_request_id
            && $request->items->contains(fn ($i) => $i->resource_id && $i->resource?->is_returnable);

        return $isPcsBorrowing
            ? route('admin.borrowed-items')
            : route('admin.view-request', $request->id);
    }
}
