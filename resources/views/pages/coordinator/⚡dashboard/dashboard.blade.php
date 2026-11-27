<div>
<div class="space-y-5 sm:space-y-6 max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
    @php
        $me       = auth()->user();
        $local    = now();  // ✅ the app runs on Philippine time (config/app.php)
        $greet    = $local->hour < 12 ? 'Good morning' : ($local->hour < 18 ? 'Good afternoon' : 'Good evening');
        $first    = \Illuminate\Support\Str::of($me->name)->trim()->explode(' ')->first();
        $mine     = $this->waitingForMe;
        $mineLink = $mine['facility'] === 0 && $mine['material'] > 0
            ? route('coordinator.material')
            : route('coordinator.facility');
    @endphp

    {{-- ✅ Welcome + what needs the Program Head right now --}}
    <div class="rounded-2xl bg-[#E8F3EC] dark:bg-[#1C6B45]/20 ring-1 ring-inset ring-[#1C6B45]/10 dark:ring-[#7FBF8E]/15 px-4 py-4 sm:px-6 sm:py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">{{ $greet }}, {{ ucfirst($first) }}</h1>
            <p class="mt-1 text-sm text-[#1C6B45]/90 dark:text-[#9FD3AE]">
                {{ $local->format('l, F j, Y') }}@if($me->department) · Program Head, {{ $me->department->department_name }}@endif
            </p>
            <p class="mt-2 text-sm font-semibold {{ $mine['total'] ? 'text-amber-800 dark:text-amber-300' : 'text-[#123524] dark:text-white' }}">
                @if ($mine['total'])
                    {{ $mine['total'] }} {{ \Illuminate\Support\Str::plural('request', $mine['total']) }} waiting for you
                    <span class="font-normal text-gray-600 dark:text-neutral-300">· {{ $mine['facility'] }} facility, {{ $mine['material'] }} material</span>
                @else
                    ✓ All caught up — nothing waiting for you
                @endif
            </p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            @if ($mine['total'])
                <a href="{{ $mineLink }}" wire:navigate
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-semibold bg-[#123524] text-white hover:bg-[#0B2418] dark:bg-[#1C6B45] dark:hover:bg-[#23855A] transition">
                    Review requests →
                </a>
            @endif
            @can('program-head-requests.create')
                <a href="{{ route('coordinator.request-to-admin.create-request') }}" wire:navigate
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-sm font-semibold transition
                          {{ $mine['total'] ? 'bg-white text-[#123524] ring-1 ring-inset ring-[#1C6B45]/25 hover:bg-[#F3F8F5] dark:bg-neutral-800 dark:text-white dark:ring-neutral-600 dark:hover:bg-neutral-700' : 'bg-[#123524] text-white hover:bg-[#0B2418] dark:bg-[#1C6B45] dark:hover:bg-[#23855A]' }}">
                    + New request to Admin
                </a>
            @endcan
        </div>
    </div>

    {{-- Stats — 2×2 on a phone, one row on a laptop --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5">

        {{-- Waiting for you --}}
        <a href="{{ $mineLink }}" wire:navigate
           class="flex flex-col bg-white border shadow-sm rounded-2xl p-4 sm:p-5 dark:bg-[#16281F] dark:border-[#2A4B3A] transition hover:shadow-md {{ $mine['total'] ? 'border-amber-300 dark:border-amber-500/40' : 'border-gray-200' }}">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">Waiting for you</p>
                <span class="hidden sm:flex size-9 rounded-full bg-amber-50 dark:bg-amber-500/15 items-center justify-center shrink-0">
                    <svg class="size-5 text-amber-600 dark:text-amber-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </span>
            </div>
            <p class="mt-1 text-2xl sm:text-3xl font-bold {{ $mine['total'] ? 'text-amber-700 dark:text-amber-300' : 'text-[#123524] dark:text-white' }}">{{ $mine['total'] }}</p>
            <p class="text-[11px] sm:text-xs text-gray-400 mt-1">{{ $mine['total'] ? 'Tap to review' : 'Nothing to review' }}</p>
        </a>

        {{-- Waiting for Admin --}}
        <a href="{{ route('coordinator.request-to-admin.view-request') }}" wire:navigate
           class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-2xl p-4 sm:p-5 dark:bg-[#16281F] dark:border-[#2A4B3A] transition hover:shadow-md">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">Waiting for Admin</p>
                <span class="hidden sm:flex size-9 rounded-full bg-[#E8F3EC] dark:bg-[#1C6B45]/25 items-center justify-center shrink-0">
                    <svg class="size-5 text-[#1C6B45] dark:text-[#9FD3AE]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 12 3.269 3.125A59.769 59.769 0 0 1 21.485 12 59.768 59.768 0 0 1 3.27 20.875L5.999 12Zm0 0h7.5" /></svg>
                </span>
            </div>
            <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#123524] dark:text-white">{{ $this->waitingForAdmin }}</p>
            <p class="text-[11px] sm:text-xs text-gray-400 mt-1">Your requests to the Admin</p>
        </a>

        {{-- Total Requests --}}
        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-2xl p-4 sm:p-5 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">Total requests</p>
                <span class="hidden sm:flex size-9 rounded-full bg-[#123524] dark:bg-[#1C6B45] items-center justify-center shrink-0">
                    <svg class="size-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
                </span>
            </div>
            <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#123524] dark:text-white">{{ $this->totalRequests }}</p>
            <p class="text-[11px] sm:text-xs text-gray-400 mt-1">All time</p>
        </div>

        {{-- Total Faculty --}}
        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-2xl p-4 sm:p-5 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-start justify-between gap-2">
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">Faculty</p>
                <span class="hidden sm:flex size-9 rounded-full bg-[#E8F3EC] dark:bg-[#1C6B45]/25 items-center justify-center shrink-0">
                    <svg class="size-5 text-[#1C6B45] dark:text-[#9FD3AE]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                </span>
            </div>
            <p class="mt-1 text-2xl sm:text-3xl font-bold text-[#123524] dark:text-white">{{ $this->totalFaculty }}</p>
            <p class="text-[11px] sm:text-xs text-gray-400 mt-1">In your department</p>
        </div>

    </div>

    {{-- ✅ Overdue borrowed items (only when something is out) — details live on Borrowed Items --}}
    @php $borrowed = $this->borrowedItems; @endphp
    @if ($borrowed['out'] > 0)
        <a href="{{ route('coordinator.borrowed-items', $borrowed['overdue'] ? ['filter' => 'overdue'] : []) }}" wire:navigate
           class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-4 px-4 sm:px-5 py-3.5 rounded-2xl border transition hover:shadow-sm
                  {{ $borrowed['overdue']
                      ? 'bg-red-50 border-red-200 text-red-800 dark:bg-red-900/20 dark:border-red-900/50 dark:text-red-300'
                      : 'bg-white border-gray-200 text-[#123524] dark:bg-[#16281F] dark:border-[#2A4B3A] dark:text-neutral-200' }}">
            <span class="flex items-center gap-3 min-w-0">
                <svg class="size-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span class="text-sm">
                    @if ($borrowed['overdue'])
                        <strong>{{ $borrowed['overdue'] }} {{ \Illuminate\Support\Str::plural('item', $borrowed['overdue']) }} overdue</strong>
                        · {{ $borrowed['out'] }} out on loan
                    @else
                        <strong>{{ $borrowed['out'] }} {{ \Illuminate\Support\Str::plural('item', $borrowed['out']) }} out on loan</strong>
                        · none overdue
                    @endif
                </span>
            </span>
            <span class="text-sm font-semibold whitespace-nowrap">View borrowed items →</span>
        </a>
    @endif

    <div class="grid lg:grid-cols-2 gap-4 sm:gap-6">

        {{-- Bar Chart - Request Types --}}
        <div class="bg-white border border-gray-200 shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="mb-4">
                <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200">Request Types</h2>
                <p class="text-xs sm:text-sm text-gray-400">Facility vs Material Requests</p>
            </div>
            <div class="flex flex-wrap gap-3 sm:gap-4 mb-4">
                <div class="inline-flex rounded-full border border-gray-200 dark:border-[#2A4B3A] p-0.5 text-xs font-medium" id="lineFilters">
                    <button type="button" data-filter="faculty" class="px-3 py-1 rounded-full bg-[#123524] text-white">Faculty</button>
                </div>
            </div>
            <div class="relative w-full h-[200px]">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        {{-- ✅ Request Status (department) — drawn in SVG, no extra script --}}
        @php $status = $this->statusBreakdown; @endphp
        <div class="bg-white border border-gray-200 shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="mb-4">
                <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200">Request Status</h2>
                <p class="text-xs sm:text-sm text-gray-400">Approved, pending and rejected — your department</p>
            </div>

            @if ($status['total'] === 0)
                <div class="flex items-center justify-center h-[200px] text-sm text-gray-400">No requests yet.</div>
            @else
                <div class="flex flex-col sm:flex-row items-center gap-6 min-h-[200px]">
                    {{-- Donut: circumference 100, so each slice's length = its percent --}}
                    <div class="relative size-40 shrink-0">
                        <svg viewBox="0 0 42 42" class="size-full -rotate-90" role="img"
                             aria-label="Request status: {{ collect($status['slices'])->map(fn ($s) => $s['label'] . ' ' . $s['count'])->implode(', ') }}">
                            <circle cx="21" cy="21" r="15.9155" fill="none" stroke-width="6" stroke="#EEF3EF"></circle>
                            @php $offset = 0; @endphp
                            @foreach ($status['slices'] as $slice)
                                @if ($slice['count'] > 0)
                                    <circle cx="21" cy="21" r="15.9155" fill="none" stroke-width="6"
                                        stroke="{{ $slice['color'] }}"
                                        stroke-dasharray="{{ $slice['percent'] }} {{ 100 - $slice['percent'] }}"
                                        stroke-dashoffset="{{ -$offset }}">
                                        <title>{{ $slice['label'] }}: {{ $slice['count'] }} ({{ $slice['percent'] }}%)</title>
                                    </circle>
                                    @php $offset += $slice['percent']; @endphp
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-bold text-[#123524] dark:text-white leading-none">{{ $status['total'] }}</span>
                            <span class="text-[11px] text-gray-400 mt-1">requests</span>
                        </div>
                    </div>

                    <ul class="flex-1 w-full space-y-2.5">
                        @foreach ($status['slices'] as $slice)
                            <li class="flex items-center justify-between gap-3">
                                <span class="flex items-center gap-2">
                                    <span class="size-2.5 rounded-full shrink-0" style="background: {{ $slice['color'] }}"></span>
                                    <span class="text-sm text-gray-700 dark:text-neutral-300">{{ $slice['label'] }}</span>
                                </span>
                                <span class="text-sm whitespace-nowrap">
                                    <span class="font-semibold text-[#123524] dark:text-white">{{ $slice['count'] }}</span>
                                    <span class="text-gray-400">· {{ $slice['percent'] }}%</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>

    {{-- Faculty Request History --}}
    <div class="bg-white border border-gray-200 rounded-2xl shadow-sm overflow-hidden dark:bg-[#16281F] dark:border-[#2A4B3A]">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-[#2A4B3A]">
            <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200">
                Request History
            </h2>
            <p class="text-xs sm:text-sm text-gray-400">How many reservations and material requests each person has made</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[640px] w-full divide-y divide-gray-200 dark:divide-[#2A4B3A]">
                <thead class="bg-[#F3F8F5] dark:bg-[#0E1A14]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Role</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Facility Reservations</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Material Requests</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-[#2A4B3A]">
                    @forelse($this->userRequestHistory as $user)
                        <tr wire:key="history-{{ $user->id }}" class="hover:bg-[#F3F8F5] dark:hover:bg-[#0E1A14]/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">

                                    {{-- Avatar (photo if uploaded, otherwise initial) --}}
                                    @if ($user->avatar)
                                        <img src="{{ asset($user->avatar) }}"
                                             alt="{{ $user->name }}"
                                             class="w-8 h-8 rounded-full object-cover ring-2 ring-[#7FBF8E]/60 shrink-0">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#7FBF8E]/60 shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                    {{ 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]' }}">
                                    {{ ucfirst($user->getRoleNames()->first() ?? 'N/A') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-neutral-300">
                                {{ $user->facility_count }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm text-gray-700 dark:text-neutral-300">
                                {{ $user->material_count }}
                            </td>
                            <td class="px-6 py-4 text-center text-sm font-semibold text-[#123524] dark:text-neutral-200">
                                {{ $user->total_count }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"><x-portal.empty message="No request history yet" hint="Requests from your faculty will be counted here." /></td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Pre-computed data for the filter toggle (Faculty)
    const requestData = {
        faculty: [{{ $this->facultyFacilityRequests }}, {{ $this->facultyMaterialRequests }}],
    };

    // 1. Bar Chart - Request Types
    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Facility Reservation', 'Material Request'],
            datasets: [{
                label: 'Total',
                data: requestData.faculty,
                backgroundColor: [
                    'rgba(18, 53, 36, 0.85)',
                    'rgba(127, 191, 142, 0.9)',
                ],
                borderRadius: 8,
                barThickness: 60,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 },
                    grid: { color: 'rgba(0,0,0,0.05)' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // Filter button handling (Faculty)
    const filterButtons = document.querySelectorAll('#lineFilters button');
    filterButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const filter = btn.dataset.filter;

            // Swap chart data
            barChart.data.datasets[0].data = requestData[filter];
            barChart.update();

            // Update active pill styling
            filterButtons.forEach(b => {
                b.classList.remove('bg-[#123524]', 'text-white');
                b.classList.add('text-gray-500', 'dark:text-neutral-400');
            });
            btn.classList.add('bg-[#123524]', 'text-white');
            btn.classList.remove('text-gray-500', 'dark:text-neutral-400');
        });
    });
</script>
</div>
