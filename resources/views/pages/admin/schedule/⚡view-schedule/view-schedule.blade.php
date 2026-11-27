<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5">

    {{-- Flash --}}
    @if(session()->has('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-[#1C6B45] dark:bg-[#1C6B45]/20 dark:border-[#1C6B45/40] dark:text-green-400 rounded-lg text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 rounded-lg text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Facilities</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400">Rooms that faculty and program heads can reserve</p>
        </div>
        <div class="flex items-center gap-2 w-full sm:w-auto">
            <div class="relative flex-1 sm:flex-none">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <label for="fac-search" class="sr-only">Search facilities</label>
                <input id="fac-search" type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search name, kind, building…"
                    class="pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 w-full sm:w-64">
            </div>

            @can('facility-schedule.create')
                <a href="{{ route('admin.schedule.create') }}"
                    class="shrink-0 px-3.5 py-2 text-sm bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition font-medium flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Add Facility
                </a>
            @endcan
        </div>
    </div>

    {{-- ✅ Summary cards (click to filter) --}}
    @php $c = $this->counts; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach ([
            ''             => ['All facilities',     $c['all'],          'text-gray-800 dark:text-neutral-100',  'border-gray-400'],
            'available'    => ['Available',          $c['available'],    'text-[#1C6B45] dark:text-green-400',   'border-green-500'],
            'maintenance'  => ['Under maintenance',  $c['maintenance'],  'text-amber-700 dark:text-amber-400',   'border-amber-500'],
            'booked-today' => ['Booked today',       $c['booked-today'], 'text-[#1C6B45] dark:text-[#7FBF8E]',     'border-[#1C6B45]'],
        ] as $key => [$label, $n, $tone, $ring])
            <button type="button" wire:click="filterStatus('{{ $key }}')"
                class="text-left p-4 rounded-xl border bg-white dark:bg-neutral-800 shadow-sm hover:shadow transition
                {{ $statusFilter === $key ? $ring . ' ring-1 ring-offset-0' : 'border-gray-200 dark:border-neutral-700' }}">
                <p class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-neutral-400">{{ $label }}</p>
                <p class="mt-1 text-2xl font-semibold {{ $tone }}">{{ $n }}</p>
            </button>
        @endforeach
    </div>

    {{-- Filters --}}
    @if (count($this->kinds) || count($this->buildings) || $search !== '' || $statusFilter !== '')
        <div class="flex flex-wrap items-center gap-2">
            @if (count($this->kinds))
                <label for="fac-kind" class="sr-only">Kind</label>
                <select id="fac-kind" wire:model.live="kindFilter"
                    class="py-1.5 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 dark:text-neutral-200">
                    <option value="">All kinds</option>
                    @foreach ($this->kinds as $k)
                        <option value="{{ $k }}" @selected($k === $kindFilter)>{{ $k }}</option>
                    @endforeach
                </select>
            @endif
            @if (count($this->buildings))
                <label for="fac-building" class="sr-only">Building</label>
                <select id="fac-building" wire:model.live="buildingFilter"
                    class="py-1.5 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 dark:text-neutral-200">
                    <option value="">All buildings</option>
                    @foreach ($this->buildings as $b)
                        <option value="{{ $b }}" @selected($b === $buildingFilter)>{{ $b }}</option>
                    @endforeach
                </select>
            @endif
            @if ($search !== '' || $statusFilter !== '' || $kindFilter !== '' || $buildingFilter !== '')
                <button type="button" wire:click="clearFilters" class="px-2.5 py-1 text-xs font-medium rounded-full text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                    ✕ Clear filters
                </button>
            @endif
            <span class="ms-auto text-xs text-gray-500 dark:text-neutral-400">{{ $this->facilities->total() }} {{ \Illuminate\Support\Str::plural('facility', $this->facilities->total()) }}</span>
        </div>
    @endif

    {{-- Table --}}
    <div class="@container bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">
        @php $cols = '@3xl:grid @3xl:grid-cols-[minmax(0,1.3fr)_minmax(0,1fr)_minmax(0,1.3fr)_auto]'; @endphp

        @if($this->facilities->isNotEmpty())
            <div class="hidden {{ $cols }} gap-4 px-4 py-2 bg-gray-50 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-700 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-neutral-500">
                <span>Facility</span>
                <span>Status</span>
                <span>Next booking</span>
                <span class="text-right">Actions</span>
            </div>
        @endif

        <div class="divide-y divide-gray-100 dark:divide-neutral-700">
            @forelse($this->facilities as $facility)
                @php
                    $upcoming = $this->upcomingFor($facility);
                    $next     = $this->nextBookings[mb_strtolower($facility->resource_name)] ?? null;
                    $isMaint  = $facility->status === 'maintenance';
                    $note     = \App\Support\Facilities::maintenanceNote($facility);
                    $ended    = $isMaint && $facility->maintenance_until && $facility->maintenance_until->lt(today());
                    $details  = array_filter([$facility->room_kind, $facility->capacity ? $facility->capacity . ' seats' : null, $facility->location]);
                @endphp
                <div wire:key="fac-{{ $facility->id }}" class="px-4 py-3 hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">
                    <div class="flex flex-col {{ $cols }} gap-2 @3xl:gap-4 @3xl:items-center">

                        {{-- Facility --}}
                        <div class="min-w-0">
                            <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200 truncate">{{ $facility->resource_name }}</p>
                            @if ($details)
                                <p class="text-xs text-gray-500 dark:text-neutral-400 truncate">{{ implode(' · ', $details) }}</p>
                            @endif
                        </div>

                        {{-- Status --}}
                        <div class="min-w-0">
                            @can('facility-schedule.update')
                                @if ($isMaint)
                                    <button type="button" wire:click="setAvailable({{ $facility->id }})"
                                        wire:confirm="Set {{ $facility->resource_name }} back to Available?"
                                        title="Click to set it back to Available"
                                        class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-amber-100 text-amber-700 hover:bg-amber-200 dark:bg-amber-900/30 dark:text-amber-400 transition">
                                        Under Maintenance
                                    </button>
                                @else
                                    <button type="button" wire:click="openMaintenance({{ $facility->id }})"
                                        title="Click to put it under maintenance"
                                        class="px-2.5 py-1 text-[11px] font-semibold rounded-full bg-green-100 text-[#1C6B45] hover:bg-green-200 dark:bg-[#1C6B45]/30 dark:text-green-400 transition">
                                        Available
                                    </button>
                                @endif
                            @else
                                <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full {{ $isMaint ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' : 'bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-green-400' }}">
                                    {{ $isMaint ? 'Under Maintenance' : 'Available' }}
                                </span>
                            @endcan
                            @if ($note)
                                <p class="mt-1 text-[11px] text-amber-700 dark:text-amber-400 truncate" title="{{ $note }}">{{ $note }}</p>
                            @endif
                            @if ($ended)
                                <p class="text-[11px] font-semibold text-red-600 dark:text-red-400">The "until" date has passed — available again?</p>
                            @endif
                        </div>

                        {{-- Next booking --}}
                        <div class="min-w-0 text-xs">
                            @if ($next)
                                @php $nd = \Carbon\Carbon::parse($next->request_date); @endphp
                                <p class="font-semibold text-gray-700 dark:text-neutral-200">
                                    {{ $nd->isToday() ? 'Today' : ($nd->isTomorrow() ? 'Tomorrow' : $nd->format('D, M d')) }}
                                    @if ($next->start_time && $next->end_time)
                                        · {{ \Carbon\Carbon::parse($next->start_time)->format('g:i A') }}–{{ \Carbon\Carbon::parse($next->end_time)->format('g:i A') }}
                                    @endif
                                </p>
                                <p class="text-gray-500 dark:text-neutral-400 truncate">{{ $next->request?->user?->department?->department_name ?? $next->request?->user?->name }}</p>
                            @else
                                <p class="text-gray-400 dark:text-neutral-500">No upcoming booking</p>
                            @endif
                            @if ($upcoming)
                                <p class="text-gray-400 dark:text-neutral-500">{{ $upcoming }} upcoming in all</p>
                            @endif
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1 @3xl:justify-end">
                            <a href="{{ route('admin.calendar', ['room' => $facility->resource_name]) }}"
                                class="px-2 py-1.5 inline-flex items-center gap-1 text-xs font-medium text-[#1C6B45] hover:bg-[#F3F8F5] dark:text-[#9FD3AE] dark:hover:bg-[#1C6B45]/30 rounded-md transition"
                                title="See this room on the calendar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                Calendar
                            </a>
                            @can('facility-schedule.update')
                                <a href="{{ route('admin.schedule.edit', $facility->id) }}" aria-label="Edit {{ $facility->resource_name }}"
                                    class="p-1.5 text-gray-400 hover:text-[#1C6B45] hover:bg-[#F3F8F5] dark:hover:bg-[#1C6B45]/20 rounded-md transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                            @endcan
                            @can('facility-schedule.delete')
                                <button wire:click="delete({{ $facility->id }})" aria-label="Delete {{ $facility->resource_name }}"
                                    wire:confirm="{{ $upcoming ? $facility->resource_name . ' has ' . $upcoming . ' upcoming bookings, so it cannot be deleted. Try anyway?' : 'Delete ' . $facility->resource_name . '?' }}"
                                    class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition" title="Delete">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endcan
                        </div>
                    </div>

                    {{-- ✅ Put under maintenance: reason + until --}}
                    @if ($maintenanceId === $facility->id)
                        <form wire:submit="saveMaintenance" class="mt-3 p-3 rounded-lg border border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/20 grid gap-3 @2xl:grid-cols-[1fr_180px_auto] @2xl:items-end">
                            <div>
                                <label for="m-reason-{{ $facility->id }}" class="block text-xs font-medium text-amber-900 dark:text-amber-200 mb-1">Reason (sent to people who booked)</label>
                                <input id="m-reason-{{ $facility->id }}" type="text" wire:model="maintenance_reason" maxlength="255" placeholder="e.g. Aircon repair"
                                    class="w-full py-2 px-3 text-sm rounded-lg border border-amber-200 dark:border-amber-900 bg-white dark:bg-neutral-900 dark:text-neutral-200">
                                @error('maintenance_reason') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="m-until-{{ $facility->id }}" class="block text-xs font-medium text-amber-900 dark:text-amber-200 mb-1">Until</label>
                                <input id="m-until-{{ $facility->id }}" type="date" wire:model="maintenance_until" min="{{ today()->toDateString() }}"
                                    class="w-full py-2 px-3 text-sm rounded-lg border border-amber-200 dark:border-amber-900 bg-white dark:bg-neutral-900 dark:text-neutral-200">
                                @error('maintenance_until') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div class="flex gap-2">
                                <button type="button" wire:click="cancelMaintenance"
                                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 bg-white text-gray-700 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200">Cancel</button>
                                <button type="submit" wire:loading.attr="disabled" wire:target="saveMaintenance"
                                    wire:confirm="{{ $upcoming ? 'Put ' . $facility->resource_name . ' under maintenance? It has ' . $upcoming . ' upcoming ' . \Illuminate\Support\Str::plural('booking', $upcoming) . ' — those people will be notified.' : 'Put ' . $facility->resource_name . ' under maintenance?' }}"
                                    class="px-3 py-2 text-sm font-semibold rounded-lg bg-amber-500 text-white hover:bg-amber-600 disabled:opacity-60">Put under maintenance</button>
                            </div>
                        </form>
                    @endif
                </div>
            @empty
                <div class="py-14 text-center">
                    <p class="text-sm font-medium text-gray-600 dark:text-neutral-400">No facilities found</p>
                    <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">Try a different search or filter, or add a facility</p>
                    @can('facility-schedule.create')
                        <a href="{{ route('admin.schedule.create') }}"
                            class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 text-sm bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition font-medium">
                            Add Facility
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

        @if ($this->facilities->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-neutral-700">
                {{ $this->facilities->links() }}
            </div>
        @endif
    </div>

</div>
</div>
