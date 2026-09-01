<div class="select-none">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-5 mb-6 sm:mb-8">

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

            {{-- Approval Rate --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] p-3 sm:p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate">Approval Rate</p>
                        <p class="text-lg sm:text-2xl font-bold text-[#123524] dark:text-white mt-1">
                            @php
                                $total = $this->totalMaterialRequests;
                                $approved = $this->approvedMaterialRequests;
                                $rate = $total > 0 ? round(($approved / $total) * 100) : 0;
                            @endphp
                            {{ $rate }}%
                        </p>
                    </div>
                    <div class="p-2 sm:p-3 bg-[#1C6B45]/10 dark:bg-[#1C6B45]/25 rounded-full shrink-0">
                        <svg class="w-4 h-4 sm:w-6 sm:h-6 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25 6-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-3 w-full bg-gray-100 dark:bg-[#0E1A14] rounded-full h-1.5 overflow-hidden">
                    <div class="bg-[#1C6B45] h-1.5 rounded-full transition-all duration-700" style="width: {{ $rate }}%"></div>
                </div>
            </div>

            {{-- Active Pending --}}
            <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] p-3 sm:p-5 hover:shadow-md transition">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-[11px] sm:text-sm text-gray-500 dark:text-gray-400 truncate">Active (Pending)</p>
                        <p class="text-lg sm:text-2xl font-bold text-[#123524] dark:text-white mt-1">{{ $this->pendingMaterialRequests }}</p>
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

        {{-- Monthly Facility Reservation Trends --}}
        <div class="bg-white dark:bg-[#16281F] rounded-2xl shadow-sm border border-[#E4E1D8] dark:border-[#2A4B3A] p-4 sm:p-6 mb-6 sm:mb-8">
            <h3 class="text-base sm:text-lg font-semibold text-[#123524] dark:text-white mb-1" style="font-family: 'Fraunces', serif;">Monthly Facility Reservations</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-5">Your reservation activity over the past months</p>

            @php
                $maxCount = max(1, collect($this->monthlyStats)->max('count'));
            @endphp
            <div class="flex items-end gap-1.5 xs:gap-2 sm:gap-3 h-32 sm:h-40">
                @foreach($this->monthlyStats as $stat)
                    <div class="flex-1 flex flex-col items-center group min-w-0">
                        <div class="w-full bg-gradient-to-t from-[#123524] to-[#1C6B45] dark:from-[#0E1A14] dark:to-[#1C6B45] rounded-t-lg transition-all duration-500 group-hover:opacity-80"
                             style="height: {{ max(4, ($stat['count'] / $maxCount) * 100) }}%">
                        </div>
                        <div class="text-[9px] sm:text-xs text-gray-500 dark:text-gray-400 mt-2 truncate w-full text-center">{{ $stat['month'] }}</div>
                        <div class="text-[10px] sm:text-sm font-semibold text-[#123524] dark:text-white">{{ $stat['count'] }}</div>
                    </div>
                @endforeach
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
                                {{ $status === 'submitted' ? 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300' : '' }}
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
