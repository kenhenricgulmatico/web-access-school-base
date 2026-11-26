<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800 dark:text-neutral-200">Stock History</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400">Complete log of restocks, materials released via admin-approved requests, and returned borrowed items</p>
        </div>
        <a href="{{ route('admin.inventory-stock-material') }}"
            class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-green-600 text-white hover:bg-green-700 focus:outline-none focus:bg-green-700 disabled:opacity-50 disabled:pointer-events-none">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
            Manage Stock
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">

        {{-- Total Restocks --}}
        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center gap-x-2 mb-3">
                <span class="size-8 inline-flex justify-center items-center rounded-full border-4 border-blue-50 bg-blue-100 text-blue-800 dark:border-blue-900 dark:bg-blue-800 dark:text-blue-400">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                    </svg>
                </span>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Total Restocks</p>
            </div>
            <div class="flex items-center gap-x-2">
                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                    {{ $this->totalRestocks }}
                </h3>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">All time</p>
        </div>

        {{-- Total Quantity Added --}}
        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center gap-x-2 mb-3">
                <span class="size-8 inline-flex justify-center items-center rounded-full border-4 border-green-50 bg-green-100 text-green-800 dark:border-green-900 dark:bg-green-800 dark:text-green-400">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </span>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Total Qty Added</p>
            </div>
            <div class="flex items-center gap-x-2">
                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                    {{ number_format($this->totalQuantityAdded) }}
                </h3>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">Units restocked</p>
        </div>

        {{-- Today --}}
        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center gap-x-2 mb-3">
                <span class="size-8 inline-flex justify-center items-center rounded-full border-4 border-purple-50 bg-purple-100 text-purple-800 dark:border-purple-900 dark:bg-purple-800 dark:text-purple-400">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                </span>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Today</p>
            </div>
            <div class="flex items-center gap-x-2">
                <h3 class="text-xl sm:text-2xl font-medium text-gray-800 dark:text-neutral-200">
                    {{ $this->todayRestocks }}
                </h3>
            </div>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">Restocks today</p>
        </div>

    </div>

    {{-- Table Card --}}
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

                    {{-- Table Header --}}
                    <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Restock, Approval &amp; Return Logs</h2>
                            <p class="text-sm text-gray-600 dark:text-neutral-400">Full history of restocking, admin-approved material requests, and returned borrowed items</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">

                            {{-- Search --}}
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                    <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/>
                                    </svg>
                                </div>
                                <input type="text" wire:model.live="search"
                                    placeholder="Search material, supplier, department..."
                                    class="py-2 ps-9 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 disabled:opacity-50 disabled:pointer-events-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500 dark:focus:ring-neutral-600">
                            </div>

                            {{-- Date From --}}
                            <input type="date" wire:model.live="dateFrom"
                                class="py-2 px-3 block border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">

                            {{-- Date To --}}
                            <input type="date" wire:model.live="dateTo"
                                class="py-2 px-3 block border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">

                        </div>
                    </div>

                    {{-- Table --}}
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Material</span>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Type</span>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Qty</span>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Before / After</span>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Supplier / Department</span>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Date</span>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">By</span>
                                </th>
                                <th class="px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Remarks</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($this->histories as $stock)
                                @php
                                    $isRestock = $stock->entry_type === 'restock';
                                    $isReturn  = $stock->entry_type === 'return';
                                    $isPlus    = $isRestock || $isReturn;   // ✅ restocks and returns ADD to stock
                                @endphp
                                <tr wire:key="{{ $stock->entry_type }}-{{ $stock->id }}" class="bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:hover:bg-neutral-700">

                                    {{-- Material --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-x-3">
                                            <div class="size-8 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                <svg class="size-4 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                                    {{ $stock->resource_name ?? 'N/A' }}
                                                </span>
                                                <span class="block text-xs text-gray-500 dark:text-neutral-400">
                                                    {{ $stock->resource_type_name ?? 'N/A' }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Type --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($isRestock)
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full dark:bg-blue-900 dark:text-blue-400">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Stock In
                                            </span>
                                        @elseif($isReturn)
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full dark:bg-teal-900 dark:text-teal-300">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                                                </svg>
                                                Returned
                                            </span>
                                        @else
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-green-100 text-green-800 rounded-full dark:bg-green-900 dark:text-green-400">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Approved by Admin
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Qty --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($isPlus)
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-green-100 text-green-800 rounded-full dark:bg-green-900 dark:text-green-400">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                                </svg>
                                                <span class="sr-only">plus</span>{{ number_format(abs($stock->quantity_added)) }}
                                            </span>
                                        @else
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-900 dark:text-red-400">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                                                </svg>
                                                {{-- ✅ The icon already shows the minus — print the number only (abs, in case a negative is ever stored) --}}
                                                <span class="sr-only">minus</span>{{ number_format(abs($stock->quantity_added)) }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Before / After --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if(!is_null($stock->quantity_before) && !is_null($stock->quantity_after))
                                            <div class="flex items-center gap-x-2 text-sm text-gray-600 dark:text-neutral-400">
                                                <span class="font-medium text-gray-500">{{ number_format($stock->quantity_before) }}</span>
                                                <svg class="size-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                                </svg>
                                                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ number_format($stock->quantity_after) }}</span>
                                            </div>
                                        @elseif(! $isRestock)
                                            {{-- Approved / returned before stock levels were recorded --}}
                                            <span class="text-xs text-gray-400 dark:text-neutral-500 italic"
                                                  title="This {{ $isReturn ? 'return' : 'approval' }} happened before stock levels were recorded.">Not recorded</span>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-neutral-500">—</span>
                                        @endif
                                    </td>

                                    {{-- Supplier / Department --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="text-sm text-gray-600 dark:text-neutral-400">
                                            {{ $isRestock ? ($stock->supplier ?? '—') : ($stock->department_name ?? '—') }}
                                        </span>
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="block text-sm text-gray-800 dark:text-neutral-200">
                                            {{ \Carbon\Carbon::parse($stock->arrival_date)->format('M d, Y') }}
                                        </span>
                                        @if($stock->arrival_time)
                                            <span class="block text-xs text-gray-500 dark:text-neutral-400">
                                                {{ \Carbon\Carbon::parse($stock->arrival_time)->format('h:i A') }}
                                            </span>
                                        @endif
                                    </td>

                                    {{-- By --}}
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-x-2">
                                            <div class="size-6 rounded-full bg-green-700 text-white flex items-center justify-center text-[10px] font-bold">
                                                {{ strtoupper(substr($stock->actor_name ?? 'A', 0, 1)) }}
                                            </div>
                                            <span class="text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $stock->actor_name ?? 'N/A' }}
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">
                                                    ({{ $isRestock ? 'Restocked' : ($isReturn ? 'Received' : 'Requested') }})
                                                </span>
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Remarks --}}
                                    <td class="px-6 py-4">
                                        <span class="text-sm text-gray-500 dark:text-neutral-400">
                                            {{ $stock->remarks ?? '—' }}
                                        </span>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-10 text-center">
                                        <div class="flex flex-col items-center gap-2">
                                            <svg class="size-10 text-gray-300 dark:text-neutral-600" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                            </svg>
                                            <p class="text-sm text-gray-500 dark:text-neutral-400 font-medium">No stock history found</p>
                                            <p class="text-xs text-gray-400 dark:text-neutral-500">Add stock, approve a material request, or confirm a return to see history here</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    @if($this->histories->hasPages())
                        <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                            {{ $this->histories->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>

</div>
</div>
