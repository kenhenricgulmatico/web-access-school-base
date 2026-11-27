<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
    <div class="mb-6">
        <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Notifications</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">All system notifications and requests</p>
    </div>

    {{-- Stats --}}
    <div class="flex flex-wrap gap-4 mb-6">
        <div class="bg-amber-100 dark:bg-amber-900/30 rounded-full px-4 py-2">
            <span class="text-sm font-medium text-amber-800 dark:text-amber-300">Unread: {{ $this->unreadCount }}</span>
        </div>
        <div class="bg-green-100 dark:bg-green-900/30 rounded-full px-4 py-2">
            <span class="text-sm font-medium text-green-800 dark:text-green-300">Read: {{ $this->readCount }}</span>
        </div>
        <div class="bg-red-100 dark:bg-red-900/30 rounded-full px-4 py-2">
            <span class="text-sm font-medium text-red-800 dark:text-red-300">Failed: {{ $this->failedCount }}</span>
        </div>
    </div>

    {{-- Filters & Actions --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Search</label>
                <input type="text" wire:model.live="search" placeholder="Search message..."
                       class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900">
            </div>
            <div class="w-36">
                <label class="text-xs font-medium text-gray-600 dark:text-gray-400 mb-1 block">Status</label>
                <select wire:model.live="statusFilter" class="w-full px-3 py-2 text-sm rounded-lg border">
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="sent">Sent</option>
                    <option value="failed">Failed</option>
                </select>
            </div>
            <div>
                <button wire:click="clearFilters" class="px-4 py-2 text-sm bg-gray-500 text-white rounded-lg">Clear</button>
            </div>
            @if($this->unreadCount > 0)
                <div>
                    <button wire:click="markAllAsRead" class="px-4 py-2 text-sm font-semibold bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition">Mark all as read</button>
                </div>
            @endif
        </div>
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto bg-white dark:bg-gray-800 rounded-xl border shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Received</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse($this->notifications as $index => $notif)
                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 {{ $notif->status === 'pending' ? 'bg-amber-50 dark:bg-amber-900/10' : '' }}">
                    <td class="px-6 py-3 text-sm text-gray-800 dark:text-gray-200 max-w-md">{{ $notif->message }}</td>
                    <td class="px-6 py-3">
                        @if($notif->status === 'pending')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800">Unread</span>
                        @elseif($notif->status === 'sent')
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Read</span>
                        @else
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Failed</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-sm text-gray-500">
                        {{ $notif->created_at->format('M d, Y h:i A') }}<br>
                        <span class="text-xs text-gray-400">{{ $notif->created_at->diffForHumans() }}</span>
                    </td>
                    <td class="px-6 py-3 text-right space-x-2 whitespace-nowrap">
                        @if($notif->status === 'pending')
                            <button wire:click="markAsRead({{ $notif->id }})" class="text-green-600 hover:text-green-800 text-xs">Mark read</button>
                        @else
                            <button wire:click="markAsUnread({{ $notif->id }})" class="text-amber-600 hover:text-amber-800 text-xs">Mark unread</button>
                        @endif
                        <button wire:click="deleteNotification({{ $notif->id }})" wire:confirm="Delete this notification?" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">No notifications found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t">
            {{ $this->notifications->links() }}
        </div>
    </div>
</div>
</div>
