<div class="select-none">
    <div class="p-6">
    {{-- Header --}}
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-foreground">Audit Logs</h2>
        <p class="text-muted-foreground-1 mt-1">Track student, faculty, and program head activity</p>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border">
            <div class="text-sm text-muted-foreground-1">Total Logs</div>
            <div class="text-2xl font-bold">{{ $this->totalLogs }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border">
            <div class="text-sm text-muted-foreground-1">Today's Logs</div>
            <div class="text-2xl font-bold">{{ $this->todayLogs }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border">
            <div class="text-sm text-muted-foreground-1">Search Results</div>
            <div class="text-2xl font-bold">{{ $this->logs->total() }}</div>
        </div>
    </div>

    {{-- Search + Role Filter --}}
    <div class="mb-4 flex flex-col sm:flex-row gap-3">
        <input type="text"
            wire:model.live="search"
            placeholder="Search by user, action, or table..."
            class="w-full sm:w-96 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-foreground">

        <select wire:model.live="roleFilter"
            class="w-full sm:w-48 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-foreground">
            <option value="all">All Roles</option>
            <option value="student">Student</option>
            <option value="faculty">Faculty</option>
            <option value="program head">Program Head</option>
        </select>
    </div>

    {{-- Logs Table --}}
    <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg border border-gray-200 dark:border-gray-700">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Table</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Record Data</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->logs as $log)
                @php
                    $roleName = $log->user?->roles->first()?->name;
                @endphp
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                    <td class="px-4 py-3 text-sm text-foreground">{{ $log->id }}</td>
                    <td class="px-4 py-3 text-sm text-foreground">
                        {{ $log->user->name ?? 'System' }}
                        <div class="text-xs text-muted-foreground-1">{{ $log->user->email ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if($roleName)
                            <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                                {{ $roleName === 'student' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                {{ $roleName === 'faculty' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                {{ $roleName === 'program head' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}">
                                {{ ucwords($roleName) }}
                            </span>
                        @else
                            <span class="text-xs text-muted-foreground-1">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full
                            {{ $log->action == 'created' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : '' }}
                            {{ $log->action == 'updated' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                            {{ $log->action == 'deleted' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200' : '' }}
                            {{ $log->action == 'login' ? 'bg-teal-100 text-teal-800 dark:bg-teal-900 dark:text-teal-200' : '' }}
                            {{ $log->action == 'logout' ? 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200' : '' }}">
                            {{ ucfirst($log->action) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-foreground">
                        {{ ucfirst(str_replace('_', ' ', $log->table_name)) }}
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <button type="button"
                            x-data="{}"
                            @click="$dispatch('open-modal', { record: {{ json_encode($log->record) }} })"
                            class="text-blue-600 hover:text-blue-800 dark:text-blue-400">
                            View Details
                        </button>
                    </td>
                    <td class="px-4 py-3 text-sm text-muted-foreground-1">
                        {{ $log->created_at->format('Y-m-d H:i:s') }}
                        <div class="text-xs">{{ $log->created_at->diffForHumans() }}</div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-muted-foreground-1">
                        No audit logs found
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

    {{-- Modal for Viewing Record --}}
    <div x-data="{ showModal: false, recordData: null }"
         x-on:open-modal.window="showModal = true; recordData = $event.detail.record"
         x-show="showModal"
         x-cloak
         class="fixed inset-0 z-50 overflow-y-auto"
         style="display: none;">

        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" @click="showModal = false"></div>

            <div class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                            <h3 class="text-lg font-medium leading-6 text-foreground" id="modal-title">
                                Record Details
                            </h3>
                            <div class="mt-4">
                                <pre class="text-sm text-foreground bg-gray-100 dark:bg-gray-900 p-4 rounded-lg overflow-x-auto">
                                    <span x-text="JSON.stringify(recordData, null, 2)"></span>
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-900 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button"
                        @click="showModal = false"
                        class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-foreground hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>
</div>
