<div>
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
        @php
            $me    = auth()->user();
            $local = now();  // ✅ the app runs on Philippine time (config/app.php)
            $greet = $local->hour < 12 ? 'Good morning' : ($local->hour < 18 ? 'Good afternoon' : 'Good evening');
            $first = \Illuminate\Support\Str::of($me->name)->trim()->explode(' ')->first();
        @endphp

        {{-- ✅ Welcome + the two things faculty do most --}}
        <div class="mb-5 sm:mb-6 rounded-2xl bg-[#E8F3EC] dark:bg-[#1C6B45]/20 ring-1 ring-inset ring-[#1C6B45]/10 dark:ring-[#7FBF8E]/15 px-4 py-4 sm:px-6 sm:py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">{{ $greet }}, {{ ucfirst($first) }}</h1>
                <p class="mt-1 text-sm text-[#1C6B45]/90 dark:text-[#9FD3AE]">
                    {{ $local->format('l, F j, Y') }}@if($me->department) · {{ $me->department->department_name }}@endif
                </p>
            </div>
            <div class="flex flex-wrap gap-2 shrink-0">
                @can('facility-requests.create')
                    <a href="{{ route('portal.create-reservation') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-semibold bg-[#123524] text-white hover:bg-[#0B2418] dark:bg-[#1C6B45] dark:hover:bg-[#23855A] transition">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                        New reservation
                    </a>
                @endcan
                @can('material-requests.create')
                    <a href="{{ route('portal.create-material') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-semibold bg-white text-[#123524] ring-1 ring-inset ring-[#1C6B45]/25 hover:bg-[#F3F8F5] dark:bg-neutral-800 dark:text-white dark:ring-neutral-600 dark:hover:bg-neutral-700 transition">
                        Request materials
                    </a>
                @endcan
            </div>
        </div>

        {{-- Stats — one row of three, even on a phone --}}
        <div class="grid grid-cols-3 gap-2 sm:gap-5 mb-5 sm:mb-8">

            {{-- Approved Facility Reservations --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-gray-200 dark:border-[#2A4B3A] p-3 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate"><span class="sm:hidden">Rooms</span><span class="hidden sm:inline">Approved Facility</span></p>
                        <p class="text-xl sm:text-2xl font-bold text-[#123524] dark:text-white mt-0.5 sm:mt-1">{{ $this->totalFacilityReservations }}</p>
                    </div>
                    <div class="hidden sm:block p-3 bg-[#123524] dark:bg-[#1C6B45] rounded-full shrink-0">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m4.5 0v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                </div>
                <div class="mt-1 sm:mt-3 flex flex-wrap gap-x-3 gap-y-1 text-[10px] sm:text-xs">
                    <span class="hidden sm:inline text-[#1C6B45] dark:text-[#7FBF8E]">{{ $this->approvedFacilityReservations }} approved</span>
                    <span class="{{ $this->pendingFacilityReservations ? 'text-amber-700 dark:text-amber-300' : 'text-gray-400' }}">{{ $this->pendingFacilityReservations }} pending</span>
                </div>
            </div>

            {{-- Approved Material Requests --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-gray-200 dark:border-[#2A4B3A] p-3 sm:p-5">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate"><span class="sm:hidden">Materials</span><span class="hidden sm:inline">Approved Material</span></p>
                        <p class="text-xl sm:text-2xl font-bold text-[#123524] dark:text-white mt-0.5 sm:mt-1">{{ $this->totalMaterialRequests }}</p>
                    </div>
                    <div class="hidden sm:block p-3 bg-[#E8F3EC] dark:bg-[#1C6B45]/25 rounded-full shrink-0">
                        <svg class="w-6 h-6 text-[#1C6B45] dark:text-[#9FD3AE]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-1 sm:mt-3 flex flex-wrap gap-x-3 gap-y-1 text-[10px] sm:text-xs">
                    <span class="hidden sm:inline text-[#1C6B45] dark:text-[#7FBF8E]">{{ $this->approvedMaterialRequests }} approved</span>
                    <span class="{{ $this->pendingMaterialRequests ? 'text-amber-700 dark:text-amber-300' : 'text-gray-400' }}">{{ $this->pendingMaterialRequests }} pending</span>
                </div>
            </div>

            {{-- Waiting for approval --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border p-3 sm:p-5 {{ $this->totalPendingRequests ? 'border-amber-200 dark:border-amber-500/30' : 'border-gray-200 dark:border-[#2A4B3A]' }}">
                <div class="flex items-start justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate"><span class="sm:hidden">Waiting</span><span class="hidden sm:inline">Waiting for approval</span></p>
                        <p class="text-xl sm:text-2xl font-bold mt-0.5 sm:mt-1 {{ $this->totalPendingRequests ? 'text-amber-700 dark:text-amber-300' : 'text-[#123524] dark:text-white' }}">{{ $this->totalPendingRequests }}</p>
                    </div>
                    <div class="hidden sm:block p-3 bg-amber-50 dark:bg-amber-500/15 rounded-full shrink-0">
                        <svg class="w-6 h-6 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="mt-1 sm:mt-3 text-[10px] sm:text-xs text-gray-400 dark:text-gray-400"><span class="sm:hidden">to approve</span><span class="hidden sm:inline">Waiting for your Program Head</span></p>
            </div>
        </div>

        {{-- ✅ Items to return + Upcoming reservations --}}
        @php
            $toReturn = $this->itemsToReturn;
            $upcoming = $this->upcomingReservations;
        @endphp
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-5 mb-6 sm:mb-8">

            {{-- Items to return --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border overflow-hidden
                        {{ $toReturn['overdue'] ? 'border-[#B8352A]/50 ring-1 ring-[#B8352A]/30' : 'border-gray-200 dark:border-[#2A4B3A]' }}">
                <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-gray-200 dark:border-[#2A4B3A]">
                    <div>
                        <h3 class="text-base font-semibold text-[#123524] dark:text-white">Items to return</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            @if ($toReturn['overdue'])
                                <span class="font-semibold text-[#B8352A]">{{ $toReturn['overdue'] }} overdue</span> ·
                            @endif
                            {{ $toReturn['total'] }} borrowed
                        </p>
                    </div>
                    <a href="{{ route('portal.borrowed-items') }}" wire:navigate class="text-xs font-semibold text-[#1C6B45] hover:underline whitespace-nowrap">View all →</a>
                </div>

                <ul class="divide-y divide-gray-100 dark:divide-[#2A4B3A]">
                    @forelse ($toReturn['items'] as $item)
                        <li class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#1F2A24] dark:text-white truncate">{{ $item['name'] }} <span class="text-gray-400 font-normal">×{{ $item['qty'] }}</span></p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Return by {{ $item['due'] }}</p>
                            </div>
                            <x-portal.status class="shrink-0" :status="$item['state']">{{ $item['label'] }}</x-portal.status>
                        </li>
                    @empty
                        <li class="px-4 sm:px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">Nothing to return — you're all set.</li>
                    @endforelse
                </ul>
            </div>

            {{-- Upcoming reservations --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-gray-200 dark:border-[#2A4B3A] overflow-hidden">
                <div class="flex items-center justify-between gap-3 px-4 sm:px-5 py-3.5 border-b border-gray-200 dark:border-[#2A4B3A]">
                    <div>
                        <h3 class="text-base font-semibold text-[#123524] dark:text-white">Upcoming reservations</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Your next approved room bookings</p>
                    </div>
                    <a href="{{ route('portal.calendar') }}" wire:navigate class="text-xs font-semibold text-[#1C6B45] hover:underline whitespace-nowrap">Calendar →</a>
                </div>

                <ul class="divide-y divide-gray-100 dark:divide-[#2A4B3A]">
                    @forelse ($upcoming as $r)
                        <li class="flex items-center gap-3 px-4 sm:px-5 py-3">
                            <span class="shrink-0 w-20 text-center px-2 py-1 rounded-lg text-[11px] font-semibold
                                {{ $r['day'] === 'Today' ? 'bg-[#123524] text-white' : 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]' }}">
                                {{ $r['day'] }}
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#1F2A24] dark:text-white truncate">{{ $r['room'] }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $r['time'] }}</p>
                            </div>
                        </li>
                    @empty
                        <li class="px-4 sm:px-5 py-8 text-center text-sm text-gray-400 dark:text-gray-500">
                            No upcoming reservations.
                            <a href="{{ route('portal.create-reservation') }}" wire:navigate class="text-[#1C6B45] font-semibold hover:underline">Book a room</a>
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        @php
            // Combine monthlyStats (Facility) and monthlyMaterialStats (Material) by month label
            $materialStats = collect($this->monthlyMaterialStats)->keyBy('month');

            $analyticsMonths = collect($this->monthlyStats)->map(function ($item) use ($materialStats) {
                $monthKey = $item['month'];
                return [
                    'month'    => $monthKey,
                    'facility' => $item['count'],
                    'material' => $materialStats->get($monthKey)['count'] ?? 0,
                    'total'    => $item['count'] + ($materialStats->get($monthKey)['count'] ?? 0),
                ];
            });

            $grandTotal    = $analyticsMonths->sum('total');
            $totalFacility = $analyticsMonths->sum('facility');
            $totalMaterial = $analyticsMonths->sum('material');

            $chartLabels   = $analyticsMonths->pluck('month')->values()->all();
            $chartFacility = $analyticsMonths->pluck('facility')->values()->all();
            $chartMaterial = $analyticsMonths->pluck('material')->values()->all();
        @endphp

        {{-- Analytics Chart (Facility vs Material) --}}
        <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-gray-200 dark:border-[#2A4B3A] p-4 sm:p-6 mb-6 sm:mb-8">
            <h3 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-white mb-1">Analytics</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Approved requests for the last 6 months — facility vs material</p>

            {{-- Summary numbers --}}
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="text-center p-2 bg-gray-50 dark:bg-[#0E1A14] rounded-xl">
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total</p>
                    <p class="text-sm sm:text-xl font-bold text-[#123524] dark:text-white">{{ $grandTotal }}</p>
                </div>
                <div class="text-center p-2 bg-[#123524]/[0.06] dark:bg-[#123524]/30 rounded-xl">
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Facility</p>
                    <p class="text-sm sm:text-xl font-bold text-[#123524] dark:text-[#9FD3AE]">{{ $totalFacility }}</p>
                </div>
                <div class="text-center p-2 bg-[#E8F3EC] dark:bg-[#1C6B45]/20 rounded-xl">
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Material</p>
                    <p class="text-sm sm:text-xl font-bold text-[#2F8A5B] dark:text-[#7FBF8E]">{{ $totalMaterial }}</p>
                </div>
            </div>

            <div class="relative h-48 sm:h-56 w-full">
                <canvas id="analyticsChart"></canvas>
            </div>

            <div class="flex items-center justify-center gap-4 mt-4 text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#123524] dark:bg-[#9FD3AE]"></span>
                    Facility
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#7FBF8E]"></span>
                    Material
                </span>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="@container bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-gray-200 dark:border-[#2A4B3A] overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-[#2A4B3A]">
                <h3 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-white">Recent Activities</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Your latest facility reservations and material requests</p>
            </div>

            {{-- Mobile: stacked cards --}}
            <div class="@3xl:hidden divide-y divide-gray-200 dark:divide-[#2A4B3A]">
                @forelse($this->recentActivities as $activity)
                    @php $status = $activity['status']; @endphp
                    <div class="px-4 py-3.5">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                {{ $activity['type'] === 'Facility' ? 'bg-[#123524]/10 text-[#123524] dark:bg-[#123524]/40 dark:text-[#9FD3AE]' : 'bg-[#E8F3EC] text-[#2F8A5B] dark:bg-[#1C6B45]/25 dark:text-[#7FBF8E]' }}">
                                {{ $activity['type'] }}
                            </span>
                            <x-portal.status class="shrink-0" :status="$status" />
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $activity['name'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $activity['details'] }}</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">{{ $activity['time_ago'] }}</p>
                    </div>
                @empty
                    <x-portal.empty message="No activities yet" hint="Start by booking a room or requesting materials." />
                @endforelse
            </div>

            {{-- Desktop: table --}}
            <div class="hidden @3xl:block">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-[#2A4B3A]">
                    <thead class="bg-[#F3F8F5] dark:bg-[#0E1A14]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E] whitespace-nowrap">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E] whitespace-nowrap">Name / Item</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E] whitespace-nowrap">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E] whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E] whitespace-nowrap">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-[#2A4B3A]">
                        @forelse($this->recentActivities as $activity)
                            <tr class="hover:bg-[#F3F8F5]/70 dark:hover:bg-[#0E1A14]/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                        {{ $activity['type'] === 'Facility' ? 'bg-[#123524]/10 text-[#123524] dark:bg-[#123524]/40 dark:text-[#9FD3AE]' : 'bg-[#E8F3EC] text-[#2F8A5B] dark:bg-[#1C6B45]/25 dark:text-[#7FBF8E]' }}">
                                        {{ $activity['type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $activity['name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $activity['details'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $status = $activity['status']; @endphp
                                    <x-portal.status :status="$status" />
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $activity['time_ago'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-portal.empty message="No activities yet" hint="Start by booking a room or requesting materials." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    (function () {
        const labels       = @json($chartLabels);
        const facilityData = @json($chartFacility);
        const materialData = @json($chartMaterial);

        const canvas = document.getElementById('analyticsChart');
        if (!canvas) return;

        // Destroy any previous instance on this canvas so re-renders
        // (Livewire polling, wire:click, etc.) don't stack duplicate charts.
        const existing = Chart.getChart(canvas);
        if (existing) existing.destroy();

        const isDark = document.documentElement.classList.contains('dark');
        const gridColor = isDark ? 'rgba(255,255,255,0.08)' : 'rgba(0,0,0,0.06)';
        const textColor = isDark ? '#9CA3AF' : '#6B7280';

        new Chart(canvas.getContext('2d'), {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Facility',
                        data: facilityData,
                        borderColor: isDark ? '#9FD3AE' : '#123524',
                        backgroundColor: isDark ? 'rgba(159, 211, 174, 0.08)' : 'rgba(18, 53, 36, 0.06)',
                        borderWidth: 2.5,
                        pointBackgroundColor: isDark ? '#9FD3AE' : '#123524',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    },
                    {
                        label: 'Material',
                        data: materialData,
                        borderColor: '#7FBF8E',
                        backgroundColor: 'rgba(127, 191, 142, 0.14)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#7FBF8E',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 1.5,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: isDark ? '#16281F' : '#fff',
                        titleColor: isDark ? '#fff' : '#123524',
                        bodyColor: isDark ? '#D1D5DB' : '#4B5563',
                        borderColor: isDark ? '#2A4B3A' : '#DDE9E1',
                        borderWidth: 1,
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: true,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: textColor, font: { size: 10 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor, drawBorder: false },
                        ticks: { color: textColor, font: { size: 10 }, stepSize: 1, precision: 0 }
                    }
                }
            }
        });
    })();
</script>
