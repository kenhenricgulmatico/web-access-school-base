<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
  <div class="flex flex-col">
    <div class="overflow-x-auto">
      <div class="min-w-full inline-block align-middle">
        <div class="bg-white dark:bg-neutral-800 border rounded-xl shadow overflow-hidden">

          {{-- Header --}}
          <div class="px-6 py-4 flex justify-between items-center border-b">
            <div>
              <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Reservation Facilities</h2>
              <p class="text-sm text-gray-600 dark:text-neutral-400">Review and manage facility reservations from students</p>
            </div>
          </div>

          {{-- Table --}}
          <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Requestor</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Facility / Materials</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Time</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Purpose</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              @forelse($this->reservations as $reservation)
                @php
                    $facilityItem  = $reservation->items->firstWhere('resource_id', null);
                    $materialItems = $reservation->items->whereNotNull('resource_id');
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700 transition align-top">

                  {{-- Requestor --}}
                  <td class="px-6 py-3">
                    <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ $reservation->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $reservation->user->email }}</p>
                  </td>

                  {{-- Department --}}
                  <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                    {{ $reservation->user->department->department_name ?? 'N/A' }}
                  </td>

                  {{-- Facility / Materials --}}
                  <td class="px-6 py-3 text-sm text-gray-700 dark:text-neutral-300">
                    @if($facilityItem)
                        <p class="font-semibold text-gray-800 dark:text-neutral-200">{{ $facilityItem->item_name }}</p>
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

                  {{-- Date --}}
                  <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                    @if($facilityItem)
                        {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}
                    @endif
                  </td>

                  {{-- Time --}}
                  <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                    @if($facilityItem && $facilityItem->start_time && $facilityItem->end_time)
                        {{ \Carbon\Carbon::parse($facilityItem->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($facilityItem->end_time)->format('h:i A') }}
                    @else
                        <span class="text-gray-400">N/A</span>
                    @endif
                  </td>

                  {{-- Purpose --}}
                  <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                    {{ $reservation->purpose }}
                  </td>

                  {{-- Status --}}
                  <td class="px-6 py-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                      @if($reservation->status === 'pending') bg-yellow-100 text-yellow-800
                      @elseif($reservation->status === 'approved') bg-green-100 text-green-800
                      @elseif($reservation->status === 'rejected') bg-red-100 text-red-800
                      @endif">
                      @if($reservation->status === 'pending') Pending
                      @elseif($reservation->status === 'approved') Approved
                      @elseif($reservation->status === 'rejected') Rejected
                      @endif
                    </span>
                  </td>

                  {{-- Actions --}}
                  <td class="px-6 py-3 text-right space-x-2">
                    @if($reservation->status === 'pending')
                      <button wire:click="accept({{ $reservation->id }})"
                              wire:confirm="Accept this reservation?"
                              class="px-3 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        Accept
                      </button>
                      <button wire:click="reject({{ $reservation->id }})"
                              wire:confirm="Reject this reservation?"
                              class="px-3 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                        Reject
                      </button>
                    @elseif($reservation->status === 'approved')
                      <span class="text-xs text-green-600 font-medium">✓ Approved</span>
                    @elseif($reservation->status === 'rejected')
                      <span class="text-xs text-red-500 font-medium">✗ Rejected</span>
                    @endif
                  </td>

                </tr>
              @empty
                <tr>
                  <td colspan="8" class="px-6 py-10 text-center text-gray-400">
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
