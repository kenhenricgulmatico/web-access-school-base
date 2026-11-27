<div>
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
        <x-portal.page-header title="My Material Requests" subtitle="View and manage your requested materials">
            @can('material-requests.create')
                <x-slot:actions>
                    <a href="{{ route('portal.create-material') }}" wire:navigate
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#123524] hover:bg-[#0B2418] text-white rounded-lg text-sm font-semibold transition">
                        + Request Materials
                    </a>
                </x-slot:actions>
            @endcan
        </x-portal.page-header>

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
        <div class="mb-4">
            <x-portal.tabs label="Filter material requests">
                @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'closed' => 'Cancelled & Rejected'] as $key => $label)
                    <x-portal.tab :active="$tab === $key" :count="$this->counts[$key]" wire:click="$set('tab', '{{ $key }}')">{{ $label }}</x-portal.tab>
                @endforeach
            </x-portal.tabs>
        </div>

        @php $rows = $this->materialRequests; @endphp

        <div class="@container" wire:loading.class="opacity-60" wire:target="tab">

        {{-- Mobile Cards View --}}
        <div class="block @3xl:hidden space-y-3">
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
                        <x-portal.status class="shrink-0" :status="$info['tone']">{{ $info['label'] }}</x-portal.status>
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
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700">
                    @include('partials.material-request-empty')
                </div>
            @endforelse
        </div>

        {{-- Desktop Table View --}}
        <div class="hidden @3xl:block bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-[#F3F8F5] dark:bg-gray-900/50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#1C6B45] dark:text-[#7FBF8E] uppercase tracking-wide">Needed On</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#1C6B45] dark:text-[#7FBF8E] uppercase tracking-wide">Items (Qty)</th>
                            <th class="px-6 py-3 text-left text-xs font-semibold text-[#1C6B45] dark:text-[#7FBF8E] uppercase tracking-wide">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-semibold text-[#1C6B45] dark:text-[#7FBF8E] uppercase tracking-wide">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($rows as $request)
                            @php $info = $this->rowInfo($request); @endphp
                            <tr wire:key="m-row-{{ $request->id }}" class="align-top hover:bg-[#F3F8F5]/60 dark:hover:bg-gray-700/50">
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
                                    <x-portal.status :status="$info['tone']">{{ $info['label'] }}</x-portal.status>
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
                                <td colspan="4">
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
        <div class="mt-4 @3xl:hidden">
            {{ $rows->links() }}
        </div>

        </div>
    </div>
</div>
