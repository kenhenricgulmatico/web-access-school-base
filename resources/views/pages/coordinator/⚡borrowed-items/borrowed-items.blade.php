<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

  <x-portal.page-header title="Borrowed Items">
    @if($view === 'mine')
      Items you borrowed from the Admin. Click <strong class="text-gray-700 dark:text-neutral-200">Return to Admin</strong> when you give them back — the Admin will confirm it.
    @elseif($view === 'assigned')
      Items your teachers keep (no return date). Hand them over when a teacher leaves.
    @else
      Items your faculty must return. Mark them returned and record their condition.
    @endif
  </x-portal.page-header>

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

  {{-- ✅ My items due back to the Admin today / tomorrow --}}
  @if($this->myDueSoonCount > 0)
    <div class="mb-4 flex flex-wrap items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800 dark:bg-amber-950/30 dark:border-amber-900 dark:text-amber-300">
      <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span>
        <strong>{{ $this->myDueSoonCount }} {{ \Illuminate\Support\Str::plural('item', $this->myDueSoonCount) }}</strong>
        borrowed from the Admin {{ $this->myDueSoonCount === 1 ? 'is' : 'are' }} due back today or tomorrow.
      </span>
      @if($view !== 'mine')
        <button type="button" wire:click="$set('view', 'mine')" class="font-semibold underline">View</button>
      @endif
    </div>
  @endif

  {{-- ✅ Switch: Students & Faculty | My borrowed items --}}
  <div class="mb-4">
    <x-portal.tabs label="Whose borrowed items">
      @foreach(['students' => 'Faculty', 'mine' => 'My borrowed items', 'assigned' => 'Assigned (kept)'] as $key => $label)
        <x-portal.tab :active="$view === $key" :count="in_array($key, ['mine', 'assigned']) && $this->counts[$key] > 0 ? $this->counts[$key] : null" wire:click="$set('view', '{{ $key }}')">{{ $label }}</x-portal.tab>
      @endforeach
    </x-portal.tabs>
  </div>

  @if($view === 'assigned')
    {{-- ✅ Kept by my department's teachers (no return date) — hand over to the next teacher --}}
    <livewire:assigned-items scope="department" :key="'assigned-dept'" />
  @elseif($view === 'mine')
    {{-- ============================================================
         ✅ MY BORROWED ITEMS (from the Admin)
         ============================================================ --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
      <div>
        <table class="min-w-full text-sm block sm:table">
          <thead class="hidden sm:table-header-group bg-[#F3F8F5] dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
            <tr>
              <th class="px-4 py-3 font-semibold">Item</th>
              <th class="px-4 py-3 font-semibold">Return by</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold text-right">Action</th>
            </tr>
          </thead>
          <tbody class="block sm:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
            @forelse($this->myItems as $item)
              @php
                $dueDate     = \Carbon\Carbon::parse($item->due_date);
                $label       = $this->myStatusLabel($item);
                // ✅ Borrowed for a faculty member → who has it, and have they returned it to me yet?
                $facultyItem = $item->returned_at ? null : \App\Support\ReturnTracking::facultyItemFor($item);
                $withFaculty = $facultyItem && $facultyItem->request?->status === 'approved' && ! $facultyItem->returned_at;
                $badge   = match (true) {
                  $item->returned_at && $item->return_condition === 'good' => 'returned',
                  (bool) $item->returned_at                                 => 'damaged',
                  (bool) $item->return_requested_at                         => 'waiting',
                  $dueDate->lt(today())                                     => 'overdue',
                  $dueDate->isToday()                                       => 'soon',
                  default                                                   => 'borrowed',
                };
              @endphp
              <tr wire:key="mine-{{ $item->id }}" class="block sm:table-row py-3 sm:py-0">
                <td class="block sm:table-cell px-4 py-0.5 sm:py-3">
                  <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                  <p class="text-xs text-gray-500">{{ number_format($item->quantity) }} pcs</p>
                  @if($facultyItem)
                    <p class="text-xs text-gray-500">For {{ $facultyItem->request?->user?->name ?? 'a faculty member' }}</p>
                  @endif
                </td>
                <td class="block sm:table-cell px-4 py-0.5 sm:py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300"><span class="sm:hidden text-xs text-gray-500">Return by </span>{{ $dueDate->format('M d, Y') }}</td>
                <td class="block sm:table-cell px-4 py-0.5 sm:py-3">
                  <x-portal.status :status="$badge">{{ $label }}</x-portal.status>
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
                <td class="block sm:table-cell px-4 pt-2 pb-1 sm:py-3 text-left sm:text-right sm:whitespace-nowrap">
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
                    @can('borrowed-items.manage')
                    <button type="button" wire:click="requestReturn({{ $item->id }})"
                      wire:confirm="Return {{ $item->quantity }} pcs of {{ $item->item_name }} to the Admin?"
                      wire:loading.attr="disabled" wire:target="requestReturn({{ $item->id }})"
                      class="disabled:opacity-60 min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">
                      Return to Admin
                    </button>
                    @endcan
                  @elseif(! $item->returned_at)
                    @can('borrowed-items.manage')
                    <button type="button" wire:click="cancelRequestReturn({{ $item->id }})"
                      wire:confirm="Cancel the return of {{ $item->item_name }}? The Admin will be told the items are still with you."
                      wire:loading.attr="disabled" wire:target="cancelRequestReturn({{ $item->id }})"
                      class="disabled:opacity-60 min-h-10 px-3 rounded-lg border border-gray-300 text-xs font-medium bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">
                      Cancel return
                    </button>
                    @endcan
                  @endif
                </td>
              </tr>
            @empty
              <tr>
                <td colspan="4" class="block sm:table-cell"><x-portal.empty message="Nothing borrowed from the Admin" hint="Items you borrow through a request to the Admin will show here." /></td>
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
      <div class="mb-4 flex flex-wrap items-center gap-2 px-4 py-3 rounded-lg bg-[#E8F3EC] border border-[#1C6B45]/20 text-sm text-[#123524] dark:bg-[#1C6B45]/15 dark:border-[#7FBF8E]/25 dark:text-[#9FD3AE]">
        <span>
          <strong>{{ $this->readyForAdminCount }} {{ \Illuminate\Support\Str::plural('item', $this->readyForAdminCount) }}</strong>
          borrowed from the Admin {{ $this->readyForAdminCount === 1 ? 'is' : 'are' }} back from faculty — return {{ $this->readyForAdminCount === 1 ? 'it' : 'them' }} to the Admin.
        </span>
        @if($filter !== 'returned')
          <button type="button" wire:click="$set('filter', 'returned')" class="font-semibold underline">View</button>
        @endif
      </div>
    @endif

    {{-- ✅ Faculty items due today / tomorrow — remind people before they're late --}}
    @if($this->counts['due_soon'] > 0)
      <div class="mb-4 flex flex-wrap items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800 dark:bg-amber-950/30 dark:border-amber-900 dark:text-amber-300">
        <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <span>
          <strong>{{ $this->counts['due_soon'] }} {{ \Illuminate\Support\Str::plural('item', $this->counts['due_soon']) }}</strong>
          borrowed by faculty {{ $this->counts['due_soon'] === 1 ? 'is' : 'are' }} due today or tomorrow.
        </span>
      </div>
    @endif

    {{-- Tabs + search --}}
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
      <x-portal.tabs label="Filter borrowed items">
        @foreach(['outstanding' => 'Not returned', 'overdue' => 'Overdue', 'returned' => 'Returned'] as $key => $label)
          <x-portal.tab :active="$filter === $key" :count="$this->counts[$key]" wire:click="$set('filter', '{{ $key }}')">{{ $label }}</x-portal.tab>
        @endforeach
      </x-portal.tabs>

      <label class="sr-only" for="borrowed-search">Search</label>
      <input id="borrowed-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search item or borrower…"
        class="w-full sm:w-64 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-[#1C6B45]/30 focus:border-[#1C6B45] focus:outline-none">
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
      <div>
        <table class="min-w-full text-sm block sm:table">
          <thead class="hidden sm:table-header-group bg-[#F3F8F5] dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
            <tr>
              <th class="px-4 py-3 font-semibold">Item</th>
              <th class="px-4 py-3 font-semibold">Borrower</th>
              <th class="px-4 py-3 font-semibold">Return by</th>
              <th class="px-4 py-3 font-semibold">Status</th>
              <th class="px-4 py-3 font-semibold text-right">Action</th>
            </tr>
          </thead>
          <tbody class="block sm:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
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
                  (bool) $item->returned_at => 'damaged',
                  $sentBack => 'waiting',
                  $item->isOverdue() => 'overdue',
                  $item->due_back_at->isToday() => 'soon',
                  default => 'borrowed',
                };
              @endphp
              <tr wire:key="item-{{ $item->id }}" class="block sm:table-row py-3 sm:py-0">
                <td class="block sm:table-cell px-4 py-0.5 sm:py-3">
                  <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                  <p class="text-xs text-gray-500">Qty {{ $item->quantity }}</p>
                  @if($adminItem)
                    <span class="inline-flex mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white text-[#1C6B45] ring-1 ring-inset ring-[#1C6B45]/30 dark:bg-transparent dark:text-[#9FD3AE] dark:ring-[#7FBF8E]/40">Borrowed from the Admin</span>
                  @endif
                </td>
                <td class="block sm:table-cell px-4 py-0.5 sm:py-3 text-gray-700 dark:text-neutral-300">
                  {{ $item->request->user->name ?? '—' }}
                  <p class="text-xs text-gray-500">{{ $item->request->user->email ?? '' }}</p>
                </td>
                <td class="block sm:table-cell px-4 py-0.5 sm:py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300"><span class="sm:hidden text-xs text-gray-500">Return by </span>{{ $item->due_back_at->format('M d, Y') }}</td>
                <td class="block sm:table-cell px-4 py-0.5 sm:py-3">
                  <x-portal.status :status="$badge">{{ $label }}</x-portal.status>
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
                        <p class="text-xs font-medium text-[#1C6B45] dark:text-[#7FBF8E] mt-1">✓ Returned to the Admin {{ $adminItem->returned_at->format('M d, Y') }}</p>
                      @elseif($adminItem->return_requested_at)
                        <p class="text-xs font-medium text-[#1C6B45] dark:text-[#9FD3AE] mt-1">Sent to the Admin — waiting for them to confirm</p>
                      @else
                        <p class="text-xs font-medium text-amber-700 dark:text-amber-300 mt-1">Next: return it to the Admin</p>
                      @endif
                    @endif
                  @elseif($sentBack)
                    <p class="text-xs font-medium text-[#1C6B45] dark:text-[#9FD3AE] mt-1">
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
                <td class="block sm:table-cell px-4 pt-2 pb-1 sm:py-3 text-left sm:text-right sm:whitespace-nowrap">
                  @if($item->returned_at && $adminItem && ! $adminItem->returned_at)
                    {{-- ✅ Faculty gave it back → now I return it to the Admin (same row, not "My borrowed items") --}}
                    @if(! $adminItem->return_requested_at)
                      @can('borrowed-items.manage')
                      <button type="button" wire:click="requestReturn({{ $adminItem->id }})"
                        wire:confirm="Return {{ $adminItem->quantity }} pcs of {{ $adminItem->item_name }} to the Admin?"
                        wire:loading.attr="disabled" wire:target="requestReturn({{ $adminItem->id }})"
                        class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] disabled:opacity-60 transition">
                        Return to Admin
                      </button>
                      @endcan
                    @else
                      @can('borrowed-items.manage')
                      <button type="button" wire:click="cancelRequestReturn({{ $adminItem->id }})"
                        wire:confirm="Cancel the return of {{ $adminItem->item_name }}? The Admin will be told the items are still with you."
                        wire:loading.attr="disabled" wire:target="cancelRequestReturn({{ $adminItem->id }})"
                        class="disabled:opacity-60 min-h-10 px-3 rounded-lg border border-gray-300 text-xs font-medium bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">
                        Cancel return
                      </button>
                      @endcan
                    @endif
                  @elseif(! $item->returned_at)
                    @can('borrowed-items.manage')
                      @if($sentBack)
                        {{-- ✅ Faculty clicked "Return to Program Head" → check the item and confirm --}}
                        @can('borrowed-items.manage')
                        <button type="button" wire:click="openReturn({{ $item->id }})"
                          class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">
                          {{ $adminItem && ! $adminItem->returned_at ? 'Confirm & send to Admin' : 'Confirm return' }}
                        </button>
                        @endcan
                        {{-- ✅ Several items from this request sent back → confirm them all as Good in one go --}}
                        @php $sentBackHere = $this->sentBackCounts[$item->request_id] ?? 0; @endphp
                        @if($sentBackHere > 1)
                          @can('borrowed-items.manage')
                          <button type="button" wire:click="confirmAllGood({{ $item->request_id }})"
                            wire:confirm="Confirm all {{ $sentBackHere }} items from {{ $item->request->user->name ?? 'this faculty member' }} as returned in GOOD condition? (Use “Confirm return” for anything damaged or lost.)"
                            wire:loading.attr="disabled" wire:target="confirmAllGood({{ $item->request_id }})"
                            class="block sm:ml-auto mt-1.5 text-xs font-medium text-[#1C6B45] dark:text-green-300 hover:underline disabled:opacity-60">
                            Confirm all {{ $sentBackHere }} as Good
                          </button>
                          @endcan
                        @endif
                      @elseif($isOverdue)
                        {{-- ✅ Overdue: message the faculty member --}}
                        @if($reminded?->isToday())
                          <span class="inline-flex items-center gap-1.5 min-h-10 px-3 rounded-lg bg-gray-100 text-gray-500 text-xs font-semibold dark:bg-neutral-700 dark:text-neutral-300">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            Reminded today
                          </span>
                        @else
                          @can('borrowed-items.manage')
                          <button type="button" wire:click="sendReminder({{ $item->id }})"
                            wire:confirm="Send a reminder to {{ $item->request->user->name ?? 'the borrower' }} to return {{ $item->item_name }} (x{{ $item->quantity }})?"
                            wire:loading.attr="disabled" wire:target="sendReminder({{ $item->id }})"
                            class="inline-flex items-center gap-1.5 min-h-10 px-3 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 disabled:opacity-60 transition">
                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                            </svg>
                            {{ $reminded ? 'Remind again' : 'Send reminder' }}
                          </button>
                          @endcan
                        @endif
                      @else
                        {{-- Still with the faculty member: nothing to confirm until they click "Return to Program Head" --}}
                        <span class="inline-flex items-center min-h-10 px-3 rounded-lg bg-gray-100 text-gray-500 text-xs font-semibold dark:bg-neutral-700 dark:text-neutral-300">
                          Not returned yet
                        </span>
                      @endif
                    @endcan
                  @endif
                  @if (! $item->returned_at && ! $item->return_requested_at)
                    @can('borrowed-items.manage')
                      {{-- ✅ The teacher keeps it (faculty room furniture): no return date, no overdue --}}
                      <button type="button" wire:click="convertToAssigned({{ $item->id }})"
                        wire:confirm="Let {{ $item->request?->user?->name ?? 'this teacher' }} KEEP {{ $item->item_name }} ×{{ $item->quantity }}? No return date, no reminders — it is handed over to the next teacher when needed."
                        class="block md:ml-auto mt-1.5 text-xs font-medium text-[#1C6B45] dark:text-green-300 hover:underline">
                        Keep (no return)
                      </button>
                    @endcan
                  @endif
                </td>
              </tr>

              {{-- Inline "mark returned" form --}}
              @if($returningId === $item->id)
                <tr wire:key="form-{{ $item->id }}" class="block sm:table-row bg-gray-50 dark:bg-neutral-900/40">
                  <td colspan="5" class="block sm:table-cell px-4 py-4">
                    @can('borrowed-items.manage')
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
                        <button type="button" wire:click="cancelReturn" class="min-h-10 px-3 rounded-lg border border-gray-300 text-sm font-medium bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">Cancel</button>
                        <button type="submit" wire:loading.attr="disabled" wire:target="confirmReturn" class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418] disabled:opacity-60">
                          {{ $adminItem && ! $adminItem->returned_at ? 'Confirm & send to Admin' : 'Confirm return' }}
                        </button>
                      </div>
                    </form>
                    @endcan
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
                <td colspan="5" class="block sm:table-cell">
                  @if(trim($search) !== '')
                    <x-portal.empty message="Nothing matches &quot;{{ $search }}&quot;" hint="Try a different item or name." />
                  @elseif($filter === 'overdue')
                    <x-portal.empty message="No overdue items" hint="Every borrowed item is still on time." />
                  @elseif($filter === 'returned')
                    <x-portal.empty message="No returned items yet" />
                  @else
                    <x-portal.empty message="Nothing is borrowed right now" hint="Items your faculty borrow will show here." />
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
