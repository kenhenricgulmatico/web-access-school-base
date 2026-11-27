<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
    <x-portal.page-header title="My Requests to Admin" subtitle="Track the status of your own requests sent to the Admin">
        @can('program-head-requests.create')
            <x-slot:actions>
                <a href="{{ route('coordinator.request-to-admin.create-request') }}" wire:navigate
                    class="inline-flex items-center gap-x-1.5 px-3.5 py-2 text-sm font-semibold bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition">
                    + New Request
                </a>
            </x-slot:actions>
        @endcan
    </x-portal.page-header>

    <div class="@container bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

                    {{-- Flash Messages --}}
                    @if(session()->has('success'))
                        <div class="mx-6 mt-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="mx-6 mt-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Table --}}
                    <table class="min-w-full block @3xl:table divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="hidden @3xl:table-header-group bg-[#F3F8F5] dark:bg-neutral-900/40">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E]">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="block @3xl:table-row-group divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($this->requests as $index => $request)
                                <tr class="block @3xl:table-row px-4 py-4 @3xl:p-0 hover:bg-[#F3F8F5]/60 dark:hover:bg-neutral-700/40 transition align-top">

                                    <td class="hidden @3xl:table-cell px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>

                                    {{-- Type --}}
                                    <td class="inline-block @3xl:table-cell mr-2 @3xl:mr-0 @3xl:px-6 @3xl:py-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full whitespace-nowrap
                                            @if($request->request_type_id == \App\Models\RequestType::FACILITY) bg-[#123524]/10 text-[#123524] dark:bg-[#123524]/40 dark:text-[#9FD3AE]
                                            @else bg-[#E8F3EC] text-[#2F8A5B] dark:bg-[#1C6B45]/25 dark:text-[#7FBF8E] @endif">
                                            {{ $request->requestType->type_name ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- Date --}}
                                    <td class="inline-block @3xl:table-cell mr-2 @3xl:mr-0 @3xl:px-6 @3xl:py-4 text-sm text-gray-400">
                                        {{ $request->created_at->format('M d, Y') }}
                                    </td>

                                    {{-- Status --}}
                                    <td class="inline-block @3xl:table-cell @3xl:px-6 @3xl:py-4">
                                        @php
                                            [$tone, $text] = match ($request->status) {
                                                'admin_review'       => ['waiting', 'Admin reviewing'],
                                                'coordinator_review' => ['approved', 'Admin accepted'],
                                                default              => [$request->status, ucfirst(str_replace('_', ' ', $request->status))],
                                            };
                                        @endphp
                                        <x-portal.status :status="$tone">{{ $text }}</x-portal.status>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="block @3xl:table-cell pt-3 @3xl:px-6 @3xl:py-4 text-left @3xl:text-right @3xl:whitespace-nowrap">
                                        <div class="flex @3xl:inline-flex flex-wrap items-center gap-1.5">

                                            {{-- ✅ View — requires `program-head-requests.view` --}}
                                            @can('program-head-requests.view')
                                                <a href="{{ route('coordinator.request-to-admin.view-admin', $request->id) }}"
                                                    class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent bg-neutral-100 text-neutral-700 hover:bg-neutral-200 dark:bg-neutral-700 dark:text-neutral-200 dark:hover:bg-neutral-600 transition">
                                                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    View
                                                </a>
                                            @endcan

                                            {{-- ✅ Edit / Delete — only pending requests --}}
                                            @if($request->status === 'pending')
                                                @can('program-head-requests.update')
                                                    <button wire:click="openEdit({{ $request->id }})"
                                                        class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent bg-[#E8F3EC] text-[#1C6B45] hover:bg-[#D6EBDD] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE] dark:hover:bg-[#1C6B45]/35 transition">
                                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                                        </svg>
                                                        Edit
                                                    </button>
                                                @endcan

                                                @can('program-head-requests.delete')
                                                    <button wire:click="delete({{ $request->id }})"
                                                        wire:confirm="Are you sure you want to delete this request?"
                                                        class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-transparent bg-red-100 text-red-700 hover:bg-red-200 dark:bg-red-900 dark:text-red-400 dark:hover:bg-red-800 transition">
                                                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                        </svg>
                                                        Delete
                                                    </button>
                                                @endcan
                                            @elseif($request->status === 'approved')
                                                <a href="{{ route('printable.receipt', ['request_id' => $request->id]) }}" target="_blank"
                                                    class="inline-flex items-center gap-x-1.5 px-3 py-1.5 text-xs font-medium rounded-lg border border-[#1C6B45]/30 bg-white text-[#123524] hover:bg-[#F3F8F5] dark:bg-neutral-800 dark:text-white dark:border-neutral-600 dark:hover:bg-neutral-700 transition">
                                                    <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                                                    </svg>
                                                    Print
                                                </a>
                                            @endif
                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr class="block @3xl:table-row">
                                    <td colspan="5" class="block @3xl:table-cell">
                                        @can('program-head-requests.create')
                                            <x-portal.empty message="No requests to the Admin yet" hint="Need a room or materials for your department? Ask the Admin here." :href="route('coordinator.request-to-admin.create-request')" action="+ New Request" />
                                        @else
                                            <x-portal.empty message="No requests to the Admin yet" />
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
    </div>
</div>

{{-- Edit Modal --}}
@can('program-head-requests.update')
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">Edit Request</h2>
                <button wire:click="closeEdit"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5">


                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model.live="request_date"
                        min="{{ \App\Support\BookingDate::min() }}"
                        max="{{ \App\Support\BookingDate::max() }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                    @error('request_date')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                @if($request_type_id == \App\Models\RequestType::FACILITY)
                <div class="space-y-4 p-4 bg-[#F3F8F5] dark:bg-[#1C6B45]/10 rounded-xl border border-[#1C6B45]/15 dark:border-[#7FBF8E]/20">
                    <p class="text-sm font-semibold text-[#1C6B45] dark:text-[#9FD3AE]">Facility Details</p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            Facility Name <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="facility_name"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                            <option value="">Select a facility</option>
                            @foreach($facilityOptions as $facility)
                                <option value="{{ $facility }}">{{ $facility }}</option>
                            @endforeach
                        </select>
                        @error('facility_name')
                            <p class="text-xs text-red-500 mt-1 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    @include('partials.reservation-day-bookings', ['date' => $request_date])

                    @include('partials.reservation-time-pickers')
                </div>
                @endif


                @include('partials.purpose-field', ['placeholder' => 'What is it for? (at least 10 characters)'])
            </div>

            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
                <button wire:click="closeEdit"
                    class="px-4 py-2 text-sm bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-600 rounded-lg transition">
                    Cancel
                </button>
                <button wire:click="saveEdit"
                    wire:loading.attr="disabled" wire:target="saveEdit"
                    wire:loading.class="opacity-50 cursor-not-allowed"
                    class="px-4 py-2 text-sm bg-[#123524] hover:bg-[#0B2418] text-white rounded-lg font-medium transition">
                    <span wire:loading.remove wire:target="saveEdit">Save Changes</span>
                    <span wire:loading wire:target="saveEdit">Saving...</span>
                </button>
            </div>

        </div>
    </div>
    @endif
@endcan

</div>
