<div class="select-none">
    <div class="max-w-[85rem] px-4 py-8 sm:px-6 lg:px-8 lg:py-14 mx-auto">
        <div class="flex flex-col">

            {{-- Flash messages --}}
            @if (session()->has('success'))
                <div class="mb-4 p-3 rounded-lg bg-green-50 text-green-700 border border-green-200 text-sm dark:bg-green-900/20 dark:text-green-300 dark:border-green-900">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 p-3 rounded-lg bg-red-50 text-red-700 border border-red-200 text-sm dark:bg-red-900/20 dark:text-red-300 dark:border-red-900">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700">

                {{-- Header --}}
                <div class="px-4 sm:px-5 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                    <div class="min-w-0">
                        <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">My Reservations</h2>
                        <p class="text-xs text-gray-500 dark:text-neutral-400">Track your facility requests</p>
                    </div>

                    @can(['facility-requests.create'])
                        <a href="{{ route('portal.create-reservation') }}" wire:navigate
                            class="self-start sm:self-auto shrink-0 inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition">
                            + Create Reservation
                        </a>
                    @endcan
                </div>

                {{-- ✅ Tabs: what's coming up / what's done / what didn't go through --}}
                <div class="px-4 sm:px-5 pb-3">
                    <div class="inline-flex flex-wrap p-1 bg-gray-100 dark:bg-neutral-900/60 rounded-xl gap-1" role="tablist" aria-label="Filter reservations">
                        @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'closed' => 'Cancelled & Rejected'] as $key => $label)
                            <button type="button" role="tab" wire:click="$set('tab', '{{ $key }}')" aria-selected="{{ $tab === $key ? 'true' : 'false' }}"
                                class="min-h-9 px-3 rounded-lg text-sm font-semibold transition inline-flex items-center gap-2
                                    {{ $tab === $key ? 'bg-white dark:bg-neutral-700 text-[#123524] dark:text-white shadow-sm' : 'text-gray-600 dark:text-neutral-400 hover:text-[#123524] dark:hover:text-white' }}">
                                {{ $label }}
                                <span class="text-xs px-1.5 py-0.5 rounded-full bg-gray-200 text-gray-700 dark:bg-neutral-600 dark:text-neutral-200">{{ $this->counts[$key] }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Table (tablet / desktop) — stacked cards on phones --}}
                <div class="overflow-x-auto" wire:loading.class="opacity-60" wire:target="tab">
                    <table class="min-w-full text-sm block md:table">
                        <thead class="hidden md:table-header-group">
                            <tr class="border-y border-gray-100 dark:border-neutral-700">
                                <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Facility / Materials</th>
                                <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Date & Time</th>
                                <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Status</th>
                                <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Requested On</th>
                                <th class="px-5 py-2 text-left text-xs font-medium uppercase text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="block md:table-row-group divide-y divide-gray-100 dark:divide-neutral-700 border-t md:border-t-0 border-gray-100 dark:border-neutral-700">
                            @forelse($this->reservations as $request)
                                @php
                                    $facilityItem  = $request->items->firstWhere('resource_id', null);
                                    $materialItems = $request->items->whereNotNull('resource_id');
                                    $endorsed      = $request->status === 'pending' && $this->isEndorsed($request);
                                    $reason        = $request->status === 'rejected' ? $this->rejectionReason($request) : null;
                                @endphp
                                <tr wire:key="res-{{ $request->id }}" class="block md:table-row align-top px-4 py-4 md:p-0">
                                    {{-- Facility / Materials --}}
                                    <td class="block md:table-cell md:px-5 md:py-3">
                                        @if($facilityItem)
                                            <p class="font-medium text-gray-800 dark:text-neutral-200">{{ $facilityItem->item_name }}</p>
                                        @endif
                                        @if($materialItems->isNotEmpty())
                                            <p class="mt-0.5 text-xs text-gray-400">
                                                {{ $materialItems->map(fn($m) => $m->item_name . ' x' . $m->quantity)->implode(', ') }}
                                            </p>
                                        @endif
                                    </td>

                                    {{-- Date & Time --}}
                                    <td class="block md:table-cell pt-1 md:px-5 md:py-3 text-xs text-gray-500 dark:text-neutral-400">
                                        @if($facilityItem)
                                            {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}<span class="md:hidden"> · </span><br class="hidden md:inline">
                                            @if($facilityItem->start_time && $facilityItem->end_time)
                                                {{ \Carbon\Carbon::parse($facilityItem->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($facilityItem->end_time)->format('g:i A') }}
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="block md:table-cell pt-2 md:px-5 md:py-3">
                                        @php $status = $request->status; @endphp
                                        @if($endorsed)
                                            {{-- ✅ The Program Head sent it on to the Admin --}}
                                            <span class="px-2 py-0.5 text-xs font-medium bg-indigo-50 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300 rounded-full">Endorsed — waiting for Admin</span>
                                        @elseif($status === 'pending')
                                            <span class="px-2 py-0.5 text-xs font-medium bg-yellow-50 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300 rounded-full">Pending</span>
                                        @elseif($status === 'approved')
                                            <span class="px-2 py-0.5 text-xs font-medium bg-green-50 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-full">Approved</span>
                                        @elseif($status === 'rejected')
                                            <span class="px-2 py-0.5 text-xs font-medium bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300 rounded-full">Rejected</span>
                                        @elseif($status === 'cancelled')
                                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-500 dark:bg-neutral-700 dark:text-neutral-300 rounded-full">Cancelled</span>
                                        @else
                                            <span class="px-2 py-0.5 text-xs font-medium bg-gray-100 text-gray-600 rounded-full">{{ ucfirst($status) }}</span>
                                        @endif

                                        {{-- ✅ Why it was rejected, right in the list --}}
                                        @if($reason)
                                            <p class="mt-1.5 max-w-xs text-xs text-red-700 dark:text-red-300 whitespace-normal"><span class="font-semibold">Reason:</span> {{ $reason }}</p>
                                        @endif
                                    </td>

                                    {{-- Requested On --}}
                                    <td class="block md:table-cell pt-1 md:px-5 md:py-3 text-xs text-gray-500 dark:text-neutral-400">
                                        <span class="md:hidden">Requested </span>{{ $request->created_at->format('M d, Y h:i A') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="block md:table-cell pt-3 md:px-5 md:py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @can('facility-requests.view')
                                                <a href="{{ route('portal.view-material-reservation', $request->id) }}" wire:navigate
                                                    class="px-3 py-1.5 md:px-2.5 md:py-1 text-xs font-medium text-gray-600 bg-gray-100 dark:bg-neutral-700 dark:text-neutral-200 rounded-md hover:bg-gray-200 dark:hover:bg-neutral-600 transition">
                                                    View
                                                </a>
                                            @endcan

                                            @if($request->status === 'pending')
                                                {{-- ✅ Edit — not once it's endorsed to the Admin --}}
                                                @if($this->canEdit($request))
                                                    @can('facility-requests.update')
                                                        <a href="{{ route('portal.edit-reservation', $request->id) }}"
                                                            class="px-3 py-1.5 md:px-2.5 md:py-1 text-xs font-medium text-[#1C6B45] bg-[#1C6B45]/10 dark:text-green-300 rounded-md hover:bg-[#1C6B45]/15 transition">
                                                            Edit
                                                        </a>
                                                    @endcan
                                                @elseif($endorsed)
                                                    <span class="text-[11px] text-gray-400 dark:text-neutral-500" title="The Admin is reviewing this exact request. Cancel it and make a new one if you need changes.">Can't edit — sent to Admin</span>
                                                @endif

                                                {{-- ✅ Cancel — also withdraws the endorsed copy at the Admin --}}
                                                @if($this->canCancel($request))
                                                    @can('facility-requests.cancel')
                                                        <button type="button"
                                                            wire:click="cancelReservation({{ $request->id }})"
                                                            wire:confirm="{{ $endorsed ? 'Cancel this reservation? It was already sent to the Admin — that request will be withdrawn too.' : 'Cancel this reservation? Your Program Head will be told.' }}"
                                                            wire:loading.attr="disabled" wire:target="cancelReservation({{ $request->id }})"
                                                            class="px-3 py-1.5 md:px-2.5 md:py-1 text-xs font-medium text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-300 rounded-md hover:bg-red-100 disabled:opacity-60 transition">
                                                            Cancel
                                                        </button>
                                                    @endcan
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="block md:table-row">
                                    <td colspan="5" class="block md:table-cell px-5 py-10 text-center text-sm text-gray-400">
                                        @if($tab === 'upcoming') No upcoming reservations.
                                            @can('facility-requests.create')
                                                <a href="{{ route('portal.create-reservation') }}" wire:navigate class="text-[#1C6B45] dark:text-green-300 font-semibold hover:underline">Book a room</a>
                                            @endcan
                                        @elseif($tab === 'past') No past reservations yet.
                                        @else No cancelled or rejected reservations.
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                <div class="px-5 py-3">
                    {{ $this->reservations->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
