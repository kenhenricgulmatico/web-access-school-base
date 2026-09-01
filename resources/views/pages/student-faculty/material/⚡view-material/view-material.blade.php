<div class="select-none">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        {{-- Header and Create Button --}}
        <div class="mb-6 flex items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">My Material Requests</h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">View and manage your requested materials</p>
            </div>
            <a href="{{ route('portal.create-material') }}"
               class="inline-flex items-center shrink-0 px-3 py-2 sm:px-4 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs sm:text-sm font-medium transition">
                <svg class="w-4 h-4 mr-1.5 sm:mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                <span>Create Material Request</span>
            </a>
        </div>

        {{-- Flash Messages --}}
        @if (session()->has('message'))
            <div class="mb-4 p-3 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800 text-sm">
                {{ session('message') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- Mobile Cards View (Hidden on md and up) --}}
        <div class="block md:hidden space-y-4">
            @forelse($this->materialRequests as $request)
                @php $status = $request->status; @endphp
                <div class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                    <div class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700/50 pb-2">
                        <span class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">#{{ $request->id }}</span>
                        <span class="px-2.5 py-0.5 text-xs font-medium rounded-full
                            {{ $status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : '' }}
                            {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' : '' }}
                            {{ $status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : '' }}
                            {{ $status === 'cancelled' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                            {{ ucfirst($status) }}
                        </span>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Request Date</div>
                        <div class="text-sm font-medium text-gray-800 dark:text-gray-200">
                            {{ $request->created_at->format('M d, Y h:i A') }}
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Items</div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 space-y-0.5 mt-0.5">
                            @foreach($request->items as $item)
                                <div>{{ $item->item_name }} <span class="text-gray-500 text-xs">(x{{ $item->quantity }})</span></div>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Purpose</div>
                        <div class="text-sm text-gray-700 dark:text-gray-300">
                            {{ $request->purpose }}
                        </div>
                    </div>

                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-end gap-3">
                        @if($request->status === 'pending')
                            <a href="{{ route('portal.edit-material', $request->id) }}"
                               class="text-blue-600 hover:text-blue-800 dark:text-blue-400 text-sm font-medium">
                                Edit
                            </a>
                            <button wire:click="cancelRequest({{ $request->id }})"
                                    wire:confirm="Cancel this request?"
                                    class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 text-sm font-medium">
                                Cancel
                            </button>
                        @elseif($request->status === 'cancelled')
                            <button wire:click="deleteRequest({{ $request->id }})"
                                    wire:confirm="Permanently delete this cancelled request?"
                                    class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm font-medium">
                                Delete
                            </button>
                        @else
                            <span class="text-gray-400 text-xs">No actions</span>
                        @endif
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center text-gray-500 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm">
                    You haven't made any material requests yet.
                    <a href="{{ route('portal.create-material') }}" class="text-blue-600 hover:underline ml-1">Create one now</a>
                </div>
            @endforelse
        </div>

        {{-- Desktop Table View (Hidden on small screens) --}}
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Request Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items (Qty)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($this->materialRequests as $request)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-6 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $request->id }}</td>
                                <td class="px-6 py-4 text-sm text-gray-800 dark:text-gray-200 whitespace-nowrap">
                                    {{ $request->created_at->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    @foreach($request->items as $item)
                                        <div>{{ $item->item_name }} <span class="text-gray-400 text-xs">(x{{ $item->quantity }})</span></div>
                                    @endforeach
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300 max-w-xs truncate">
                                    {{ $request->purpose }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php $status = $request->status; @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        {{ $status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300' : '' }}
                                        {{ $status === 'pending' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/40 dark:text-yellow-300' : '' }}
                                        {{ $status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300' : '' }}
                                        {{ $status === 'cancelled' ? 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300' : '' }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right space-x-2 whitespace-nowrap">
                                    @if($request->status === 'pending')
                                        <a href="{{ route('portal.edit-material', $request->id) }}"
                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300 text-sm font-medium">
                                            Edit
                                        </a>
                                        <button wire:click="cancelRequest({{ $request->id }})"
                                                wire:confirm="Cancel this request?"
                                                class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-400 dark:hover:text-yellow-300 text-sm font-medium">
                                            Cancel
                                        </button>
                                    @elseif($request->status === 'cancelled')
                                        <button wire:click="deleteRequest({{ $request->id }})"
                                                wire:confirm="Permanently delete this cancelled request?"
                                                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium">
                                            Delete
                                        </button>
                                    @else
                                        <span class="text-gray-400 dark:text-gray-500 text-sm">No actions</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    You haven't made any material requests yet.
                                    <a href="{{ route('portal.create-material') }}" class="text-blue-600 dark:text-blue-400 hover:underline ml-1">Create one now</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $this->materialRequests->links() }}
            </div>
        </div>

        {{-- Mobile Pagination Container --}}
        <div class="mt-4 md:hidden">
            {{ $this->materialRequests->links() }}
        </div>
    </div>
</div>
