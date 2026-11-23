<div class="select-none">
<div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 sm:py-10 lg:px-8">

  {{-- Header --}}
  <div class="mb-6">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Borrowed Items</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
      @if($view === 'mine')
        Items you borrowed from the Admin. Click <strong>Return to Admin</strong> when you give them back — the Admin will confirm it.
      @else
        Items your students and faculty must return. Mark them returned and record their condition.
      @endif
    </p>
  </div>

  {{-- Flash --}}
  @if(session('message'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-800 dark:bg-green-950/30 dark:border-green-900 dark:text-green-300">{{ session('message') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">{{ session('error') }}</div>
  @endif

  {{-- ✅ Switch: Students & Faculty | My borrowed items --}}
  <div class="flex gap-2 mb-4 border-b border-gray-200 dark:border-neutral-700" role="tablist" aria-label="Whose borrowed items">
    @foreach(['students' => 'Students & Faculty', 'mine' => 'My borrowed items'] as $key => $label)
      <button type="button" role="tab" wire:click="$set('view', '{{ $key }}')" aria-selected="{{ $view === $key ? 'true' : 'false' }}"
        class="min-h-10 px-4 -mb-px border-b-2 text-sm font-semibold transition inline-flex items-center gap-2
          {{ $view === $key ? 'border-[#123524] text-[#123524] dark:border-green-300 dark:text-green-300' : 'border-transparent text-gray-500 hover:text-[#123524] dark:text-neutral-400' }}">
        {{ $label }}
        @if($key === 'mine' && $this->counts['mine'] > 0)
          <span class="text-xs px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800">{{ $this->counts['mine'] }}</span>
        @endif
      </button>
    @endforeach
  </div>

  @if($view === 'mine')
    {{-- ============================================================
         ✅ MY BORROWED ITEMS (from the Admin)
         ============================================================ --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-400">
            <tr>
              <th class="px-4 py-3 font-semibold">Item</th>
              <th class="px-4 py-3 font-semibold">Return by</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
            @forelse($this->myItems as $item)
              @php
                $dueDate = \Carbon\Carbon::parse($item->due_date);
                $label   = $this->myStatusLabel($item);
                $badge   = match (true) {
                  $item->returned_at && $item->return_condition === 'good' => 'bg-green-100 text-green-800',
                  (bool) $item->returned_at                                 => 'bg-orange-100 text-orange-800',
                  (bool) $item->return_requested_at                         => 'bg-blue-100 text-blue-800',
                  $dueDate->lt(today())                                     => 'bg-red-100 text-red-800',
                  $dueDate->isToday()                                       => 'bg-amber-100 text-amber-800',
                  default                                                   => 'bg-gray-100 text-gray-700',
                };
              @endphp
              <tr wire:key="mine-{{ $item->id }}">
                <td class="px-4 py-3">
                  <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                  <p class="text-xs text-gray-500">{{ number_format($item->quantity) }} pcs · Request #{{ $item->request_id }}</p>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300">{{ $dueDate->format('M d, Y') }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $label }}</span>
                  @if($item->returned_at)
                    <p class="text-xs text-gray-500 mt-1">
                      {{ $item->returned_at->format('M d, Y') }}@if($item->receiver) · confirmed by {{ $item->receiver->name }}@endif
                    </p>
                    @if($item->return_notes)
                      <p class="text-xs text-gray-500 italic">"{{ $item->return_notes }}"</p>
                    @endif
                  @elseif($item->return_requested_at)
                    <p class="text-xs text-gray-500 mt-1">Sent {{ \Carbon\Carbon::parse($item->return_requested_at)->format('M d, Y g:i A') }}</p>
                  @endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  @if(! $item->returned_at && ! $item->return_requested_at)
                    <button type="button" wire:click="requestReturn({{ $item->id }})"
                      wire:confirm="Return {{ $item->quantity }} pcs of {{ $item->item_name }} to the Admin?"
                      class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">
                      Return to Admin
                    </button>
                  @elseif(! $item->returned_at)
                    <button type="button" wire:click="cancelRequestReturn({{ $item->id }})"
                      class="min-h-10 px-3 rounded-lg border border-gray-300 text-xs font-medium bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">
                      Cancel return
                    </button>
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="px-4 py-12 text-center text-sm text-gray-500">You have not borrowed any items from the Admin.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $this->myItems->links() }}</div>

  @else
    {{-- ============================================================
         STUDENTS & FACULTY
         ============================================================ --}}

    {{-- Tabs + search --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
      <div class="inline-flex p-1 bg-gray-100 dark:bg-neutral-800 rounded-xl gap-1" role="tablist" aria-label="Filter borrowed items">
        @foreach(['outstanding' => 'Not returned', 'overdue' => 'Overdue', 'returned' => 'Returned'] as $key => $label)
          <button type="button" role="tab" wire:click="$set('filter', '{{ $key }}')" aria-selected="{{ $filter === $key ? 'true' : 'false' }}"
            class="min-h-10 px-4 rounded-lg text-sm font-semibold transition inline-flex items-center gap-2
              {{ $filter === $key ? 'bg-white dark:bg-neutral-700 text-[#123524] dark:text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:text-[#123524]' }}">
            {{ $label }}
            <span class="text-xs px-1.5 py-0.5 rounded-full
              {{ $key === 'overdue' && $this->counts['overdue'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-700 dark:bg-neutral-600 dark:text-neutral-200' }}">
              {{ $this->counts[$key] }}
            </span>
          </button>
        @endforeach
      </div>

      <label class="sr-only" for="borrowed-search">Search</label>
      <input id="borrowed-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search item or borrower…"
        class="w-full sm:w-64 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead class="bg-gray-50 dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-400">
            <tr>
              <th class="px-4 py-3 font-semibold">Item</th>
              <th class="px-4 py-3 font-semibold">Borrower</th>
              <th class="px-4 py-3 font-semibold">Return by</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold text-right">Action</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
            @forelse($this->items as $item)
              @php
                $label = \App\Support\ReturnTracking::label($item);
                $badge = match (true) {
                  $item->returned_at && $item->return_condition === 'good' => 'bg-green-100 text-green-800',
                  (bool) $item->returned_at => 'bg-orange-100 text-orange-800',
                  $item->isOverdue() => 'bg-red-100 text-red-800',
                  $item->due_back_at->isToday() => 'bg-amber-100 text-amber-800',
                  default => 'bg-gray-100 text-gray-700',
                };
              @endphp
              <tr wire:key="item-{{ $item->id }}">
                <td class="px-4 py-3">
                  <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                  <p class="text-xs text-gray-500">Qty {{ $item->quantity }} · Request #{{ $item->request_id }}</p>
                </td>
                <td class="px-4 py-3 text-gray-700 dark:text-neutral-300">
                  {{ $item->request->user->name ?? '—' }}
                  <p class="text-xs text-gray-500">{{ $item->request->user->email ?? '' }}</p>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300">{{ $item->due_back_at->format('M d, Y') }}</td>
                <td class="px-4 py-3">
                  <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $label }}</span>
                  @if($item->returned_at)
                    <p class="text-xs text-gray-500 mt-1">
                      {{ $item->returned_at->format('M d, Y') }}@if($item->receiver) · by {{ $item->receiver->name }}@endif
                    </p>
                    @if($item->return_notes)
                      <p class="text-xs text-gray-500 italic">"{{ $item->return_notes }}"</p>
                    @endif
                  @endif
                </td>
                <td class="px-4 py-3 text-right">
                  @if(! $item->returned_at)
                    @can('requests.approve')
                      <button type="button" wire:click="openReturn({{ $item->id }})"
                        class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">
                        Mark returned
                      </button>
                    @endcan
                  @endif
                </td>
              </tr>

              {{-- Inline "mark returned" form --}}
              @if($returningId === $item->id)
                <tr wire:key="form-{{ $item->id }}" class="bg-gray-50 dark:bg-neutral-900/40">
                  <td colspan="5" class="px-4 py-4">
                    <form wire:submit="confirmReturn" class="flex flex-col sm:flex-row sm:items-end gap-3">
                      <div>
                        <label for="cond-{{ $item->id }}" class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Condition</label>
                        <select id="cond-{{ $item->id }}" wire:model="returnCondition"
                          class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800">
                          @foreach(\App\Models\RequestItem::CONDITIONS as $value => $text)
                            <option value="{{ $value }}">{{ $text }}</option>
                          @endforeach
                        </select>
                      </div>
                      <div class="flex-1">
                        <label for="notes-{{ $item->id }}" class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Notes (optional)</label>
                        <input id="notes-{{ $item->id }}" type="text" wire:model="returnNotes" maxlength="500" placeholder="e.g. one leg is loose"
                          class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800">
                        @error('returnNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                      </div>
                      <div class="flex gap-2">
                        <button type="button" wire:click="cancelReturn" class="min-h-10 px-3 rounded-lg border border-gray-300 text-sm font-medium bg-white hover:bg-gray-50">Cancel</button>
                        <button type="submit" class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418]">Confirm return</button>
                      </div>
                    </form>
                    <p class="text-xs text-gray-500 mt-2">Items returned in <strong>Good</strong> condition go back to your department's stock. Damaged or lost items don't.</p>
                  </td>
                </tr>
              @endif
            @empty
              <tr>
                <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">
                  @if($filter === 'overdue') No overdue items.
                  @elseif($filter === 'returned') No returned items yet.
                  @else Nothing is currently borrowed.
                  @endif
                </td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

    <div class="mt-4">{{ $this->items->links() }}</div>
  @endif
</div>
</div>
