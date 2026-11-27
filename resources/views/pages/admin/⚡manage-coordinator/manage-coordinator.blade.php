<div>
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

        {{-- Header --}}
        <div class="mb-6">
            <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Incoming Requests</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Review and approve requests from Program Heads</p>
        </div>

        {{-- Stats --}}
        <div class="flex flex-wrap gap-4 mb-6">
            <button wire:click="$set('statusFilter', 'pending')"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition
                {{ $statusFilter === 'pending' ? 'bg-amber-100 ring-2 ring-amber-400 dark:bg-amber-900/40' : 'bg-amber-50 dark:bg-amber-900/20' }}">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                </svg>
                <span class="text-sm font-medium text-amber-800 dark:text-amber-300">Pending:
                    {{ $this->pendingCount }}</span>
            </button>
            <button wire:click="$set('statusFilter', 'approved')"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition
                {{ $statusFilter === 'approved' ? 'bg-[#E8F3EC] ring-2 ring-[#1C6B45] dark:bg-[#1C6B45]/40' : 'bg-[#E8F3EC]/60 dark:bg-[#1C6B45]/20' }}">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium text-[#123524] dark:text-green-300">Approved:
                    {{ $this->approvedCount }}</span>
            </button>
            <button wire:click="$set('statusFilter', 'rejected')"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition
                {{ $statusFilter === 'rejected' ? 'bg-red-100 ring-2 ring-red-400 dark:bg-red-900/40' : 'bg-red-50 dark:bg-red-900/20' }}">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-sm font-medium text-red-800 dark:text-red-300">Rejected:
                    {{ $this->rejectedCount }}</span>
            </button>
            @if($this->overdueCount > 0 || $statusFilter === 'overdue')
                {{-- ✅ Pending, but the day it was needed has passed --}}
                <button wire:click="$set('statusFilter', 'overdue')"
                    class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition
                    {{ $statusFilter === 'overdue' ? 'bg-amber-500 text-white' : 'bg-amber-100 dark:bg-amber-900/30' }}">
                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span class="text-sm font-medium {{ $statusFilter === 'overdue' ? 'text-white' : 'text-amber-800 dark:text-amber-300' }}">Overdue:
                        {{ $this->overdueCount }}</span>
                </button>
            @endif
        </div>

        {{-- Filters --}}
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-neutral-700 p-4 mb-6">
            <div class="flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Search</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="7" />
                            <path stroke-linecap="round" d="M21 21l-4.35-4.35" />
                        </svg>
                        <input type="text" wire:model.live.debounce.300ms="search"
                            placeholder="Search requester, item, room, purpose, department..."
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                    </div>
                </div>
                <div class="w-36">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Status</label>
                    <select wire:model.live="statusFilter"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="overdue">Overdue (pending, date passed)</option>
                    </select>
                </div>
                <div>
                    <button wire:click="clearFilters"
                        class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg transition">
                        Clear filters
                    </button>
                </div>
            </div>
        </div>

        {{-- ✅ Overdue: clear them in one go --}}
        @if($statusFilter === 'overdue' && $this->overdueCount > 0)
            @can('program-head-requests.reject')
                <div class="mb-4 p-4 rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-950/20 dark:border-amber-900 text-sm">
                    @if(! $confirmingOverdue)
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="text-amber-900 dark:text-amber-200">
                                <strong>{{ $this->overdueCount }} pending {{ \Illuminate\Support\Str::plural('request', $this->overdueCount) }}</strong>
                                {{ $this->overdueCount === 1 ? 'was' : 'were' }} needed on a day that has already passed.
                            </p>
                            <button type="button" wire:click="openRejectOverdue"
                                class="px-4 py-2 text-sm font-semibold rounded-lg bg-amber-600 text-white hover:bg-amber-700 transition">
                                Reject all overdue
                            </button>
                        </div>
                    @else
                        <label for="overdue-reason" class="block font-semibold text-amber-900 dark:text-amber-200 mb-1.5">
                            Reason sent to each Program Head
                        </label>
                        <textarea id="overdue-reason" wire:model="overdueReason" rows="2" maxlength="500"
                            class="w-full px-3 py-2 text-sm rounded-lg border border-amber-200 dark:border-amber-900 bg-white dark:bg-neutral-900 dark:text-neutral-200 focus:ring-2 focus:ring-amber-300"></textarea>
                        <div class="mt-2 flex justify-end gap-2">
                            <button type="button" wire:click="cancelRejectOverdue"
                                class="px-4 py-2 text-sm font-medium rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-700 dark:text-neutral-200">
                                Cancel
                            </button>
                            <button type="button" wire:click="rejectAllOverdue"
                                wire:confirm="Reject all {{ $this->overdueCount }} overdue requests? Each Program Head will be notified."
                                wire:loading.attr="disabled" wire:target="rejectAllOverdue"
                                class="px-4 py-2 text-sm font-semibold rounded-lg bg-red-600 text-white hover:bg-red-700 disabled:opacity-60">
                                <span wire:loading.remove wire:target="rejectAllOverdue">Reject {{ $this->overdueCount }} {{ \Illuminate\Support\Str::plural('request', $this->overdueCount) }}</span>
                                <span wire:loading wire:target="rejectAllOverdue">Rejecting…</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endcan
        @endif

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-[#123524] border border-green-200 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200 text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01" />
                </svg>
                {{ session('error') }}
            </div>
        @endif

        {{-- Table --}}
        <div class="@container overflow-hidden bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
            <table class="min-w-full block @4xl:table divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="hidden @4xl:table-header-group bg-[#F3F8F5] dark:bg-neutral-900/30">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
                            Requester</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
                            Request</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
                            Needed on</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
                            Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="block @4xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse($this->requests as $req)
                        <tr wire:key="pr-{{ $req->id }}" class="block @4xl:table-row px-4 py-4 @4xl:p-0 hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">

                            {{-- Requester --}}
                            <td class="block @4xl:table-cell px-0 @4xl:px-4 pb-2 @4xl:py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-[#0B2418] text-white flex items-center justify-center text-[11px] font-bold shrink-0">
                                        {{ strtoupper(substr($req->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-800 dark:text-neutral-200 leading-tight">
                                            {{ $req->user->name }}</p>
                                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 leading-tight">
                                            {{ $req->department->department_name ?? '—' }}</p>
                                        {{-- ✅ Forwarded from a student / faculty request --}}
                                        @if($req->forwarded_from_request_id)
                                            @php $__from = \App\Models\Request::with('user')->find($req->forwarded_from_request_id); @endphp
                                            <span class="inline-flex items-center gap-1 mt-1 px-1.5 py-0.5 rounded text-[10px] font-semibold bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-[#9FD3AE]">
                                                Forwarded · {{ $__from?->user?->name ?? 'faculty' }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- ✅ What was asked for (no need to open "View") --}}
                            @php $sum = $this->summary($req); @endphp
                            <td class="block @4xl:table-cell px-0 @4xl:px-4 py-1 @4xl:py-2.5">
                                <span class="inline-flex items-center px-1.5 py-0.5 text-[10px] font-semibold rounded
                                    {{ $req->request_type_id == \App\Models\RequestType::FACILITY ? 'bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-green-300' : 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-[#9FD3AE]' }}">
                                    {{ $req->request_type_id == \App\Models\RequestType::FACILITY ? 'Facility' : 'Material' }}
                                </span>
                                <p class="mt-1 text-sm font-medium text-gray-800 dark:text-neutral-200 @4xl:max-w-[15rem] truncate" title="{{ $sum['main'] }}">{{ $sum['main'] }}</p>
                                @if($sum['sub'])
                                    <p class="text-[11px] text-gray-500 dark:text-neutral-400">{{ $sum['sub'] }}</p>
                                @endif
                                @if($sum['more'] > 0)
                                    <a href="{{ route('admin.view-request', $req->id) }}"
                                       class="text-[11px] font-medium text-[#1C6B45] dark:text-green-300 hover:underline">
                                        +{{ $sum['more'] }} {{ $req->request_type_id == \App\Models\RequestType::FACILITY ? \Illuminate\Support\Str::plural('item', $sum['more']) : 'more' }}
                                    </a>
                                @endif
                            </td>

                            {{-- ✅ Needed on (pending ones needed today / tomorrow are marked) --}}
                            @php
                                $needed = $this->neededOn($req);
                                $urgent = $req->status === 'pending' && $needed && $needed->lte(today()->addDay());
                            @endphp
                            <td class="block @4xl:table-cell px-0 @4xl:px-4 py-1 @4xl:py-2.5 @4xl:whitespace-nowrap">
                                @if($needed)
                                    <p class="text-xs font-medium {{ $urgent ? 'text-red-700 dark:text-red-300' : 'text-gray-800 dark:text-neutral-200' }}">{{ $needed->format('M d, Y') }}</p>
                                    @if($urgent)
                                        <span class="mt-0.5 inline-flex px-1.5 py-0.5 rounded text-[10px] font-bold uppercase tracking-wide bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">
                                            {{ $needed->isToday() ? 'Today' : ($needed->isPast() ? 'Overdue' : 'Tomorrow') }}
                                        </span>
                                    @endif
                                @else
                                    <span class="text-xs text-gray-300">—</span>
                                @endif
                            </td>


                            {{-- Status --}}
                            <td class="block @4xl:table-cell px-0 @4xl:px-4 py-1 @4xl:py-2.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium rounded-full
                                    @if ($req->status === 'pending') bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300
                                    @elseif($req->status === 'approved') bg-green-100 text-[#123524] dark:bg-[#1C6B45]/30 dark:text-green-300
                                    @else bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300 @endif">
                                    @if ($req->status === 'pending')
                                        <svg class="size-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="9" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                                        </svg>
                                    @elseif($req->status === 'approved')
                                        <svg class="size-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                        </svg>
                                    @else
                                        <svg class="size-2.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    @endif
                                    {{ ucfirst($req->status) }}
                                </span>

                                {{-- ✅ Can it be approved? Main stock and room, at a glance (pending only) --}}
                                @if($req->status === 'pending')
                                    @php
                                        $short = $this->shortItems($req);
                                        $clash = $this->clashes($req);
                                    @endphp
                                    @if(! empty($clash))
                                        <p class="mt-1.5 max-w-[16rem] text-[11px] text-red-700 dark:text-red-300 whitespace-normal">
                                            <span class="font-semibold">⚠ Room already booked:</span> {{ implode(', ', $clash) }}
                                        </p>
                                    @endif
                                    @if(! empty($short))
                                        <p class="mt-1.5 max-w-[16rem] text-[11px] text-amber-700 dark:text-amber-300 whitespace-normal">
                                            <span class="font-semibold">Not enough stock:</span> {{ implode(', ', $short) }}
                                        </p>
                                    @endif
                                    @if(empty($clash) && empty($short))
                                        <p class="mt-1.5 text-[11px] font-medium text-[#1C6B45] dark:text-green-400">
                                            ✓ {{ $req->request_type_id == \App\Models\RequestType::FACILITY ? 'Room free' : 'In stock' }}{{ $req->request_type_id == \App\Models\RequestType::FACILITY && $req->items->whereNotNull('resource_id')->isNotEmpty() ? ' · items in stock' : '' }}
                                        </p>
                                    @endif
                                @endif

                                {{-- ✅ What the Program Head was told --}}
                                @if($req->status === 'rejected' && ($reason = $this->rejectionReason($req)))
                                    <p class="mt-1.5 max-w-[16rem] text-[11px] text-red-700 dark:text-red-300 whitespace-normal"><span class="font-semibold">Reason:</span> {{ $reason }}</p>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="block @4xl:table-cell px-0 @4xl:px-6 pt-3 @4xl:py-4 @4xl:text-right @4xl:whitespace-nowrap space-x-2">

                                {{-- ✅ View — new permission name --}}
                                @can('program-head-requests.view')
                                    <a href="{{ route('admin.view-request', $req->id) }}"
                                        class="inline-flex items-center px-3 py-1 text-xs bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
                                        View
                                    </a>
                                @endcan

                                {{-- Approve / Reject: only if user can approve AND request is pending --}}
                                @if ($req->status === 'pending')
                                    {{-- ✅ Approve — new permission name --}}
                                    @can('program-head-requests.approve')
                                        <button wire:click="approve({{ $req->id }})"
                                            wire:confirm="{{ $this->approveConfirm($req) }}"
                                            wire:loading.attr="disabled" wire:target="approve({{ $req->id }})"
                                            class="px-3 py-1 text-xs bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition">
                                            Approve
                                        </button>
                                    @endcan

                                    {{-- ✅ Reject — new permission name --}}
                                    @can('program-head-requests.reject')
                                        <button wire:click="openReject({{ $req->id }})"
                                            class="px-3 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                            Reject
                                        </button>
                                    @endcan
                                @endif

                            </td>

                        </tr>

                        {{-- ✅ Inline Reject Form (also permission-gated) --}}
                        @can('program-head-requests.reject')
                            @if ($rejectingRequestId === $req->id)
                                <tr class="block @4xl:table-row bg-red-50/40 dark:bg-red-900/10">
                                    <td colspan="5" class="block @4xl:table-cell px-4 @4xl:px-6 py-4">
                                        <div class="max-w-2xl bg-white dark:bg-neutral-800 border border-red-200 dark:border-red-900/50 rounded-lg p-4 space-y-3">
                                            <div class="flex items-center gap-2">
                                                <svg class="size-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                                                </svg>
                                                <label class="text-sm font-semibold text-red-700 dark:text-red-400">
                                                    Reason for rejecting this request
                                                </label>
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">(optional)</span>
                                            </div>

                                            <textarea wire:model="rejectReason" rows="3"
                                                placeholder="e.g. Missing documents, budget exceeded, conflicting schedule..."
                                                class="w-full px-3 py-2 text-sm rounded-lg border border-red-200 dark:border-red-900/50 dark:bg-neutral-900 dark:text-neutral-200 focus:ring-2 focus:ring-red-300 focus:border-red-400"></textarea>

                                            <p class="text-xs text-gray-500 dark:text-neutral-400">
                                                This reason will be sent to <strong>{{ $req->user->name }}</strong> via in-app notification and email.
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
                            <td colspan="5" class="block @4xl:table-cell px-6 py-14 text-center">
                                <svg class="w-10 h-10 text-gray-300 dark:text-neutral-600 mx-auto mb-3" fill="none"
                                    stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                                </svg>
                                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No requests found</p>
                                <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">
                                    {{ $search || $statusFilter ? 'Try adjusting your filters.' : 'No incoming requests yet.' }}
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Pagination --}}
            <div class="px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                {{ $this->requests->links() }}
            </div>
        </div>
    </div>
</div>
