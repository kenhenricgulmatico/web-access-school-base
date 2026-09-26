<div class="select-none">
<div class="max-w-3xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">

    <a href="{{ $isFacility ? route('portal.reservation') : route('portal.material') }}" wire:navigate
       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 transition mb-5">
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

<div class="px-6 py-5 flex justify-between items-start gap-4 border-b border-gray-100 dark:border-gray-700">
    <div>
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
            {{ $isFacility ? 'Reservation' : 'Request' }}
        </p>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            Submitted {{ $requestModel->created_at->format('M d, Y · h:i A') }}
        </p>
    </div>

    <span class="shrink-0 px-2.5 py-1 text-xs font-medium rounded-full
        {{ $status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : '' }}
        {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' : '' }}
        {{ $status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : '' }}
        {{ $status === 'cancelled' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}">
        {{ ucfirst($status) }}
    </span>
</div>

        <div class="px-6 py-6 space-y-6">

            {{-- Facility schedule (shown once, only for reservations) --}}
            @if($isFacility && $facilityItem)
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Facility / Schedule</p>
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
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">
                        {{ $isFacility ? 'Materials Requested' : 'Items' }}
                    </p>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($materialItems as $item)
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item->item_name }}</p>
                                <span class="text-xs font-medium text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-2 py-0.5 rounded-full">x{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Plain material request (not a facility reservation) with no facility item at all --}}
            @if(!$isFacility && $materialItems->isEmpty() && $requestModel->items->isNotEmpty())
                <div>
                    <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2">Items</p>
                    <div class="bg-gray-50 dark:bg-gray-900/40 rounded-lg divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($requestModel->items as $item)
                            <div class="px-4 py-2.5 flex items-center justify-between">
                                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $item->item_name }}</p>
                                <span class="text-xs font-medium text-blue-600 bg-blue-50 dark:bg-blue-950/30 px-2 py-0.5 rounded-full">x{{ $item->quantity }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Purpose</p>
                <p class="text-sm text-gray-700 dark:text-gray-300">{{ $requestModel->purpose }}</p>
            </div>

        </div>

        <div class="px-6 py-4 bg-gray-50 dark:bg-gray-900/40 border-t border-gray-100 dark:border-gray-700 flex justify-end gap-2">
            @if($status === 'pending')
                <a href="{{ $isFacility ? route('portal.edit-reservation', $requestModel->id) : route('portal.edit-material', $requestModel->id) }}" wire:navigate
                   class="px-4 py-2 text-sm font-medium bg-white dark:bg-gray-800 text-blue-600 border border-blue-200 dark:border-blue-900 rounded-lg hover:bg-blue-50 dark:hover:bg-blue-950/30 transition">
                    Edit
                </a>
                <button wire:click="cancelRequest"
                        wire:confirm="Cancel this request?"
                        class="px-4 py-2 text-sm font-medium bg-yellow-500 text-white rounded-lg hover:bg-yellow-600 transition">
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
