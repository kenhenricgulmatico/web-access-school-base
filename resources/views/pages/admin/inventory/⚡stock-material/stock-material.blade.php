<div>
    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto space-y-6">

        {{-- Flash --}}
        @if (session()->has('success'))
            <div
                class="p-4 bg-teal-100 border border-teal-200 text-teal-800 rounded-xl text-sm font-medium dark:bg-teal-800/30 dark:border-teal-900 dark:text-teal-500">
                {{ session('success') }}
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800 dark:text-neutral-200">Stock Materials</h2>
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
                <button wire:click="$set('showAddModal', true)"
                    class="py-2 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-green-600 text-white hover:bg-green-700">
                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                        stroke-width="2" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14" />
                    </svg>
                    Add Material
                </button>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">

            <div
                class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
                <div class="flex items-center gap-x-2 mb-3">
                    <span
                        class="size-8 inline-flex justify-center items-center rounded-full border-4 border-blue-50 bg-blue-100 text-blue-800 dark:border-blue-900 dark:bg-blue-800 dark:text-blue-400">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path
                                d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                        </svg>
                    </span>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Total Materials</p>
                </div>
                <h3 class="text-2xl font-medium text-gray-800 dark:text-neutral-200">{{ $this->totalMaterials }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">All items</p>
            </div>

            <div
                class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
                <div class="flex items-center gap-x-2 mb-3">
                    <span
                        class="size-8 inline-flex justify-center items-center rounded-full border-4 border-green-50 bg-green-100 text-green-800 dark:border-green-900 dark:bg-green-800 dark:text-green-400">
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

            <div
                class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
                <div class="flex items-center gap-x-2 mb-3">
                    <span
                        class="size-8 inline-flex justify-center items-center rounded-full border-4 border-yellow-50 bg-yellow-100 text-yellow-800 dark:border-yellow-900 dark:bg-yellow-800 dark:text-yellow-400">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path
                                d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                        </svg>
                    </span>
                    <p class="text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-500">Low Stock</p>
                </div>
                <h3 class="text-2xl font-medium text-gray-800 dark:text-neutral-200">{{ $this->lowStock }}</h3>
                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">≤ 5 units left</p>
            </div>

            <div
                class="flex flex-col bg-white border border-gray-200 shadow-sm rounded-xl p-4 md:p-5 dark:bg-neutral-800 dark:border-neutral-700">
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
                <p class="mt-1 text-xs text-gray-500 dark:text-neutral-500">Zero units</p>
            </div>

        </div>

        {{-- Table --}}
        <div class="flex flex-col">
            <div class="-m-1.5 overflow-x-auto">
                <div class="p-1.5 min-w-full inline-block align-middle">
                    <div
                        class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

                        {{-- Table Header --}}
                        <div
                            class="px-6 py-4 grid gap-3 md:flex md:justify-between md:items-center border-b border-gray-200 dark:border-neutral-700">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Materials</h2>
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
                                    <input type="text" wire:model.live="search" placeholder="Search material..."
                                        class="py-2 ps-9 pe-4 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                                </div>
                                <select wire:model.live="statusFilter"
                                    class="py-2 px-3 block border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                    <option value="">All Status</option>
                                    <option value="available">Available</option>
                                    <option value="unavailable">Unavailable</option>
                                    <option value="maintenance">Maintenance</option>
                                </select>
                            </div>
                        </div>

                        {{-- Table Body --}}
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        #</th>
                                    <th
                                        class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Material</th>
                                    <th
                                        class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Type</th>
                                    <th
                                        class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Qty Available</th>
                                    <th
                                        class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Last Restocked</th>
                                    <th
                                        class="px-6 py-3 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-end text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                        Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @forelse($this->materials as $index => $material)
                                    <tr
                                        class="bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:hover:bg-neutral-700">

                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $this->materials->firstItem() + $index }}
                                        </td>

                                        {{-- Material --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-x-3">
                                                <div
                                                    class="size-9 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center">
                                                    <svg class="size-4 text-blue-600 dark:text-blue-400"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path
                                                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                                    </svg>
                                                </div>
                                                <div>
                                                    <span
                                                        class="block text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                                        {{ $material->resource_name }}
                                                    </span>
                                                    <span
                                                        class="block text-xs text-gray-500 dark:text-neutral-400 truncate max-w-[200px]">
                                                        {{ $material->description }}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>

                                        {{-- Type --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span
                                                class="py-1 px-2 inline-flex items-center text-xs font-medium bg-gray-100 text-gray-800 rounded-full dark:bg-neutral-700 dark:text-neutral-300">
                                                {{ $material->resourceType->type_name ?? 'N/A' }}
                                            </span>
                                        </td>

                                        {{-- Qty Available --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center gap-x-2">
                                                <span
                                                    class="text-sm font-semibold
                                                @if ($material->quantity_available == 0) text-red-600 dark:text-red-400
                                                @elseif($material->quantity_available <= 5) text-yellow-600 dark:text-yellow-400
                                                @else text-green-600 dark:text-green-400 @endif">
                                                    {{ number_format($material->quantity_available) }}
                                                </span>
                                                @if ($material->quantity_available == 0)
                                                    <span
                                                        class="py-0.5 px-1.5 text-[10px] font-medium bg-red-100 text-red-800 rounded-full dark:bg-red-900 dark:text-red-400">Out
                                                        of order</span>
                                                @elseif($material->quantity_available <= 5)
                                                    <span
                                                        class="py-0.5 px-1.5 text-[10px] font-medium bg-yellow-100 text-yellow-800 rounded-full dark:bg-yellow-900 dark:text-yellow-400">Low</span>
                                                @endif
                                            </div>
                                        </td>

                                        {{-- Last Restocked --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if ($material->latestStock)
                                                <span class="block text-sm text-gray-800 dark:text-neutral-200">
                                                    {{ \Carbon\Carbon::parse($material->latestStock->arrival_date)->format('M d, Y') }}
                                                </span>
                                                @if ($material->latestStock->arrival_time)
                                                    <span class="block text-xs text-gray-500 dark:text-neutral-400">
                                                        {{ \Carbon\Carbon::parse($material->latestStock->arrival_time)->format('h:i A') }}
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">Never
                                                    restocked</span>
                                            @endif
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @php
                                                $hasSupplier =
                                                    $material->latestStock && !empty($material->latestStock->supplier);
                                                $displayStatus = $hasSupplier ? $material->status : 'unavailable';
                                            @endphp
                                            <span
                                                class="py-1 px-2 inline-flex items-center gap-x-1 text-xs font-medium rounded-full
        @if ($displayStatus === 'available') bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-400
        @elseif($displayStatus === 'maintenance') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-400
        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-400 @endif">
                                                <span
                                                    class="size-1.5 rounded-full inline-block
            @if ($displayStatus === 'available') bg-teal-500
            @elseif($displayStatus === 'maintenance') bg-yellow-500
            @else bg-red-500 @endif">
                                                </span>
                                                {{ ucfirst($displayStatus) }}
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-end">
                                            <div class="flex items-center justify-end gap-x-1">
                                                <button wire:click="openStockModal({{ $material->id }})"
                                                    class="py-1.5 px-3 inline-flex items-center gap-x-1.5 text-xs font-medium rounded-lg border border-transparent bg-green-100 text-green-800 hover:bg-green-200 dark:bg-green-900 dark:text-green-400 dark:hover:bg-green-800">
                                                    <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path d="M12 5v14M5 12h14" />
                                                    </svg>
                                                    Add Stock
                                                </button>
                                                <button wire:click="delete({{ $material->id }})"
                                                    wire:confirm="Delete this material? This cannot be undone."
                                                    class="py-1.5 px-2 inline-flex items-center gap-x-1 text-xs font-medium rounded-lg border border-transparent text-red-500 hover:bg-red-100 dark:hover:bg-red-900 dark:text-red-400">
                                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg"
                                                        fill="none" stroke="currentColor" stroke-width="2"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-10 text-center">
                                            <div class="flex flex-col items-center gap-2">
                                                <svg class="size-10 text-gray-300 dark:text-neutral-600"
                                                    xmlns="http://www.w3.org/2000/svg" fill="none"
                                                    stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path
                                                        d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z" />
                                                </svg>
                                                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No
                                                    materials found</p>
                                                <button wire:click="$set('showAddModal', true)"
                                                    class="mt-2 py-1.5 px-3 text-xs font-medium rounded-lg bg-green-600 text-white hover:bg-green-700">
                                                    + Add First Material
                                                </button>
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

    {{-- Add Stock Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50 dark:bg-neutral-900/80" wire:click="closeModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-sm dark:bg-neutral-800 p-6">

                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">Add Stock</h3>
                        <button wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">

                        {{-- Resource --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Material <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="resource_id"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                <option value="">-- Select Material --</option>
                                @foreach ($this->allResources as $res)
                                    <option value="{{ $res->id }}"
                                        {{ $resource_id == $res->id ? 'selected' : '' }}>
                                        {{ $res->resource_name }} ({{ $res->quantity_available }} units)
                                    </option>
                                @endforeach
                            </select>
                            @error('resource_id')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Quantity to Add <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model="quantity_added" min="1" autofocus
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            @error('quantity_added')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Supplier --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Supplier
                            </label>
                            <input type="text" wire:model="supplier"
                                placeholder="e.g. ABC Trading, National Bookstore"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            @error('supplier')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Unit Price --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Unit Price
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                    <span class="text-gray-500 text-sm dark:text-neutral-400">₱</span>
                                </div>
                                <input type="number" wire:model="unit_price" min="0" step="0.01"
                                    placeholder="0.00"
                                    class="py-2 ps-7 pe-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            </div>
                            @error('unit_price')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Remarks --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Remarks
                            </label>
                            <textarea wire:model="remarks" rows="2" placeholder="Optional notes..."
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400"></textarea>
                            @error('remarks')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="closeModal"
                            class="py-2 px-4 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700">
                            Cancel
                        </button>
                        <button wire:click="addStock" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="py-2 px-4 text-sm font-medium rounded-lg border border-transparent bg-green-600 text-white hover:bg-green-700">
                            <span wire:loading.remove wire:target="addStock">Add Stock</span>
                            <span wire:loading wire:target="addStock">Adding...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

    {{-- Add Material Modal --}}
    @if ($showAddModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="fixed inset-0 bg-gray-900/50 dark:bg-neutral-900/80" wire:click="closeModal"></div>
                <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg dark:bg-neutral-800 p-6">

                    <div class="flex items-center justify-between mb-5">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">Add New Material</h3>
                        <button wire:click="closeModal"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300">
                            <svg class="size-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M18 6 6 18M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="space-y-4">

                        {{-- Material Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Material Name <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="resource_name"
                                placeholder="e.g. Bond Paper, Ballpen, Ruler, Photo Paper"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            @error('resource_name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Description --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Description <span class="text-red-500">*</span>
                            </label>
                            <textarea wire:model="description" rows="2" placeholder="Brief description of the material..."
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400"></textarea>
                            @error('description')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Type --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Type <span class="text-red-500">*</span>
                            </label>
                            <input type="text" wire:model="type_name"
                                placeholder="e.g. Paper Supplies, Writing Materials, Art Supplies"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            @error('type_name')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Initial Quantity --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Initial Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model="initial_quantity" min="0" placeholder="0"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            @error('initial_quantity')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Supplier --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Supplier
                            </label>
                            <input type="text" wire:model="material_supplier"
                                placeholder="e.g. ABC Trading, National Bookstore"
                                class="py-2 px-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            @error('material_supplier')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Unit Price --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                                Unit Price
                            </label>
                            <div class="relative">
                                <div class="absolute inset-y-0 start-0 flex items-center pointer-events-none ps-3">
                                    <span class="text-gray-500 text-sm dark:text-neutral-400">₱</span>
                                </div>
                                <input type="number" wire:model="material_unit_price" min="0" step="0.01"
                                    placeholder="0.00"
                                    class="py-2 ps-7 pe-3 block w-full border border-gray-200 rounded-lg text-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                            </div>
                            @error('material_unit_price')
                                <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>

                    <div class="flex justify-end gap-3 mt-6">
                        <button wire:click="closeModal"
                            class="py-2 px-4 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-white dark:hover:bg-neutral-700">
                            Cancel
                        </button>
                        <button wire:click="addMaterial" wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="py-2 px-4 text-sm font-medium rounded-lg border border-transparent bg-green-600 text-white hover:bg-green-700">
                            <span wire:loading.remove wire:target="addMaterial">Save Material</span>
                            <span wire:loading wire:target="addMaterial">Saving...</span>
                        </button>
                    </div>

                </div>
            </div>
        </div>
    @endif

</div>
