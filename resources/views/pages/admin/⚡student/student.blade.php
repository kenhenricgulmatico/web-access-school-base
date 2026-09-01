<div class="select-none">
<div class="max-w-[85rem] px-4 py-6 sm:px-6 sm:py-10 lg:px-8 lg:py-14 mx-auto">

    {{-- Quick Nav --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.users') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.users')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span class="hidden xs:inline sm:inline">Users</span>
        </a>

        <a href="{{ route('admin.students') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.students')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/>
                <path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
            </svg>
            <span>Students</span>
        </a>

        <a href="{{ route('admin.roles') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.roles*')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span>Roles &amp; Permissions</span>
        </a>

        <a href="{{ route('admin.departments') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-xs sm:text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.departments*')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9h.01M9 13h.01M9 17h.01"/>
            </svg>
            <span>Departments</span>
        </a>
    </div>

    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow overflow-hidden">

        {{-- Header --}}
        <div class="px-4 sm:px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex flex-col gap-3 lg:flex-row lg:flex-wrap lg:justify-between lg:items-center">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-neutral-200">User Management</h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">Approve or reject student, faculty, and coordinator accounts</p>
            </div>

            {{-- Search --}}
            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2">
                <input wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search name or email..."
                    class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 w-full sm:w-52 focus:outline-none focus:ring-2 focus:ring-green-500">

                <div class="flex gap-2">
                    {{-- Role Filter --}}
                    <select wire:model.live="roleFilter"
                        class="flex-1 sm:flex-none px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="all">All Roles</option>
                        <option value="student">Student</option>
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
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Name</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Email</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Role</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Department</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Status</th>
                        <th class="px-4 sm:px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Registered</th>
                        <th class="px-4 sm:px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse($this->users as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700 transition">

                            {{-- Name --}}
                            <td class="px-4 sm:px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-green-700 text-white flex items-center justify-center text-sm font-bold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
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
                                    @if($role === 'student') bg-blue-100 text-blue-700
                                    @elseif($role === 'faculty') bg-purple-100 text-purple-700
                                    @elseif($role === 'program head') bg-yellow-100 text-yellow-700
                                    @else bg-gray-100 text-gray-600 @endif">
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
                                    @if($user->status === 'pending') bg-yellow-100 text-yellow-700
                                    @elseif($user->status === 'approved') bg-green-100 text-green-700
                                    @elseif($user->status === 'rejected') bg-red-100 text-red-700
                                    @endif">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>

                            {{-- Registered --}}
                            <td class="px-4 sm:px-6 py-3 text-sm text-gray-400 whitespace-nowrap">
                                {{ $user->created_at->diffForHumans() }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 sm:px-6 py-3 text-right whitespace-nowrap space-x-2">
                                @if($user->status === 'pending')
                                    <button wire:click="approve({{ $user->id }})"
                                        wire:confirm="Approve {{ $user->name }}?"
                                        class="px-3 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700 text-xs transition">
                                        Approve
                                    </button>
                                    <button wire:click="reject({{ $user->id }})"
                                        wire:confirm="Reject {{ $user->name }}?"
                                        class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700 text-xs transition">
                                        Reject
                                    </button>
                                @elseif($user->status === 'approved')
                                    <span class="inline-flex items-center gap-1 text-xs text-green-600 font-medium">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 6L9 17l-5-5"/>
                                        </svg>
                                        Approved
                                    </span>
                                @elseif($user->status === 'rejected')
                                    <span class="inline-flex items-center gap-1 text-xs text-red-500 font-medium">
                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M18 6L6 18M6 6l12 12"/>
                                        </svg>
                                        Rejected
                                    </span>
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-14 text-center">
                                <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M17 20h5v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2h5"/>
                                    <circle cx="12" cy="7" r="4"/>
                                </svg>
                                <p class="text-sm text-gray-400 font-medium">No users found</p>
                                <p class="text-xs text-gray-400 mt-1">Try adjusting your search or filters</p>
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
