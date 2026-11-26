<div class="select-none">
<div class="max-w-5xl mx-auto px-4 py-6 sm:px-6 sm:py-10 lg:px-8">

  {{-- Header --}}
  <div class="mb-6">
    <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">Borrowed Items</h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
      Items you borrowed that must be given back. When you return an item, click
      <strong>Return to Program Head</strong> — your Program Head will check and confirm it.
    </p>
  </div>

  {{-- Flash --}}
  @if(session('message'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-800 dark:bg-green-950/30 dark:border-green-900 dark:text-green-300">{{ session('message') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">{{ session('error') }}</div>
  @endif

  {{-- ✅ Overdue warning --}}
  @if($this->counts['overdue'] > 0)
    <div class="mb-4 flex items-center gap-2 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">
      <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
      </svg>
      <span>
        You have <strong>{{ $this->counts['overdue'] }} overdue {{ \Illuminate\Support\Str::plural('item', $this->counts['overdue']) }}</strong>.
        Please return {{ $this->counts['overdue'] === 1 ? 'it' : 'them' }} to your Program Head as soon as possible.
      </span>
    </div>
  @endif

  {{-- Tabs --}}
  <div class="inline-flex p-1 mb-4 bg-gray-100 dark:bg-neutral-800 rounded-xl gap-1" role="tablist" aria-label="Filter borrowed items">
    @foreach(['outstanding' => 'Not returned', 'returned' => 'Returned'] as $key => $label)
      <button type="button" role="tab" wire:click="$set('filter', '{{ $key }}')" aria-selected="{{ $filter === $key ? 'true' : 'false' }}"
        class="min-h-10 px-4 rounded-lg text-sm font-semibold transition inline-flex items-center gap-2
          {{ $filter === $key ? 'bg-white dark:bg-neutral-700 text-[#123524] dark:text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:text-[#123524]' }}">
        {{ $label }}
        <span class="text-xs px-1.5 py-0.5 rounded-full
          {{ $key === 'outstanding' && $this->counts['overdue'] > 0 ? 'bg-red-100 text-red-700' : 'bg-gray-200 text-gray-700 dark:bg-neutral-600 dark:text-neutral-200' }}">
          {{ $this->counts[$key] }}
        </span>
      </button>
    @endforeach
  </div>

  {{-- Table --}}
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
          @forelse($this->items as $item)
            @php
              $label     = $this->statusLabel($item);
              $isOverdue = $item->isOverdue() && ! $item->return_requested_at;
              $reminder  = $isOverdue ? $this->reminder($item) : null;
              $badge     = match (true) {
                $item->returned_at && $item->return_condition === 'good' => 'bg-green-100 text-green-800',
                (bool) $item->returned_at                                 => 'bg-orange-100 text-orange-800',
                (bool) $item->return_requested_at                         => 'bg-blue-100 text-blue-800',
                $isOverdue                                                => 'bg-red-100 text-red-800',
                $item->due_back_at->isToday()                             => 'bg-amber-100 text-amber-800',
                default                                                   => 'bg-gray-100 text-gray-700',
              };
            @endphp
            <tr wire:key="borrowed-{{ $item->id }}" class="align-top">
              <td class="px-4 py-3">
                <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                <p class="text-xs text-gray-500">Qty {{ number_format($item->quantity) }} ·
                  <a href="{{ route('portal.view-material-reservation', $item->request_id) }}" wire:navigate class="hover:underline">Request #{{ $item->request_id }}</a>
                </p>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300">{{ $item->due_back_at->format('M d, Y') }}</td>
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
                @elseif($reminder)
                  {{-- ✅ Message from the Program Head --}}
                  <div class="mt-2 max-w-sm px-3 py-2 rounded-lg bg-red-50 border border-red-100 dark:bg-red-950/30 dark:border-red-900">
                    <p class="flex items-center gap-1 text-[11px] font-semibold text-red-700 dark:text-red-300">
                      <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
                      </svg>
                      Message from your Program Head · {{ $reminder->created_at->diffForHumans() }}
                    </p>
                    <p class="text-xs text-red-800 dark:text-red-200 mt-0.5 whitespace-normal">{{ $reminder->message }}</p>
                  </div>
                @endif
              </td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                @if(! $item->returned_at && ! $item->return_requested_at)
                  <button type="button" wire:click="requestReturn({{ $item->id }})"
                    wire:confirm="Return {{ $item->item_name }} (x{{ $item->quantity }}) to your Program Head?"
                    wire:loading.attr="disabled" wire:target="requestReturn({{ $item->id }})"
                    class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] disabled:opacity-60 transition">
                    Return to Program Head
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
              <td colspan="4" class="px-4 py-12 text-center text-sm text-gray-500">
                @if($filter === 'returned') No returned items yet.
                @else You have no borrowed items to return.
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">{{ $this->items->links() }}</div>
</div>
</div>
