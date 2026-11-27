<div>
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-5">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Audit Logs</h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                    Who did what — Admin, Program Heads and Faculty
                </p>
            </div>
            {{-- ✅ Download what's shown --}}
            <button type="button" wire:click="exportCsv" wire:loading.attr="disabled" wire:target="exportCsv"
                class="self-start sm:self-auto py-2 px-3 inline-flex items-center gap-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-800 shadow-sm hover:bg-gray-50 disabled:opacity-60 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-100 dark:hover:bg-neutral-700">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4"/></svg>
                <span wire:loading.remove wire:target="exportCsv">Download CSV</span>
                <span wire:loading wire:target="exportCsv">Preparing…</span>
            </button>
        </div>

        {{-- ✅ Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
            @foreach ([
                ['Requests this week', $this->requestsThisWeek, 'text-[#1C6B45] dark:text-[#9FD3AE]'],
                ['Approved / rejected this week', $this->decisionsThisWeek, 'text-[#1C6B45] dark:text-green-300'],
                ['Sign-ins today', $this->signInsToday, 'text-slate-700 dark:text-slate-300'],
                ['Active people this week', $this->activePeople, 'text-amber-700 dark:text-amber-300'],
            ] as [$label, $value, $tone])
                <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm p-4">
                    <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">{{ $label }}</p>
                    <p class="text-2xl font-bold mt-1 {{ $tone }}">{{ $value }}</p>
                </div>
            @endforeach
        </div>

        <div class="@container bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm overflow-hidden">

            {{-- ✅ Filters --}}
            @php
                $fld     = 'py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200 focus:border-[#1C6B45] focus:ring-2 focus:ring-[#1C6B45]/20 focus:outline-none';
                $chipOn  = 'bg-gray-900 border-gray-900 text-white dark:bg-neutral-100 dark:border-neutral-100 dark:text-neutral-900';
                $chipOff = 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700';
                $range   = $this->activeRange();
                $filtered = $search !== '' || $roleFilter !== '' || $personId !== '' || $dateFrom !== '' || $dateTo !== '' || $what !== 'requests';
            @endphp
            <div class="px-4 sm:px-5 py-4 border-b border-gray-200 dark:border-neutral-700 space-y-3">
                <div class="flex flex-wrap items-center gap-2" role="group" aria-label="What to show">
                    @foreach ($this::WHAT as $key => $label)
                        <button type="button" wire:click="setWhat('{{ $key }}')"
                            class="px-3 py-1 text-xs font-semibold rounded-full border transition {{ $what === $key ? $chipOn : $chipOff }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </div>

                <div class="grid gap-2 @xl:grid-cols-2 @4xl:grid-cols-[1fr_200px_150px_150px]">
                    <div class="relative">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 dark:text-neutral-500 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                        <label for="aal-search" class="sr-only">Search</label>
                        <input id="aal-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or email…" class="{{ $fld }} w-full pl-9">
                    </div>
                    <div>
                        <label for="aal-role" class="sr-only">Role</label>
                        <select id="aal-role" wire:model.live="roleFilter" class="{{ $fld }} w-full">
                            <option value="">Everyone</option>
                            @foreach ($this::ROLES as $key => $label)
                                <option value="{{ $key }}" @selected($roleFilter === $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="aal-from" class="sr-only">From</label>
                        <input id="aal-from" type="date" wire:model.live="dateFrom" max="{{ today()->toDateString() }}" title="From" class="{{ $fld }} w-full">
                    </div>
                    <div>
                        <label for="aal-to" class="sr-only">To</label>
                        <input id="aal-to" type="date" wire:model.live="dateTo" max="{{ today()->toDateString() }}" title="To" class="{{ $fld }} w-full">
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['today' => 'Today', 'week' => 'This week', 'month' => 'This month'] as $key => $label)
                        <button type="button" wire:click="setRange('{{ $key }}')"
                            class="px-3 py-1 text-xs font-medium rounded-full border transition {{ $range === $key ? $chipOn : $chipOff }}">
                            {{ $label }}
                        </button>
                    @endforeach
                    @if ($this->person)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                            Only {{ $this->person->name }}
                            <button type="button" wire:click="filterPerson({{ $this->person->id }})" class="ms-0.5 hover:underline" aria-label="Show everyone">✕</button>
                        </span>
                    @endif
                    @if ($filtered)
                        <button type="button" wire:click="clearFilters"
                            class="ms-auto px-2.5 py-1 text-xs font-medium rounded-full text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                            ✕ Clear filters
                        </button>
                    @endif
                    <span class="{{ $filtered ? '' : 'ms-auto' }} text-xs text-gray-500 dark:text-neutral-400">
                        {{ number_format($this->logs->total()) }} {{ \Illuminate\Support\Str::plural('entry', $this->logs->total()) }}
                    </span>
                </div>
            </div>

            {{-- ✅ Table (cards on narrow screens) --}}
            <table class="w-full block @3xl:table divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="hidden @3xl:table-header-group bg-gray-50 dark:bg-neutral-900/40">
                    <tr>
                        <th class="ps-5 pe-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Person</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">What happened</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">When</th>
                        <th class="pe-5 ps-3 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400"><span class="sr-only">Actions</span></th>
                    </tr>
                </thead>
                <tbody class="block @3xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse ($this->logs as $log)
                        @php
                            $role     = $log->user?->roles->first()?->name;
                            $roleTone = match ($role) {
                                'admin'        => 'bg-gray-800 text-white dark:bg-neutral-200 dark:text-neutral-900',
                                'program head' => 'bg-[#E8F3EC] text-[#123524] dark:bg-[#1C6B45]/40 dark:text-[#9FD3AE]',
                                'faculty'      => 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
                                default        => 'bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300',
                            };
                            $reqId   = \App\Support\AuditLogDetails::requestId($log);
                            $canOpen = $reqId && isset($this->openableRequests[$reqId]);
                            $summary = \App\Support\AuditLogDetails::summary($log);
                            [$badge, $badgeText] = match (true) {
                                $log->action === 'login'  => ['bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-[#9FD3AE]', 'Signed in'],
                                $log->action === 'logout' => ['bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300', 'Signed out'],
                                $log->action === 'deleted' => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'Deleted'],
                                $log->table_name === 'requests' && $log->action === 'updated' && str_contains($summary, '· Approved') => ['bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-green-300', 'Approved'],
                                $log->table_name === 'requests' && $log->action === 'updated' && str_contains($summary, '· Rejected') => ['bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300', 'Rejected'],
                                $log->action === 'created' => ['bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-green-300', match ($log->table_name) { 'requests' => 'New request', 'request_items' => 'Item added', 'users' => 'New account', 'resources' => 'Added', default => 'Added' }],
                                default => ['bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-[#9FD3AE]', match ($log->table_name) { 'requests' => 'Request changed', 'request_items' => 'Item changed', 'users' => 'Account', 'resources' => 'Room / material', 'departments' => 'Department', 'roles' => 'Roles', default => 'Changed' }],
                            };
                        @endphp
                        <tr wire:key="alog-{{ $log->id }}" class="block @3xl:table-row px-4 py-3 @3xl:p-0 hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">

                            {{-- Person (click = only this person) --}}
                            <td class="block @3xl:table-cell @3xl:ps-5 @3xl:pe-3 py-1 @3xl:py-3">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <div class="size-8 shrink-0 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        @if ($log->user)
                                            <button type="button" wire:click="filterPerson({{ $log->user->id }})" title="Show only this person's activity"
                                                class="text-sm font-medium text-gray-800 dark:text-neutral-100 hover:underline text-left">{{ $log->user->name }}</button>
                                        @else
                                            <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">System / deleted user</p>
                                        @endif
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @if ($role)
                                                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded {{ $roleTone }}">{{ ucwords($role) }}</span>
                                            @endif
                                            <span class="text-xs text-gray-400 dark:text-neutral-500 truncate">{{ $log->user?->department?->department_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- What happened --}}
                            <td class="block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3 min-w-0">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap {{ $badge }}">{{ $badgeText }}</span>
                                <p class="mt-1 text-sm text-gray-700 dark:text-neutral-300 break-words @3xl:max-w-md">{{ $summary }}</p>
                            </td>

                            {{-- When --}}
                            <td class="block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3 @3xl:whitespace-nowrap">
                                <span class="text-sm text-gray-700 dark:text-neutral-300" title="{{ $log->created_at->format('M d, Y h:i A') }}">
                                    {{ $log->created_at->isToday() ? 'Today' : ($log->created_at->isYesterday() ? 'Yesterday' : $log->created_at->format('M d, Y')) }}
                                </span>
                                <span class="@3xl:block text-xs text-gray-400 dark:text-neutral-500">{{ $log->created_at->format('h:i A') }}</span>
                            </td>

                            {{-- Actions --}}
                            <td class="block @3xl:table-cell @3xl:pe-5 @3xl:ps-3 pt-2 @3xl:py-3 @3xl:text-right @3xl:whitespace-nowrap">
                                <button type="button"
                                    @click="$dispatch('open-modal', {{ \Illuminate\Support\Js::from(\App\Support\AuditLogDetails::for($log)) }})"
                                    class="text-sm font-medium text-[#1C6B45] hover:underline dark:text-[#7FBF8E]">Details</button>
                                @if ($canOpen)
                                    <span class="text-gray-300 dark:text-neutral-600 mx-1">·</span>
                                    <a href="{{ route('admin.view-request', $reqId) }}" class="text-sm font-medium text-[#1C6B45] hover:underline dark:text-green-300">Open request</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr class="block @3xl:table-row">
                            <td colspan="4" class="block @3xl:table-cell px-4 py-12 text-center text-sm text-gray-500 dark:text-neutral-400">
                                {{ $filtered ? 'Nothing matches these filters.' : 'No activity recorded yet.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @if ($this->logs->hasPages())
                <div class="px-4 sm:px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                    {{ $this->logs->links() }}
                </div>
            @endif
        </div>

        {{-- Record Details Modal --}}
        @include('partials.audit-details-modal')
    </div>
</div>
