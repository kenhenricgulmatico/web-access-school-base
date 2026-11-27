<div>
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5">

        {{-- Back --}}
        <button wire:click="back"
            class="inline-flex items-center gap-1 text-xs font-semibold text-[#1C6B45] dark:text-[#7FBF8E] hover:underline">
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to My Requests
        </button>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-200 dark:border-neutral-700 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                        {{ $requestModel->requestType->type_name ?? 'Request' }} <span class="text-gray-400 dark:text-neutral-500 font-normal">#{{ $requestModel->id }}</span>
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-neutral-400">
                        Submitted {{ $requestModel->created_at->format('M d, Y \a\t h:i A') }}
                    </p>
                </div>

                {{-- Status badge --}}
                @php
                    $status = $requestModel->status;
                    $badgeClasses = match ($status) {
                        'approved'  => 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]',
                        'rejected'  => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
                        'pending'   => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                        'cancelled' => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300',
                        default     => 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-300',
                    };
                @endphp
                <span class="shrink-0 px-3 py-1 text-xs font-semibold rounded-full {{ $badgeClasses }}">
                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                </span>
            </div>

            <div class="p-6 space-y-6">

                {{-- Rejection reason panel (only if rejected) --}}
                @if ($status === 'rejected' && $this->rejection?->remarks)
                    <div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 p-4 text-left dark:border-red-900/60 dark:bg-red-950/30">
                        <div class="flex size-9 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/50 dark:text-red-300">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 8v4m0 4h.01" />
                            </svg>
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-[11px] font-semibold uppercase tracking-wide text-red-700 dark:text-red-300">
                                Rejection Reason
                            </h3>

                            <p class="mt-1 whitespace-pre-line break-words text-sm leading-relaxed text-red-900 dark:text-red-200">{{ trim($this->rejection->remarks) }}</p>

                            @if ($this->rejection->approver)
                                <p class="mt-2 text-[11px] text-red-700/70 dark:text-red-400/70">
                                    Rejected by <span class="font-semibold">{{ $this->rejection->approver->name }}</span>
                                    @if ($this->rejection->approved_at)
                                        · {{ \Carbon\Carbon::parse($this->rejection->approved_at)->diffForHumans() }}
                                    @endif
                                </p>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Purpose --}}
                <div>
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-500 mb-1">
                        Purpose
                    </h3>
                    <p class="text-sm text-gray-800 dark:text-neutral-200">
                        {{ $requestModel->purpose }}
                    </p>
                </div>

                {{-- Department / Type --}}
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-500 mb-1">
                            Department
                        </h3>
                        <p class="text-sm text-gray-800 dark:text-neutral-200">
                            {{ $requestModel->department->department_name ?? 'N/A' }}
                        </p>
                    </div>
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-500 mb-1">
                            Request Type
                        </h3>
                        <p class="text-sm text-gray-800 dark:text-neutral-200">
                            {{ $requestModel->requestType->type_name ?? 'N/A' }}
                        </p>
                    </div>
                </div>

                @php
                    $facilityItem = $requestModel->items->whereNull('resource_id')->first();
                    $materialItems = $requestModel->items->whereNotNull('resource_id');
                @endphp

                {{-- Facility Details --}}
                @if ($facilityItem)
                    <div class="p-4 bg-[#F3F8F5] dark:bg-[#1C6B45]/10 rounded-xl border border-[#1C6B45]/15 dark:border-[#7FBF8E]/20">
                        <p class="text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#9FD3AE] mb-3">
                            Facility Reservation
                        </p>
                        <div class="grid sm:grid-cols-3 gap-4">
                            <div>
                                <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-500 mb-1">
                                    Facility
                                </h4>
                                <p class="text-sm text-gray-800 dark:text-neutral-200">
                                    {{ $facilityItem->item_name }}
                                </p>
                            </div>
                            <div>
                                <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-500 mb-1">
                                    Date
                                </h4>
                                <p class="text-sm text-gray-800 dark:text-neutral-200">
                                    {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}
                                </p>
                            </div>
                            <div>
                                <h4 class="text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-500 mb-1">
                                    Time
                                </h4>
                                <p class="text-sm text-gray-800 dark:text-neutral-200">
                                    @if ($facilityItem->start_time && $facilityItem->end_time)
                                        {{ \Carbon\Carbon::parse($facilityItem->start_time)->format('h:i A') }}
                                        &ndash;
                                        {{ \Carbon\Carbon::parse($facilityItem->end_time)->format('h:i A') }}
                                    @else
                                        &mdash;
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Materials --}}
                @if ($materialItems->isNotEmpty())
                    <div>
                        <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-500 mb-3">
                            Materials
                        </h3>

                        <div class="border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                                <thead class="bg-gray-50 dark:bg-neutral-900">
                                    <tr>
                                        <th class="px-4 py-2 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                            Item
                                        </th>
                                        <th class="px-4 py-2 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                            Qty
                                        </th>
                                        <th class="px-4 py-2 text-start text-xs font-semibold uppercase tracking-wide text-gray-800 dark:text-neutral-200">
                                            Date
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700 bg-white dark:bg-neutral-800">
                                    @foreach ($materialItems as $item)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $item->item_name }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-neutral-200">
                                                {{ $item->quantity }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-800 dark:text-neutral-200">
                                                {{ \Carbon\Carbon::parse($item->request_date)->format('M d, Y') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if (!$facilityItem && $materialItems->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-neutral-500">No items found for this request.</p>
                @endif

            </div>

        </div>

    </div>
</div>
