<?php

use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
use App\Models\RequestItem;
use App\Support\ReturnTracking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

new #[Layout('layouts.student-faculty')] class extends Component
{
    // ─────────────────────────────────────────────────────────────
    // FACILITY RESERVATIONS
    // ─────────────────────────────────────────────────────────────

    /**
     * Big number on the "Facility Reservations" card.
     * Only counts APPROVED — pending and rejected do not increase it.
     */
    #[Computed]
    public function totalFacilityReservations(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::FACILITY)
            ->where('status', 'approved')
            ->count();
    }

    #[Computed]
    public function approvedFacilityReservations(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::FACILITY)
            ->where('status', 'approved')
            ->count();
    }

    #[Computed]
    public function pendingFacilityReservations(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::FACILITY)
            ->where('status', 'pending')
            ->count();
    }

    // ─────────────────────────────────────────────────────────────
    // MATERIAL REQUESTS
    // ─────────────────────────────────────────────────────────────

    /**
     * Big number on the "Material Requests" card.
     * Only counts APPROVED — pending and rejected do not increase it.
     */
    #[Computed]
    public function totalMaterialRequests(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::MATERIAL)
            ->where('status', 'approved')
            ->count();
    }

    #[Computed]
    public function approvedMaterialRequests(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::MATERIAL)
            ->where('status', 'approved')
            ->count();
    }

    #[Computed]
    public function pendingMaterialRequests(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->where('request_type_id', RequestType::MATERIAL)
            ->where('status', 'pending')
            ->count();
    }

    // ─────────────────────────────────────────────────────────────
    // ACTIVE (PENDING) — combined
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function totalPendingRequests(): int
    {
        return ResourceRequest::where('user_id', Auth::id())
            ->whereIn('request_type_id', [RequestType::FACILITY, RequestType::MATERIAL])
            ->where('status', 'pending')
            ->count();
    }

    // ─────────────────────────────────────────────────────────────
    // RECENT ACTIVITIES
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function recentActivities()
    {
        return ResourceRequest::with('items')
            ->where('user_id', Auth::id())
            ->whereIn('request_type_id', [RequestType::FACILITY, RequestType::MATERIAL])
            ->latest()
            ->take(7)
            ->get()
            ->map(function ($req) {
                $isFacility = (int) $req->request_type_id === RequestType::FACILITY;
                $firstItem = $req->items->first();

                $details = $isFacility
                    ? ($firstItem
                        ? Carbon::parse($firstItem->request_date)->format('M d, Y')
                            . ($firstItem->start_time
                                ? ' · ' . Carbon::parse($firstItem->start_time)->format('h:i A')
                                : '')
                        : $req->purpose)
                    : ($req->items->count() > 1
                        ? $req->items->count() . ' items'
                        : 'Qty: ' . ($firstItem->quantity ?? '—'));

                return [
                    'type'     => $isFacility ? 'Facility' : 'Material',
                    'name'     => $firstItem->item_name ?? $req->purpose,
                    'status'   => $req->status,
                    'details'  => $details,
                    'time_ago' => $req->created_at->diffForHumans(),
                ];
            });
    }

    // ─────────────────────────────────────────────────────────────
    // MONTHLY CHART DATA — last 6 months, only approved
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function monthlyStats()
    {
        $months = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $count = ResourceRequest::where('user_id', Auth::id())
                ->where('request_type_id', RequestType::FACILITY)
                ->where('status', 'approved')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $months->push([
                'month' => $month->format('M Y'),
                'count' => $count,
            ]);
        }

        return $months;
    }

    #[Computed]
    public function monthlyMaterialStats()
    {
        $months = collect();

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);

            $count = ResourceRequest::where('user_id', Auth::id())
                ->where('request_type_id', RequestType::MATERIAL)
                ->where('status', 'approved')
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->count();

            $months->push([
                'month' => $month->format('M Y'),
                'count' => $count,
            ]);
        }

        return $months;
    }

    // ─────────────────────────────────────────────────────────────
    // ✅ ITEMS TO RETURN — same rules as the Borrowed Items page
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function itemsToReturn(): array
    {
        $query = RequestItem::whereNotNull('due_back_at')
            ->whereNull('returned_at')
            ->whereHas('request', fn ($q) => $q
                ->where('user_id', Auth::id())
                ->where('status', 'approved'));

        $items = (clone $query)->orderBy('due_back_at')->take(4)->get()
            ->map(function (RequestItem $item) {
                $days = (int) today()->diffInDays($item->due_back_at, false);

                $state = match (true) {
                    (bool) $item->return_requested_at => 'waiting',  // already handed back, PH to confirm
                    $days < 0                         => 'overdue',
                    $days <= 2                        => 'soon',
                    default                           => 'ok',
                };

                return [
                    'name'  => $item->item_name,
                    'qty'   => $item->quantity,
                    'due'   => Carbon::parse($item->due_back_at)->format('M d, Y'),
                    'label' => $state === 'waiting' ? 'Waiting for Program Head to confirm' : ReturnTracking::label($item),
                    'state' => $state,
                ];
            })
            ->all();

        return [
            'total'   => (clone $query)->count(),
            'overdue' => (clone $query)->whereNull('return_requested_at')->whereDate('due_back_at', '<', today())->count(),
            'items'   => $items,
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // ✅ UPCOMING RESERVATIONS — my next approved room bookings
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function upcomingReservations(): array
    {
        $now = now();

        return RequestItem::whereNull('resource_id')
            ->whereHas('request', fn ($q) => $q
                ->where('user_id', Auth::id())
                ->where('status', 'approved')
                ->where('request_type_id', RequestType::FACILITY))
            // later days, or today's bookings that haven't ended yet
            ->where(fn ($q) => $q
                ->whereDate('request_date', '>', $now->toDateString())
                ->orWhere(fn ($t) => $t
                    ->whereDate('request_date', $now->toDateString())
                    ->where(fn ($e) => $e->whereNull('end_time')->orWhere('end_time', '>=', $now->format('H:i:s')))))
            ->orderBy('request_date')
            ->orderBy('start_time')
            ->take(4)
            ->get()
            ->map(function (RequestItem $item) {
                $date = Carbon::parse($item->request_date);

                return [
                    'room' => $item->item_name,
                    'day'  => $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : $date->format('D, M d')),
                    'time' => $item->start_time && $item->end_time
                        ? Carbon::parse($item->start_time)->format('g:i A') . ' – ' . Carbon::parse($item->end_time)->format('g:i A')
                        : 'No time set',
                    'request_id' => $item->request_id,
                ];
            })
            ->all();
    }
};
