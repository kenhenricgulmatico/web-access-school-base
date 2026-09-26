<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
  <div class="flex flex-col">
    <div class="overflow-x-auto">
      <div class="min-w-full inline-block align-middle">
        <div class="bg-white dark:bg-neutral-800 border rounded-xl shadow overflow-hidden">

          <div class="px-6 py-4 flex justify-between items-center border-b">
            <div>
              <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Reservation Facilities</h2>
              <p class="text-sm text-gray-600 dark:text-neutral-400">Review and manage facility reservations from students</p>
            </div>

            <div class="flex items-center gap-1 bg-gray-100 dark:bg-neutral-700 rounded-lg p-1">
              <a href="{{ route('coordinator.facility') }}" wire:navigate
                 class="px-4 py-1.5 text-sm font-semibold rounded-md transition
                   {{ request()->routeIs('coordinator.facility')
                        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow'
                        : 'text-gray-500 dark:text-neutral-400 hover:text-gray-700 dark:hover:text-neutral-200' }}">
                Facility
              </a>
              <a href="{{ route('coordinator.material') }}" wire:navigate
                 class="px-4 py-1.5 text-sm font-semibold rounded-md transition
                   {{ request()->routeIs('coordinator.material')
                        ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900 shadow'
                        : 'text-gray-500 dark:text-neutral-400 hover:text-gray-700 dark:hover:text-neutral-200' }}">
                Material
              </a>
            </div>
          </div>

          <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Requestor</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Facility</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Purpose</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              @forelse($this->reservations as $reservation)
                @php
                    $facilityItem = $reservation->items->firstWhere('resource_id', null);
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700 transition align-top">
                  <td class="px-6 py-3">
                    <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ $reservation->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $reservation->user->email }}</p>
                  </td>
                  <td class="px-6 py-3 text-sm text-gray-700 dark:text-neutral-300">
                    {{ $facilityItem->item_name ?? '—' }}
                  </td>
                  <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                    {{ $reservation->purpose }}
                  </td>
                  <td class="px-6 py-3 text-right whitespace-nowrap">
                    <div class="inline-flex items-center gap-1.5">
                      <a href="{{ route('coordinator.view-request-reserve', $reservation->id) }}" wire:navigate
                         class="px-2.5 py-1 text-xs bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600">
                        View
                      </a>
                      @if($reservation->status === 'pending')
                        <button wire:click="accept({{ $reservation->id }})"
                                wire:confirm="Accept this reservation?"
                                class="px-2.5 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                          Accept
                        </button>
                        <button wire:click="reject({{ $reservation->id }})"
                                wire:confirm="Reject this reservation?"
                                class="px-2.5 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                          Reject
                        </button>
                      @endif
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="px-6 py-10 text-center text-gray-400">
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
