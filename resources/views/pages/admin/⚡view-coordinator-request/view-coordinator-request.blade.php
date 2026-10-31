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
                            $isFacility = !$row['resource'] || optional($row['resource']->resourceType)->type_name === 'Facility';
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

</div>
</div>
