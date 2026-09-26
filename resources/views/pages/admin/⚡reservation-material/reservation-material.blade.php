<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
  <div class="flex flex-col">
    <div class="overflow-x-auto">
      <div class="min-w-full inline-block align-middle">
        <div class="bg-white dark:bg-neutral-800 border rounded-xl shadow overflow-hidden">

          {{-- Header --}}
          <div class="px-6 py-4 flex justify-between items-center border-b">
            <div>
              <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Materials Requests</h2>
              <p class="text-sm text-gray-600 dark:text-neutral-400">Review and manage material requests</p>
            </div>
          </div>

          {{-- Table --}}
          <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-800">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Requestor</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Purpose</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Items</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Status</th>
                <th class="px-6 py-3 text-left text-xs font-semibold uppercase">Date Requested</th>
                <th class="px-6 py-3 text-right text-xs font-semibold uppercase">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
              @forelse($this->requests as $request)
                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700">

                  {{-- Requestor --}}
                  <td class="px-6 py-3">
                    <p class="font-medium text-sm text-gray-800 dark:text-neutral-200">{{ $request->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $request->user->email }}</p>
                    <p class="text-xs text-gray-400">College of {{ $request->user->department->department_name ?? 'N/A' }}</p>
                  </td>

                  {{-- Purpose --}}
                  <td class="px-6 py-3 text-sm text-gray-700 dark:text-neutral-300">
                    {{ $request->purpose }}
                  </td>

                  {{-- Items --}}
                  <td class="px-6 py-3">
                    <ul class="text-xs text-gray-600 dark:text-neutral-400 space-y-1">
                      @foreach($request->items as $item)
                        <li>• {{ $item->item_name }} (x{{ $item->quantity }})</li>
                      @endforeach
                    </ul>
                  </td>

                  {{-- Status --}}
                  <td class="px-6 py-3">
                    <span class="px-2 py-1 text-xs font-medium rounded-full
                      @if($request->status === 'pending') bg-yellow-100 text-yellow-800
                      @elseif($request->status === 'approved') bg-green-100 text-green-800
                      @elseif($request->status === 'rejected') bg-red-100 text-red-800
                      @elseif($request->status === 'cancelled') bg-gray-100 text-gray-700
                      @else bg-gray-100 text-gray-700 @endif">
                      {{ ucfirst(str_replace('_', ' ', $request->status)) }}
                    </span>
                  </td>

                  {{-- Date --}}
                  <td class="px-6 py-3 text-sm text-gray-600 dark:text-neutral-400">
                    @if($request->items->count() && $request->items->first()->request_date)
                      {{ \Carbon\Carbon::parse($request->items->first()->request_date)->format('M d, Y') }}
                    @else
                      N/A
                    @endif
                  </td>

                  {{-- Actions --}}
                  <td class="px-6 py-3 text-right space-x-2">
                    @if($request->status === 'pending')
                      <button wire:click="accept({{ $request->id }})"
                              wire:confirm="Accept this request?"
                              class="px-2 py-1 text-xs bg-green-600 text-white rounded hover:bg-green-700">
                        Accept
                      </button>
                      <button wire:click="reject({{ $request->id }})"
                              wire:confirm="Reject this request?"
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
                  <td colspan="6" class="px-6 py-6 text-center text-gray-500">
                    No material requests found.
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
