<div class="select-none">
<div class="max-w-[85rem] px-4 py-6 sm:px-6 sm:py-10 lg:px-8 lg:py-14 mx-auto">

    {{-- Quick Nav --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.users') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.users')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Users
        </a>

        <a href="{{ route('admin.students') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.students')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/>
                <path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
            </svg>
            Account Approvals
        </a>

        <a href="{{ route('admin.roles') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.roles*')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            Roles &amp; Permissions
        </a>

        <a href="{{ route('admin.departments') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.departments*')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9h.01M9 13h.01M9 17h.01"/>
            </svg>
            Departments
        </a>
    </div>

    {{-- Flash messages --}}
    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-700 dark:bg-red-900/20 dark:border-red-800 dark:text-red-400 rounded-lg text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:justify-between lg:items-center">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-neutral-200">User Management</h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">Approve or reject faculty and program head accounts</p>
            </div>

            {{-- Filters --}}
            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 pointer-events-none"
                         fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/>
                        <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input wire:model.live.debounce.300ms="search"
                        type="text"
                        placeholder="Search name or email..."
                        class="pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 w-full sm:w-52 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <div class="flex gap-2">
                    {{-- Role Filter --}}
                    <select wire:model.live="roleFilter"
                        class="flex-1 sm:flex-none px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="all">All Roles</option>
                        <option value="faculty">Faculty</option>
                        <option value="program head">Program Head</option>
                    </select>

                    {{-- Status Filter --}}
                    <select wire:model.live="statusFilter"
                        class="flex-1 sm:flex-none px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="all">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto [&::-webkit-scrollbar]:h-2 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-600">
            <table class="min-w-[860px] w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-800">
                    <tr>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Name</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Email</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Role</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Department</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Registered</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse($this->users as $user)
                        <tr wire:key="user-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-neutral-700/40 transition">

                            {{-- Name + Avatar --}}
                            <td class="px-4 sm:px-6 py-3">
                                <div class="flex items-center gap-3">
                                    @if ($user->avatar)
                                        <img src="{{ asset($user->avatar) }}"
                                             alt="{{ $user->name }}"
                                             class="w-8 h-8 rounded-full object-cover shrink-0 ring-1 ring-gray-200 dark:ring-neutral-600">
                                    @else
                                        <div class="w-8 h-8 rounded-full bg-green-700 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif
                                    <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 whitespace-nowrap">{{ $user->name }}</p>
                                </div>
                            </td>

                            {{-- Email --}}
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-neutral-400 whitespace-nowrap">
                                {{ $user->email }}
                            </td>

                            {{-- Role --}}
                            <td class="px-4 sm:px-6 py-3">
                                @php $role = $user->roles->first()?->name ?? 'N/A'; @endphp
                                <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                    @if($role === 'faculty') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                                    @elseif($role === 'program head') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300
                                    @else bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300 @endif">
                                    {{ ucfirst($role) }}
                                </span>
                            </td>

                            {{-- Department --}}
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-600 dark:text-neutral-400 whitespace-nowrap">
                                {{ $user->department->department_name ?? 'N/A' }}
                            </td>

                            {{-- Status --}}
                            <td class="px-4 sm:px-6 py-3">
                                <span class="px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap
                                    @if($user->status === 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300
                                    @elseif($user->status === 'approved') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                                    @elseif($user->status === 'rejected') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300
                                    @endif">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>

                            {{-- Registered --}}
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                                {{ $user->created_at->diffForHumans() }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap">
                                @if($user->status === 'pending')
                                    @can('users.approve')
                                        <button wire:click="approve({{ $user->id }})"
                                            wire:confirm="Approve {{ $user->name }}?"
                                            class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xs transition">
                                            Approve
                                        </button>
                                        <button wire:click="openReject({{ $user->id }})"
                                            class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs transition">
                                            Reject
                                        </button>
                                    @endcan
                                @elseif($user->status === 'approved')
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400 font-medium">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5"/>
                                        </svg>
                                        Approved
                                    </span>
                                @elseif($user->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 text-xs text-red-500 dark:text-red-400 font-medium">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18M6 6l12 12"/>
                                        </svg>
                                        Rejected
                                    </span>
                                @endif
                            </td>

                        </tr>

                        {{-- ✅ Inline Reject Form --}}
                        @can('users.approve')
                            @if ($rejectingUserId === $user->id)
                                <tr class="bg-red-50/40 dark:bg-red-900/10">
                                    <td colspan="7" class="px-6 py-4">
                                        <div class="max-w-2xl bg-white dark:bg-neutral-800 border border-red-200 dark:border-red-900/50 rounded-lg p-4 space-y-3">
                                            <div class="flex items-center gap-2">
                                                <svg class="size-4 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                    <circle cx="12" cy="12" r="10" />
                                                    <path stroke-linecap="round" d="M12 8v4m0 4h.01" />
                                                </svg>
                                                <label class="text-sm font-semibold text-red-700 dark:text-red-400">
                                                    Reason for rejecting this account
                                                </label>
                                                <span class="text-xs text-gray-400 dark:text-neutral-500">(optional)</span>
                                            </div>

                                            <textarea wire:model="rejectReason" rows="3"
                                                placeholder="e.g. Invalid email address, duplicate account, not enrolled this semester..."
                                                class="w-full px-3 py-2 text-sm rounded-lg border border-red-200 dark:border-red-900/50 dark:bg-neutral-900 dark:text-neutral-200 focus:ring-2 focus:ring-red-300 focus:border-red-400"></textarea>

                                            <p class="text-xs text-gray-500 dark:text-neutral-400">
                                                This reason will be sent to <strong>{{ $user->name }}</strong> via email.
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
                            <td colspan="7" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="size-12 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center">
                                        <svg class="size-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path d="M17 20h5v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2h5"/>
                                            <circle cx="12" cy="7" r="4"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No users found</p>
                                    <p class="text-xs text-gray-400 dark:text-neutral-500">
                                        {{ $search || $roleFilter !== 'all' || $statusFilter !== 'all'
                                            ? 'Try adjusting your search or filters.'
                                            : 'No faculty or program heads have registered yet.' }}
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="px-4 sm:px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
            {{ $this->users->links() }}
        </div>

    </div>
</div>
</div>
