<div class="select-none">
    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <div class="flex flex-col">
            <div class="overflow-x-auto">
                <div class="min-w-full inline-block align-middle">
                    <div class="bg-white dark:bg-neutral-800 border rounded-xl shadow overflow-hidden">

                        {{-- Header --}}
                        <div class="px-6 py-4 flex justify-between items-center border-b">
                            <div>
                                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Reservation
                                    Facilities</h2>
                                <p class="text-sm text-gray-600 dark:text-neutral-400">Review and manage facility
                                    reservations</p>
                            </div>
                        </div>

                        {{-- Table --}}
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                            <thead class="bg-gray-50 dark:bg-neutral-800">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Requestor</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Department</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Facility</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Date</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Purpose</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Status</th>
                                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                                @forelse($this->reservations as $reservation)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700">

                                        {{-- Requestor --}}
                                        <td class="px-6 py-3">
                                            <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                                                {{ $reservation->user->name }}</p>
                                            <p class="text-xs text-gray-500">{{ $reservation->user->email }}</p>
                                        </td>

                                        {{-- Department --}}
                                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                                            {{ $reservation->user->department->department_name ?? 'N/A' }}
                                        </td>

                                        {{-- Facility --}}
                                        <td class="px-6 py-3 text-sm text-gray-700 dark:text-neutral-300">
                                            @foreach ($reservation->items as $item)
                                                <p>{{ $item->item_name ?? 'N/A' }}</p>
                                            @endforeach
                                        </td>

                                        {{-- Date --}}
                                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                                            @foreach ($reservation->items as $item)
                                                <p>{{ $item->request_date ?? 'N/A' }}</p>
                                            @endforeach
                                        </td>

                                        {{-- Time --}}
                                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                                            @foreach ($reservation->items as $item)
                                                @if ($item->start_time && $item->end_time)
                                                    <p>{{ $item->start_time }} - {{ $item->end_time }}</p>
                                                @else
                                                    <p>N/A</p>
                                                @endif
                                            @endforeach
                                        </td>

                                        {{-- Purpose --}}
                                        <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                                            {{ $reservation->purpose }}
                                        </td>

                                        {{-- Status --}}
                                        <td class="px-6 py-3">
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                                @if ($reservation->status === 'pending') bg-yellow-100 text-yellow-800
                                                @elseif($reservation->status === 'approved') bg-green-100 text-green-800
                                                @elseif($reservation->status === 'rejected') bg-red-100 text-red-800
                                                @elseif($reservation->status === 'cancelled') bg-gray-100 text-gray-700
                                                @else bg-gray-100 text-gray-700 @endif">
                                                {{ ucfirst(str_replace('_', ' ', $reservation->status)) }}
                                            </span>
                                        </td>

                                        {{-- Actions --}}
                                        <td class="px-6 py-3 text-right space-x-2">
                                            @if($reservation->status === 'pending')
                                            <button wire:click="accept({{ $reservation->id }})"
                                                    wire:confirm="Accept this reservation?"
                                                    class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                                                Accept
                                            </button>
                                            <button wire:click="reject({{ $reservation->id }})"
                                                    wire:confirm="Reject this reservation?"
                                                    class="px-2 py-1 text-xs bg-red-600 text-white rounded hover:bg-red-700">
                                                Reject
                                            </button>
                                            @else
                                            <span class="text-xs text-gray-500">No actions</span>
                                            @endif
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-6 text-center text-gray-500">
                                            No reservations found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
