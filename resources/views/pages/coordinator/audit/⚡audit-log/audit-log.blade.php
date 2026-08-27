<div>
<div class="max-w-[85rem] px-4 py-8 sm:px-6 lg:px-8 mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Audit Logs</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">
                Activity logs for students and faculty in
                <span class="font-medium text-[#123524] dark:text-green-400">
                    {{ Auth::user()->department->department_name ?? 'your department' }}
                </span>
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500 dark:text-neutral-400">Total Logs</p>
            </div>
            <p class="text-2xl font-semibold text-gray-800 dark:text-neutral-200">{{ $this->totalLogs }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500 dark:text-neutral-400">Today</p>
            </div>
            <p class="text-2xl font-semibold text-gray-800 dark:text-neutral-200">{{ $this->todayLogs }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500 dark:text-neutral-400">Logins today</p>
            </div>
            <p class="text-2xl font-semibold text-gray-800 dark:text-neutral-200">{{ $this->loginToday }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-full bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
                <p class="text-xs text-gray-500 dark:text-neutral-400">Faculty logs</p>
            </div>
            <p class="text-2xl font-semibold text-gray-800 dark:text-neutral-200">{{ $this->facultyLogs }}</p>
        </div>

    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        <div class="px-5 py-4 border-b border-gray-200 dark:border-neutral-700 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Activity log</h3>
                <p class="text-xs text-gray-400 dark:text-neutral-500 mt-0.5">Logins, logouts, reservations and requests from your department</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">

                <div class="relative">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" wire:model.live="search"
                        placeholder="Search name or email..."
                        class="pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45] w-52">
                </div>

                {{-- ✅ Updated action filter with login/logout/facility/material --}}
                <select wire:model.live="actionFilter"
                    class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                    <option value="">All actions</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>

                <select wire:model.live="roleFilter"
                    class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                    <option value="">All roles</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                </select>

            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
            <thead class="bg-gray-50 dark:bg-neutral-900/30">
                <tr>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">User</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Role</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Action</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Table</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Details</th>
                    <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Date & Time</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                @forelse($this->logs as $log)
                    <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition">

                        {{-- User --}}
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold shrink-0">
                                    {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">
                                        {{ $log->user->name ?? 'Deleted user' }}
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-neutral-500">
                                        {{ $log->user->email ?? '—' }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        {{-- Role --}}
                        <td class="px-5 py-3">
                            @php $role = $log->user?->roles->first()?->name ?? 'N/A'; @endphp
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                @if($role === 'student') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                                @elseif($role === 'faculty') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300
                                @else bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-400 @endif">
                                {{ ucfirst($role) }}
                            </span>
                        </td>

                        {{-- ✅ Action with login/logout colors --}}
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-full inline-flex items-center gap-1
                                @if($log->action === 'login')   bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300
                                @elseif($log->action === 'logout')  bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300
                                @elseif($log->action === 'created') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                                @elseif($log->action === 'updated') bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300
                                @elseif($log->action === 'deleted') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300
                                @else bg-gray-100 text-gray-500 @endif">

                                @if($log->action === 'login')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                @elseif($log->action === 'logout')
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                                @endif

                                {{ ucfirst($log->action) }}
                            </span>
                        </td>

                        {{-- Table --}}
                        <td class="px-5 py-3 text-sm text-gray-600 dark:text-neutral-400">
                            @php
                                $tableLabels = [
                                    'sessions'      => 'Session',
                                    'requests'      => 'Request',
                                    'request_items' => 'Request Item',
                                    'users'         => 'User',
                                ];
                            @endphp
                            <code class="text-xs bg-gray-100 dark:bg-neutral-700 px-1.5 py-0.5 rounded">
                                {{ $tableLabels[$log->table_name] ?? $log->table_name }}
                            </code>
                        </td>

                        {{-- ✅ Smart record preview --}}
                        <td class="px-5 py-3 text-xs text-gray-500 dark:text-neutral-400 max-w-xs">
                            @php
                                $record = is_array($log->record)
                                    ? $log->record
                                    : json_decode($log->record, true);

                                if ($log->action === 'login') {
                                    $preview = 'Logged in' . (isset($record['ip']) ? ' from ' . $record['ip'] : '');
                                } elseif ($log->action === 'logout') {
                                    $preview = 'Logged out';
                                } elseif ($log->table_name === 'requests') {
                                    $type    = ($record['request_type_id'] ?? null) == 1 ? 'Facility reservation' : 'Material request';
                                    $status  = $record['status'] ?? '';
                                    $preview = $type . ($status ? ' · ' . $status : '');
                                } elseif ($log->table_name === 'request_items') {
                                    $preview = ($record['item_name'] ?? 'Item') . ' x' . ($record['quantity'] ?? '?');
                                } else {
                                    $preview = collect($record)
                                        ->only(['name', 'email', 'status'])
                                        ->map(fn($v, $k) => "$k: $v")
                                        ->values()
                                        ->join(' · ');
                                }
                            @endphp
                            <span class="truncate block max-w-[220px]" title="{{ $preview }}">
                                {{ $preview ?: '—' }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-3 text-sm text-gray-500 dark:text-neutral-400 whitespace-nowrap">
                            <p>{{ $log->created_at->format('M d, Y') }}</p>
                            <p class="text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</p>
                        </td>

                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-14 text-center">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-10 h-10 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                                </svg>
                                <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No logs found</p>
                                <p class="text-xs text-gray-400 dark:text-neutral-500">
                                    {{ $search || $actionFilter || $roleFilter
                                        ? 'Try adjusting your filters.'
                                        : 'No activity recorded yet. Logs appear when students or faculty log in, log out, or submit requests.' }}
                                </p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if($this->logs->hasPages())
            <div class="px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                {{ $this->logs->links() }}
            </div>
        @endif

    </div>

</div>
</div>
