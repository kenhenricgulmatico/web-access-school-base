<div class="select-none">
<div class="max-w-7xl mx-auto px-4 py-6 sm:px-6 sm:py-10 lg:px-8">

  {{-- Header --}}
  <div class="mb-6">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Borrowed Items</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
      Items counted in <strong>pieces (Pcs)</strong> that Program Heads borrow — like chairs.
      Accept a request with a <strong>return deadline</strong>, then mark the items returned.
    </p>
  </div>

  {{-- Flash --}}
  @if(session('message'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-800 dark:bg-green-950/30 dark:border-green-900 dark:text-green-300">{{ session('message') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">{{ session('error') }}</div>
  @endif

  {{-- ✅ Program Heads waiting for the Admin to confirm a return --}}
  @if($this->counts['sentBack'] > 0)
    <div class="mb-4 px-4 py-3 rounded-lg bg-blue-50 border border-blue-200 text-sm text-blue-800 dark:bg-blue-950/30 dark:border-blue-900 dark:text-blue-300">
      {{ $this->counts['sentBack'] }} {{ \Illuminate\Support\Str::plural('item', $this->counts['sentBack']) }} returned by Program Heads {{ $this->counts['sentBack'] === 1 ? 'is' : 'are' }} waiting for you to confirm.
      <button type="button" wire:click="$set('filter', 'outstanding')" class="font-semibold underline ml-1">View</button>
    </div>
  @endif

  {{-- Tabs + search --}}
  <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
    <div class="inline-flex p-1 bg-gray-100 dark:bg-neutral-800 rounded-xl gap-1" role="tablist" aria-label="Filter borrowed items">
      @foreach(['pending' => 'To approve', 'outstanding' => 'Not returned', 'overdue' => 'Overdue', 'returned' => 'Returned'] as $key => $label)
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

    <label class="sr-only" for="admin-borrowed-search">Search</label>
    <input id="admin-borrowed-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search item, program head or department…"
      class="w-full sm:w-72 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
  </div>

  {{-- ============================================================
       ✅ TO APPROVE — pending Program Head requests with Pcs items
       ============================================================ --}}
  @if($filter === 'pending')
  <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-400">
          <tr>
            <th class="px-4 py-3 font-semibold">Requested items</th>
            <th class="px-4 py-3 font-semibold">Requested by</th>
            <th class="px-4 py-3 font-semibold">Needed on</th>
            <th class="px-4 py-3 font-semibold">Return deadline</th>
            <th class="px-4 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
          @forelse($this->pendingRequests as $req)
            @php
              $neededOn = $req->items->min('request_date');
              $minDate  = $neededOn && \Carbon\Carbon::parse($neededOn)->gte(today())
                ? \Carbon\Carbon::parse($neededOn)->format('Y-m-d')
                : today()->format('Y-m-d');
            @endphp
            <tr wire:key="pending-{{ $req->id }}" class="align-top">
              <td class="px-4 py-3">
                <ul class="space-y-1">
                  @foreach($req->items as $ri)
                    <li class="flex items-center gap-2">
                      @if($ri->resource?->unit === 'Pcs')
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
              </td>
              <td class="px-4 py-3 text-gray-700 dark:text-neutral-300">
                {{ $req->user->name ?? '—' }}
                <p class="text-xs text-gray-500">{{ $req->department->department_name ?? ($req->user->department->department_name ?? '') }}</p>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300">
                {{ $neededOn ? \Carbon\Carbon::parse($neededOn)->format('M d, Y') : '—' }}
              </td>
              <td class="px-4 py-3">
                <label for="deadline-{{ $req->id }}" class="sr-only">Return deadline</label>
                <input id="deadline-{{ $req->id }}" type="date" wire:model="deadlines.{{ $req->id }}" min="{{ $minDate }}"
                  class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
                @error('deadlines.' . $req->id)
                  <p class="text-xs text-red-500 mt-1 max-w-56">{{ $message }}</p>
                @enderror
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <button type="button" wire:click="accept({{ $req->id }})" wire:loading.attr="disabled" wire:target="accept({{ $req->id }})"
                  class="min-h-10 px-4 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 disabled:opacity-60 transition">
                  Accept
                </button>
                <button type="button" wire:click="openReject({{ $req->id }})"
                  class="min-h-10 px-4 ml-1 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition">
                  Reject
                </button>
              </td>
            </tr>

            {{-- ✅ Inline "reject" form --}}
            @if($rejectingRequestId === $req->id)
              <tr wire:key="reject-{{ $req->id }}" class="bg-red-50/50 dark:bg-red-950/20">
                <td colspan="5" class="px-4 py-4">
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
            <tr>
              <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-500">No borrow requests waiting for approval.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">{{ $this->pendingRequests->links() }}</div>
  @else

  {{-- Table --}}
  <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-gray-50 dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-gray-500 dark:text-neutral-400">
          <tr>
            <th class="px-4 py-3 font-semibold">Item</th>
            <th class="px-4 py-3 font-semibold">Borrowed by</th>
            <th class="px-4 py-3 font-semibold">Return by</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
          @forelse($this->items as $item)
            @php
              $dueDate = \Carbon\Carbon::parse($item->due_date);
              $label   = $this->statusLabel($item);
              $badge   = match (true) {
                $item->returned_at && $item->return_condition === 'good' => 'bg-green-100 text-green-800',
                (bool) $item->returned_at                                 => 'bg-orange-100 text-orange-800',
                (bool) $item->return_requested_at                         => 'bg-blue-100 text-blue-800',
                $dueDate->lt(today())                                     => 'bg-red-100 text-red-800',
                $dueDate->isToday()                                       => 'bg-amber-100 text-amber-800',
                default                                                   => 'bg-gray-100 text-gray-700',
              };
            @endphp
            <tr wire:key="item-{{ $item->id }}">
              <td class="px-4 py-3">
                <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                <p class="text-xs text-gray-500">{{ number_format($item->quantity) }} pcs · Request #{{ $item->request_id }}</p>
              </td>
              <td class="px-4 py-3 text-gray-700 dark:text-neutral-300">
                {{ $item->request->user->name ?? '—' }}
                <p class="text-xs text-gray-500">
                  {{ $item->request->department->department_name ?? ($item->request->user->department->department_name ?? '') }}
                </p>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300">{{ $dueDate->format('M d, Y') }}</td>
              <td class="px-4 py-3">
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
                  <p class="text-xs font-medium text-blue-700 dark:text-blue-300 mt-1">
                    Program Head returned it {{ \Carbon\Carbon::parse($item->return_requested_at)->format('M d, g:i A') }} — please check and confirm
                  </p>
                @endif
              </td>
              <td class="px-4 py-3 text-right">
                @if(! $item->returned_at)
                  <button type="button" wire:click="openReturn({{ $item->id }})"
                    class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">
                    {{ $item->return_requested_at ? 'Confirm return' : 'Mark returned' }}
                  </button>
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
                      <input id="notes-{{ $item->id }}" type="text" wire:model="returnNotes" maxlength="500" placeholder="e.g. 2 chairs have loose legs"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800">
                      @error('returnNotes') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex gap-2">
                      <button type="button" wire:click="cancelReturn" class="min-h-10 px-3 rounded-lg border border-gray-300 text-sm font-medium bg-white hover:bg-gray-50">Cancel</button>
                      <button type="submit" class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418]">Confirm return</button>
                    </div>
                  </form>
                  <p class="text-xs text-gray-500 mt-2">Items returned in <strong>Good</strong> condition go back to the main stock. Damaged or lost items don't.</p>
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
