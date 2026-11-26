<div class="select-none">
<div class="max-w-[85rem] px-4 py-8 sm:px-6 lg:px-8 lg:py-10 mx-auto">

  <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 dark:border-neutral-700">
      <div>
        <div class="flex items-center gap-2">
          <h2 class="text-lg font-semibold text-gray-900 dark:text-neutral-100">Material Requests</h2>
          <span class="px-2 py-0.5 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300">
            {{ $this->requests->count() }}
          </span>
        </div>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">Review and manage material requests from faculty</p>
      </div>

      <div class="inline-flex items-center gap-1 bg-gray-100 dark:bg-neutral-700 rounded-lg p-1 self-start sm:self-auto">
        <a href="{{ route('coordinator.facility') }}" wire:navigate
           class="px-4 py-1.5 text-sm font-semibold rounded-md transition
             {{ request()->routeIs('coordinator.facility')
                  ? 'bg-white text-gray-900 shadow-sm dark:bg-neutral-900 dark:text-white'
                  : 'text-gray-500 dark:text-neutral-400 hover:text-gray-800 dark:hover:text-neutral-200' }}">
          Facility
        </a>
        <a href="{{ route('coordinator.material') }}" wire:navigate
           class="px-4 py-1.5 text-sm font-semibold rounded-md transition
             {{ request()->routeIs('coordinator.material')
                  ? 'bg-white text-gray-900 shadow-sm dark:bg-neutral-900 dark:text-white'
                  : 'text-gray-500 dark:text-neutral-400 hover:text-gray-800 dark:hover:text-neutral-200' }}">
          Material
        </a>
      </div>
    </div>

    {{-- Flash messages --}}
    @if (session('message'))
      <div class="px-6 py-3 bg-green-50 dark:bg-green-900/20 border-b border-green-100 dark:border-green-900 text-sm text-green-700 dark:text-green-400">
        {{ session('message') }}
      </div>
    @endif
    @if (session('error'))
      <div class="px-6 py-3 bg-red-50 dark:bg-red-950/30 border-b border-red-100 dark:border-red-900 text-sm text-red-700 dark:text-red-400">
        {{ session('error') }}
      </div>
    @endif

    {{-- Table --}}
    <div class="overflow-x-auto">
      <table class="min-w-full">
        <thead>
          <tr class="border-b border-gray-100 dark:border-neutral-700">
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Requestor</th>
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Items</th>
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Purpose</th>
            <th class="px-6 py-3 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-400">Status</th>
            <th class="px-6 py-3 w-14"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>

        <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
          @forelse($this->requests as $request)
            @php
              $returnBy  = $request->items->whereNotNull('due_back_at')->first()?->due_back_at;
              $isPending = $request->status === 'pending';
              $waiting   = $isPending && \App\Support\ForwardedRequests::isWaitingForAdmin($request);
              $canAct    = $isPending && ! $waiting;

              [$statusText, $statusClass, $dotClass] = match (true) {
                $waiting                          => ['Waiting for Admin', 'bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300', 'bg-indigo-500'],
                $isPending                        => ['Pending',           'bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',     'bg-amber-500'],
                $request->status === 'approved'   => ['Approved',          'bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300',     'bg-green-500'],
                $request->status === 'rejected'   => ['Rejected',          'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',             'bg-red-500'],
                $request->status === 'cancelled'  => ['Cancelled',         'bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300',     'bg-gray-400'],
                default                           => [ucfirst($request->status), 'bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300', 'bg-gray-400'],
              };
              // ✅ Can my department give this from its own Resource Allocation?
              $short = $canAct ? $this->shortItems($request) : [];
            @endphp

            <tr wire:key="mat-{{ $request->id }}" class="hover:bg-gray-50/70 dark:hover:bg-neutral-700/40 transition">

              {{-- Requestor --}}
              <td class="px-6 py-4 align-top">
                <div class="flex items-center gap-3">
                  <div class="shrink-0 size-9 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-semibold">
                    {{ strtoupper(substr($request->user->name, 0, 1)) }}
                  </div>
                  <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-900 dark:text-neutral-100 truncate">{{ $request->user->name }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $request->user->email }}</p>
                  </div>
                </div>
              </td>

              {{-- Items --}}
              <td class="px-6 py-4 align-top">
                {{-- ✅ Only the first item — the full list is on "View" --}}
                <div class="flex flex-wrap items-center gap-1">
                  @forelse($request->items->take(1) as $item)
                    <span class="max-w-[14rem] truncate px-1.5 py-0.5 text-xs rounded-md bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-200">
                      {{ $item->item_name }} <span class="text-gray-400">×{{ $item->quantity }}</span>
                    </span>
                  @empty
                    <span class="text-gray-300">—</span>
                  @endforelse
                  @if($request->items->count() > 1)
                    <a href="{{ route('coordinator.view-request-reserve', $request->id) }}" wire:navigate
                       class="text-xs font-medium text-blue-600 hover:underline dark:text-blue-400">+{{ $request->items->count() - 1 }} more</a>
                  @endif
                </div>

                @if($returnBy)
                  <p class="flex items-center gap-1 mt-1.5 text-[11px] text-gray-500 dark:text-neutral-400">
                    <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Return by {{ $returnBy->format('M d, Y') }}
                  </p>
                @endif
              </td>

              {{-- Purpose --}}
              <td class="px-6 py-4 align-top">
                <p class="text-sm text-gray-600 dark:text-neutral-400 max-w-[16rem] truncate" title="{{ $request->purpose }}">
                  {{ $request->purpose ?: '—' }}
                </p>
              </td>

              {{-- Status --}}
              <td class="px-6 py-4 align-top whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full {{ $statusClass }}">
                  <span class="size-1.5 rounded-full {{ $dotClass }}"></span>
                  {{ $statusText }}
                </span>
              </td>

              {{-- ⋮ Actions --}}
              <td class="px-6 py-4 align-top text-right">
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
                    class="fixed z-50 w-60 py-1 text-left bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-lg">

                    {{-- View --}}
                    @can('requests.view-department')
                      <a href="{{ route('coordinator.view-request-reserve', $request->id) }}" wire:navigate
                        class="flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700">
                        <svg class="size-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5S2.25 12 2.25 12z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                        View
                      </a>
                    @endcan

                    @if($canAct)
                      {{-- ✅ Accept — give it from my department's Resource Allocation --}}
                      @can('requests.approve')
                        @if(empty($short))
                          <button type="button"
                            @click="open = false"
                            wire:click="accept({{ $request->id }})"
                            wire:confirm="Accept this request? The items will be taken from your department's Resource Allocation."
                            wire:loading.attr="disabled" wire:target="accept({{ $request->id }})"
                            class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700 disabled:opacity-50">
                            <svg class="size-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                            Accept
                          </button>
                        @else
                          <div class="px-3 py-2 text-xs text-gray-400 dark:text-neutral-500 whitespace-normal">
                            <p class="flex items-center gap-2.5 font-medium text-gray-400">
                              <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                              Accept
                            </p>
                            <p class="mt-1 ps-6.5">Not enough in your department: {{ implode(', ', $short) }}. Endorse it to the Admin instead.</p>
                          </div>
                        @endif
                      @endcan

                      {{-- Endorse (send to Admin) --}}
                      @can('program-head-requests.create')
                        <button type="button"
                          @click="open = false"
                          wire:click="forwardToAdmin({{ $request->id }})"
                          wire:confirm="Endorse this request to the Admin?"
                          wire:loading.attr="disabled" wire:target="forwardToAdmin({{ $request->id }})"
                          class="w-full flex items-center gap-2.5 px-3 py-2 text-sm text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700 disabled:opacity-50">
                          <svg class="size-4 text-indigo-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
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
                          wire:click="openReject({{ $request->id }})"
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
              @if($rejectingRequestId === $request->id)
                <tr wire:key="reject-{{ $request->id }}" class="bg-red-50/40 dark:bg-red-900/10">
                  <td colspan="5" class="px-6 py-4">
                    <div class="max-w-2xl bg-white dark:bg-neutral-800 border border-red-200 dark:border-red-900/50 rounded-xl p-4 space-y-3">
                      <div class="flex items-center gap-2">
                        <svg class="size-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                          <circle cx="12" cy="12" r="10" /><path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                        </svg>
                        <label class="text-sm font-semibold text-red-700 dark:text-red-400">Reason for rejecting this material request</label>
                        <span class="text-xs text-gray-400 dark:text-neutral-500">(optional)</span>
                      </div>

                      <textarea wire:model="rejectReason" rows="3"
                        placeholder="e.g. Insufficient department stock, incorrect items, please re-submit with proper documentation..."
                        class="w-full px-3 py-2 text-sm rounded-lg border border-red-200 dark:border-red-900/50 dark:bg-neutral-900 dark:text-neutral-200 focus:ring-2 focus:ring-red-300 focus:border-red-400"></textarea>

                      <p class="text-xs text-gray-500 dark:text-neutral-400">
                        This reason will be sent to <strong>{{ $request->user->name }}</strong> via in-app notification and email.
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
            <tr>
              <td colspan="5" class="px-6 py-14 text-center">
                <div class="flex flex-col items-center gap-2">
                  <div class="size-12 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center">
                    <svg class="size-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                  </div>
                  <p class="text-sm font-medium text-gray-600 dark:text-neutral-400">No material requests yet</p>
                  <p class="text-xs text-gray-400 dark:text-neutral-500">Requests from faculty will appear here</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

  </div>

</div>
</div>
