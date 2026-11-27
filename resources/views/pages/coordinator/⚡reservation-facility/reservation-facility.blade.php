<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

  <x-portal.page-header title="Facility Requests">
    @if($this->counts['pending'])<strong class="text-amber-700 dark:text-amber-300">{{ $this->counts['pending'] }} waiting for you</strong> · @endif Review and manage facility reservations from faculty
    <x-slot:actions>
      <div class="inline-flex items-center gap-1 p-1 rounded-xl bg-[#F3F8F5] ring-1 ring-inset ring-[#1C6B45]/10 dark:bg-neutral-900/60 dark:ring-neutral-700">
            @foreach(['coordinator.facility' => 'Facility', 'coordinator.material' => 'Material'] as $route => $label)
              <a href="{{ route($route) }}" wire:navigate
                 class="px-4 py-1.5 text-sm font-semibold rounded-lg transition {{ request()->routeIs($route) ? 'bg-white text-[#123524] shadow-sm ring-1 ring-[#1C6B45]/15 dark:bg-neutral-700 dark:text-white dark:ring-0' : 'text-gray-600 hover:text-[#123524] dark:text-neutral-400 dark:hover:text-white' }}">{{ $label }}</a>
            @endforeach
          </div>
    </x-slot:actions>
  </x-portal.page-header>

  <div class="@container bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">

    {{-- Flash messages --}}
    @if (session('message'))
      <div class="px-6 py-3 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-900 text-sm text-[#1C6B45] dark:text-[#7FBF8E]">
        {{ session('message') }}
      </div>
    @endif
    @if (session('error'))
      <div class="px-6 py-3 bg-red-50 dark:bg-red-900/20 border-b border-red-100 dark:border-red-900 text-sm text-red-700 dark:text-red-400">
        {{ session('error') }}
      </div>
    @endif

    {{-- ✅ Tabs + search --}}
    <div class="px-4 sm:px-6 py-3 flex flex-col @2xl:flex-row @2xl:items-center justify-between gap-3 border-b border-gray-100 dark:border-neutral-700">
      <x-portal.tabs label="Filter facility requests">
        @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label)
          <x-portal.tab :active="$tab === $key" :count="$this->counts[$key]" wire:click="$set('tab', '{{ $key }}')">{{ $label }}</x-portal.tab>
        @endforeach
      </x-portal.tabs>
      <label class="sr-only" for="fac-search">Search</label>
      <input id="fac-search" type="search" wire:model.live.debounce.300ms="search" placeholder="Search faculty or room…"
        class="w-full @2xl:w-64 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-[#1C6B45]/30 focus:border-[#1C6B45] focus:outline-none">
    </div>

    {{-- Table --}}
    <div wire:loading.class="opacity-60" wire:target="tab, search">
      <table class="min-w-full block @4xl:table">
        <thead class="hidden @4xl:table-header-group">
          <tr class="border-b border-gray-100 dark:border-neutral-700 bg-[#F3F8F5] dark:bg-neutral-900/40">
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#1C6B45] dark:text-[#7FBF8E]">Requestor</th>
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#1C6B45] dark:text-[#7FBF8E]">Facility</th>
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#1C6B45] dark:text-[#7FBF8E]">Schedule</th>
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#1C6B45] dark:text-[#7FBF8E]">Purpose</th>
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-[#1C6B45] dark:text-[#7FBF8E]">Status</th>
            <th class="px-6 py-3 w-14"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>

        <tbody class="block @4xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
          @forelse($this->reservations as $reservation)
            @php
              $facilityItem = $reservation->items->firstWhere('resource_id', null);
              $isPending    = $reservation->status === 'pending';
              $waiting      = $isPending && \App\Support\ForwardedRequests::isWaitingForAdmin($reservation);
              $canAct       = $isPending && ! $waiting;

              [$statusText, $statusClass, $dotClass] = match (true) {
                $waiting                              => ['Waiting for Admin', 'endorsed', ''],
                $isPending                            => ['Pending', 'pending', ''],
                $reservation->status === 'approved'   => ['Approved', 'approved', ''],
                $reservation->status === 'rejected'   => ['Rejected', 'rejected', ''],
                $reservation->status === 'cancelled'  => ['Cancelled', 'cancelled', ''],
                default                               => [ucfirst($reservation->status), 'other', ''],
              };
              $bookDay = $facilityItem?->request_date ? \Carbon\Carbon::parse($facilityItem->request_date) : null;
              $urgent  = $isPending && $bookDay && $bookDay->lte(today()->addDay());
              $clash   = $canAct ? $this->clashes($reservation) : ['booked' => [], 'competing' => []];
              $reason  = $reservation->status === 'rejected' ? $this->rejectionReason($reservation) : null;
              $calUrl  = $this->calendarUrl($reservation);
            @endphp

            <tr wire:key="res-{{ $reservation->id }}" class="relative block @4xl:table-row px-4 py-4 @4xl:p-0 hover:bg-[#F3F8F5]/60 dark:hover:bg-neutral-700/40 transition">

              {{-- Requestor --}}
              <td class="block @4xl:table-cell pr-12 @4xl:px-6 pb-2 @4xl:py-4 align-top">
                <div class="flex items-center gap-3">
                  <div class="shrink-0 size-9 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-semibold">
                    {{ strtoupper(substr($reservation->user->name, 0, 1)) }}
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">{{ $reservation->user->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $reservation->user->email }}</p>
                  </div>
                </div>
              </td>

              {{-- Facility --}}
              <td class="block @4xl:table-cell @4xl:px-6 py-1 @4xl:py-4 align-top">
                <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ $facilityItem->item_name ?? '—' }}</p>

                {{-- Materials and their return date are on the View page --}}
              </td>

              {{-- Schedule --}}
              <td class="block @4xl:table-cell @4xl:px-6 py-1 @4xl:py-4 align-top @4xl:whitespace-nowrap">
                @if($facilityItem && $facilityItem->request_date)
                  <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                    {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}
                  </p>
                  @if($facilityItem->start_time && $facilityItem->end_time)
                    <p class="text-xs text-gray-400 mt-0.5">
                      {{ \Carbon\Carbon::parse($facilityItem->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($facilityItem->end_time)->format('g:i A') }}
                    </p>
                  @endif
                  @if($urgent)
                    <span class="mt-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                      {{ $bookDay->isToday() ? 'Today' : ($bookDay->isPast() ? 'Overdue' : 'Tomorrow') }}
                    </span>
                  @endif
                  {{-- ✅ Open the calendar on this day with this room selected --}}
                  @if($calUrl)
                    <a href="{{ $calUrl }}" wire:navigate class="block mt-1 text-[11px] font-medium text-[#1C6B45] dark:text-[#7FBF8E] hover:underline">See on calendar →</a>
                  @endif
                @else
                  <span class="text-gray-300">—</span>
                @endif
              </td>

              {{-- Purpose --}}
              <td class="block @4xl:table-cell @4xl:px-6 py-1 @4xl:py-4 align-top">
                <p class="text-sm text-gray-600 dark:text-neutral-400 line-clamp-2 @4xl:line-clamp-none @4xl:max-w-[14rem] @4xl:truncate" title="{{ $reservation->purpose }}">
                  {{ $reservation->purpose ?: '—' }}
                </p>
              </td>

              {{-- Status --}}
              <td class="block @4xl:table-cell @4xl:px-6 py-1 @4xl:py-4 align-top @4xl:whitespace-nowrap">
                <x-portal.status :status="$statusClass">{{ $statusText }}</x-portal.status>

                {{-- ✅ Is the room free? (pending, not yet endorsed) --}}
                @if($canAct && $facilityItem?->start_time)
                  @if(empty($clash['booked']) && empty($clash['competing']))
                    <p class="mt-1.5 text-xs font-medium text-[#1C6B45] dark:text-[#7FBF8E] whitespace-normal">✓ Room is free at that time</p>
                  @endif
                  @if(! empty($clash['booked']))
                    <p class="mt-1.5 max-w-xs text-xs text-red-700 dark:text-red-300 whitespace-normal">
                      <span class="font-semibold">⚠ Already booked:</span> {{ implode(', ', $clash['booked']) }} — reject or ask for another time
                    </p>
                  @endif
                  @if(! empty($clash['competing']))
                    <p class="mt-1.5 max-w-xs text-xs text-amber-700 dark:text-amber-300 whitespace-normal">
                      <span class="font-semibold">⚠ Also wanted by:</span> {{ implode(', ', $clash['competing']) }} — pick one
                    </p>
                  @endif
                @endif

                {{-- ✅ What the faculty member was told --}}
                @if($reason)
                  <p class="mt-1.5 max-w-xs text-xs text-red-700 dark:text-red-300 whitespace-normal"><span class="font-semibold">Reason:</span> {{ $reason }}</p>
                @endif
              </td>

              {{-- ⋮ Actions --}}
              <td class="absolute top-3 right-3 @4xl:static @4xl:table-cell @4xl:px-6 @4xl:py-4 align-top text-right">
                <div x-data="{
                       open: false, placed: false, top: 0, left: 0,
                       toggle() {
                         if (this.open) { this.open = false; return; }
                         this.placed = false;
                         this.open   = true;
                         this.$nextTick(() => this.place());
                       },
                       // ✅ keep the menu on screen: open upward when there is no room below
                       place() {
                         const r = this.$refs.btn.getBoundingClientRect();
                         const m = this.$refs.menu;
                         const gap = 6, pad = 8, w = m.offsetWidth, h = m.offsetHeight;
                         let top = r.bottom + gap;
                         if (top + h > window.innerHeight - pad && r.top - gap - h >= pad) top = r.top - gap - h;
                         this.top    = Math.max(pad, top);
                         this.left   = Math.max(pad, Math.min(r.right - w, window.innerWidth - w - pad));
                         this.placed = true;
                       }
                     }"
                     @click.outside="open = false"
                     @keydown.escape.window="open = false"
                     @scroll.window="open = false"
                     @resize.window="open = false"
                     class="inline-block">

                  <button type="button" x-ref="btn" @click="toggle()"
                    class="size-8 inline-flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition"
                    :class="open ? 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-200' : ''"
                    aria-label="Actions">
                    <svg class="size-5" fill="currentColor" viewBox="0 0 24 24">
                      <circle cx="12" cy="5" r="1.75"/><circle cx="12" cy="12" r="1.75"/><circle cx="12" cy="19" r="1.75"/>
                    </svg>
                  </button>

                  <div x-show="open" x-cloak x-ref="menu" x-transition.opacity
                    :style="`top:${top}px; left:${left}px; visibility:${placed ? 'visible' : 'hidden'}`"
                    class="fixed z-50 w-44 py-1 text-left bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-lg">

                    {{-- View --}}
                    @can('requests.view-department')
                      <a href="{{ route('coordinator.view-request-reserve', $reservation->id) }}" wire:navigate
                        class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700">
                        <svg class="size-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        View
                      </a>
                    @endcan

                    {{-- Endorse (send to Admin) --}}
                    @if($canAct)
                      @can('program-head-requests.create')
                        <button type="button"
                          @click="open = false"
                          wire:click="forwardToAdmin({{ $reservation->id }})"
                          wire:confirm="Endorse this request to the Admin?"
                          wire:loading.attr="disabled" wire:target="forwardToAdmin({{ $reservation->id }})"
                          class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700 disabled:opacity-50">
                          <svg class="size-4 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.27 3.13A59.77 59.77 0 0121.49 12 59.77 59.77 0 013.27 20.88L6 12zm0 0h7.5"/>
                          </svg>
                          Endorse
                        </button>
                      @endcan

                      {{-- Reject --}}
                      @can('requests.reject')
                        <div class="my-1 border-t border-gray-100 dark:border-neutral-700"></div>
                        <button type="button"
                          @click="open = false"
                          wire:click="openReject({{ $reservation->id }})"
                          class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/20">
                          <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M15 9l-6 6M9 9l6 6"/>
                          </svg>
                          Reject
                        </button>
                      @endcan
                    @endif
                  </div>
                </div>
              </td>
            </tr>

            {{-- Inline reject form --}}
            @can('requests.reject')
              @if($rejectingRequestId === $reservation->id)
                <tr class="block @4xl:table-row" wire:key="reject-{{ $reservation->id }}" class="bg-red-50/40 dark:bg-red-900/10">
                  <td colspan="6" class="block @4xl:table-cell px-4 @4xl:px-6 py-4">
                    <div class="max-w-2xl bg-white dark:bg-neutral-800 border border-red-200 dark:border-red-900/50 rounded-xl p-4 space-y-3">
                      <div class="flex items-center gap-2">
                        <svg class="size-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <circle cx="12" cy="12" r="10" /><path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                        </svg>
                        <label class="text-sm font-semibold text-red-700 dark:text-red-400">Reason for rejecting this reservation</label>
                        <span class="text-xs text-gray-400 dark:text-neutral-500">(optional)</span>
                      </div>

                      <textarea wire:model="rejectReason" rows="3"
                        placeholder="e.g. Facility already booked, insufficient equipment, safety concern..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-red-200 dark:border-red-900/50 dark:bg-neutral-900 dark:text-neutral-200 focus:ring-2 focus:ring-red-300 focus:border-red-400"></textarea>

                      <p class="text-xs text-gray-500 dark:text-neutral-400">
                        This reason will be sent to <strong>{{ $reservation->user->name }}</strong> via in-app notification and email.
                      </p>

                      <div class="flex justify-end gap-2">
                        <button wire:click="cancelReject"
                          class="px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition">
                          Cancel
                        </button>
                        <button wire:click="confirmReject"
                          wire:loading.attr="disabled"
                          wire:loading.class="opacity-50 cursor-not-allowed"
                          class="px-4 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                          <span wire:loading.remove wire:target="confirmReject">Confirm Rejection</span>
                          <span wire:loading wire:target="confirmReject">Rejecting...</span>
                        </button>
                      </div>
                    </div>
                  </td>
                </tr>
              @endif
            @endcan

          @empty
            <tr class="block @4xl:table-row">
              <td colspan="6" class="block @4xl:table-cell">
                @if(trim($search) !== '')
                  <x-portal.empty message="Nothing matches &quot;{{ $search }}&quot;" hint="Try a different name, room or email." />
                @elseif($tab === 'pending')
                  <x-portal.empty message="You're all caught up" hint="No facility requests are waiting for you." />
                @elseif($tab === 'approved')
                  <x-portal.empty message="No approved facility requests yet" />
                @else
                  <x-portal.empty message="No rejected facility requests" />
                @endif
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    @if($this->reservations->hasPages())
      <div class="px-4 sm:px-6 py-3 border-t border-gray-100 dark:border-neutral-700">{{ $this->reservations->links() }}</div>
    @endif

  </div>

</div>
</div>
