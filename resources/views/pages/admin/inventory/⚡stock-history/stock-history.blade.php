<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Stock History</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400">Every change to the main stock: restocks, materials released on approval, returned borrowed items and stock removed</p>
        </div>
        <a href="{{ route('admin.inventory-stock-material') }}"
            class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-[#123524] text-white hover:bg-[#0B2418] focus:outline-none focus:bg-[#0B2418] disabled:opacity-50 disabled:pointer-events-none">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
            </svg>
            Manage Stock
        </a>
    </div>

    {{-- ✅ Stats: in / out / net for the current filters --}}
    @php
        $tot    = $this->totals;
        $period = $this->periodLabel();
        $unit   = $this->selectedMaterial?->unit;
        $scope  = $this->selectedMaterial ? $this->selectedMaterial->resource_name : 'all materials';
    @endphp
    <div class="grid sm:grid-cols-3 gap-3">
        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center gap-x-2 mb-3">
                <span class="size-8 inline-flex justify-center items-center rounded-full border-4 border-green-50 bg-green-100 text-[#123524] dark:border-[#1C6B45/40] dark:bg-[#1C6B45/25] dark:text-green-300">
                    <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                </span>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Stock in</p>
            </div>
            <h3 class="text-xl sm:text-2xl font-medium text-[#1C6B45] dark:text-green-400">+{{ number_format($tot['in']) }} <span class="text-sm font-normal text-gray-400">{{ $unit }}</span></h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">{{ $period }} · {{ $tot['inEntries'] }} restocks &amp; returns · {{ $scope }}</p>
        </div>

        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center gap-x-2 mb-3">
                <span class="size-8 inline-flex justify-center items-center rounded-full border-4 border-red-50 bg-red-100 text-red-800 dark:border-red-900 dark:bg-red-800 dark:text-red-300">
                    <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M5 12h14"/></svg>
                </span>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Stock out</p>
            </div>
            <h3 class="text-xl sm:text-2xl font-medium text-red-700 dark:text-red-400">−{{ number_format($tot['out']) }} <span class="text-sm font-normal text-gray-400">{{ $unit }}</span></h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">{{ $period }} · {{ $tot['outEntries'] }} approved &amp; removed · {{ $scope }}</p>
        </div>

        <div class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex items-center gap-x-2 mb-3">
                <span class="size-8 inline-flex justify-center items-center rounded-full border-4 border-[#1C6B45/15] bg-[#E8F3EC] text-[#123524] dark:border-[#1C6B45/40] dark:bg-[#1C6B45/25] dark:text-[#9FD3AE]">
                    <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                </span>
                <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Net change</p>
            </div>
            <h3 class="text-xl sm:text-2xl font-medium {{ $tot['net'] > 0 ? 'text-[#1C6B45] dark:text-green-400' : ($tot['net'] < 0 ? 'text-red-700 dark:text-red-400' : 'text-gray-800 dark:text-neutral-200') }}">
                {{ $tot['net'] > 0 ? '+' : ($tot['net'] < 0 ? '−' : '') }}{{ number_format(abs($tot['net'])) }} <span class="text-sm font-normal text-gray-400">{{ $unit }}</span>
            </h3>
            <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">{{ $period }} · in minus out{{ $this->selectedMaterial ? '' : ' (all units together)' }}</p>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="flex flex-col">
        <div class="-m-1.5 overflow-x-auto">
            <div class="p-1.5 min-w-full inline-block align-middle">
                {{-- ✅ @container: the table follows the width of THIS box, not the window --}}
                <div class="@container bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

                    {{-- Table Header --}}
                    <div class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Restock, Approval &amp; Return Logs</h2>
                            <p class="text-sm text-gray-600 dark:text-neutral-400">Newest first. Click a type to show only that kind.</p>
                        </div>
                        {{-- ✅ Download what's shown (all pages) --}}
                        @can('stock-history.export')
                        <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
                            class="self-start md:self-center py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700">
                            <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                            <span wire:loading.remove wire:target="exportCsv">Download CSV</span>
                            <span wire:loading wire:target="exportCsv">Preparing…</span>
                        </button>
                        @endcan
                    </div>

                    {{-- ✅ Filters --}}
                    @php
                        $fld   = 'py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300';
                        $range = $this->activeRange();
                        $sum   = $this->summary;
                    @endphp
                    <div class="px-6 py-4 space-y-3 border-b border-gray-200 dark:border-neutral-700">
                        <div class="grid gap-3 @2xl:grid-cols-2 @5xl:grid-cols-4">
                            {{-- Search --}}
                            <div>
                                <label for="sh-search" class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Search</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                        <svg class="size-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                                    </div>
                                    <input id="sh-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Material, supplier, department, person…"
                                        class="{{ $fld }} ps-9">
                                </div>
                            </div>

                            {{-- Material --}}
                            <div>
                                <label for="sh-material" class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">Material</label>
                                <select id="sh-material" wire:model.live="materialId" class="{{ $fld }}">
                                    <option value="">All materials</option>
                                    @foreach ($this->materials as $m)
                                        <option value="{{ $m->id }}">{{ $m->resource_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- From / To --}}
                            <div>
                                <label for="sh-from" class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">From</label>
                                <input id="sh-from" type="date" wire:model.live="dateFrom" max="{{ today()->toDateString() }}" class="{{ $fld }}">
                            </div>
                            <div>
                                <label for="sh-to" class="block text-xs font-medium text-gray-500 dark:text-neutral-400 mb-1">To</label>
                                <input id="sh-to" type="date" wire:model.live="dateTo" max="{{ today()->toDateString() }}" class="{{ $fld }}">
                            </div>
                        </div>

                        @if ($this->datesSwapped())
                            <p class="text-xs text-amber-700 dark:text-amber-400">"From" was after "To", so the dates were swapped: showing {{ $this->periodLabel() }}.</p>
                        @endif

                        <div class="flex flex-wrap items-center gap-2">
                            {{-- Quick ranges --}}
                            @foreach (['today' => 'Today', 'week' => 'This week', 'month' => 'This month'] as $key => $label)
                                <button type="button" wire:click="setRange('{{ $key }}')"
                                    class="px-3 py-1 text-xs font-medium rounded-full border transition
                                    {{ $range === $key ? 'bg-[#123524] border-[#123524] text-white' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                                    {{ $label }}
                                </button>
                            @endforeach

                            <span class="hidden @xl:inline-block w-px h-5 bg-gray-200 dark:bg-neutral-700 mx-1"></span>

                            {{-- ✅ Type chips with counts --}}
                            @php
                                $chipColors = [
                                    'restock'  => 'bg-[#123524] border-[#1C6B45]',
                                    'approval' => 'bg-[#123524] border-[#1C6B45]',
                                    'return'   => 'bg-[#123524] border-[#1C6B45]',
                                    'removal'  => 'bg-amber-600 border-amber-600',
                                ];
                            @endphp
                            <button type="button" wire:click="setType('')"
                                class="px-3 py-1 text-xs font-medium rounded-full border transition
                                {{ $entryType === '' ? 'bg-gray-800 border-gray-800 text-white dark:bg-neutral-200 dark:border-neutral-200 dark:text-neutral-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                                All ({{ array_sum(array_column($sum, 'entries')) }})
                            </button>
                            @foreach ($this::TYPES as $key => $label)
                                <button type="button" wire:click="setType('{{ $key }}')"
                                    class="px-3 py-1 text-xs font-medium rounded-full border transition
                                    {{ $entryType === $key ? $chipColors[$key] . ' text-white' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                                    {{ $label }} ({{ $sum[$key]['entries'] }})
                                </button>
                            @endforeach

                            {{-- Clear --}}
                            @if ($search !== '' || $materialId || $dateFrom !== '' || $dateTo !== '' || $entryType !== '')
                                <button type="button" wire:click="clearFilters"
                                    class="ms-auto px-3 py-1 text-xs font-medium rounded-full text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                                    ✕ Clear filters
                                </button>
                            @endif
                        </div>
                    </div>

                    {{-- Table --}}
                    <table class="min-w-full block @4xl:table divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="hidden @4xl:table-header-group bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Material</span>
                                </th>
                                <th class="px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Type</span>
                                </th>
                                <th class="px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Qty</span>
                                </th>
                                <th class="px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Before / After</span>
                                </th>
                                <th class="px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Supplier / Department</span>
                                </th>
                                <th class="px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Date</span>
                                </th>
                                <th class="hidden @7xl:table-cell px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">By</span>
                                </th>
                                <th class="hidden @5xl:table-cell px-4 @7xl:px-6 py-3 text-start">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">Remarks</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="block @4xl:table-row-group divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($this->histories as $stock)
                                @php
                                    $isRestock = $stock->entry_type === 'restock';
                                    $isReturn  = $stock->entry_type === 'return';
                                    $isRemoval = $stock->entry_type === 'removal';   // ✅ taken out by the Admin
                                    $isPlus    = $isRestock || $isReturn;   // ✅ restocks and returns ADD to stock
                                @endphp
                                <tr wire:key="{{ $stock->entry_type }}-{{ $stock->id }}" class="block @4xl:table-row px-4 py-4 @4xl:p-0 bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:hover:bg-neutral-700">

                                    {{-- Material --}}
                                    <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 pb-2 @4xl:py-4 @4xl:whitespace-nowrap">
                                        <div class="flex items-center gap-x-3">
                                            <div class="size-8 rounded-full bg-[#E8F3EC] dark:bg-[#1C6B45/25] flex items-center justify-center">
                                                <svg class="size-4 text-[#1C6B45] dark:text-[#7FBF8E]" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                                    <td class="inline-block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                        @if($isRestock)
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-[#E8F3EC] text-[#123524] rounded-full dark:bg-[#1C6B45/25] dark:text-[#7FBF8E]">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                                                </svg>
                                                Stock In
                                            </span>
                                        @elseif($isRemoval)
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-amber-100 text-amber-800 rounded-full dark:bg-amber-900 dark:text-amber-300">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/>
                                                </svg>
                                                Removed
                                            </span>
                                        @elseif($isReturn)
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-[#E8F3EC] text-[#123524] rounded-full dark:bg-[#1C6B45/25] dark:text-[#9FD3AE]">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
                                                </svg>
                                                Returned
                                            </span>
                                        @else
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-green-100 text-[#123524] rounded-full dark:bg-[#1C6B45/25] dark:text-green-400">
                                                <svg class="size-3" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                Approved by Admin
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Qty --}}
                                    <td class="inline-block @4xl:table-cell px-0 ps-1 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                        @if($isPlus)
                                            <span class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium bg-green-100 text-[#123524] rounded-full dark:bg-[#1C6B45/25] dark:text-green-400">
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
                                    <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                        @if(!is_null($stock->quantity_before) && !is_null($stock->quantity_after))
                                            <div class="flex items-center gap-x-2 text-sm text-gray-600 dark:text-neutral-400">
                                                <span class="font-medium text-gray-500">{{ number_format($stock->quantity_before) }}</span>
                                                <svg class="size-3 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <path d="M5 12h14M12 5l7 7-7 7"/>
                                                </svg>
                                                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ number_format($stock->quantity_after) }}</span>
                                            </div>
                                        @elseif(! $isRestock && ! $isRemoval)
                                            {{-- Approved / returned before stock levels were recorded --}}
                                            <span class="text-xs text-gray-400 dark:text-neutral-500 italic"
                                                  title="This {{ $isReturn ? 'return' : 'approval' }} happened before stock levels were recorded.">Not recorded</span>
                                        @else
                                            <span class="text-sm text-gray-400 dark:text-neutral-500">—</span>
                                        @endif
                                    </td>

                                    {{-- Supplier / Department --}}
                                    <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:max-w-[14rem]">
                                        <span class="text-sm text-gray-600 dark:text-neutral-400">
                                            {{ $isRemoval ? (\App\Models\Stock::REASONS[$stock->supplier] ?? 'Removed') : ($isRestock ? ($stock->supplier ?? '—') : ($stock->department_name ?? '—')) }}
                                        </span>
                                        @if ($stock->remarks)
                                            <span class="@5xl:hidden block text-xs text-gray-400 dark:text-neutral-500 line-clamp-2 max-w-xs" title="{{ $stock->remarks }}">{{ $stock->remarks }}</span>
                                        @endif
                                    </td>

                                    {{-- Date --}}
                                    <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                        <span class="block text-sm text-gray-800 dark:text-neutral-200">
                                            {{ \Carbon\Carbon::parse($stock->arrival_date)->format('M d, Y') }}
                                        </span>
                                        @if($stock->arrival_time)
                                            <span class="block text-xs text-gray-500 dark:text-neutral-400">
                                                {{ \Carbon\Carbon::parse($stock->arrival_time)->format('h:i A') }}
                                            </span>
                                        @endif
                                        <span class="@7xl:hidden block text-xs text-gray-400 dark:text-neutral-500">
                                            {{ $isRemoval ? 'Removed' : ($isRestock ? 'Restocked' : ($isReturn ? 'Received' : 'Requested')) }} by {{ $stock->actor_name ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- By --}}
                                    <td class="hidden @7xl:table-cell px-4 @7xl:px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center gap-x-2">
                                            <div class="size-6 rounded-full bg-[#0B2418] text-white flex items-center justify-center text-[10px] font-bold">
                                                {{ strtoupper(substr($stock->actor_name ?? 'A', 0, 1)) }}
                                            </div>
                                            <span class="text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $stock->actor_name ?? 'N/A' }}
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">
                                                    ({{ $isRemoval ? 'Removed' : ($isRestock ? 'Restocked' : ($isReturn ? 'Received' : 'Requested')) }})
                                                </span>
                                            </span>
                                        </div>
                                    </td>

                                    {{-- Remarks --}}
                                    <td class="hidden @5xl:table-cell px-4 @7xl:px-6 py-4 max-w-[16rem]">
                                        <span class="text-sm text-gray-500 dark:text-neutral-400 line-clamp-2" title="{{ $stock->remarks }}">
                                            {{ $stock->remarks ?? '—' }}
                                        </span>
                                    </td>

                                </tr>
                            @empty
                                <tr class="block @4xl:table-row">
                                    <td colspan="8" class="block @4xl:table-cell px-6 py-10 text-center">
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
