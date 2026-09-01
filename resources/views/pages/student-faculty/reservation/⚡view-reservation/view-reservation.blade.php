<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
    <div class="flex flex-col">

        @if (session()->has('success'))
            <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="bg-white dark:bg-neutral-800 border rounded-xl shadow overflow-hidden">

                    <div class="px-6 py-4 flex justify-between items-center border-b">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">My Reservations</h2>
                            <p class="text-sm text-gray-600 dark:text-neutral-400">Track your facility requests</p>
                        </div>
                        <a href="{{ route('portal.create-reservation') }}"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                            + Create Reservation
                        </a>
                    </div>

                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Facility / Materials</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Date & Time</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Requested On</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($this->reservations as $index => $request)
                                @php
                                    $facilityItem  = $request->items->firstWhere('resource_id', null);
                                    $materialItems = $request->items->whereNotNull('resource_id');
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700 align-top">
                                    <td class="px-6 py-3 text-sm text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-3">
                                        @if($facilityItem)
                                            <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                                {{ $facilityItem->item_name }}
                                            </p>
                                        @endif

                                        @if($materialItems->isNotEmpty())
                                            <div class="mt-1.5 space-y-0.5">
                                                @foreach($materialItems as $material)
                                                    <p class="text-xs font-medium text-red-600 flex items-center gap-1.5">
                                                        <span class="w-1 h-1 rounded-full bg-red-500 inline-block"></span>
                                                        {{ $material->item_name }}
                                                        <span class="text-red-400">x{{ $material->quantity }}</span>
                                                    </p>
                                                @endforeach
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500">
                                        @if($facilityItem)
                                            {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}<br>
                                            <span class="text-xs">{{ $facilityItem->start_time }} – {{ $facilityItem->end_time }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-600 max-w-xs truncate">
                                        {{ $request->purpose }}
                                    </td>
                                    <td class="px-6 py-3">
                                        @php $status = $request->status; @endphp
                                        @if($status === 'pending')
                                            <span class="px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full">Pending</span>
                                        @elseif($status === 'approved')
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Approved</span>
                                        @elseif($status === 'rejected')
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Rejected</span>
                                        @elseif($status === 'cancelled')
                                            <span class="px-2 py-1 text-xs font-medium bg-gray-200 text-gray-600 rounded-full">Cancelled</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full">{{ ucfirst($status) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3 text-sm text-gray-500">
                                        {{ $request->created_at->format('M d, Y h:i A') }}
                                    </td>
                                    <td class="px-6 py-3 text-sm">
                                        @if($request->status === 'pending')
                                            <div class="flex items-center gap-3">
                                                <a href="{{ route('portal.edit-reservation', $request->id) }}"
                                                    class="text-blue-600 hover:text-blue-800 font-medium">
                                                    Edit
                                                </a>
                                                <button type="button"
                                                    wire:click="cancelReservation({{ $request->id }})"
                                                    wire:confirm="Cancel this reservation?"
                                                    class="text-red-600 hover:text-red-800 font-medium">
                                                    Cancel
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-xs text-gray-400 italic">No actions</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                        No reservations found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="px-6 py-4 border-t">
                        {{ $this->reservations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
