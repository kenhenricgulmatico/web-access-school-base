<div class="select-none">
<div class="space-y-5 sm:space-y-6 max-w-7xl mx-auto px-3 sm:px-6 py-5 sm:py-8">

    {{-- Page Title --}}
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 sm:w-11 sm:h-11 rounded-full bg-[#123524] flex items-center justify-center ring-2 ring-[#D4A537]/40 shrink-0">
            <svg class="w-4.5 h-4.5 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
            </svg>
        </div>
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold text-[#123524] dark:text-neutral-200 truncate" style="font-family: 'Fraunces', serif;">Program Head Dashboard</h1>
            <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400 truncate">Overview of all faculty and student requests</p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">

        {{-- Total Requests --}}
        <div class="relative overflow-hidden flex flex-col bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-center justify-between mb-2 sm:mb-4">
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-neutral-400">Total Requests</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#123524]/8 dark:bg-[#123524]/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl sm:text-4xl font-bold text-[#123524] dark:text-neutral-200">{{ $this->totalRequests }}</h3>
            <p class="text-xs text-gray-400 mt-1">All time</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-[#123524] rounded-b-2xl"></div>
        </div>

        {{-- Pending --}}
        <div class="relative overflow-hidden flex flex-col bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-center justify-between mb-2 sm:mb-4">
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-neutral-400">Pending</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#D4A537]/12 dark:bg-[#D4A537]/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#B8862A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl sm:text-4xl font-bold text-[#123524] dark:text-neutral-200">{{ $this->pendingRequests }}</h3>
            <p class="text-xs text-gray-400 mt-1">Awaiting action</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-[#D4A537] rounded-b-2xl"></div>
        </div>

        {{-- Approved --}}
        <div class="relative overflow-hidden flex flex-col bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-center justify-between mb-2 sm:mb-4">
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-neutral-400">Approved</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#1C6B45]/10 dark:bg-[#1C6B45]/25 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl sm:text-4xl font-bold text-[#123524] dark:text-neutral-200">{{ $this->approvedRequests }}</h3>
            <p class="text-xs text-gray-400 mt-1">Successfully approved</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-[#1C6B45] rounded-b-2xl"></div>
        </div>

        {{-- Total Students --}}
        <div class="relative overflow-hidden flex flex-col bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-center justify-between mb-2 sm:mb-4">
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-neutral-400">Total Students</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#B8352A]/8 dark:bg-[#B8352A]/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-[#B8352A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl sm:text-4xl font-bold text-[#123524] dark:text-neutral-200">{{ $this->totalStudents }}</h3>
            <p class="text-xs text-gray-400 mt-1">Registered students</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-[#B8352A] rounded-b-2xl"></div>
        </div>

    </div>

    {{-- Chart Composition: Bar + Pie + Radial --}}
    <div class="grid lg:grid-cols-3 gap-4 sm:gap-6">

        {{-- Bar Chart - Request Types --}}
        <div class="bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="mb-4">
                <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200" style="font-family: 'Fraunces', serif;">Request Types</h2>
                <p class="text-xs sm:text-sm text-gray-400">Facility vs Material breakdown</p>
            </div>
            <div class="flex flex-wrap gap-3 sm:gap-4 mb-4">
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="w-3 h-3 rounded-sm bg-[#123524] inline-block shrink-0"></span>
                    <span class="text-gray-500 dark:text-neutral-400">Facility — {{ $this->facilityRequests }}</span>
                </div>
                <div class="flex items-center gap-1.5 text-xs">
                    <span class="w-3 h-3 rounded-sm bg-[#D4A537] inline-block shrink-0"></span>
                    <span class="text-gray-500 dark:text-neutral-400">Material — {{ $this->materialRequests }}</span>
                </div>
            </div>
            <div class="relative w-full h-[200px]">
                <canvas id="barChart"></canvas>
            </div>
        </div>

        {{-- Pie Chart - Status Breakdown --}}
        <div class="bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="mb-4">
                <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200" style="font-family: 'Fraunces', serif;">Status Breakdown</h2>
                <p class="text-xs sm:text-sm text-gray-400">Distribution of all request statuses</p>
            </div>
            <div class="relative w-full h-[200px]">
                <canvas id="pieChart"></canvas>
            </div>
            <div class="mt-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#D4A537] inline-block shrink-0"></span>
                        <span class="text-gray-600 dark:text-neutral-400">Pending</span>
                    </div>
                    <span class="font-semibold text-[#123524] dark:text-neutral-200">{{ $this->pendingRequests }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#1C6B45] inline-block shrink-0"></span>
                        <span class="text-gray-600 dark:text-neutral-400">Approved</span>
                    </div>
                    <span class="font-semibold text-[#123524] dark:text-neutral-200">{{ $this->approvedRequests }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-[#B8352A] inline-block shrink-0"></span>
                        <span class="text-gray-600 dark:text-neutral-400">Rejected</span>
                    </div>
                    <span class="font-semibold text-[#123524] dark:text-neutral-200">{{ $this->rejectedRequests }}</span>
                </div>
            </div>
        </div>

        {{-- Radial/Doughnut Chart - Approval Rate --}}
        <div class="bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="mb-4">
                <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200" style="font-family: 'Fraunces', serif;">Approval Rate</h2>
                <p class="text-xs sm:text-sm text-gray-400">Overall approval percentage</p>
            </div>
            <div class="relative flex items-center justify-center mb-4 w-full h-[200px]">
                <canvas id="radialChart"></canvas>
                <div class="absolute text-center">
                    @php
                        $total = $this->totalRequests;
                        $rate = $total > 0 ? round(($this->approvedRequests / $total) * 100) : 0;
                    @endphp
                    <p class="text-2xl sm:text-3xl font-bold text-[#123524] dark:text-neutral-200">{{ $rate }}%</p>
                    <p class="text-xs text-gray-400">Approved</p>
                </div>
            </div>
            <div class="grid grid-cols-3 gap-2 mt-2">
                <div class="text-center p-2 bg-[#1C6B45]/8 dark:bg-[#1C6B45]/15 rounded-xl">
                    <p class="text-sm sm:text-base font-bold text-[#1C6B45] dark:text-[#7FBF8E]">{{ $this->approvedRequests }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400">Approved</p>
                </div>
                <div class="text-center p-2 bg-[#D4A537]/12 dark:bg-[#D4A537]/15 rounded-xl">
                    <p class="text-sm sm:text-base font-bold text-[#B8862A]">{{ $this->pendingRequests }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400">Pending</p>
                </div>
                <div class="text-center p-2 bg-[#B8352A]/8 dark:bg-[#B8352A]/15 rounded-xl">
                    <p class="text-sm sm:text-base font-bold text-[#B8352A]">{{ $this->rejectedRequests }}</p>
                    <p class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400">Rejected</p>
                </div>
            </div>
        </div>

    </div>

    {{-- Recent Pending Requests Table --}}
    <div class="bg-white border border-[#E4E1D8] rounded-2xl shadow-sm overflow-hidden dark:bg-[#16281F] dark:border-[#2A4B3A]">
        <div class="px-4 sm:px-6 py-4 border-b border-[#E4E1D8] dark:border-[#2A4B3A] flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div class="min-w-0">
                <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200 truncate" style="font-family: 'Fraunces', serif;">Pending Requests</h2>
                <p class="text-xs sm:text-sm text-gray-400">Latest requests awaiting your action</p>
            </div>
            <div class="flex gap-3 shrink-0">
                <a href="/coordinator/facility" class="text-xs text-[#1C6B45] hover:text-[#123524] dark:text-[#7FBF8E] hover:underline font-medium whitespace-nowrap">Facility →</a>
                <a href="/coordinator/material" class="text-xs text-[#B8862A] hover:text-[#96701F] hover:underline font-medium whitespace-nowrap">Materials →</a>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[720px] w-full divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">
                <thead class="bg-[#FAF7EF] dark:bg-[#0E1A14]">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Requestor</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Department</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">
                    @forelse($this->recentRequests as $request)
                        <tr class="hover:bg-[#FAF7EF] dark:hover:bg-[#0E1A14]/50 transition">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#D4A537]/40 shrink-0">
                                        {{ strtoupper(substr($request->user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $request->user->name }}</p>
                                        <p class="text-xs text-gray-400 truncate">{{ $request->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400 whitespace-nowrap">
                                {{ $request->user->department->department_name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                    @if($request->request_type_id == 1) bg-[#123524]/8 text-[#1C6B45] dark:bg-[#123524]/25 dark:text-[#7FBF8E]
                                    @else bg-[#D4A537]/12 text-[#B8862A] @endif">
                                    {{ $request->requestType->type_name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                                {{ Str::limit($request->purpose, 40) }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                    @if($request->status === 'pending') bg-[#D4A537]/15 text-[#B8862A]
                                    @elseif($request->status === 'coordinator_review') bg-[#123524]/8 text-[#1C6B45] dark:bg-[#123524]/25 dark:text-[#7FBF8E]
                                    @else bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-400 whitespace-nowrap">
                                {{ $request->created_at->format('M d, Y') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                No pending requests.
                            </td>
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
    // 1. Bar Chart - Request Types
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Facility Reservation', 'Material Request'],
            datasets: [{
                label: 'Total',
                data: [{{ $this->facilityRequests }}, {{ $this->materialRequests }}],
                backgroundColor: [
                    'rgba(18, 53, 36, 0.85)',
                    'rgba(212, 165, 55, 0.85)',
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

    // 2. Pie Chart - Status Breakdown
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [
                    {{ $this->pendingRequests }},
                    {{ $this->approvedRequests }},
                    {{ $this->rejectedRequests }}
                ],
                backgroundColor: [
                    'rgba(212, 165, 55, 0.9)',
                    'rgba(28, 107, 69, 0.9)',
                    'rgba(184, 53, 42, 0.9)',
                ],
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });

    // 3. Radial/Doughnut Chart - Approval Rate
    new Chart(document.getElementById('radialChart'), {
        type: 'doughnut',
        data: {
            labels: ['Approved', 'Pending', 'Rejected'],
            datasets: [{
                data: [
                    {{ $this->approvedRequests }},
                    {{ $this->pendingRequests }},
                    {{ $this->rejectedRequests }}
                ],
                backgroundColor: [
                    'rgba(28, 107, 69, 0.9)',
                    'rgba(212, 165, 55, 0.9)',
                    'rgba(184, 53, 42, 0.9)',
                ],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { display: false } }
        }
    });
</script>
</div>
