<div class="select-none">
<div class="max-w-[85rem] px-4 py-8 sm:px-6 lg:px-8 mx-auto space-y-5">

    {{-- Flash --}}
    @if(session()->has('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400 rounded-lg text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session()->has('error'))
        <div class="p-3 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 rounded-lg text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" d="M12 8v4m0 4h.01"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Facilities</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400">Manage available rooms and facilities</p>
        </div>
        <div class="flex items-center gap-2">
            <div class="relative">
                <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text" wire:model.live="search"
                    placeholder="Search facility..."
                    class="pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 w-64">
            </div>

            {{-- ✅ Add Facility — requires `facility-schedule.create` --}}
            @can('facility-schedule.create')
                <a href="{{ route('admin.schedule.create') }}"
                    class="px-3.5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Add Facility
                </a>
            @endcan
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        {{-- Table header (desktop only) --}}
        @if($this->facilities->isNotEmpty())
            <div class="hidden md:grid grid-cols-[1fr_160px_100px] gap-3 px-4 py-2 bg-gray-50 dark:bg-neutral-900/40 border-b border-gray-200 dark:border-neutral-700 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-neutral-500">
                <span>Facility Name</span>
                <span>Status</span>
                <span class="text-right">Actions</span>
            </div>
        @endif

        {{-- Rows --}}
        <div class="divide-y divide-gray-100 dark:divide-neutral-700">
            @forelse($this->facilities as $facility)
                <div class="flex flex-col md:grid md:grid-cols-[1fr_160px_100px] gap-2 md:gap-3 md:items-center px-4 py-3 hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">

                    {{-- Facility Name --}}
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200 truncate">
                            {{ $facility->resource_name }}
                        </p>
                    </div>

                    {{-- Status --}}
                    @php $upcoming = $this->upcomingCounts[$facility->resource_name] ?? 0; @endphp
                    <div>
                        {{-- ✅ Toggle — requires `facility-schedule.update` --}}
                        @can('facility-schedule.update')
                            <button wire:click="toggleStatus({{ $facility->id }})"
                                @if($facility->status === 'available')
                                    wire:confirm="{{ $upcoming ? 'Put ' . $facility->resource_name . ' under maintenance? It has ' . $upcoming . ' upcoming ' . \Illuminate\Support\Str::plural('booking', $upcoming) . ' — those people will be notified.' : 'Put ' . $facility->resource_name . ' under maintenance?' }}"
                                @endif
                                class="px-2.5 py-1 text-[11px] font-semibold rounded-full transition
                                    {{ $facility->status === 'available'
                                        ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400 hover:bg-green-200'
                                        : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400 hover:bg-amber-200' }}">
                                {{ $facility->status === 'available' ? 'Available' : 'Under Maintenance' }}
                            </button>
                            @if($upcoming)
                                <p class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400">{{ $upcoming }} upcoming {{ \Illuminate\Support\Str::plural('booking', $upcoming) }}</p>
                            @endif
                        @else
                            <span class="px-2.5 py-1 text-[11px] font-semibold rounded-full
                                {{ $facility->status === 'available'
                                    ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400'
                                    : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' }}">
                                {{ $facility->status === 'available' ? 'Available' : 'Under Maintenance' }}
                            </span>
                        @endcan
                    </div>

                    {{-- Actions --}}
                    <div class="flex items-center gap-1 md:justify-end">
                        {{-- ✅ Edit — requires `facility-schedule.update` --}}
                        @can('facility-schedule.update')
                            <a href="{{ route('admin.schedule.edit', $facility->id) }}"
                                class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition"
                                title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                        @endcan

                        {{-- ✅ Delete — requires `facility-schedule.delete` --}}
                        @can('facility-schedule.delete')
                            <button wire:click="delete({{ $facility->id }})"
                                wire:confirm="{{ $upcoming ? $facility->resource_name . ' has ' . $upcoming . ' upcoming bookings, so it cannot be deleted. Try anyway?' : 'Delete ' . $facility->resource_name . '?' }}"
                                class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition"
                                title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        @endcan
                    </div>

                </div>
            @empty
                <div class="py-14 text-center">
                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                    </div>
                    <p class="text-sm font-medium text-gray-600 dark:text-neutral-400">
                        No facilities found
                    </p>
                    <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">
                        Try a different search or add a facility
                    </p>

                    {{-- ✅ Add Facility (empty state) — requires `facility-schedule.create` --}}
                    @can('facility-schedule.create')
                        <a href="{{ route('admin.schedule.create') }}"
                            class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                            Add Facility
                        </a>
                    @endcan
                </div>
            @endforelse
        </div>

    </div>

</div>
</div>
