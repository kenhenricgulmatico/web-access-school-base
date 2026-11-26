<div class="select-none">
    <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 py-4 sm:py-6 lg:py-8">
        {{-- Header and Create Button --}}
        <div class="mb-5 flex items-center justify-between gap-4">
            <div class="min-w-0">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-white">My Material Requests</h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-gray-400">View and manage your requested materials</p>
            </div>

            @can('material-requests.create')
                <a href="{{ route('portal.create-material') }}" wire:navigate
                   class="inline-flex items-center shrink-0 px-3 py-2 sm:px-4 sm:py-2 bg-[#123524] hover:bg-[#0B2418] text-white rounded-lg text-xs sm:text-sm font-semibold transition">
                    <svg class="w-4 h-4 mr-1.5 sm:mr-2 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Create Material Request</span>
                </a>
            @endcan
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

        {{-- ✅ Tabs --}}
        <div class="mb-4 inline-flex flex-wrap p-1 bg-gray-100 dark:bg-gray-800 rounded-xl gap-1" role="tablist" aria-label="Filter material requests">
            @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'closed' => 'Cancelled & Rejected'] as $key => $label)
                <button type="button" role="tab" wire:click="$set('tab', '{{ $key }}')" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                    class="min-h-9 px-3 rounded-lg text-sm font-semibold transition inline-flex items-center gap-2
                        {{ $tab === $key ? 'bg-white dark:bg-gray-700 text-[#123524] dark:text-white shadow-sm' : 'text-gray-600 dark:text-gray-400 hover:text-[#123524] dark:hover:text-white' }}">
                    {{ $label }}
                    <span class="text-xs px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-200">{{ $this->counts[$key] }}</span>
                </button>
            @endforeach
        </div>

        @php $rows = $this->materialRequests; @endphp

        <div wire:loading.class="opacity-60" wire:target="tab">

        {{-- Mobile Cards View --}}
        <div class="block md:hidden space-y-4">
            @forelse($rows as $request)
                @php $info = $this->rowInfo($request); @endphp
                <div wire:key="m-card-{{ $request->id }}" class="bg-white dark:bg-gray-800 rounded-xl p-4 border border-gray-200 dark:border-gray-700 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3 border-b border-gray-100 dark:border-gray-700/50 pb-2">
                        <div>
                            @if($info['needed'])
                                <p class="text-sm font-semibold text-gray-800 dark:text-gray-200">Needed on {{ $info['needed'] }}</p>
                            @endif
                            <p class="text-xs text-gray-500 dark:text-gray-400">Requested {{ $request->created_at->format('M d, Y g:i A') }}</p>
                        </div>
                        <span class="shrink-0 px-2.5 py-0.5 text-xs font-medium rounded-full {{ $info['badge'] }}">{{ $info['label'] }}</span>
                    </div>

                    <div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Items</div>
                        <div class="text-sm text-gray-700 dark:text-gray-300 space-y-0.5 mt-0.5">
                            @foreach($request->items->take(1) as $item)
                                <div class="truncate">{{ $item->item_name }} <span class="text-gray-500 text-xs">(x{{ $item->quantity }})</span></div>
                            @endforeach
                            @if($request->items->count() > 1)
                                <a href="{{ route('portal.view-material-reservation', $request->id) }}" wire:navigate
                                   class="inline-block text-xs font-medium text-[#1C6B45] hover:underline dark:text-green-300">+{{ $request->items->count() - 1 }} more</a>
                            @endif
                        </div>
                    </div>

                    @include('partials.material-request-notes')

                    <div class="pt-2 border-t border-gray-100 dark:border-gray-700/50 flex flex-wrap items-center justify-end gap-3">
                        @include('partials.material-request-actions')
                    </div>
                </div>
            @empty
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 text-center text-gray-500 border border-gray-200 dark:border-gray-700 text-xs sm:text-sm">
                    @include('partials.material-request-empty')
                </div>
            @endforelse
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden md:block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Needed On</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Items (Qty)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($rows as $request)
                            @php $info = $this->rowInfo($request); @endphp
                            <tr wire:key="m-row-{{ $request->id }}" class="align-top hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                {{-- ✅ The date the materials are needed (the date it was sent is underneath) --}}
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <p class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $info['needed'] ?? '—' }}</p>
                                    <p class="text-xs text-gray-400">Requested {{ $request->created_at->format('M d, Y') }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-300">
                                    @foreach($request->items->take(1) as $item)
                                        <div class="truncate max-w-[16rem]">{{ $item->item_name }} <span class="text-gray-400 text-xs">(x{{ $item->quantity }})</span></div>
                                    @endforeach
                                    @if($request->items->count() > 1)
                                        <a href="{{ route('portal.view-material-reservation', $request->id) }}" wire:navigate
                                           class="inline-block text-xs font-medium text-[#1C6B45] hover:underline dark:text-green-300">+{{ $request->items->count() - 1 }} more</a>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <span class="inline-flex px-2 py-1 text-xs font-medium rounded-full whitespace-nowrap {{ $info['badge'] }}">{{ $info['label'] }}</span>
                                    @include('partials.material-request-notes')
                                </td>
                                <td class="px-6 py-4 text-right whitespace-nowrap">
                                    <div class="inline-flex flex-wrap items-center justify-end gap-3">
                                        @include('partials.material-request-actions')
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                    @include('partials.material-request-empty')
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $rows->links() }}
            </div>
        </div>

        {{-- Mobile Pagination --}}
        <div class="mt-4 md:hidden">
            {{ $rows->links() }}
        </div>

        </div>
    </div>
</div>
