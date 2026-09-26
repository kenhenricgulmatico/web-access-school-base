<div class="select-none">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 sm:gap-5 mb-6 sm:mb-8">

            {{-- Total Facility Reservations --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] p-3 sm:p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate">Facility Reservations</p>
                        <p class="text-lg sm:text-2xl font-bold text-[#123524] dark:text-white mt-1">{{ $this->totalFacilityReservations }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-[#123524]/8 dark:bg-[#123524]/25 rounded-full shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m4.5 0v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-x-2 gap-y-1 text-[10px] sm:text-xs">
                    <span class="flex items-center gap-1 text-[#1C6B45] dark:text-[#7FBF8E]">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        {{ $this->approvedFacilityReservations }} approved
                    </span>
                    <span class="flex items-center gap-1 text-[#B8862A]">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{ $this->pendingFacilityReservations }} pending
                    </span>
                </div>
            </div>

            {{-- Total Material Requests --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] p-3 sm:p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate">Material Requests</p>
                        <p class="text-lg sm:text-2xl font-bold text-[#123524] dark:text-white mt-1">{{ $this->totalMaterialRequests }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-[#D4A537]/12 dark:bg-[#D4A537]/20 rounded-full shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-[#B8862A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 flex flex-wrap gap-x-2 gap-y-1 text-[10px] sm:text-xs">
                    <span class="flex items-center gap-1 text-[#1C6B45] dark:text-[#7FBF8E]">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        {{ $this->approvedMaterialRequests }} approved
                    </span>
                    <span class="flex items-center gap-1 text-[#B8862A]">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                        {{ $this->pendingMaterialRequests }} pending
                    </span>
                </div>
            </div>

            {{-- Active Pending --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] p-3 sm:p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate">Active (Pending)</p>
                        <p class="text-lg sm:text-2xl font-bold text-[#123524] dark:text-white mt-1">{{ $this->totalPendingRequests }}</p>
                    </div>
                    <div class="p-2 sm:p-3 bg-[#B8352A]/8 dark:bg-[#B8352A]/20 rounded-full shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-[#B8352A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 mt-2">Awaiting coordinator approval</p>
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
        <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] p-4 sm:p-6 mb-6 sm:mb-8">
            <h3 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-white mb-1" style="font-family: 'Fraunces', serif;">Analytics</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Request volume for the last 6 months — facility vs material</p>

            {{-- Summary numbers --}}
            <div class="grid grid-cols-3 gap-3 mb-6">
                <div class="text-center p-2 bg-[#FAF7EF] dark:bg-[#0E1A14] rounded-xl">
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total</p>
                    <p class="text-sm sm:text-xl font-bold text-[#123524] dark:text-white">{{ $grandTotal }}</p>
                </div>
                <div class="text-center p-2 bg-[#123524]/5 dark:bg-[#123524]/20 rounded-xl">
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Facility</p>
                    <p class="text-sm sm:text-xl font-bold text-[#1C6B45] dark:text-[#7FBF8E]">{{ $totalFacility }}</p>
                </div>
                <div class="text-center p-2 bg-[#D4A537]/8 dark:bg-[#D4A537]/15 rounded-xl">
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">Material</p>
                    <p class="text-sm sm:text-xl font-bold text-[#B8862A]">{{ $totalMaterial }}</p>
                </div>
            </div>

            <div class="relative h-48 sm:h-56 w-full">
                <canvas id="analyticsChart"></canvas>
            </div>

            <div class="flex items-center justify-center gap-4 mt-4 text-[10px] sm:text-xs text-gray-500 dark:text-gray-400">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#1C6B45]"></span>
                    Facility
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded-full bg-[#D4A537]"></span>
                    Material
                </span>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] overflow-hidden">
            <div class="px-4 sm:px-6 py-4 border-b border-[#E4E1D8] dark:border-[#2A4B3A]">
                <h3 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-white" style="font-family: 'Fraunces', serif;">Recent Activities</h3>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Your latest facility reservations and material requests</p>
            </div>

            {{-- Mobile: stacked cards --}}
            <div class="md:hidden divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">
                @forelse($this->recentActivities as $activity)
                    @php $status = $activity['status']; @endphp
                    <div class="px-4 py-3.5">
                        <div class="flex items-center justify-between gap-2 mb-1.5">
                            <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                {{ $activity['type'] === 'Facility' ? 'bg-[#123524]/8 text-[#1C6B45] dark:bg-[#123524]/25 dark:text-[#7FBF8E]' : 'bg-[#D4A537]/12 text-[#B8862A]' }}">
                                {{ $activity['type'] }}
                            </span>
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full shrink-0
                                {{ $status === 'approved' ? 'bg-[#1C6B45]/10 text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#7FBF8E]' : '' }}
                                {{ $status === 'pending' ? 'bg-[#D4A537]/15 text-[#B8862A]' : '' }}
                                {{ $status === 'rejected' ? 'bg-[#B8352A]/10 text-[#B8352A]' : '' }}">
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                        <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $activity['name'] }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ $activity['details'] }}</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 mt-1">{{ $activity['time_ago'] }}</p>
                    </div>
                @empty
                    <div class="px-4 py-8 text-center text-xs text-gray-400 dark:text-gray-500">No activities yet. Start by creating a reservation or request.</div>
                @endforelse
            </div>

            {{-- Desktop: table --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="min-w-full divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">
                    <thead class="bg-[#FAF7EF] dark:bg-[#0E1A14]">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A] whitespace-nowrap">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A] whitespace-nowrap">Name / Item</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A] whitespace-nowrap">Details</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A] whitespace-nowrap">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A] whitespace-nowrap">When</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">
                        @forelse($this->recentActivities as $activity)
                            <tr class="hover:bg-[#FAF7EF] dark:hover:bg-[#0E1A14]/50 transition">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex px-2.5 py-1 text-xs font-medium rounded-full
                                        {{ $activity['type'] === 'Facility' ? 'bg-[#123524]/8 text-[#1C6B45] dark:bg-[#123524]/25 dark:text-[#7FBF8E]' : 'bg-[#D4A537]/12 text-[#B8862A]' }}">
                                        {{ $activity['type'] }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $activity['name'] }}</td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $activity['details'] }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $status = $activity['status']; @endphp
                                    <span class="px-2.5 py-1 text-xs font-medium rounded-full
                                        {{ $status === 'approved' ? 'bg-[#1C6B45]/10 text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#7FBF8E]' : '' }}
                                        {{ $status === 'pending' ? 'bg-[#D4A537]/15 text-[#B8862A]' : '' }}
                                        {{ $status === 'submitted' ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' : '' }}
                                        {{ $status === 'rejected' ? 'bg-[#B8352A]/10 text-[#B8352A]' : '' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400 whitespace-nowrap">{{ $activity['time_ago'] }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-sm text-gray-400 dark:text-gray-500">No activities yet. Start by creating a reservation or request.</td></tr>
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
                        borderColor: '#1C6B45',
                        backgroundColor: 'rgba(28, 107, 69, 0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#1C6B45',
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
                        borderColor: '#D4A537',
                        backgroundColor: 'rgba(212, 165, 55, 0.08)',
                        borderWidth: 2.5,
                        pointBackgroundColor: '#D4A537',
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
                        borderColor: isDark ? '#2A4B3A' : '#E4E1D8',
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
