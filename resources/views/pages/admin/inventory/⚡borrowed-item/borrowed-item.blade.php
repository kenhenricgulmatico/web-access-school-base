<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

  {{-- Header --}}
  <div class="mb-6">
    <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Borrowed Items</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
      <strong>Material</strong> items that Program Heads borrow and must return — like chairs and projectors. (Supplies such as bond paper are used up and not listed here.)
      Accept a request with a <strong>return deadline</strong>. When the Program Head clicks <strong>Return to Admin</strong>, check the items and confirm the return.
      If an item is <strong>overdue</strong>, send the Program Head a reminder.
    </p>
  </div>

  {{-- Flash --}}
  @if(session('message'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-sm text-[#123524] dark:bg-[#1C6B45]/30 dark:border-[#1C6B45/40] dark:text-green-300">{{ session('message') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">{{ session('error') }}</div>
  @endif

  {{-- ✅ Program Heads waiting for the Admin to confirm a return --}}
  @if($this->counts['sentBack'] > 0)
    <div class="mb-4 px-4 py-3 rounded-lg bg-[#F3F8F5] border border-[#1C6B45/25] text-sm text-[#123524] dark:bg-[#1C6B45]/30 dark:border-[#1C6B45/40] dark:text-[#9FD3AE]">
      {{ $this->counts['sentBack'] }} {{ \Illuminate\Support\Str::plural('item', $this->counts['sentBack']) }} returned by Program Heads {{ $this->counts['sentBack'] === 1 ? 'is' : 'are' }} waiting for you to confirm.
      <button type="button" wire:click="$set('filter', 'outstanding')" class="font-semibold underline ml-1">View</button>
    </div>
  @endif

  {{-- ✅ Due back today / tomorrow — remind people before they're late --}}
  @if($this->counts['dueSoon'] > 0)
    <div class="mb-4 flex flex-wrap items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800 dark:bg-amber-950/30 dark:border-amber-900 dark:text-amber-300">
      <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      <span>
        <strong>{{ $this->counts['dueSoon'] }} {{ \Illuminate\Support\Str::plural('item', $this->counts['dueSoon']) }}</strong>
        {{ $this->counts['dueSoon'] === 1 ? 'is' : 'are' }} due back today or tomorrow.
      </span>
      @if($filter !== 'outstanding')
        <button type="button" wire:click="$set('filter', 'outstanding')" class="font-semibold underline">View</button>
      @endif
    </div>
  @endif

  {{-- Tabs + search --}}
  <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="inline-flex p-1 bg-gray-100 dark:bg-neutral-800 rounded-xl gap-1" role="tablist" aria-label="Filter borrowed items">
      @foreach(['pending' => 'To approve', 'outstanding' => 'Not returned', 'overdue' => 'Overdue', 'returned' => 'Returned', 'assigned' => 'Assigned'] as $key => $label)
        <button type="button" role="tab" wire:click="$set('filter', '{{ $key }}')" aria-selected="{{ $filter === $key ? 'true' : 'false' }}"
          class="min-h-10 px-4 rounded-lg text-sm font-semibold transition inline-flex items-center gap-2
            {{ $filter === $key ? 'bg-white dark:bg-neutral-700 text-[#123524] dark:text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:text-[#123524]' }}">
          {{ $label }}
          <span class="text-xs px-1.5 py-0.5 rounded-full
            {{ match (true) {
                $key === 'overdue' && $this->counts['overdue'] > 0 => 'bg-red-100 text-red-700',
                $key === 'pending' && $this->counts['pending'] > 0 => 'bg-amber-100 text-amber-800',
                default => 'bg-gray-200 text-gray-700 dark:bg-neutral-600 dark:text-neutral-200',
            } }}">
            {{ $this->counts[$key] }}
          </span>
        </button>
      @endforeach
    </div>

    @if ($filter !== 'assigned') {{-- the Assigned list has its own search --}}
    <label class="sr-only" for="admin-borrowed-search">Search</label>
    <input id="admin-borrowed-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search item, program head or department…"
      class="w-full sm:w-72 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
    @endif
  </div>

  {{-- ============================================================
       ✅ TO APPROVE — pending Program Head requests with Pcs items
       ============================================================ --}}
  @if($filter === 'pending')
  <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
    <div class="@container">
      <table class="min-w-full text-sm block @3xl:table">
        <thead class="hidden @3xl:table-header-group bg-[#F3F8F5] dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-[#1C6B45] dark:text-neutral-400">
          <tr>
            <th class="px-4 py-3 font-semibold">Requested items</th>
            <th class="px-4 py-3 font-semibold">Requested by</th>
            <th class="px-4 py-3 font-semibold">Needed on</th>
            <th class="px-4 py-3 font-semibold">Return deadline</th>
            <th class="px-4 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="block @3xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
          @forelse($this->pendingRequests as $req)
            @php
              $neededOn = $req->items->min('request_date');
              $minDate  = $neededOn && \Carbon\Carbon::parse($neededOn)->gte(today())
                ? \Carbon\Carbon::parse($neededOn)->format('Y-m-d')
                : today()->format('Y-m-d');
            @endphp
            <tr wire:key="pending-{{ $req->id }}" class="block @3xl:table-row align-top px-4 py-4 @3xl:p-0">
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3">
                <ul class="space-y-1">
                  @foreach($req->items as $ri)
                    <li class="flex items-center gap-2">
                      @if($ri->resource?->is_returnable)
                        <span class="inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">BORROW</span>
                      @endif
                      <span class="font-semibold text-gray-900 dark:text-neutral-100">{{ $ri->item_name }}</span>
                      @if($ri->resource_id)
                        <span class="text-xs text-gray-500">x{{ number_format($ri->quantity) }} {{ $ri->resource?->unit ?? '' }}</span>
                      @elseif($ri->start_time)
                        <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($ri->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($ri->end_time)->format('g:i A') }}</span>
                      @endif
                    </li>
                  @endforeach
                </ul>

                @if($req->purpose)
                  <p class="text-xs text-gray-400 italic mt-0.5">"{{ \Illuminate\Support\Str::limit($req->purpose, 60) }}"</p>
                @endif

                {{-- ✅ Is there enough in the main stock? --}}
                @php $short = $this->shortItems($req); @endphp
                @if(empty($short))
                  <p class="mt-1.5 text-xs font-medium text-[#1C6B45] dark:text-green-400">✓ In stock — {{ $this->stockLine($req) }}</p>
                @else
                  <p class="mt-1.5 max-w-sm text-xs text-red-700 dark:text-red-300"><span class="font-semibold">Not enough:</span> {{ implode(', ', $short) }}</p>
                @endif
              </td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3 text-gray-700 dark:text-neutral-300">
                {{ $req->user->name ?? '—' }}
                <p class="text-xs text-gray-500">{{ $req->department->department_name ?? ($req->user->department->department_name ?? '') }}</p>
              </td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3 @3xl:whitespace-nowrap text-gray-700 dark:text-neutral-300">
                <span class="@3xl:hidden text-xs text-gray-500">Needed on </span>{{ $neededOn ? \Carbon\Carbon::parse($neededOn)->format('M d, Y') : '—' }}
              </td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3">
                {{-- ✅ Borrow (return by a date) or Assign (they keep it — e.g. furniture for a faculty room) --}}
                @php $mode = $modes[$req->id] ?? 'borrow'; @endphp
                <div class="inline-flex p-0.5 mb-1.5 rounded-lg bg-gray-100 dark:bg-neutral-700 text-xs font-semibold" role="radiogroup" aria-label="Borrow or assign">
                  @foreach (['borrow' => 'Borrow', 'assign' => 'Assign (keep)'] as $mk => $ml)
                    <label class="cursor-pointer">
                      <input type="radio" class="sr-only peer" wire:model.live="modes.{{ $req->id }}" value="{{ $mk }}" @checked($mode === $mk)>
                      <span class="block px-2.5 py-1 rounded-md transition peer-checked:bg-white peer-checked:text-[#123524] peer-checked:shadow-sm dark:peer-checked:bg-neutral-900 dark:peer-checked:text-green-300 text-gray-600 dark:text-neutral-300">{{ $ml }}</span>
                    </label>
                  @endforeach
                </div>
                @if ($mode === 'assign')
                  <p class="text-xs text-gray-600 dark:text-neutral-300 max-w-56">No return date — they keep it (e.g. faculty room furniture) and it can be handed over to the next teacher.</p>
                @else
                <label for="deadline-{{ $req->id }}" class="sr-only">Return deadline</label>
                <input id="deadline-{{ $req->id }}" type="date" wire:model.live.debounce.400ms="deadlines.{{ $req->id }}" min="{{ $minDate }}"
                  class="block px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
                @endif
                @error('deadlines.' . $req->id)
                  <p class="text-xs text-red-500 mt-1 max-w-56">{{ $message }}</p>
                @enderror
              </td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 pt-3 @3xl:py-3 @3xl:text-right @3xl:whitespace-nowrap">
                @can('borrowed-items.manage')
                <button type="button" wire:click="accept({{ $req->id }})" wire:loading.attr="disabled" wire:target="accept({{ $req->id }})"
                  wire:confirm="{{ $this->acceptConfirm($req) }}"
                  class="min-h-10 px-4 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] disabled:opacity-60 transition">
                  Accept
                </button>
                @endcan
                @can('borrowed-items.manage')
                <button type="button" wire:click="openReject({{ $req->id }})"
                  class="min-h-10 px-4 ml-1 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">
                  Reject
                </button>
                @endcan
              </td>
            </tr>

            {{-- ✅ Inline "reject" form --}}
            @if($rejectingRequestId === $req->id)
              <tr wire:key="reject-{{ $req->id }}" class="block @3xl:table-row bg-red-50/50 dark:bg-red-950/20">
                <td colspan="5" class="block @3xl:table-cell px-4 py-4">
                  <form wire:submit="confirmReject" class="flex flex-col sm:flex-row sm:items-end gap-3">
                    <div class="flex-1">
                      <label for="reject-reason-{{ $req->id }}" class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Reason for rejecting (optional)</label>
                      <input id="reject-reason-{{ $req->id }}" type="text" wire:model="rejectReason" maxlength="500" placeholder="e.g. Not enough chairs available on that date"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800">
                      @error('rejectReason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2">
                      <button type="button" wire:click="cancelReject" class="min-h-10 px-3 rounded-lg border border-gray-300 text-sm font-medium bg-white hover:bg-gray-50">Cancel</button>
                      <button type="submit" class="min-h-10 px-3 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700">Confirm reject</button>
                    </div>
                  </form>
                </td>
              </tr>
            @endif
          @empty
            <tr class="block @3xl:table-row">
              <td colspan="5" class="block @3xl:table-cell px-4 py-12 text-center text-sm text-gray-500">No borrow requests waiting for approval.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">{{ $this->pendingRequests->links() }}</div>
  @elseif($filter === 'assigned')
    {{-- ✅ Kept items (no return date) — hand over to the next teacher, or take back into stock --}}
    <livewire:assigned-items scope="admin" :key="'assigned-admin'" />
  @else

  {{-- Table --}}
  <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
    <div class="@container">
      <table class="min-w-full text-sm block @3xl:table">
        <thead class="hidden @3xl:table-header-group bg-[#F3F8F5] dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-[#1C6B45] dark:text-neutral-400">
          <tr>
            <th class="px-4 py-3 font-semibold">Item</th>
            <th class="px-4 py-3 font-semibold">Borrowed by</th>
            <th class="px-4 py-3 font-semibold">Return by</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="block @3xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
          @forelse($this->items as $item)
            @php
              $dueDate      = \Carbon\Carbon::parse($item->due_date);
              $label        = $this->statusLabel($item);
              $isOverdue    = ! $item->returned_at && $dueDate->lt(today());
              $lastReminder = $isOverdue ? $this->lastReminder($item) : null;
              // ✅ Borrowed for a faculty member: who has it right now?
              $facultyItem  = ($item->returned_at || $item->return_requested_at) ? null : \App\Support\ReturnTracking::facultyItemFor($item);
              $withFaculty  = $facultyItem && $facultyItem->request?->status === 'approved' && ! $facultyItem->returned_at;
              $badge        = match (true) {
                $item->returned_at && $item->return_condition === 'good' => 'bg-green-100 text-[#123524]',
                (bool) $item->returned_at                                 => 'bg-amber-100 text-amber-800',
                (bool) $item->return_requested_at                         => 'bg-[#E8F3EC] text-[#123524]',
                $dueDate->lt(today())                                     => 'bg-red-100 text-red-800',
                $dueDate->lte(today()->addDay())                          => 'bg-amber-100 text-amber-800',
                default                                                   => 'bg-gray-100 text-gray-700',
              };
            @endphp
            <tr wire:key="item-{{ $item->id }}" class="block @3xl:table-row px-4 py-4 @3xl:p-0">
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3">
                <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                <p class="text-xs text-gray-500">{{ number_format($item->quantity) }} pcs</p>
              </td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3 text-gray-700 dark:text-neutral-300">
                {{ $item->request->user->name ?? '—' }}
                <p class="text-xs text-gray-500">
                  {{ $item->request->department->department_name ?? ($item->request->user->department->department_name ?? '') }}
                </p>
              </td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3 @3xl:whitespace-nowrap text-gray-700 dark:text-neutral-300"><span class="@3xl:hidden text-xs text-gray-500">Return by </span>{{ $dueDate->format('M d, Y') }}</td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 py-1 @3xl:py-3">
                <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $badge }}">{{ $label }}</span>
                @if($item->returned_at)
                  <p class="text-xs text-gray-500 mt-1">
                    {{ $item->returned_at->format('M d, Y') }}@if($item->receiver) · by {{ $item->receiver->name }}@endif
                  </p>
                  @if($item->return_notes)
                    <p class="text-xs text-gray-500 italic">"{{ $item->return_notes }}"</p>
                  @endif
                @elseif($item->return_requested_at)
                  {{-- ✅ Program Head clicked "Return to Admin" --}}
                  <p class="text-xs font-medium text-[#1C6B45] dark:text-[#9FD3AE] mt-1">
                    Program Head returned it {{ \Carbon\Carbon::parse($item->return_requested_at)->format('M d, g:i A') }} — please check and confirm
                  </p>
                @else
                  {{-- ✅ Where the item is right now --}}
                  <p class="text-xs text-gray-500 mt-1">
                    @if($withFaculty)
                      With {{ $facultyItem->request?->user?->name ?? 'a faculty member' }} (faculty) — the Program Head returns it after they get it back
                    @else
                      With the Program Head — waiting for "Return to Admin"
                    @endif
                  </p>
                  @if($isOverdue && $lastReminder)
                    {{-- ✅ Reminder already sent --}}
                    <p class="text-xs text-gray-500">
                      Reminder sent {{ $lastReminder->isToday() ? 'today, ' . $lastReminder->format('g:i A') : $lastReminder->format('M d, g:i A') }}
                    </p>
                  @endif
                @endif
              </td>
              <td class="block @3xl:table-cell px-0 @3xl:px-4 pt-3 @3xl:py-3 @3xl:text-right @3xl:whitespace-nowrap">
                @if(! $item->returned_at)
                  @if($item->return_requested_at)
                    {{-- ✅ Program Head returned it → the Admin checks and confirms --}}
                    @can('borrowed-items.manage')
                    <button type="button" wire:click="openReturn({{ $item->id }})"
                      class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">
                      Confirm return
                    </button>
                    @endcan
                    {{-- ✅ Several items from this request sent back → confirm them all as Good --}}
                    @php $sentBackHere = $this->sentBackCounts[$item->request_id] ?? 0; @endphp
                    @if($sentBackHere > 1)
                      @can('borrowed-items.manage')
                      <button type="button" wire:click="confirmAllGood({{ $item->request_id }})"
                        wire:confirm="Confirm all {{ $sentBackHere }} items from {{ $item->request->user->name ?? 'this Program Head' }} as returned in GOOD condition? They go back to the main stock. (Use “Confirm return” for anything damaged or lost.)"
                        wire:loading.attr="disabled" wire:target="confirmAllGood({{ $item->request_id }})"
                        class="block @3xl:ml-auto mt-1.5 text-xs font-medium text-[#1C6B45] dark:text-green-300 hover:underline disabled:opacity-60">
                        Confirm all {{ $sentBackHere }} as Good
                      </button>
                      @endcan
                    @endif
                  @elseif($isOverdue)
                    {{-- ✅ Overdue: message the Program Head instead of "Mark returned" --}}
                    @if($lastReminder?->isToday())
                      <span class="inline-flex items-center gap-1.5 min-h-10 px-3 rounded-lg bg-gray-100 text-gray-500 text-xs font-semibold dark:bg-neutral-700 dark:text-neutral-300">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        Reminded today
                      </span>
                    @else
                      @can('borrowed-items.manage')
                      <button type="button" wire:click="sendReminder({{ $item->id }})"
                        wire:confirm="Send a reminder to {{ $item->request->user->name ?? 'the Program Head' }} to return {{ $item->quantity }} pcs of {{ $item->item_name }}?"
                        wire:loading.attr="disabled" wire:target="sendReminder({{ $item->id }})"
                        class="inline-flex items-center gap-1.5 min-h-10 px-3 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 disabled:opacity-60 transition">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                        </svg>
                        {{ $lastReminder ? 'Remind again' : 'Send reminder' }}
                      </button>
                      @endcan
                    @endif
                  @else
                    {{-- Not returned yet and not overdue: nothing to do until the Program Head returns it --}}
                    <span class="inline-flex items-center min-h-10 px-3 rounded-lg bg-gray-100 text-gray-500 text-xs font-semibold dark:bg-neutral-700 dark:text-neutral-300">
                      Not returned yet
                    </span>
                  @endif
                @endif
                @if (! $item->returned_at && ! $item->return_requested_at)
                  @can('borrowed-items.manage')
                    {{-- ✅ They keep it (e.g. faculty room furniture): no return date, no overdue --}}
                    <button type="button" wire:click="convertToAssigned({{ $item->id }})"
                      wire:confirm="Change to ASSIGNED? {{ $item->item_name }} ×{{ $item->quantity }} stays with the borrower with no return date (no overdue, no reminders) and can be handed over to the next teacher."
                      class="block @3xl:ml-auto mt-1.5 text-xs font-medium text-[#1C6B45] dark:text-green-300 hover:underline">
                      Assign instead (keep)
                    </button>
                  @endcan
                @endif
              </td>
            </tr>

            {{-- Inline "mark returned" form --}}
            @if($returningId === $item->id)
              <tr wire:key="form-{{ $item->id }}" class="block @3xl:table-row bg-gray-50 dark:bg-neutral-900/40">
                <td colspan="5" class="block @3xl:table-cell px-4 py-4">
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
                      <input id="notes-{{ $item->id }}" type="text" wire:model="returnNotes" maxlength="500" placeholder="e.g. 2 chairs have loose legs"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800">
                      @error('returnNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2">
                      <button type="button" wire:click="cancelReturn" class="min-h-10 px-3 rounded-lg border border-gray-300 text-sm font-medium bg-white hover:bg-gray-50">Cancel</button>
                      <button type="submit" wire:loading.attr="disabled" wire:target="confirmReturn" class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418] disabled:opacity-60">Confirm return</button>
                    </div>
                  </form>
                  @endcan
                  <p class="text-xs text-gray-500 mt-2">Items returned in <strong>Good</strong> condition go back to the main stock. Damaged or lost items don't.</p>
                </td>
              </tr>
            @endif
          @empty
            <tr class="block @3xl:table-row">
              <td colspan="5" class="block @3xl:table-cell px-4 py-12 text-center text-sm text-gray-500">
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
