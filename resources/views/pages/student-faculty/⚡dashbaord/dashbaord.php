<?php

use App\Models\RequestType;
use App\Models\Request as ResourceRequest;
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
};
