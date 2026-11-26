<div class="select-none">
<div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 sm:py-10 lg:px-8">

  {{-- Header --}}
  <div class="mb-6">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Borrowed Items</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
      @if($view === 'mine')
        Items you borrowed from the Admin. Click <strong>Return to Admin</strong> when you give them back — the Admin will confirm it.
      @else
        Items your faculty must return. Mark them returned and record their condition.
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

  {{-- ✅ Overdue items borrowed from the Admin --}}
  @if($this->myOverdueCount > 0)
    <div class="mb-4 flex flex-wrap items-center gap-2 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">
      <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
      </svg>
      <span>
        You have <strong>{{ $this->myOverdueCount }} overdue {{ \Illuminate\Support\Str::plural('item', $this->myOverdueCount) }}</strong>
        borrowed from the Admin. Please return {{ $this->myOverdueCount === 1 ? 'it' : 'them' }} as soon as possible.
      </span>
      @if($view !== 'mine')
        <button type="button" wire:click="$set('view', 'mine')" class="font-semibold underline">View</button>
      @endif
    </div>
  @endif

  {{-- ✅ Switch: Students & Faculty | My borrowed items --}}
  <div class="flex gap-2 mb-4 border-b border-gray-200 dark:border-neutral-700" role="tablist" aria-label="Whose borrowed items">
    @foreach(['students' => 'Faculty', 'mine' => 'My borrowed items'] as $key => $label)
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
                $dueDate     = \Carbon\Carbon::parse($item->due_date);
                $label       = $this->myStatusLabel($item);
                // ✅ Borrowed for a faculty member → who has it, and have they returned it to me yet?
                $facultyItem = $item->returned_at ? null : \App\Support\ReturnTracking::facultyItemFor($item);
                $withFaculty = $facultyItem && $facultyItem->request?->status === 'approved' && ! $facultyItem->returned_at;
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
                  <p class="text-xs text-gray-500">{{ number_format($item->quantity) }} pcs</p>
                  @if($facultyItem)
                    <p class="text-xs text-gray-500">For {{ $facultyItem->request?->user?->name ?? 'a faculty member' }}</p>
                  @endif
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
                  @elseif($dueDate->lt(today()) && ($reminder = $this->adminReminder($item)))
                    {{-- ✅ Message from the Admin --}}
                    <div class="mt-2 max-w-sm px-3 py-2 rounded-lg bg-red-50 border border-red-100 dark:bg-red-950/30 dark:border-red-900">
                      <p class="flex items-center gap-1 text-[11px] font-semibold text-red-700 dark:text-red-300">
                        <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        Message from the Admin · {{ $reminder->created_at->diffForHumans() }}
                      </p>
                      <p class="text-xs text-red-800 dark:text-red-200 mt-0.5 whitespace-normal">{{ $reminder->message }}</p>
                    </div>
                  @endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  @if(! $item->returned_at && ! $item->return_requested_at && $withFaculty)
                    {{-- ✅ The faculty member must return it to me first --}}
                    <span class="inline-flex items-center min-h-10 px-3 rounded-lg bg-gray-100 text-gray-500 text-xs font-semibold dark:bg-neutral-700 dark:text-neutral-300"
                      title="Confirm the faculty member's return in the Faculty tab first">
                      Waiting for {{ $facultyItem->request?->user?->name ?? 'faculty' }}
                    </span>
                    <p class="text-[11px] text-gray-400 mt-1 whitespace-normal">
                      {{ $facultyItem->return_requested_at ? 'They sent it back — confirm it in the Faculty tab.' : 'They have not returned it yet.' }}
                    </p>
                  @elseif(! $item->returned_at && ! $item->return_requested_at)
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
         FACULTY
         ============================================================ --}}

    {{-- ✅ Items faculty gave back that came from the Admin → return them to the Admin --}}
    @if($this->readyForAdminCount > 0)
      <div class="mb-4 flex flex-wrap items-center gap-2 px-4 py-3 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800 dark:bg-blue-950/30 dark:border-blue-900 dark:text-blue-300">
        <span>
          <strong>{{ $this->readyForAdminCount }} {{ \Illuminate\Support\Str::plural('item', $this->readyForAdminCount) }}</strong>
          borrowed from the Admin {{ $this->readyForAdminCount === 1 ? 'is' : 'are' }} back from faculty — return {{ $this->readyForAdminCount === 1 ? 'it' : 'them' }} to the Admin.
        </span>
        @if($filter !== 'returned')
          <button type="button" wire:click="$set('filter', 'returned')" class="font-semibold underline">View</button>
        @endif
      </div>
    @endif

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
                $sentBack  = ! $item->returned_at && $item->return_requested_at;   // ✅ faculty clicked "Return to Program Head"
                $label     = $sentBack ? 'Returned — please confirm' : \App\Support\ReturnTracking::label($item);
                $isOverdue = $item->isOverdue() && ! $sentBack;
                $reminded  = $isOverdue ? $this->studentReminder($item) : null;
                // ✅ Came from the Admin (endorsed request)? Then I return it to the Admin after the faculty returns it.
                $adminItem = \App\Support\ReturnTracking::adminItemFor($item);
                $badge = match (true) {
                  $item->returned_at && $item->return_condition === 'good' => 'bg-green-100 text-green-800',
                  (bool) $item->returned_at => 'bg-orange-100 text-orange-800',
                  $sentBack => 'bg-blue-100 text-blue-800',
                  $item->isOverdue() => 'bg-red-100 text-red-800',
                  $item->due_back_at->isToday() => 'bg-amber-100 text-amber-800',
                  default => 'bg-gray-100 text-gray-700',
                };
              @endphp
              <tr wire:key="item-{{ $item->id }}">
                <td class="px-4 py-3">
                  <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                  <p class="text-xs text-gray-500">Qty {{ $item->quantity }}</p>
                  @if($adminItem)
                    <span class="inline-flex mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">Borrowed from the Admin</span>
                  @endif
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
                    {{-- ✅ Next step for items from the Admin --}}
                    @if($adminItem)
                      @if($adminItem->returned_at)
                        <p class="text-xs font-medium text-green-700 dark:text-green-400 mt-1">✓ Returned to the Admin {{ $adminItem->returned_at->format('M d, Y') }}</p>
                      @elseif($adminItem->return_requested_at)
                        <p class="text-xs font-medium text-blue-700 dark:text-blue-300 mt-1">Sent to the Admin — waiting for them to confirm</p>
                      @else
                        <p class="text-xs font-medium text-amber-700 dark:text-amber-300 mt-1">Next: return it to the Admin</p>
                      @endif
                    @endif
                  @elseif($sentBack)
                    <p class="text-xs font-medium text-blue-700 dark:text-blue-300 mt-1">
                      {{ $item->request->user->name ?? 'The borrower' }} returned it {{ \Carbon\Carbon::parse($item->return_requested_at)->format('M d, g:i A') }} — please check and confirm
                    </p>
                  @else
                    {{-- ✅ Still with the faculty member --}}
                    <p class="text-xs text-gray-500 mt-1">With {{ $item->request->user->name ?? 'the faculty member' }} — waiting for "Return to Program Head"</p>
                    @if($reminded)
                      <p class="text-xs text-gray-500">
                        Reminder sent {{ $reminded->isToday() ? 'today, ' . $reminded->format('g:i A') : $reminded->format('M d, g:i A') }}
                      </p>
                    @endif
                  @endif
                </td>
                <td class="px-4 py-3 text-right whitespace-nowrap">
                  @if($item->returned_at && $adminItem && ! $adminItem->returned_at)
                    {{-- ✅ Faculty gave it back → now I return it to the Admin (same row, not "My borrowed items") --}}
                    @if(! $adminItem->return_requested_at)
                      <button type="button" wire:click="requestReturn({{ $adminItem->id }})"
                        wire:confirm="Return {{ $adminItem->quantity }} pcs of {{ $adminItem->item_name }} to the Admin?"
                        wire:loading.attr="disabled" wire:target="requestReturn({{ $adminItem->id }})"
                        class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] disabled:opacity-60 transition">
                        Return to Admin
                      </button>
                    @else
                      <button type="button" wire:click="cancelRequestReturn({{ $adminItem->id }})"
                        class="min-h-10 px-3 rounded-lg border border-gray-300 text-xs font-medium bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">
                        Cancel return
                      </button>
                    @endif
                  @elseif(! $item->returned_at)
                    @can('requests.approve')
                      @if($sentBack)
                        {{-- ✅ Faculty clicked "Return to Program Head" → check the item and confirm --}}
                        <button type="button" wire:click="openReturn({{ $item->id }})"
                          class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">
                          {{ $adminItem && ! $adminItem->returned_at ? 'Confirm & send to Admin' : 'Confirm return' }}
                        </button>
                      @elseif($isOverdue)
                        {{-- ✅ Overdue: message the faculty member --}}
                        @if($reminded?->isToday())
                          <span class="inline-flex items-center gap-1.5 min-h-10 px-3 rounded-lg bg-gray-100 text-gray-500 text-xs font-semibold dark:bg-neutral-700 dark:text-neutral-300">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Reminded today
                          </span>
                        @else
                          <button type="button" wire:click="sendReminder({{ $item->id }})"
                            wire:confirm="Send a reminder to {{ $item->request->user->name ?? 'the borrower' }} to return {{ $item->item_name }} (x{{ $item->quantity }})?"
                            wire:loading.attr="disabled" wire:target="sendReminder({{ $item->id }})"
                            class="inline-flex items-center gap-1.5 min-h-10 px-3 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 disabled:opacity-60 transition">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            {{ $reminded ? 'Remind again' : 'Send reminder' }}
                          </button>
                        @endif
                      @else
                        {{-- Still with the faculty member: nothing to confirm until they click "Return to Program Head" --}}
                        <span class="inline-flex items-center min-h-10 px-3 rounded-lg bg-gray-100 text-gray-500 text-xs font-semibold dark:bg-neutral-700 dark:text-neutral-300">
                          Not returned yet
                        </span>
                      @endif
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
                        <button type="submit" class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418]">
                          {{ $adminItem && ! $adminItem->returned_at ? 'Confirm & send to Admin' : 'Confirm return' }}
                        </button>
                      </div>
                    </form>
                    @if($adminItem && ! $adminItem->returned_at)
                      <p class="text-xs text-gray-500 mt-2">This item was borrowed from the <strong>Admin</strong> — confirming sends it straight back to the Admin. No extra step needed.</p>
                    @else
                      <p class="text-xs text-gray-500 mt-2">Items returned in <strong>Good</strong> condition go back to your department's stock. Damaged or lost items don't.</p>
                    @endif
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
