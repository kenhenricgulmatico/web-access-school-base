<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

  <x-portal.page-header title="Borrowed Items">
    Items you borrowed that must be given back. When you return an item, click
    <strong class="text-gray-700 dark:text-neutral-200">Return to Program Head</strong> — your Program Head will check and confirm it.
  </x-portal.page-header>

  {{-- ✅ Who to give the items back to --}}
  @php $heads = $this->programHeads; @endphp
  <div class="mb-4 flex items-start gap-3 px-4 py-3 rounded-xl bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700">
    <span class="shrink-0 inline-flex items-center justify-center size-9 rounded-full bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]">
      <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
    </span>
    <div class="text-sm">
      <p class="text-gray-500 dark:text-neutral-400">Return items to</p>
      @forelse($heads as $head)
        <p class="font-semibold text-gray-900 dark:text-white">
          {{ $head->name }}
          <span class="font-normal text-gray-500 dark:text-neutral-400">· Program Head{{ $head->department ? ', ' . $head->department->department_name : '' }}</span>
        </p>
      @empty
        <p class="font-semibold text-amber-700 dark:text-amber-300">Your department has no Program Head yet — please ask the Admin.</p>
      @endforelse
    </div>
  </div>

  {{-- ✅ What I keep (no return date) — e.g. a table and chair for my faculty room --}}
  @if ($this->keptCount > 0)
    <div class="mb-5">
      <h3 class="mb-2 text-sm font-semibold text-[#123524] dark:text-neutral-100">Assigned to me · {{ $this->keptCount }}</h3>
      <livewire:assigned-items scope="me" :key="'assigned-me'" />
    </div>
  @endif

  {{-- Flash --}}
  @if(session('message'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-800 dark:bg-green-950/30 dark:border-green-900 dark:text-green-300">{{ session('message') }}</div>
  @endif
  @if(session('error'))
    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">{{ session('error') }}</div>
  @endif

  {{-- Overdue warning --}}
  @if($this->counts['overdue'] > 0)
    <div class="mb-3 flex items-center gap-2 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-800 dark:bg-red-950/30 dark:border-red-900 dark:text-red-300">
      <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
      </svg>
      <span>
        You have <strong>{{ $this->counts['overdue'] }} overdue {{ \Illuminate\Support\Str::plural('item', $this->counts['overdue']) }}</strong>.
        Please return {{ $this->counts['overdue'] === 1 ? 'it' : 'them' }} to your Program Head as soon as possible.
      </span>
    </div>
  @endif

  {{-- ✅ Due today / tomorrow — a heads-up before it becomes overdue --}}
  @if($this->counts['due_soon'] > 0)
    <div class="mb-3 flex items-center gap-2 px-4 py-3 rounded-lg bg-amber-50 border border-amber-200 text-sm text-amber-800 dark:bg-amber-950/30 dark:border-amber-900 dark:text-amber-300">
      <svg class="size-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      <span>
        <strong>{{ $this->counts['due_soon'] }} {{ \Illuminate\Support\Str::plural('item', $this->counts['due_soon']) }}</strong>
        {{ $this->counts['due_soon'] === 1 ? 'is' : 'are' }} due today or tomorrow — return {{ $this->counts['due_soon'] === 1 ? 'it' : 'them' }} on time.
      </span>
    </div>
  @endif

  {{-- Tabs --}}
  <div class="mt-1 mb-4">
    <x-portal.tabs label="Filter borrowed items">
      @foreach(['outstanding' => 'Not returned', 'returned' => 'Returned'] as $key => $label)
        <x-portal.tab :active="$filter === $key" :count="$this->counts[$key]" wire:click="$set('filter', '{{ $key }}')">{{ $label }}</x-portal.tab>
      @endforeach
    </x-portal.tabs>
  </div>

  @php $rows = $this->items; @endphp

  {{-- ✅ Phone: one card per item, big buttons, no sideways scrolling --}}
  <div class="sm:hidden space-y-3">
    @forelse($rows as $item)
      @php $info = $this->rowInfo($item); @endphp
      <div wire:key="card-{{ $item->id }}" class="p-4 rounded-2xl bg-white dark:bg-neutral-800 border {{ $info['isOverdue'] ? 'border-red-300 dark:border-red-800' : 'border-gray-200 dark:border-neutral-700' }}">
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0">
            <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
            <p class="text-xs text-gray-500 dark:text-neutral-400">Qty {{ number_format($item->quantity) }} ·
              <a href="{{ route('portal.view-material-reservation', $item->request_id) }}" wire:navigate class="hover:underline">View request</a>
            </p>
          </div>
          <div class="text-right shrink-0">
            <p class="text-[11px] text-gray-500 dark:text-neutral-400">Return by</p>
            <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $item->due_back_at->format('M d, Y') }}</p>
          </div>
        </div>
        <div class="mt-3">@include('partials.borrowed-item-status')</div>
        <div class="mt-3">@include('partials.borrowed-item-actions')</div>
      </div>
    @empty
      <div class="rounded-2xl bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700">
        @include('partials.borrowed-items-empty')
      </div>
    @endforelse
  </div>

  {{-- Tablet / desktop: table --}}
  <div class="hidden sm:block bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead class="bg-[#F3F8F5] dark:bg-neutral-900/50 text-left text-xs uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
          <tr>
            <th class="px-4 py-3 font-semibold">Item</th>
            <th class="px-4 py-3 font-semibold">Return by</th>
            <th class="px-4 py-3 font-semibold">Status</th>
            <th class="px-4 py-3 font-semibold text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
          @forelse($rows as $item)
            @php $info = $this->rowInfo($item); @endphp
            <tr wire:key="borrowed-{{ $item->id }}" class="align-top hover:bg-[#F3F8F5]/60 dark:hover:bg-neutral-900/30 transition">
              <td class="px-4 py-3">
                <p class="font-semibold text-gray-900 dark:text-neutral-100">{{ $item->item_name }}</p>
                <p class="text-xs text-gray-500 dark:text-neutral-400">Qty {{ number_format($item->quantity) }} ·
                  <a href="{{ route('portal.view-material-reservation', $item->request_id) }}" wire:navigate class="hover:underline">View request</a>
                </p>
              </td>
              <td class="px-4 py-3 whitespace-nowrap text-gray-700 dark:text-neutral-300">{{ $item->due_back_at->format('M d, Y') }}</td>
              <td class="px-4 py-3">@include('partials.borrowed-item-status')</td>
              <td class="px-4 py-3 text-right whitespace-nowrap">
                <div class="inline-flex flex-col items-end">@include('partials.borrowed-item-actions')</div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4">
                @include('partials.borrowed-items-empty')
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div class="mt-4">{{ $rows->links() }}</div>
</div>
</div>
