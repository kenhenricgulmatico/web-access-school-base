<div class="select-none">
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

    {{-- Back --}}
    <a href="javascript:history.back()" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200 mb-6">
        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back
    </a>

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Request Details</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Full breakdown of what was reserved or requested</p>
        </div>
        <span class="inline-flex items-center gap-1 px-3 py-1 text-xs font-medium rounded-full
            @if($reservation->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
            @elseif($reservation->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
            @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
            {{ ucfirst($reservation->status) }}
        </span>
    </div>

    {{-- Requester / Department / Type card --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-neutral-500 mb-1">Requester</p>
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-green-700 text-white flex items-center justify-center text-xs font-bold shrink-0">
                        {{ strtoupper(substr($reservation->user->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 leading-tight">{{ $reservation->user->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-neutral-500 leading-tight">{{ $reservation->user->email }}</p>
                    </div>
                </div>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-neutral-500 mb-1">Department</p>
                <p class="text-sm text-gray-700 dark:text-neutral-300">{{ $reservation->department->department_name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-neutral-500 mb-1">Type</p>
                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                    {{ $reservation->request_type_id == \App\Models\RequestType::FACILITY ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                    {{ $reservation->requestType?->type_name ?? '—' }}
                </span>
            </div>
        </div>

        <div class="mt-6 pt-6 border-t border-gray-100 dark:border-neutral-700">
            <p class="text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-neutral-500 mb-1">Purpose</p>
            <p class="text-sm text-gray-700 dark:text-neutral-300">{{ $reservation->purpose }}</p>
        </div>
    </div>

    {{-- Items / Facility --}}
    <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm overflow-hidden mb-6">
        <table class="min-w-full divide-y divide-gray-100 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-900/30">
                <tr>
                    <th class="px-6 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Item/Facility</th>
                    <th class="px-6 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Quantity</th>
                    <th class="px-6 py-2.5 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Schedule</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                @foreach($this->itemsWithStock as $row)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-neutral-300">{{ $row['item']->item_name }}</td>
                        @php
                            $isFacility = !$row['resource'] || optional($row['resource']->resourceType)->type_name === \App\Models\ResourceType::FACILITY;
                        @endphp
                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-neutral-300">{{ $isFacility ? '' : $row['item']->quantity }}</td>
                        <td class="px-6 py-3 text-sm text-gray-500 dark:text-neutral-400 whitespace-nowrap">
                            @if($row['item']->request_date)
                                {{ \Carbon\Carbon::parse($row['item']->request_date)->format('M d, Y') }}
                                @if($row['item']->start_time && $row['item']->end_time)
                                    <span class="block text-xs text-gray-400">
                                        {{ \Carbon\Carbon::parse($row['item']->start_time)->format('h:i A') }}
                                        – {{ \Carbon\Carbon::parse($row['item']->end_time)->format('h:i A') }}
                                    </span>
                                @endif
                            @else
                                —
                            @endif
                                </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('message') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200 text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="9" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
            </svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- ✅ Approve / Reject right here — no need to go back to the list --}}
    @if ($this->canReview)
        <div class="flex justify-end gap-2">
            @can('program-head-requests.reject')
                <button wire:click="openRejectForm"
                    class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Reject
                </button>
            @endcan
            @can('program-head-requests.approve')
                <button wire:click="approveRequest"
                    wire:confirm="Approve this request?"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <span wire:loading.remove wire:target="approveRequest">Approve</span>
                    <span wire:loading wire:target="approveRequest">Approving...</span>
                </button>
            @endcan
        </div>

        {{-- Reject reason form --}}
        @can('program-head-requests.reject')
            @if ($rejectingRequestId === $reservation->id)
                <div class="mt-4 bg-white dark:bg-neutral-800 border border-red-200 dark:border-red-900/50 rounded-lg p-4 space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="size-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" />
                            <path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                        </svg>
                        <label class="text-sm font-semibold text-red-700 dark:text-red-400">
                            Reason for rejecting this request
                        </label>
                        <span class="text-xs text-gray-400 dark:text-neutral-500">(optional)</span>
                    </div>

                    <textarea wire:model="rejectReason" rows="3"
                        placeholder="e.g. Missing documents, budget exceeded, conflicting schedule..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-red-200 dark:border-red-900/50 dark:bg-neutral-900 dark:text-neutral-200 focus:ring-2 focus:ring-red-300 focus:border-red-400"></textarea>

                    <p class="text-xs text-gray-500 dark:text-neutral-400">
                        This reason will be sent to <strong>{{ $reservation->user->name }}</strong> via in-app notification and email.
                    </p>

                    <div class="flex justify-end gap-2">
                        <button wire:click="cancelReject"
                            class="px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition">
                            Cancel
                        </button>
                        <button wire:click="confirmRejectRequest"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                            <span wire:loading.remove wire:target="confirmRejectRequest">Confirm Rejection</span>
                            <span wire:loading wire:target="confirmRejectRequest">Rejecting...</span>
                        </button>
                    </div>
                </div>
            @endif
        @endcan
    @endif

</div>
</div>
