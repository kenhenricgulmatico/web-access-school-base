<div>
    <div class="max-w-3xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14 space-y-6">

        {{-- Back --}}
        <button wire:click="back"
            class="inline-flex items-center gap-x-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-200">
            <svg class="size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
            </svg>
            Back to My Requests
        </button>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-5 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                    {{ $requestModel->requestType->type_name ?? 'Request' }} <span class="text-gray-400 dark:text-neutral-500 font-normal">#{{ $requestModel->id }}</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400">
                    Submitted {{ $requestModel->created_at->format('M d, Y \a\t h:i A') }}
                </p>
            </div>

            <div class="p-6 space-y-6">

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
                    $decidedApprovals = $requestModel->approvals->whereIn('status', ['approved', 'rejected']);
                @endphp

                {{-- Facility Details — only shown if this request has a facility reservation --}}
                @if ($facilityItem)
                    <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-700">
                        <p class="text-xs font-semibold uppercase tracking-wide text-green-700 dark:text-green-300 mb-3">
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

                {{-- Materials — only shown if this request has attached materials --}}
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
