<div class="select-none">
    <div class="max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

        {{-- Header --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Incoming Requests</h2>
            <p class="text-sm text-gray-500 dark:text-gray-400">Review and approve requests from Program Heads</p>
        </div>

        {{-- Stats --}}
        <div class="flex flex-wrap gap-4 mb-6">
            <button wire:click="$set('statusFilter', 'pending')"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition
                {{ $statusFilter === 'pending' ? 'bg-yellow-400 text-white' : 'bg-yellow-100 dark:bg-yellow-900/30' }}">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="9" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 3" />
                </svg>
                <span class="text-sm font-medium text-yellow-800 dark:text-yellow-300">Pending:
                    {{ $this->pendingCount }}</span>
            </button>
            <button wire:click="$set('statusFilter', 'approved')"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition
                {{ $statusFilter === 'approved' ? 'bg-green-400 text-white' : 'bg-green-100 dark:bg-green-900/30' }}">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-sm font-medium text-green-800 dark:text-green-300">Approved:
                    {{ $this->approvedCount }}</span>
            </button>
            <button wire:click="$set('statusFilter', 'rejected')"
                class="inline-flex items-center gap-2 rounded-full px-4 py-2 transition
                {{ $statusFilter === 'rejected' ? 'bg-red-400 text-white' : 'bg-red-100 dark:bg-red-900/30' }}">
                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                <span class="text-sm font-medium text-red-800 dark:text-red-300">Rejected:
                    {{ $this->rejectedCount }}</span>
            </button>
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
                        <input type="text" wire:model.live="search"
                            placeholder="Search by requester, purpose, department..."
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="w-36">
                    <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Status</label>
                    <select wire:model.live="statusFilter"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
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

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200 text-sm flex items-center gap-2">
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
        <div class="overflow-x-auto bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-900/30">
                    <tr>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                            Requester</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                            Department</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                            Type</th>
                        <th class="px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                            Status</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">
                            Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse($this->requests as $req)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">

                            {{-- Requester --}}
                            <td class="px-4 py-2.5">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-green-700 text-white flex items-center justify-center text-[11px] font-bold shrink-0">
                                        {{ strtoupper(substr($req->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-xs font-medium text-gray-800 dark:text-neutral-200 leading-tight">
                                            {{ $req->user->name }}</p>
                                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 leading-tight">
                                            {{ $req->user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Department --}}
                            <td class="px-4 py-2.5 text-xs text-gray-600 dark:text-neutral-400">
                                {{ $req->department->department_name ?? '—' }}
                            </td>

                            {{-- Type --}}
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-full
                                    {{ $req->request_type_id == \App\Models\RequestType::FACILITY ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' }}">
                                    {{ $req->requestType?->type_name ?? '—' }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 py-2.5">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[11px] font-medium rounded-full
                                    @if ($req->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300
                                    @elseif($req->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300
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
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">

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
                                            wire:confirm="Approve this request?"
                                            class="px-3 py-1 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
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
                                <tr class="bg-red-50/40 dark:bg-red-900/10">
                                    <td colspan="5" class="px-6 py-4">
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
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
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
