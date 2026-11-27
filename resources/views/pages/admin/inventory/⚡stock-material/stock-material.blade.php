<div>
    @php
        // Shared input style for every popup field
        $input = 'py-2.5 px-3 block w-full border border-gray-200 rounded-lg text-sm text-gray-800 placeholder-gray-400 focus:border-[#1C6B45] focus:ring-2 focus:ring-[#1C6B45]/20 focus:outline-none dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 dark:placeholder-neutral-500 transition';
    @endphp

    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-6">

        {{-- Flash --}}
        @if (session()->has('success'))
            <div
                class="p-4 bg-[#E8F3EC] border border-[#1C6B45/25] text-[#123524] rounded-xl text-sm font-medium dark:bg-[#0B2418]/30 dark:border-[#1C6B45/40] dark:text-[#1C6B45]">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div
                class="p-4 bg-red-100 border border-red-200 text-red-800 rounded-xl text-sm font-medium dark:bg-red-800/30 dark:border-red-900 dark:text-red-500">
                {{ session('error') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Stock Materials</h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400">Manage and monitor material inventory</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('admin.inventory-stock-history') }}"
                    class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 3h18v18H3z" />
                        <path d="M8 12h8M8 8h8M8 16h5" />
                    </svg>
                    Stock History
                </a>

                {{-- ✅ Add Material — requires `stock-materials.create` --}}
                @can('stock-materials.create')
                    <button wire:click="openAddModal"
                        class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-[#123524] text-white hover:bg-[#0B2418]">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                            stroke-width="2" viewBox="0 0 24 24">
                            <path d="M12 5v14M5 12h14" />
                        </svg>
                        Add Material
                    </button>
                @endcan
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div role="button" tabindex="0" wire:click="filterStock('')" @keydown.enter="$wire.filterStock('')"
                class="flex flex-col bg-white border shadow-sm rounded-xl p-4 md:p-5 cursor-pointer hover:shadow-md transition dark:bg-neutral-800
                {{ $stockFilter === '' ? 'border-gray-200 dark:border-neutral-700' : 'border-gray-200 dark:border-neutral-700' }}">
                <div class="flex items-center gap-x-2 mb-3">
                    <span
                        class="size-8 inline-flex justify-center items-center rounded-full border-4 border-[#1C6B45/15] bg-[#E8F3EC] text-[#123524] dark:border-[#1C6B45/40] dark:bg-[#1C6B45/25] dark:text-[#7FBF8E]">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path
                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        </svg>
                    </span>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Total Materials</p>
                </div>
                <h3 class="text-2xl font-medium text-gray-800 dark:text-neutral-200">{{ $this->totalMaterials }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">All items · click to show all</p>
            </div>

            <div
                class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
                <div class="flex items-center gap-x-2 mb-3">
                    <span
                        class="size-8 inline-flex justify-center items-center rounded-full border-4 border-green-50 bg-green-100 text-[#123524] dark:border-[#1C6B45/40] dark:bg-[#1C6B45/25] dark:text-green-400">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </span>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Total Units</p>
                </div>
                <h3 class="text-2xl font-medium text-gray-800 dark:text-neutral-200">
                    {{ number_format($this->totalUnits) }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">Units available</p>
            </div>

            <div role="button" tabindex="0" wire:click="filterStock('low')" @keydown.enter="$wire.filterStock('low')"
                class="flex flex-col bg-white border shadow-sm rounded-xl p-4 md:p-5 cursor-pointer hover:shadow-md transition dark:bg-neutral-800
                {{ $stockFilter === 'low' ? 'border-amber-400 ring-2 ring-amber-300 dark:border-amber-600 dark:ring-amber-800' : 'border-gray-200 dark:border-neutral-700' }}">
                <div class="flex items-center gap-x-2 mb-3">
                    <span
                        class="size-8 inline-flex justify-center items-center rounded-full border-4 border-amber-50 bg-amber-100 text-amber-800 dark:border-amber-900 dark:bg-amber-800 dark:text-amber-400">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path
                                d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </span>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Low Stock</p>
                </div>
                <h3 class="text-2xl font-medium text-gray-800 dark:text-neutral-200">{{ $this->lowStock }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">At or below each item's warn level · click to filter</p>
            </div>

            <div role="button" tabindex="0" wire:click="filterStock('out')" @keydown.enter="$wire.filterStock('out')"
                class="flex flex-col bg-white border shadow-sm rounded-xl p-4 md:p-5 cursor-pointer hover:shadow-md transition dark:bg-neutral-800
                {{ $stockFilter === 'out' ? 'border-red-400 ring-2 ring-red-300 dark:border-red-600 dark:ring-red-800' : 'border-gray-200 dark:border-neutral-700' }}">
                <div class="flex items-center gap-x-2 mb-3">
                    <span
                        class="size-8 inline-flex justify-center items-center rounded-full border-4 border-red-50 bg-red-100 text-red-800 dark:border-red-900 dark:bg-red-800 dark:text-red-400">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <line x1="12" y1="8" x2="12" y2="12" />
                            <line x1="12" y1="16" x2="12.01" y2="16" />
                        </svg>
                    </span>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Out of Stock</p>
                </div>
                <h3 class="text-2xl font-medium text-gray-800 dark:text-neutral-200">{{ $this->outOfStock }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">Zero units · click to filter</p>
            </div>

        </div>

        {{-- Table --}}
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    {{-- ✅ @container: the table follows the width of THIS box (the sidebar takes 240px of the window) --}}
                    <div
                        class="@container bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

                        {{-- Table Header --}}
                        <div
                            class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Materials</h2>
                                @if ($stockFilter !== '')
                                    <button type="button" wire:click="filterStock('')"
                                        class="mt-1 inline-flex items-center gap-1 px-2.5 py-0.5 text-xs font-medium rounded-full
                                        {{ $stockFilter === 'low' ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300' }}">
                                        Showing {{ $stockFilter === 'low' ? 'low stock' : 'out of stock' }} only · Show all ✕
                                    </button>
                                @endif
                                <p class="text-sm text-gray-600 dark:text-neutral-400">All registered materials and
                                    their stock levels</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                        <svg class="size-4 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                            fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="11" cy="11" r="8" />
                                            <path d="m21 21-4.35-4.35" />
                                        </svg>
                                    </div>
                                    <input type="text" wire:model.live.debounce.300ms="search" placeholder="Search material..."
                                        class="py-2 ps-9 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                                </div>
                                <select wire:model.live="statusFilter"
                                    class="py-2 px-3 block border border-gray-200 rounded-lg text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                    <option value="">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>

                        {{-- Table Body --}}
                        <table class="min-w-full block @4xl:table divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="hidden @4xl:table-header-group bg-gray-50 dark:bg-neutral-800">
                                <tr>
                                    <th
                                        class="hidden @7xl:table-cell px-4 @7xl:px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        #</th>
                                    <th
                                        class="px-4 @7xl:px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Material</th>
                                    <th
                                        class="hidden @6xl:table-cell px-4 @7xl:px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Type</th>
                                    <th
                                        class="px-4 @7xl:px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Qty Available</th>
                                    <th
                                        class="px-4 @7xl:px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Last Restocked</th>
                                    <th
                                        class="px-4 @7xl:px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Status</th>
                                    <th
                                        class="px-4 @7xl:px-6 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="block @4xl:table-row-group divide-y divide-gray-200 dark:divide-neutral-700">
                                @forelse($this->materials as $index => $material)
                                    @php
                                        $lent  = $this->lentOut[$material->id] ?? 0;
                                        $isOut = $material->quantity_available <= 0;
                                        $isLow = $material->isLowStock();
                                    @endphp
                                    <tr wire:key="mat-{{ $material->id }}"
                                        class="block @4xl:table-row px-4 py-4 @4xl:p-0 bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:hover:bg-neutral-700">

                                        <td class="hidden @7xl:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $this->materials->firstItem() + $index }}
                                        </td>

                                        {{-- Material --}}
                                        <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 pb-2 @4xl:py-4 @4xl:whitespace-nowrap">
                                            <div class="flex items-center gap-x-3">
                                                <div
                                                    class="size-9 rounded-full bg-[#E8F3EC] dark:bg-[#1C6B45/25] flex items-center justify-center">
                                                    <svg class="size-4 text-[#1C6B45] dark:text-[#7FBF8E]"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path
                                                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    {{-- ✅ Name opens this material's Stock History --}}
                                                    <a href="{{ route('admin.inventory-stock-history', ['material' => $material->id]) }}"
                                                        title="See every change to {{ $material->resource_name }}"
                                                        class="block text-sm font-semibold text-gray-800 hover:text-[#1C6B45] hover:underline dark:text-neutral-200 dark:hover:text-green-300">
                                                        {{ $material->resource_name }}
                                                    </a>
                                                    @if (trim((string) $material->description) !== '' && strcasecmp(trim($material->description), trim($material->resource_name)) !== 0)
                                                        <span
                                                            class="block text-xs text-gray-500 dark:text-neutral-400 truncate max-w-[200px]">
                                                            {{ $material->description }}
                                                        </span>
                                                    @endif
                                                    {{-- mid-size screens: the Type column is hidden, show it here --}}
                                                    <span class="hidden @4xl:inline-flex @6xl:hidden mt-0.5 py-0.5 px-1.5 text-[10px] font-medium bg-gray-100 text-gray-700 rounded-full dark:bg-neutral-700 dark:text-neutral-300">
                                                        {{ $material->resourceType->type_name ?? 'N/A' }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Type --}}
                                        <td class="inline-block @4xl:hidden @6xl:table-cell px-0 @6xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                            <span
                                                class="py-1 px-2 inline-flex items-center text-xs font-medium bg-gray-100 text-gray-800 rounded-full dark:bg-neutral-700 dark:text-neutral-300">
                                                {{ $material->resourceType->type_name ?? 'N/A' }}
                                            </span>
                                        </td>

                                        {{-- Qty Available --}}
                                        <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                            <div class="flex items-center gap-x-2" title="Warns at {{ number_format($material->low_stock_at) }} or below">
                                                <span
                                                    class="text-sm font-semibold
                                                @if ($isOut) text-red-600 dark:text-red-400
                                                @elseif($isLow) text-amber-600 dark:text-amber-400
                                                @else text-[#1C6B45] dark:text-green-400 @endif">
                                                    {{ number_format($material->quantity_available) }}
                                                </span>
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">
                                                    {{ $material->unit ?? 'Ream' }}
                                                </span>
                                                @if ($isOut)
                                                    <span
                                                        class="py-0.5 px-1.5 text-[10px] font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-900 dark:text-red-400">Out
                                                        of stock</span>
                                                @elseif($isLow)
                                                    <span
                                                        class="py-0.5 px-1.5 text-[10px] font-medium bg-amber-100 text-amber-800 rounded-full dark:bg-amber-900 dark:text-amber-400">Low</span>
                                                @endif
                                            </div>
                                            {{-- ✅ Borrowed Material items still out with Program Heads --}}
                                            @if ($material->is_returnable)
                                                @php $kept = $this->assignedOut[$material->id] ?? 0; @endphp
                                                <p class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                                    @if ($lent > 0 || $kept > 0)
                                                        @if ($lent > 0)
                                                            <a href="{{ route('admin.borrowed-items') }}?filter=outstanding" class="text-[#1C6B45] dark:text-green-300 hover:underline">{{ number_format($lent) }} lent out</a> ·
                                                        @endif
                                                        @if ($kept > 0)
                                                            {{-- ✅ kept by teachers (assigned, no return date) --}}
                                                            <a href="{{ route('admin.borrowed-items') }}?filter=assigned" class="text-[#1C6B45] dark:text-green-300 hover:underline">{{ number_format($kept) }} assigned</a> ·
                                                        @endif
                                                        {{ number_format($material->quantity_available + $lent + $kept) }} owned
                                                    @else
                                                        none lent out
                                                    @endif
                                                </p>
                                            @endif
                                        </td>

                                        {{-- Last Restocked --}}
                                        <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                            <span class="@4xl:hidden text-xs text-gray-500 dark:text-neutral-400">Last restocked: </span>
                                            @if ($material->latestRestock)
                                                <span class="inline @4xl:block text-sm text-gray-800 dark:text-neutral-200">
                                                    {{ \Carbon\Carbon::parse($material->latestRestock->arrival_date)->format('M d, Y') }}
                                                </span>
                                                @if ($material->latestRestock->arrival_time)
                                                    <span class="inline @4xl:block text-xs text-gray-500 dark:text-neutral-400">
                                                        {{ \Carbon\Carbon::parse($material->latestRestock->arrival_time)->format('h:i A') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">Never
                                                    restocked</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="inline-block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 py-1 @4xl:py-4 @4xl:whitespace-nowrap">
                                            <span
                                                class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium rounded-full
                                                @if ($material->status === 'available') bg-[#E8F3EC] text-[#123524] dark:bg-[#1C6B45/25] dark:text-[#7FBF8E]
                                                @elseif($material->status === 'maintenance') bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-400
                                                @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-400 @endif">
                                                <span
                                                    class="size-1.5 rounded-full inline-block
                                                    @if ($material->status === 'available') bg-[#1C6B45]
                                                    @elseif($material->status === 'maintenance') bg-amber-500
                                                    @else bg-red-500 @endif">
                                                </span>
                                                {{ ucfirst($material->status) }}
                                            </span>
                                        </td>

                                        {{-- Actions: one line — [＋ Add | − Remove]  ✎  🗑 --}}
                                        <td class="block @4xl:table-cell px-0 @4xl:px-4 @7xl:px-6 pt-3 @4xl:py-4 @4xl:whitespace-nowrap @4xl:text-end">
                                            <div class="flex items-center @4xl:justify-end gap-1.5">

                                                {{-- ✅ Stock in / out as one pair --}}
                                                @canany(['stock-materials.create', 'stock-materials.update'])
                                                    <div class="inline-flex rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden divide-x divide-gray-200 dark:divide-neutral-700" role="group" aria-label="Stock of {{ $material->resource_name }}">
                                                        @can('stock-materials.create')
                                                            <button type="button" wire:click="openStockModal({{ $material->id }})"
                                                                title="Add stock (new delivery)"
                                                                class="h-8 px-2.5 inline-flex items-center gap-1 text-xs font-semibold text-[#1C6B45] bg-white hover:bg-green-50 dark:bg-neutral-800 dark:text-green-400 dark:hover:bg-[#1C6B45]/30 transition">
                                                                <svg class="shrink-0 size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path stroke-linecap="round" d="M12 5v14M5 12h14" />
                                                                </svg>
                                                                Add
                                                            </button>
                                                        @endcan
                                                        @can('stock-materials.update')
                                                            <button type="button" wire:click="openRemoveModal({{ $material->id }})" @disabled($isOut)
                                                                title="{{ $isOut ? 'Nothing in stock to remove' : 'Remove stock (used up, damaged, lost, count correction)' }}"
                                                                class="h-8 px-2.5 inline-flex items-center gap-1 text-xs font-semibold text-amber-700 bg-white hover:bg-amber-50 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:bg-white dark:bg-neutral-800 dark:text-amber-300 dark:hover:bg-amber-900/30 dark:disabled:hover:bg-neutral-800 transition">
                                                                <svg class="shrink-0 size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                                                                    <path stroke-linecap="round" d="M5 12h14" />
                                                                </svg>
                                                                Remove
                                                            </button>
                                                        @endcan
                                                    </div>
                                                @endcanany

                                                {{-- ✅ Edit — requires `stock-materials.update` --}}
                                                @can('stock-materials.update')
                                                    <button type="button" wire:click="openEditModal({{ $material->id }})"
                                                        title="Edit details, unit, warn level and status" aria-label="Edit {{ $material->resource_name }}"
                                                        class="h-8 px-2 @4xl:w-8 @4xl:px-0 inline-flex items-center justify-center gap-1 text-xs font-semibold rounded-lg text-[#1C6B45] hover:bg-[#F3F8F5] dark:text-[#7FBF8E] dark:hover:bg-[#1C6B45]/30 transition">
                                                        <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M12 20h9" />
                                                            <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                                        </svg>
                                                        <span class="@4xl:hidden">Edit</span>
                                                    </button>
                                                @endcan

                                                {{-- ✅ Delete — requires `stock-materials.delete` (blocked when the material is in use) --}}
                                                @can('stock-materials.delete')
                                                    <button type="button" wire:click="delete({{ $material->id }})"
                                                        wire:confirm="Delete {{ $material->resource_name }}? This cannot be undone. (Materials already used in requests or stock history cannot be deleted — set them to Unavailable instead.)"
                                                        title="Delete" aria-label="Delete {{ $material->resource_name }}"
                                                        class="h-8 px-2 @4xl:w-8 @4xl:px-0 inline-flex items-center justify-center gap-1 text-xs font-semibold rounded-lg text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 transition">
                                                        <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                            <path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        <span class="@4xl:hidden">Delete</span>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr class="block @4xl:table-row">
                                        <td colspan="7" class="block @4xl:table-cell px-6 py-10 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg class="size-10 text-gray-300 dark:text-neutral-600"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path
                                                        d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                                </svg>
                                                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No
                                                    materials found</p>

                                                {{-- ✅ Add First Material — requires `stock-materials.create` --}}
                                                @can('stock-materials.create')
                                                    <button wire:click="openAddModal"
                                                        class="mt-2 py-1.5 px-3 text-xs font-medium rounded-lg bg-[#123524] text-white hover:bg-[#0B2418]">
                                                        + Add First Material
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        {{-- Pagination --}}
                        @if ($this->materials->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                                {{ $this->materials->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- =====================================================================
         MODALS — teleported to <body> so they sit above the sidebar & header
         ===================================================================== --}}

    {{-- ============================ ADD STOCK MODAL ============================ --}}
    @can('stock-materials.create')
        @if ($showModal)
            @php
                $selectedRes = $resource_id ? collect($this->allResources)->firstWhere('id', $resource_id) : null;
                $addQty      = (int) ($quantity_added ?? 0);
            @endphp

            <div x-data="{ show: false }" x-init="$nextTick(() => show = true)">
                <template x-teleport="body">
                    <div @keydown.escape.window="$wire.closeModal()"
                        class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6">

                        {{-- Backdrop --}}
                        <div @click="$wire.closeModal()" x-show="show" x-transition.opacity.duration.150ms
                            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

                        {{-- Panel --}}
                        <div x-show="show" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="relative w-full max-w-md max-h-[92vh] flex flex-col bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                            {{-- Header --}}
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="size-10 shrink-0 inline-flex justify-center items-center rounded-full bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-green-400">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100 leading-tight">Add Stock</h3>
                                        <p class="text-xs text-gray-500 dark:text-neutral-400">Record a new delivery for an existing material</p>
                                    </div>
                                </div>
                                <button type="button" @click="$wire.closeModal()" title="Close (Esc)"
                                    class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition">
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Body --}}
                            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                                {{-- Material --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Material <span class="text-red-500">*</span>
                                    </label>
                                    <select wire:model.live="resource_id" class="{{ $input }}">
                                        <option value="">-- Select Material --</option>
                                        @foreach ($this->allResources as $res)
                                            <option value="{{ $res->id }}" {{ $resource_id == $res->id ? 'selected' : '' }}>
                                                {{ $res->resource_name }} ({{ $res->quantity_available }} {{ $res->unit ?? 'Ream' }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('resource_id')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Current stock → new total --}}
                                @if ($selectedRes)
                                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/40 p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Current stock</p>
                                                <p class="text-lg font-semibold text-gray-800 dark:text-neutral-100 leading-tight">
                                                    {{ number_format($selectedRes->quantity_available) }}
                                                    <span class="text-xs font-normal text-gray-400 dark:text-neutral-500">{{ $selectedRes->unit ?? 'Ream' }}</span>
                                                </p>
                                            </div>

                                            <svg class="size-5 shrink-0 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>

                                            <div class="text-right min-w-0">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">After restock</p>
                                                <p class="text-lg font-semibold leading-tight {{ $addQty > 0 ? 'text-[#1C6B45] dark:text-green-400' : 'text-gray-800 dark:text-neutral-100' }}">
                                                    {{ number_format($selectedRes->quantity_available + max($addQty, 0)) }}
                                                    <span class="text-xs font-normal text-gray-400 dark:text-neutral-500">{{ $selectedRes->unit ?? 'Ream' }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Quantity --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Quantity to Add <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model.live.debounce.300ms="quantity_added" min="1" placeholder="0"
                                        class="{{ $input }}">

                                    <div class="flex flex-wrap gap-2 mt-2">
                                        @foreach ([5, 10, 25, 50, 100] as $q)
                                            <button type="button" @click="$wire.set('quantity_added', {{ $q }})"
                                                class="px-2.5 py-1 text-xs font-medium rounded-full border border-gray-200 dark:border-neutral-600 text-gray-600 dark:text-neutral-300 hover:bg-green-50 hover:border-green-300 hover:text-[#1C6B45] dark:hover:bg-[#1C6B45]/20 dark:hover:text-green-400 transition">
                                                +{{ $q }}
                                            </button>
                                        @endforeach
                                    </div>

                                    @error('quantity_added')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Supplier --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Supplier</label>
                                    <input type="text" wire:model="supplier" placeholder="e.g. ABC Trading, National Bookstore"
                                        class="{{ $input }}">
                                    @error('supplier')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Remarks --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Remarks</label>
                                    <textarea wire:model="remarks" rows="2" placeholder="Optional notes..."
                                        class="{{ $input }}"></textarea>
                                    @error('remarks')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="px-6 py-3.5 border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/40 flex justify-end gap-3">
                                <button type="button" @click="$wire.closeModal()"
                                    class="py-2 px-4 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 transition">
                                    Cancel
                                </button>
                                <button type="button" wire:click="addStock" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="py-2 px-5 text-sm font-medium rounded-lg border border-transparent bg-[#123524] text-white hover:bg-[#0B2418] shadow-sm transition">
                                    <span wire:loading.remove wire:target="addStock">Add Stock</span>
                                    <span wire:loading wire:target="addStock">Adding...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    @endcan

    {{-- ============================ REMOVE STOCK MODAL ============================ --}}
    @can('stock-materials.update')
        @if ($showRemoveModal)
            @php
                $rmRes = $remove_id ? collect($this->allResources)->firstWhere('id', $remove_id) : null;
                $rmQty = (int) ($remove_qty ?? 0);
                $rmUnit = $rmRes->unit ?? 'Ream';
            @endphp

            <div x-data="{ show: false }" x-init="$nextTick(() => show = true)">
                <template x-teleport="body">
                    <div @keydown.escape.window="$wire.closeModal()"
                        class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6">

                        <div @click="$wire.closeModal()" x-show="show" x-transition.opacity.duration.150ms
                            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

                        <div x-show="show" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="relative w-full max-w-md max-h-[92vh] flex flex-col bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                            {{-- Header --}}
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="size-10 shrink-0 inline-flex justify-center items-center rounded-full bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100 leading-tight">Remove Stock</h3>
                                        <p class="text-xs text-gray-500 dark:text-neutral-400 truncate">{{ $rmRes->resource_name ?? '' }} — recorded in Stock History</p>
                                    </div>
                                </div>
                                <button type="button" @click="$wire.closeModal()" title="Close (Esc)"
                                    class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition">
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Body --}}
                            <div class="flex-1 overflow-y-auto p-6 space-y-5">
                                @if ($rmRes)
                                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/40 p-4">
                                        <div class="flex items-center justify-between gap-3">
                                            <div>
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Current stock</p>
                                                <p class="text-lg font-semibold text-gray-800 dark:text-neutral-100 leading-tight">
                                                    {{ number_format($rmRes->quantity_available) }}
                                                    <span class="text-xs font-normal text-gray-400 dark:text-neutral-500">{{ $rmUnit }}</span>
                                                </p>
                                            </div>
                                            <svg class="size-5 shrink-0 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                            </svg>
                                            <div class="text-right">
                                                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">After removing</p>
                                                <p class="text-lg font-semibold leading-tight {{ $rmQty > $rmRes->quantity_available ? 'text-red-600 dark:text-red-400' : ($rmQty > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-800 dark:text-neutral-100') }}">
                                                    {{ $rmQty > $rmRes->quantity_available ? 'Not enough' : number_format($rmRes->quantity_available - max($rmQty, 0)) }}
                                                    <span class="text-xs font-normal text-gray-400 dark:text-neutral-500">{{ $rmUnit }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Quantity --}}
                                <div>
                                    <label for="remove-qty" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        How many to remove <span class="text-red-500">*</span>
                                    </label>
                                    <input id="remove-qty" type="number" wire:model.live.debounce.300ms="remove_qty" min="1"
                                        max="{{ $rmRes->quantity_available ?? '' }}" class="{{ $input }}">
                                    @error('remove_qty')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Reason --}}
                                <div>
                                    <p class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Reason <span class="text-red-500">*</span>
                                    </p>
                                    <div class="grid grid-cols-2 gap-2">
                                        @foreach (\App\Models\Stock::REASONS as $value => $label)
                                            <label class="cursor-pointer">
                                                <input type="radio" wire:model.live="remove_reason" value="{{ $value }}" class="peer sr-only">
                                                <div class="text-center py-2.5 text-sm font-medium rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-600 dark:text-neutral-300 bg-white dark:bg-neutral-900 peer-checked:bg-amber-600 peer-checked:border-amber-600 peer-checked:text-white transition">
                                                    {{ $label }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('remove_reason')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Notes --}}
                                <div>
                                    <label for="remove-notes" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Notes @if ($remove_reason === 'correction')<span class="text-red-500">*</span>@endif
                                    </label>
                                    <textarea id="remove-notes" wire:model="remove_notes" rows="2" maxlength="255"
                                        placeholder="{{ $remove_reason === 'correction' ? 'e.g. Counted on Nov 27 — only 180 on the shelf' : 'Optional — e.g. used for the Foundation Day program' }}"
                                        class="{{ $input }}"></textarea>
                                    @error('remove_notes')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="px-6 py-3.5 border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/40 flex justify-end gap-3">
                                <button type="button" @click="$wire.closeModal()"
                                    class="py-2 px-4 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 transition">
                                    Cancel
                                </button>
                                <button type="button" wire:click="removeStock" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="py-2 px-5 text-sm font-medium rounded-lg border border-transparent bg-amber-600 text-white hover:bg-amber-700 shadow-sm transition">
                                    <span wire:loading.remove wire:target="removeStock">Remove Stock</span>
                                    <span wire:loading wire:target="removeStock">Removing...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    @endcan

    {{-- =========================== ADD MATERIAL MODAL =========================== --}}
    @can('stock-materials.create')
        @if ($showAddModal)
            <div x-data="{ show: false }" x-init="$nextTick(() => show = true)">
                <template x-teleport="body">
                    <div @keydown.escape.window="$wire.closeModal()"
                        class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6">

                        {{-- Backdrop --}}
                        <div @click="$wire.closeModal()" x-show="show" x-transition.opacity.duration.150ms
                            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

                        {{-- Panel --}}
                        <div x-show="show" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="relative w-full max-w-lg max-h-[92vh] flex flex-col bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                            {{-- Header --}}
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="size-10 shrink-0 inline-flex justify-center items-center rounded-full bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-[#7FBF8E]">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100 leading-tight">Add New Material</h3>
                                        <p class="text-xs text-gray-500 dark:text-neutral-400">Register a new item in your inventory</p>
                                    </div>
                                </div>
                                <button type="button" @click="$wire.closeModal()" title="Close (Esc)"
                                    class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition">
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Body --}}
                            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                                {{-- Material Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Material Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="resource_name"
                                        placeholder="e.g. Bond Paper, Ballpen, Ruler, Photo Paper" class="{{ $input }}">
                                    @error('resource_name')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Type <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid sm:grid-cols-2 gap-2">
                                        <label class="flex items-start gap-2.5 p-3 rounded-lg border cursor-pointer transition border-gray-200 dark:border-neutral-700 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-[#1C6B45]/20">
                                            <input type="radio" wire:model="type_name" value="Supplies" class="mt-0.5 size-4 text-[#1C6B45]">
                                            <span>
                                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">Supplies</span>
                                                <span class="block text-xs text-gray-500 dark:text-neutral-400">Used up, never returned — bond paper, markers…</span>
                                            </span>
                                        </label>
                                        <label class="flex items-start gap-2.5 p-3 rounded-lg border cursor-pointer transition border-gray-200 dark:border-neutral-700 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-[#1C6B45]/20">
                                            <input type="radio" wire:model="type_name" value="Material" class="mt-0.5 size-4 text-[#1C6B45]">
                                            <span>
                                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">Material</span>
                                                <span class="block text-xs text-gray-500 dark:text-neutral-400">Borrowed and must be returned — chairs, projectors…</span>
                                            </span>
                                        </label>
                                    </div>

                                    @error('type_name')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Unit + Initial Quantity --}}
                                <div class="grid sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                            Unit <span class="text-red-500">*</span>
                                        </label>
                                        <div class="grid grid-cols-3 gap-2">
                                            @foreach (['Ream', 'Set', 'Pcs'] as $u)
                                                <label class="cursor-pointer">
                                                    <input type="radio" wire:model="unit" value="{{ $u }}"
                                                        class="peer sr-only">
                                                    <div
                                                        class="text-center py-2.5 text-sm font-medium rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-600 dark:text-neutral-300 bg-white dark:bg-neutral-900 peer-checked:bg-[#123524] peer-checked:border-[#1C6B45] peer-checked:text-white transition">
                                                        {{ $u }}
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                        @error('unit')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                            Initial Quantity <span class="text-red-500">*</span>
                                        </label>
                                        <input type="number" wire:model="initial_quantity" min="0" placeholder="0"
                                            class="{{ $input }}">
                                        @error('initial_quantity')
                                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>

                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Warn me when stock is at or below <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="low_stock_at" min="0" class="{{ $input }}">
                                    <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">Shown as "Low" and counted in the Low Stock card. 0 = never warn.</p>
                                    @error('low_stock_at')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Supplier --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Supplier</label>
                                    <input type="text" wire:model="material_supplier"
                                        placeholder="e.g. ABC Trading, National Bookstore" class="{{ $input }}">
                                    @error('material_supplier')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>


                            </div>

                            {{-- Footer --}}
                            <div class="px-6 py-3.5 border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/40 flex justify-end gap-3">
                                <button type="button" @click="$wire.closeModal()"
                                    class="py-2 px-4 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 transition">
                                    Cancel
                                </button>
                                <button type="button" wire:click="addMaterial" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="py-2 px-5 text-sm font-medium rounded-lg border border-transparent bg-[#123524] text-white hover:bg-[#0B2418] shadow-sm transition">
                                    <span wire:loading.remove wire:target="addMaterial">Save Material</span>
                                    <span wire:loading wire:target="addMaterial">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    @endcan

    {{-- ========================== EDIT MATERIAL MODAL ========================== --}}
    @can('stock-materials.update')
        @if ($showEditModal)
            <div x-data="{ show: false }" x-init="$nextTick(() => show = true)">
                <template x-teleport="body">
                    <div @keydown.escape.window="$wire.closeModal()"
                        class="fixed inset-0 z-[100] flex items-center justify-center p-3 sm:p-6">

                        {{-- Backdrop --}}
                        <div @click="$wire.closeModal()" x-show="show" x-transition.opacity.duration.150ms
                            class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>

                        {{-- Panel --}}
                        <div x-show="show" x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            class="relative w-full max-w-lg max-h-[92vh] flex flex-col bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                            {{-- Header --}}
                            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3 min-w-0">
                                    <span class="size-10 shrink-0 inline-flex justify-center items-center rounded-full bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-[#7FBF8E]">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z" />
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-100 leading-tight">Edit Material</h3>
                                        <p class="text-xs text-gray-500 dark:text-neutral-400">Update details, unit and availability</p>
                                    </div>
                                </div>
                                <button type="button" @click="$wire.closeModal()" title="Close (Esc)"
                                    class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition">
                                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            {{-- Body --}}
                            <div class="flex-1 overflow-y-auto p-6 space-y-5">

                                {{-- Material Name --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Material Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" wire:model="edit_resource_name" placeholder="e.g. Bond Paper A4"
                                        class="{{ $input }}">
                                    @error('edit_resource_name')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Description --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">Description</label>
                                    <textarea wire:model="edit_description" rows="2" placeholder="Optional description..."
                                        class="{{ $input }}"></textarea>
                                    @error('edit_description')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Type --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Type <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid sm:grid-cols-2 gap-2">
                                        <label class="flex items-start gap-2.5 p-3 rounded-lg border cursor-pointer transition border-gray-200 dark:border-neutral-700 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-[#1C6B45]/20">
                                            <input type="radio" wire:model="edit_type_name" value="Supplies" class="mt-0.5 size-4 text-[#1C6B45]">
                                            <span>
                                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">Supplies</span>
                                                <span class="block text-xs text-gray-500 dark:text-neutral-400">Used up, never returned — bond paper, markers…</span>
                                            </span>
                                        </label>
                                        <label class="flex items-start gap-2.5 p-3 rounded-lg border cursor-pointer transition border-gray-200 dark:border-neutral-700 has-[:checked]:border-green-500 has-[:checked]:bg-green-50 dark:has-[:checked]:bg-[#1C6B45]/20">
                                            <input type="radio" wire:model="edit_type_name" value="Material" class="mt-0.5 size-4 text-[#1C6B45]">
                                            <span>
                                                <span class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">Material</span>
                                                <span class="block text-xs text-gray-500 dark:text-neutral-400">Borrowed and must be returned — chairs, projectors…</span>
                                            </span>
                                        </label>
                                    </div>

                                    @error('edit_type_name')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Unit --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Unit <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach (['Ream', 'Set', 'Pcs'] as $u)
                                            <label class="cursor-pointer">
                                                <input type="radio" wire:model="edit_unit" value="{{ $u }}"
                                                    class="peer sr-only">
                                                <div
                                                    class="text-center py-2.5 text-sm font-medium rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-600 dark:text-neutral-300 bg-white dark:bg-neutral-900 peer-checked:bg-[#123524] peer-checked:border-[#1C6B45] peer-checked:text-white transition">
                                                    {{ $u }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('edit_unit')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>


                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Warn me when stock is at or below <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" wire:model="edit_low_stock_at" min="0" class="{{ $input }}">
                                    <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">Shown as "Low" and counted in the Low Stock card. 0 = never warn.</p>
                                    @error('edit_low_stock_at')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                {{-- Status --}}
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                        Status <span class="text-red-500">*</span>
                                    </label>
                                    <div class="grid grid-cols-3 gap-2">
                                        @php
                                            $statusOptions = [
                                                'available'   => ['Available',   'peer-checked:bg-[#123524] peer-checked:border-[#1C6B45]'],
                                                'unavailable' => ['Unavailable', 'peer-checked:bg-red-600 peer-checked:border-red-600'],
                                                'maintenance' => ['Maintenance', 'peer-checked:bg-amber-500 peer-checked:border-amber-500'],
                                            ];
                                        @endphp
                                        @foreach ($statusOptions as $value => [$label, $checkedClasses])
                                            <label class="cursor-pointer">
                                                <input type="radio" wire:model="edit_status" value="{{ $value }}"
                                                    class="peer sr-only">
                                                <div
                                                    class="text-center py-2.5 text-sm font-medium rounded-lg border border-gray-200 dark:border-neutral-700 text-gray-600 dark:text-neutral-300 bg-white dark:bg-neutral-900 peer-checked:text-white {{ $checkedClasses }} transition">
                                                    {{ $label }}
                                                </div>
                                            </label>
                                        @endforeach
                                    </div>
                                    @error('edit_status')
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>



                                {{-- Note --}}
                                <div class="flex items-start gap-2.5 rounded-lg border border-[#1C6B45/25] dark:border-[#1C6B45]/50 bg-[#F3F8F5] dark:bg-[#1C6B45]/20 px-3.5 py-3">
                                    <svg class="size-4 shrink-0 mt-0.5 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <circle cx="12" cy="12" r="10" />
                                        <path stroke-linecap="round" d="M12 16v-4m0-4h.01" />
                                    </svg>
                                    <p class="text-xs text-[#123524] dark:text-[#9FD3AE]">
                                        Quantity is not edited here. Use <strong>Add Stock</strong> or <strong>Remove</strong> so every change is recorded in Stock History.
                                    </p>
                                </div>
                            </div>

                            {{-- Footer --}}
                            <div class="px-6 py-3.5 border-t border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900/40 flex justify-end gap-3">
                                <button type="button" @click="$wire.closeModal()"
                                    class="py-2 px-4 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700 transition">
                                    Cancel
                                </button>
                                <button type="button" wire:click="updateMaterial" wire:loading.attr="disabled"
                                    wire:loading.class="opacity-50 cursor-not-allowed"
                                    class="py-2 px-5 text-sm font-medium rounded-lg border border-transparent bg-[#123524] text-white hover:bg-[#0B2418] shadow-sm transition">
                                    <span wire:loading.remove wire:target="updateMaterial">Save Changes</span>
                                    <span wire:loading wire:target="updateMaterial">Saving...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        @endif
    @endcan

</div>
