<div>
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
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

            <x-portal.page-header title="My Reservations" subtitle="Track your facility requests">
                @can('facility-requests.create')
                    <x-slot:actions>
                        <a href="{{ route('portal.create-reservation') }}" wire:navigate
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-semibold bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition">
                            + Create Reservation
                        </a>
                    </x-slot:actions>
                @endcan
            </x-portal.page-header>

            <div class="@container bg-white dark:bg-neutral-800 rounded-xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

                {{-- ✅ Tabs: what's coming up / what's done / what didn't go through --}}
                <div class="px-4 sm:px-5 py-3">
                    <x-portal.tabs label="Filter reservations">
                        @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'closed' => 'Cancelled & Rejected'] as $key => $label)
                            <x-portal.tab :active="$tab === $key" :count="$this->counts[$key]" wire:click="$set('tab', '{{ $key }}')">{{ $label }}</x-portal.tab>
                        @endforeach
                    </x-portal.tabs>
                </div>

                {{-- Table (tablet / desktop) — stacked cards on phones --}}
                <div wire:loading.class="opacity-60" wire:target="tab">
                    <table class="min-w-full text-sm block @3xl:table">
                        <thead class="hidden @3xl:table-header-group">
                            <tr class="border-y border-gray-100 dark:border-neutral-700 bg-[#F3F8F5] dark:bg-neutral-900/40">
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Facility / Materials</th>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Date & Time</th>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Status</th>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Requested On</th>
                                <th class="px-5 py-2 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="block @3xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700 border-t @3xl:border-t-0 border-gray-100 dark:border-neutral-700">
                            @forelse($this->reservations as $request)
                                @php
                                    $facilityItem  = $request->items->firstWhere('resource_id', null);
                                    $materialItems = $request->items->whereNotNull('resource_id');
                                    $endorsed      = $request->status === 'pending' && $this->isEndorsed($request);
                                    $reason        = $request->status === 'rejected' ? $this->rejectionReason($request) : null;
                                @endphp
                                <tr wire:key="res-{{ $request->id }}" class="block @3xl:table-row align-top px-4 py-4 @3xl:p-0 hover:bg-[#F3F8F5]/60 dark:hover:bg-neutral-900/30 transition">
                                    {{-- Facility / Materials --}}
                                    <td class="block @3xl:table-cell @3xl:px-5 @3xl:py-3">
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
                                    <td class="block @3xl:table-cell pt-1 @3xl:px-5 @3xl:py-3 text-xs text-gray-500 dark:text-neutral-400">
                                        @if($facilityItem)
                                            {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}<span class="@3xl:hidden"> · </span><br class="hidden @3xl:inline">
                                            @if($facilityItem->start_time && $facilityItem->end_time)
                                                {{ \Carbon\Carbon::parse($facilityItem->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($facilityItem->end_time)->format('g:i A') }}
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="block @3xl:table-cell pt-2 @3xl:px-5 @3xl:py-3">
                                        @php $status = $request->status; @endphp
                                        @if($endorsed)
                                            {{-- ✅ The Program Head sent it on to the Admin --}}
                                            <x-portal.status status="endorsed">Endorsed — waiting for Admin</x-portal.status>
                                        @else
                                            <x-portal.status :status="$status" />
                                        @endif

                                        {{-- ✅ Why it was rejected, right in the list --}}
                                        @if($reason)
                                            <p class="mt-1.5 max-w-xs text-xs text-red-700 dark:text-red-300 whitespace-normal"><span class="font-semibold">Reason:</span> {{ $reason }}</p>
                                        @endif
                                    </td>

                                    {{-- Requested On --}}
                                    <td class="block @3xl:table-cell pt-1 @3xl:px-5 @3xl:py-3 text-xs text-gray-500 dark:text-neutral-400">
                                        <span class="@3xl:hidden">Requested </span>{{ $request->created_at->format('M d, Y h:i A') }}
                                    </td>

                                    {{-- Actions --}}
                                    <td class="block @3xl:table-cell pt-3 @3xl:px-5 @3xl:py-3">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @can('facility-requests.view')
                                                <a href="{{ route('portal.view-material-reservation', $request->id) }}" wire:navigate
                                                    class="px-3 py-1.5 @3xl:px-2.5 @3xl:py-1 text-xs font-medium text-gray-600 bg-gray-100 dark:bg-neutral-700 dark:text-neutral-200 rounded-md hover:bg-gray-200 dark:hover:bg-neutral-600 transition">
                                                    View
                                                </a>
                                            @endcan

                                            @if($request->status === 'pending')
                                                {{-- ✅ Edit — not once it's endorsed to the Admin --}}
                                                @if($this->canEdit($request))
                                                    @can('facility-requests.update')
                                                        <a href="{{ route('portal.edit-reservation', $request->id) }}"
                                                            class="px-3 py-1.5 @3xl:px-2.5 @3xl:py-1 text-xs font-medium text-[#1C6B45] bg-[#1C6B45]/10 dark:text-green-300 rounded-md hover:bg-[#1C6B45]/15 transition">
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
                                                            class="px-3 py-1.5 @3xl:px-2.5 @3xl:py-1 text-xs font-medium text-red-600 bg-red-50 dark:bg-red-900/20 dark:text-red-300 rounded-md hover:bg-red-100 disabled:opacity-60 transition">
                                                            Cancel
                                                        </button>
                                                    @endcan
                                                @endif
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="block @3xl:table-row">
                                    <td colspan="5" class="block @3xl:table-cell">
                                        @if($tab === 'upcoming')
                                            @can('facility-requests.create')
                                                <x-portal.empty message="No upcoming reservations" hint="Need a room for a class or event?" :href="route('portal.create-reservation')" action="+ Create Reservation" />
                                            @else
                                                <x-portal.empty message="No upcoming reservations" />
                                            @endcan
                                        @elseif($tab === 'past')
                                            <x-portal.empty message="No past reservations yet" hint="Reservations you've finished will show here." />
                                        @else
                                            <x-portal.empty message="Nothing cancelled or rejected" hint="Good news — every request went through." />
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
