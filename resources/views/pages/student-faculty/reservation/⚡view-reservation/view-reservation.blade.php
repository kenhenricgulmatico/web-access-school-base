<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
    <div class="flex flex-col">

        @if (session()->has('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">

            <div class="px-5 py-4 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">My Reservations</h2>
                    <p class="text-xs text-gray-500 dark:text-neutral-400">Track your facility requests</p>
                </div>
                <a href="{{ route('portal.create-reservation') }}"
                    class="px-3 py-1.5 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                    + Create Reservation
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-y border-gray-100 dark:border-neutral-700">
                            <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Facility / Materials</th>
                            <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Date & Time</th>
                            <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Status</th>
                            <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Requested On</th>
                            <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                        @forelse($this->reservations as $request)
                            @php
                                $facilityItem  = $request->items->firstWhere('resource_id', null);
                                $materialItems = $request->items->whereNotNull('resource_id');
                            @endphp
                            <tr class="align-top">
                                <td class="px-5 py-3">
                                    @if($facilityItem)
                                        <p class="font-medium text-gray-800 dark:text-neutral-200">
                                            {{ $facilityItem->item_name }}
                                        </p>
                                    @endif

                                    @if($materialItems->isNotEmpty())
                                        <p class="mt-0.5 text-xs text-gray-400">
                                            {{ $materialItems->map(fn($m) => $m->item_name . ' x' . $m->quantity)->implode(', ') }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    @if($facilityItem)
                                        {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}<br>
                                        {{ $facilityItem->start_time }} – {{ $facilityItem->end_time }}
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @php $status = $request->status; @endphp
                                    @if($status === 'pending')
                                        <span class="px-2 py-0.5 text-xs font-medium bg-yellow-50 text-yellow-700 rounded-full">Pending</span>
                                    @elseif($status === 'approved')
                                        <span class="px-2 py-0.5 text-xs font-medium bg-green-50 text-green-700 rounded-full">Approved</span>
                                    @elseif($status === 'rejected')
                                        <span class="px-2 py-0.5 text-xs font-medium bg-red-50 text-red-700 rounded-full">Rejected</span>
                                    @elseif($status === 'cancelled')
                                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 rounded-full">Cancelled</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">{{ ucfirst($status) }}</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-xs text-gray-500">
                                    {{ $request->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-5 py-3">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('portal.view-material-reservation', $request->id) }}" wire:navigate
                                            class="px-2.5 py-1 text-xs font-medium text-gray-600 bg-gray-100 dark:bg-neutral-700 dark:text-neutral-200 rounded-md hover:bg-gray-200 dark:hover:bg-neutral-600 transition">
                                            View
                                        </a>
                                        @if($request->status === 'pending')
                                            <a href="{{ route('portal.edit-reservation', $request->id) }}"
                                                class="px-2.5 py-1 text-xs font-medium text-blue-600 bg-blue-50 rounded-md hover:bg-blue-100 transition">
                                                Edit
                                            </a>
                                            <button type="button"
                                                wire:click="cancelReservation({{ $request->id }})"
                                                wire:confirm="Cancel this reservation?"
                                                class="px-2.5 py-1 text-xs font-medium text-red-600 bg-red-50 rounded-md hover:bg-red-100 transition">
                                                Cancel
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-gray-400">
                                    No reservations found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-5 py-3">
                {{ $this->reservations->links() }}
            </div>
        </div>
    </div>
</div>
</div>
