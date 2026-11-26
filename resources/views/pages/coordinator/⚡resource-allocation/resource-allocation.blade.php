<div class="select-none">
<div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 sm:py-10 lg:px-8 lg:py-14">

    {{-- Page Header --}}
    <div class="mb-6 sm:mb-8">
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Department Resources</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Materials used by your department's faculty
        </p>
    </div>

    {{-- ✅ Allocations are readable by anyone with `allocations.view` --}}
    @can('allocations.view')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6 sm:mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm px-4 sm:px-6 py-4 sm:py-5 flex items-center gap-3 sm:gap-4">
            <div class="shrink-0 size-10 sm:size-12 rounded-lg bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                <svg class="size-5 sm:size-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Approved</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-0.5">
                    {{ $this->materialLogs->where('status', 'approved')->count() }}
                </p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm px-4 sm:px-6 py-4 sm:py-5 flex items-center gap-3 sm:gap-4">
            <div class="shrink-0 size-10 sm:size-12 rounded-lg bg-[#123524]/10 flex items-center justify-center">
                <svg class="size-5 sm:size-6 text-[#123524] dark:text-green-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Total Units</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ number_format($this->totalAllocated) }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm px-4 sm:px-6 py-4 sm:py-5 flex items-center gap-3 sm:gap-4">
            <div class="shrink-0 size-10 sm:size-12 rounded-lg bg-[#D4A537]/10 flex items-center justify-center">
                <svg class="size-5 sm:size-6 text-[#B8862A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wide">Resource Types</p>
                <p class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white mt-0.5">{{ $this->resourceTypeCount }}</p>
            </div>
        </div>
    </div>

    {{-- My Department's Materials --}}
    @if($this->materialsSummary->isNotEmpty())
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden mb-6 sm:mb-8">
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">My Department's Materials</h3>
            <p class="text-xs text-gray-500 dark:text-gray-400">Quick view of what your department currently holds</p>
        </div>

        <div class="p-4 sm:p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($this->materialsSummary as $material)
                <div class="flex items-center justify-between gap-3 rounded-lg border border-gray-100 dark:border-gray-700 bg-gray-50/60 dark:bg-gray-900/30 px-4 py-3">
                    <span class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate">{{ $material->resource_name }}</span>
                    <span class="shrink-0 inline-flex items-center px-2.5 py-1 bg-white dark:bg-gray-800 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-full text-xs font-semibold">
                        {{ $material->formatted_quantity }}
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Material Logs (table) --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 shadow-sm overflow-hidden">

        {{-- Header + filters --}}
        <div class="px-4 sm:px-6 py-4 flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-gray-200 dark:border-gray-700">
            <div>
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-100">Material Logs</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Full history of approved and rejected materials in your department</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z"/>
                    </svg>
                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search material, requester, role..."
                           class="w-full sm:w-64 pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:border-[#123524] focus:ring-[#123524]">
                </div>
                <input type="date" wire:model.live="dateFrom"
                       class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:border-[#123524] focus:ring-[#123524]">
                <input type="date" wire:model.live="dateTo"
                       class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200 focus:border-[#123524] focus:ring-[#123524]">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900/40">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Material</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Qty</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Before / After</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Department</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Requested By</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($this->materialLogs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/30 transition">

                        {{-- Material --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-3">
                                <div class="shrink-0 size-9 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                                    <svg class="size-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-gray-800 dark:text-gray-100">{{ $log->item_name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">{{ $log->resource_type ?? '—' }}</p>
                                </div>
                            </div>
                        </td>

                        {{-- Type --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300">
                                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    Approved
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                    Rejected
                                </span>
                            @endif
                            <p class="text-[11px] text-gray-400 mt-1">{{ $log->source }}</p>
                        </td>

                        {{-- Qty --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->status === 'approved')
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                    − {{ $log->quantity }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-500 dark:bg-gray-700 dark:text-gray-300">
                                    {{ $log->quantity }}
                                </span>
                            @endif
                            <p class="text-[11px] text-gray-400 mt-1">{{ trim($log->unit ?? '') ?: 'Pcs' }}</p>
                        </td>

                        {{-- Before / After --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            <span class="text-gray-500 dark:text-gray-400">{{ $log->before }}</span>
                            <span class="mx-1 text-gray-400">→</span>
                            <span class="font-bold text-gray-800 dark:text-gray-100">{{ $log->after }}</span>
                        </td>

                        {{-- Department --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            {{ $log->department_name ?? '—' }}
                        </td>

                        {{-- Date --}}
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 dark:text-gray-300">
                            {{ \Carbon\Carbon::parse($log->decided_at)->format('M d, Y') }}
                            <p class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($log->decided_at)->format('h:i A') }}</p>
                        </td>

                        {{-- Requested by + role --}}
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center gap-2">
                                <div class="shrink-0 size-6 rounded-full bg-[#123524] text-white flex items-center justify-center text-[10px] font-bold">
                                    {{ strtoupper(substr($log->requester_name, 0, 1)) }}
                                </div>
                                <span class="text-sm text-gray-800 dark:text-gray-200">{{ $log->requester_name }}</span>

                                @if($log->requester_role === 'faculty')
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                        Faculty
                                    </span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="flex flex-col items-center gap-3">
                                <div class="size-14 rounded-full bg-gray-100 dark:bg-gray-700 flex items-center justify-center">
                                    <svg class="size-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-700 dark:text-gray-300">No material logs found</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Approved or rejected materials will appear here.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @endcan

</div>
</div>
