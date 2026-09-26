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
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-mortarboard size-5 text-blue-400 dark:text-blue-400" viewBox="0 0 16 16">
                    <path d="M15.964.686a.5.5 0 0 0-.65-.65L.767 5.855a.75.75 0 0 0-.124 1.329l4.995 3.178 1.531 2.406a.5.5 0 0 0 .844-.536L6.637 10.07l7.494-7.494-1.895 4.738a.5.5 0 1 0 .928.372zm-2.54 1.183L5.93 9.363 1.591 6.602z"/>
                    <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m.5-5v1.5a.5.5 0 0 1-1 0V11a.5.5 0 0 1 1 0m0 3a.5.5 0 1 1-1 0 .5.5 0 0 1 1 0"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-4xl font-bold text-gray-800 dark:text-white">{{ $this->totalRequests }}</h3>
            <p class="text-xs text-gray-400 mt-1">All time</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-blue-500 rounded-b-2xl"></div>
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

        {{-- Total Students --}}
        <div class="relative overflow-hidden flex flex-col bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-center justify-between mb-2 sm:mb-4">
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-neutral-400">Total Students</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-[#B8352A]/8 dark:bg-[#B8352A]/20 flex items-center justify-center shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-mortarboard size-5.5 text-red-400 dark:text-red-400" viewBox="0 0 16 16">
                    <path d="M8.211 2.047a.5.5 0 0 0-.422 0l-7.5 3.5a.5.5 0 0 0 .025.917l7.5 3a.5.5 0 0 0 .372 0L14 7.14V13a1 1 0 0 0-1 1v2h3v-2a1 1 0 0 0-1-1V6.739l.686-.275a.5.5 0 0 0 .025-.917zM8 8.46 1.758 5.965 8 3.052l6.242 2.913z"/>
                    <path d="M4.176 9.032a.5.5 0 0 0-.656.327l-.5 1.7a.5.5 0 0 0 .294.605l4.5 1.8a.5.5 0 0 0 .372 0l4.5-1.8a.5.5 0 0 0 .294-.605l-.5-1.7a.5.5 0 0 0-.656-.327L8 10.466zm-.068 1.873.22-.748 3.496 1.311a.5.5 0 0 0 .352 0l3.496-1.311.22.748L8 12.46z"/>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl sm:text-4xl font-bold text-[#123524] dark:text-neutral-200">{{ $this->totalStudents }}</h3>
            <p class="text-xs text-gray-400 mt-1">Registered students</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-[#B8352A] rounded-b-2xl"></div>
        </div>

        {{-- Total Faculty --}}
        <div class="relative overflow-hidden flex flex-col bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="flex items-center justify-between mb-2 sm:mb-4">
                <p class="text-xs sm:text-sm font-medium text-gray-500 dark:text-neutral-400">Total Faculty</p>
                <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 sm:w-5 sm:h-5 text-purple-500 dark:text-purple-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl sm:text-4xl font-bold text-[#123524] dark:text-neutral-200">{{ $this->totalFaculty }}</h3>
            <p class="text-xs text-gray-400 mt-1">Registered faculty</p>
            <div class="absolute bottom-0 left-0 right-0 h-1 bg-purple-500 rounded-b-2xl"></div>
        </div>

    </div>

    <div class="grid lg:grid-cols-1 gap-4 sm:gap-6">

        {{-- Bar Chart - Request Types --}}
        <div class="bg-white border border-[#E4E1D8] shadow-sm rounded-2xl p-4 sm:p-6 dark:bg-[#16281F] dark:border-[#2A4B3A]">
            <div class="mb-4">
                <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200" style="font-family: 'Fraunces', serif;">Request Types</h2>
                <p class="text-xs sm:text-sm text-gray-400">Facility vs Material Requests</p>
            </div>
            <div class="flex flex-wrap gap-3 sm:gap-4 mb-4">
            <div class="inline-flex rounded-full border border-[#E4E1D8] dark:border-[#2A4B3A] p-0.5 text-xs font-medium" id="lineFilters">
                <button type="button" data-filter="student" class="px-3 py-1 rounded-full bg-[#123524] text-white">Students</button>
                <button type="button" data-filter="faculty" class="px-3 py-1 rounded-full text-gray-500 dark:text-neutral-400">Faculty</button>
                </div>
            </div>
            <div class="relative w-full h-[200px]">
                <canvas id="barChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Student & Faculty Request History --}}
<div class="bg-white border border-[#E4E1D8] rounded-2xl shadow-sm overflow-hidden dark:bg-[#16281F] dark:border-[#2A4B3A]">
    <div class="px-4 sm:px-6 py-4 border-b border-[#E4E1D8] dark:border-[#2A4B3A]">
        <h2 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-neutral-200" style="font-family: 'Fraunces', serif;">
            Request History
        </h2>
        <p class="text-xs sm:text-sm text-gray-400">How many reservations and material requests each person has made</p>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-[640px] w-full divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">
            <thead class="bg-[#FAF7EF] dark:bg-[#0E1A14]">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Role</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Facility Reservations</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Material Requests</th>
                    <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide text-[#B8862A]">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">
                @forelse($this->userRequestHistory as $user)
                    <tr class="hover:bg-[#FAF7EF] dark:hover:bg-[#0E1A14]/50 transition">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#D4A537]/40 shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $user->name }}</p>
                                    <p class="text-xs text-gray-400 truncate">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                {{ $user->hasRole('student') ? 'bg-[#B8352A]/8 text-[#B8352A]' : 'bg-purple-100 text-purple-600 dark:bg-purple-900/40 dark:text-purple-300' }}">
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
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400">
                            No request history yet.
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
    // Pre-computed data for the filter toggle (Students / Faculty)
    const requestData = {
        student: [{{ $this->studentFacilityRequests }}, {{ $this->studentMaterialRequests }}],
        faculty: [{{ $this->facultyFacilityRequests }}, {{ $this->facultyMaterialRequests }}],
    };

    // 1. Bar Chart - Request Types
    const barChart = new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['Facility Reservation', 'Material Request'],
            datasets: [{
                label: 'Total',
                data: requestData.student,
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

    // Filter button handling (Students / Faculty)
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

    // 2. Pie Chart - Status Breakdown
    const pieChartEl = document.getElementById('pieChart');
    if (pieChartEl) {
        new Chart(pieChartEl, {
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
    }

    // 3. Radial/Doughnut Chart - Approval Rate
    const radialChartEl = document.getElementById('radialChart');
    if (radialChartEl) {
        new Chart(radialChartEl, {
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
    }
</script>
</div>
