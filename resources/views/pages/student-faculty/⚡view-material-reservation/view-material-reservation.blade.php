<div>
<div class="max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

    <a href="{{ $isFacility ? route('portal.reservation') : route('portal.material') }}" wire:navigate
       class="inline-flex items-center gap-1 mb-3 text-xs font-semibold text-[#1C6B45] dark:text-[#7FBF8E] hover:underline">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back to My {{ $isFacility ? 'Facility Reservations' : 'Material Requests' }}
    </a>

    @if (session()->has('message'))
        <div class="mb-4 p-3 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800 text-sm">
            {{ session('message') }}
        </div>
    @endif
    @if (session()->has('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-2xl shadow-sm overflow-hidden">

        @php
            $status = $requestModel->status;
            $facilityItem  = $requestModel->items->firstWhere('resource_id', null);
            $materialItems = $requestModel->items->whereNotNull('resource_id');
        @endphp

        {{-- Header --}}
        <div class="px-6 py-5 flex justify-between items-start gap-4 border-b border-gray-100 dark:border-gray-700">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">
                    {{ $isFacility ? 'Facility reservation' : 'Material request' }}
                    <span class="font-normal text-gray-400 dark:text-gray-500">#{{ $requestModel->id }}</span>
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Submitted {{ $requestModel->created_at->format('M d, Y · h:i A') }}
                </p>
            </div>

            <x-portal.status class="shrink-0" :status="$status" />
        </div>

        <div class="px-6 py-6 space-y-6">

            {{-- Rejection reason panel (only when rejected) --}}
            @if ($status === 'rejected' && $this->rejection?->remarks)
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-left dark:border-red-900/50 dark:bg-red-900/20">
                    <div class="flex items-start gap-3">
                        <div class="shrink-0 w-9 h-9 rounded-full bg-red-100 dark:bg-red-900/40 flex items-center justify-center">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-red-700 dark:text-red-400">
                                Rejection Reason
                            </p>
                            <p class="mt-1 whitespace-pre-line break-words text-sm leading-relaxed text-red-900 dark:text-red-200">{{ trim($this->rejection->remarks) }}</p>
                            @if ($this->rejection->approver)
                                <p class="text-[11px] text-red-600/70 dark:text-red-400/70 mt-2">
                                    Rejected by <strong>{{ $this->rejection->approver->name }}</strong>
                                    @if ($this->rejection->approved_at)
                                        · {{ \Carbon\Carbon::parse($this->rejection->approved_at)->diffForHumans() }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            {{-- Facility schedule (shown once, only for reservations) --}}
            @if($isFacility && $facilityItem)
                <div>
                    <p class="text-xs font-semibold text-[#1C6B45]/80 dark:text-[#7FBF8E] uppercase tracking-wide mb-2">Facility / Schedule</p>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg px-4 py-3">
                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $facilityItem->item_name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            {{ \Illuminate\Support\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}
                            @if ($facilityItem->start_time)
                                · {{ \Illuminate\Support\Carbon::parse($facilityItem->start_time)->format('h:i A') }}
                                – {{ \Illuminate\Support\Carbon::parse($facilityItem->end_time)->format('h:i A') }}
                            @endif
                        </p>
                    </div>
                </div>
            @endif

            {{-- Materials (with quantity, no repeated date/time) --}}
            @if($materialItems->isNotEmpty())
                <div>
                    <p class="text-xs font-semibold text-[#1C6B45]/80 dark:text-[#7FBF8E] uppercase tracking-wide mb-2">
                        {{ $isFacility ? 'Materials Requested' : 'Items' }}
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($materialItems as $item)
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item->item_name }}</p>
                                <span class="text-xs font-semibold text-[#1C6B45] bg-[#E8F3EC] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE] px-2 py-0.5 rounded-full">x{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Plain material request (not a facility reservation) with no facility item at all --}}
            @if(!$isFacility && $materialItems->isEmpty() && $requestModel->items->isNotEmpty())
                <div>
                    <p class="text-xs font-semibold text-[#1C6B45]/80 dark:text-[#7FBF8E] uppercase tracking-wide mb-2">Items</p>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($requestModel->items as $item)
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item->item_name }}</p>
                                <span class="text-xs font-semibold text-[#1C6B45] bg-[#E8F3EC] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE] px-2 py-0.5 rounded-full">x{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <p class="text-xs font-semibold text-[#1C6B45]/80 dark:text-[#7FBF8E] uppercase tracking-wide mb-1">Purpose</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $requestModel->purpose }}</p>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            @if($status === 'pending')
                <a href="{{ $isFacility ? route('portal.edit-reservation', $requestModel->id) : route('portal.edit-material', $requestModel->id) }}" wire:navigate
                   class="px-4 py-2 text-sm font-semibold bg-white dark:bg-gray-800 text-[#123524] dark:text-white border border-[#1C6B45]/30 dark:border-neutral-600 rounded-lg hover:bg-[#F3F8F5] dark:hover:bg-neutral-700 transition">
                    Edit
                </a>
                <button wire:click="cancelRequest"
                        wire:confirm="Cancel this request?"
                        class="px-4 py-2 text-sm font-semibold bg-white dark:bg-gray-800 text-red-600 dark:text-red-300 border border-red-200 dark:border-red-900 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition">
                    Cancel
                </button>
            @elseif($status === 'cancelled')
                <button wire:click="deleteRequest"
                        wire:confirm="Permanently delete this cancelled request?"
                        class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Delete
                </button>
            @endif
        </div>

    </div>
</div>
</div>
