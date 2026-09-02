<div>
<div class="max-w-[85rem] px-4 py-6 sm:py-8 sm:px-6 lg:px-8 mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800">Audit Logs</h2>
            <p class="text-sm text-gray-500 mt-0.5">
                Activity logs for students and faculty in
                <span class="font-medium text-[#123524]">
                    {{ Auth::user()->department->department_name ?? 'your department' }}
                </span>
            </p>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                    </svg>
                </div>
                <p class="text-[10px] sm:text-xs text-gray-500">Total Logs</p>
            </div>
            <p class="text-xl sm:text-2xl font-semibold text-gray-800">{{ $this->totalLogs }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-green-100 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                    </svg>
                </div>
                <p class="text-[10px] sm:text-xs text-gray-500">Today</p>
            </div>
            <p class="text-xl sm:text-2xl font-semibold text-gray-800">{{ $this->todayLogs }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-purple-100 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/>
                    </svg>
                </div>
                <p class="text-[10px] sm:text-xs text-gray-500">Logins today</p>
            </div>
            <p class="text-xl sm:text-2xl font-semibold text-gray-800">{{ $this->loginToday }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl p-3 sm:p-4 shadow-sm">
            <div class="flex items-center gap-2 sm:gap-3 mb-1 sm:mb-2">
                <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
                <p class="text-[10px] sm:text-xs text-gray-500">Faculty logs</p>
            </div>
            <p class="text-xl sm:text-2xl font-semibold text-gray-800">{{ $this->facultyLogs }}</p>
        </div>

    </div>

    {{-- Table --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">

        {{-- Filters --}}
        <div class="px-4 sm:px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row flex-wrap items-start sm:items-center justify-between gap-3">
            <div class="min-w-0">
                <h3 class="text-sm font-semibold text-gray-800">Activity log</h3>
                <p class="text-xs text-gray-400 mt-0.5 truncate">Logins, logouts, reservations and requests from your department</p>
            </div>
            <div class="flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2 w-full sm:w-auto">

                <div class="relative w-full sm:w-auto">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" wire:model.live="search"
                        placeholder="Search name or email..."
                        class="pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#123524] w-full sm:w-48 md:w-52">
                </div>

                <select wire:model.live="actionFilter"
                    class="py-2 px-3 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#123524] w-full sm:w-auto">
                    <option value="">All actions</option>
                    <option value="login">Login</option>
                    <option value="logout">Logout</option>
                    <option value="created">Created</option>
                    <option value="updated">Updated</option>
                    <option value="deleted">Deleted</option>
                </select>

                <select wire:model.live="roleFilter"
                    class="py-2 px-3 text-sm rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-[#123524] w-full sm:w-auto">
                    <option value="">All roles</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                </select>

            </div>
        </div>

        {{-- Table wrapper with horizontal scroll --}}
        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">User</th>
                        <th class="hidden sm:table-cell px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Role</th>
                        <th class="px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Action</th>
                        <th class="hidden md:table-cell px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Table</th>
                        <th class="hidden lg:table-cell px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Details</th>
                        <th class="px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500">Date & Time</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($this->logs as $log)
                        <tr class="hover:bg-gray-50 transition">

                            {{-- User --}}
                            <td class="px-4 sm:px-5 py-3">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-xs sm:text-sm font-bold shrink-0">
                                        {{ strtoupper(substr($log->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 truncate">
                                            {{ $log->user->name ?? 'Deleted user' }}
                                        </p>
                                        <p class="text-xs text-gray-400 truncate hidden sm:block">
                                            {{ $log->user->email ?? '—' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            {{-- Role (hidden on mobile) --}}
                            <td class="hidden sm:table-cell px-4 sm:px-5 py-3">
                                @php $role = $log->user?->roles->first()?->name ?? 'N/A'; @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap
                                    @if($role === 'student') bg-purple-100 text-purple-700
                                    @elseif($role === 'faculty') bg-amber-100 text-amber-700
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ ucfirst($role) }}
                                </span>
                            </td>

                            {{-- Action --}}
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2 py-0.5 text-[10px] sm:text-xs font-medium rounded-full inline-flex items-center gap-1 whitespace-nowrap
                                    @if($log->action === 'login')   bg-emerald-100 text-emerald-700
                                    @elseif($log->action === 'logout')  bg-gray-100 text-gray-600
                                    @elseif($log->action === 'created') bg-green-100 text-green-700
                                    @elseif($log->action === 'updated') bg-blue-100 text-blue-700
                                    @elseif($log->action === 'deleted') bg-red-100 text-red-700
                                    @else bg-gray-100 text-gray-500 @endif">

                                    @if($log->action === 'login')
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9"/></svg>
                                    @elseif($log->action === 'logout')
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75"/></svg>
                                    @endif

                                    <span class="hidden sm:inline">{{ ucfirst($log->action) }}</span>
                                    <span class="sm:hidden">{{ substr(ucfirst($log->action), 0, 4) }}</span>
                                </span>
                            </td>

                            {{-- Table (hidden on mobile) --}}
                            <td class="hidden md:table-cell px-4 sm:px-5 py-3 text-sm text-gray-600">
                                @php
                                    $tableLabels = [
                                        'sessions'      => 'Session',
                                        'requests'      => 'Request',
                                        'request_items' => 'Request Item',
                                        'users'         => 'User',
                                    ];
                                @endphp
                                <code class="text-[10px] sm:text-xs bg-gray-100 px-1.5 py-0.5 rounded whitespace-nowrap">
                                    {{ $tableLabels[$log->table_name] ?? $log->table_name }}
                                </code>
                            </td>

                            {{-- Details (hidden on tablet) --}}
                            <td class="hidden lg:table-cell px-4 sm:px-5 py-3 text-xs text-gray-500 max-w-xs">
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
                                <span class="truncate block max-w-[180px]" title="{{ $preview }}">
                                    {{ $preview ?: '—' }}
                                </span>
                            </td>

                            {{-- Date & Time --}}
                            <td class="px-4 sm:px-5 py-3 text-sm text-gray-500 whitespace-nowrap">
                                <p class="text-xs sm:text-sm">{{ $log->created_at->format('M d, Y') }}</p>
                                <p class="text-[10px] sm:text-xs text-gray-400">{{ $log->created_at->format('h:i A') }}</p>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 sm:px-5 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-500">No logs found</p>
                                    <p class="text-xs text-gray-400 text-center max-w-sm">
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
        </div>

        {{-- Pagination --}}
        @if($this->logs->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-gray-200">
                {{ $this->logs->links() }}
            </div>
        @endif

    </div>

</div>
</div>
