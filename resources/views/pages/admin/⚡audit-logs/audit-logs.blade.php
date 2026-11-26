<div class="select-none">
    <div class="max-w-7xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

        {{-- Header --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Audit Logs</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                Track faculty and program head activity
            </p>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">Total Logs</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $this->totalLogs }}</p>
            </div>
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">Today's Logs</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $this->todayLogs }}</p>
            </div>
            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm p-4">
                <p class="text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-neutral-400">Search Results</p>
                <p class="text-2xl font-bold text-gray-800 dark:text-white mt-1">{{ $this->logs->total() }}</p>
            </div>
        </div>

        {{-- Filters --}}
        <div class="mb-4 flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1 sm:max-w-md">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-gray-400 pointer-events-none"
                     fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="7"/>
                    <path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                </svg>
                <input type="text"
                    wire:model.live.debounce.300ms="search"
                    placeholder="Search by user, action, or table..."
                    class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600
                           bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200
                           focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
            </div>

            <select wire:model.live="roleFilter"
                class="w-full sm:w-48 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600
                       bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none transition">
                <option value="all">All Roles</option>
                <option value="faculty">Faculty</option>
                <option value="program head">Program Head</option>
            </select>
        </div>

        {{-- Logs Table --}}
        <div class="overflow-x-auto bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 shadow-sm">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-900/40">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">User</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Role</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Table</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Record Data</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse($this->logs as $log)
                        @php $roleName = $log->user?->roles->first()?->name; @endphp
                        <tr wire:key="log-{{ $log->id }}" class="hover:bg-gray-50 dark:hover:bg-neutral-700/40 transition">
                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-sm text-gray-800 dark:text-neutral-200">
                                    {{ $log->user->name ?? 'System' }}
                                </p>
                                <p class="text-xs text-gray-400 dark:text-neutral-500">
                                    {{ $log->user->email ?? '—' }}
                                </p>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                @if($roleName)
                                    <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full
                                        {{ $roleName === 'faculty' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900/30 dark:text-purple-300' : '' }}
                                        {{ $roleName === 'program head' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}">
                                        {{ ucwords($roleName) }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 dark:text-neutral-500">—</span>
                                @endif
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 text-xs font-medium rounded-full
                                    {{ $log->action == 'created' ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-300' : '' }}
                                    {{ $log->action == 'updated' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' : '' }}
                                    {{ $log->action == 'deleted' ? 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-300' : '' }}
                                    {{ $log->action == 'login'   ? 'bg-teal-100 text-teal-800 dark:bg-teal-900/30 dark:text-teal-300' : '' }}
                                    {{ $log->action == 'logout'  ? 'bg-gray-100 text-gray-700 dark:bg-neutral-700 dark:text-neutral-300' : '' }}">
                                    {{ ucfirst($log->action) }}
                                </span>
                            </td>

                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-neutral-300 whitespace-nowrap">
                                {{ ucfirst(str_replace('_', ' ', $log->table_name)) }}
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <button type="button"
                                    @click="$dispatch('open-modal', {{ \Illuminate\Support\Js::from(\App\Support\AuditLogDetails::for($log)) }})"
                                    class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300 transition">
                                    View Details
                                </button>
                            </td>

                            <td class="px-4 py-3 whitespace-nowrap">
                                <p class="text-sm text-gray-800 dark:text-neutral-200">{{ $log->created_at->format('M d, Y') }}</p>
                                <p class="text-xs text-gray-400 dark:text-neutral-500">{{ $log->created_at->diffForHumans() }}</p>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="size-12 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center">
                                        <svg class="size-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">
                                        No audit logs found
                                    </p>
                                    <p class="text-xs text-gray-400 dark:text-neutral-500">
                                        Try adjusting your search or filters.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $this->logs->links() }}
        </div>

        {{-- Record Details Modal --}}
        <div x-data="{ showModal: false, details: { title: '', rows: [] } }"
             x-on:open-modal.window="showModal = true; details = $event.detail"
             x-show="showModal"
             x-cloak
             @keydown.escape.window="showModal = false"
             class="fixed inset-0 z-50 overflow-y-auto">

            {{-- Backdrop --}}
            <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
                 x-show="showModal"
                 x-transition.opacity.duration.150ms
                 @click="showModal = false"></div>

            {{-- Panel --}}
            <div class="flex items-center justify-center min-h-screen px-4 py-6">
                <div x-show="showModal"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
                     x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                     class="relative w-full max-w-2xl bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="shrink-0 size-10 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center">
                                <svg class="size-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-base font-semibold text-gray-800 dark:text-white leading-tight">
                                    Record Details
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-neutral-400" x-text="details.title"></p>
                            </div>
                        </div>
                        <button type="button" @click="showModal = false" title="Close (Esc)"
                            class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Body --}}
                    <div class="p-6 max-h-[70vh] overflow-y-auto">
                        {{-- Readable details: label on the left, value on the right --}}
                        <dl x-show="details.rows.length" class="divide-y divide-gray-100 dark:divide-neutral-700 rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
                            <template x-for="(row, i) in details.rows" :key="i">
                                <div class="grid grid-cols-3 gap-4 px-4 py-2.5 bg-white dark:bg-neutral-800">
                                    <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400" x-text="row.label"></dt>
                                    <dd class="col-span-2 text-sm text-gray-800 dark:text-neutral-200 whitespace-pre-line break-words" x-text="row.value"></dd>
                                </div>
                            </template>
                        </dl>
                        <p x-show="! details.rows.length" class="text-sm text-gray-500 dark:text-neutral-400 text-center py-6">
                            No details were recorded for this action.
                        </p>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-3.5 bg-gray-50 dark:bg-neutral-900/40 border-t border-gray-200 dark:border-neutral-700 flex justify-end">
                        <button type="button" @click="showModal = false"
                            class="px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
