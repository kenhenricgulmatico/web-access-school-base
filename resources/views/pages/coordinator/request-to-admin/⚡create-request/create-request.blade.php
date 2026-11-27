<div>
<div class="max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
    <x-portal.page-header title="Create Request to Admin" subtitle="Submit a facility reservation or material request" :back="route('coordinator.request-to-admin.view-request')" back-label="My Requests to Admin" />

    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        <div class="p-6">

            {{-- Success Flash --}}
            @if(session()->has('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Error Flash --}}
            @if(session()->has('error'))
                <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- ✅ Forwarded from a student / faculty request --}}
            @if($forwardedFromId)
                <div class="mb-4 p-4 rounded-lg bg-[#E8F3EC] text-[#123524] border border-[#1C6B45]/20 text-sm dark:bg-[#1C6B45]/15 dark:text-[#9FD3AE] dark:border-[#7FBF8E]/25">
                    <p class="font-semibold">Forwarding {{ $forwardedFromName }}'s request to the Admin</p>
                    <p class="text-xs mt-0.5">The details below come from {{ $forwardedFromName }}'s request and can't be changed here,
                        so the Admin approves exactly what was asked for. When the Admin approves, {{ $forwardedFromName }}'s request is <strong>approved automatically</strong>.</p>
                </div>
            @endif

            {{-- ✅ Only users with `program-head-requests.create` can see & submit this form --}}
            @can('program-head-requests.create')
            <form wire:submit="submit" class="space-y-6">

                {{-- ✅ Endorsing: a locked summary of the faculty request (nothing to edit except the return date) --}}
                @if($forwardedFromId)
                    @php
                        $isFac   = $request_type_id == \App\Models\RequestType::FACILITY;
                        $picked  = collect($materials)->filter(fn ($m) => ! empty($m['resource_id']));
                    @endphp
                    <div class="rounded-xl border border-gray-200 dark:border-neutral-700 divide-y divide-gray-100 dark:divide-neutral-700 text-sm">
                        <div class="flex justify-between gap-4 px-4 py-2.5">
                            <span class="text-gray-500 dark:text-neutral-400">Request type</span>
                            <span class="font-medium text-gray-800 dark:text-neutral-200">{{ $isFac ? 'Facility Reservation' : 'Material Request' }}</span>
                        </div>
                        @if($isFac)
                            <div class="flex justify-between gap-4 px-4 py-2.5">
                                <span class="text-gray-500 dark:text-neutral-400">Facility</span>
                                <span class="font-medium text-gray-800 dark:text-neutral-200">{{ $facility_name }}</span>
                            </div>
                            <div class="flex justify-between gap-4 px-4 py-2.5">
                                <span class="text-gray-500 dark:text-neutral-400">Date &amp; time</span>
                                <span class="font-medium text-gray-800 dark:text-neutral-200 text-right">
                                    {{ \Carbon\Carbon::parse($request_date)->format('M d, Y') }} ·
                                    {{ \Carbon\Carbon::parse($start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($end_time)->format('g:i A') }}
                                </span>
                            </div>
                        @else
                            <div class="flex justify-between gap-4 px-4 py-2.5">
                                <span class="text-gray-500 dark:text-neutral-400">Date needed</span>
                                <span class="font-medium text-gray-800 dark:text-neutral-200">{{ \Carbon\Carbon::parse($request_date)->format('M d, Y') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between gap-4 px-4 py-2.5">
                            <span class="text-gray-500 dark:text-neutral-400">Items</span>
                            <span class="font-medium text-gray-800 dark:text-neutral-200 text-right">
                                @forelse($picked as $m)
                                    {{ $this->getMaterialResource($m['resource_id'])?->resource_name }} ×{{ $m['quantity'] }}@if(! $loop->last)<br>@endif
                                @empty
                                    <span class="text-gray-400">None</span>
                                @endforelse
                            </span>
                        </div>
                        <div class="px-4 py-2.5">
                            <span class="block text-gray-500 dark:text-neutral-400 mb-0.5">Purpose</span>
                            <span class="text-gray-800 dark:text-neutral-200">{{ $purpose }}</span>
                        </div>
                    </div>
                    @error('facility_name') <p class="text-xs text-red-500 font-medium">{{ $message }}</p> @enderror
                    @error('materials') <p class="text-xs text-red-500">{{ $message }}</p> @enderror
                @else

                {{-- Request Type --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Request Type <span class="text-red-500">*</span>
                    </label>
                    <select wire:model.live="request_type_id"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                        <option value="">-- Select Request Type --</option>
                        @foreach($this->requestTypes as $type)
                            <option value="{{ $type->id }}">{{ $type->type_name }}</option>
                        @endforeach
                    </select>
                    @error('request_type_id')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Shared: Date --}}
                @if($request_type_id)
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        {{ $request_type_id == \App\Models\RequestType::FACILITY ? 'Reservation Date' : 'Date Needed' }} <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model.live="request_date"
                        min="{{ \App\Support\BookingDate::min() }}"
                        max="{{ \App\Support\BookingDate::max() }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                    @error('request_date')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                {{-- Facility Fields --}}
                @if($request_type_id == \App\Models\RequestType::FACILITY)
                <div class="space-y-4 p-4 bg-[#F3F8F5] dark:bg-[#1C6B45]/10 rounded-xl border border-[#1C6B45]/15 dark:border-[#7FBF8E]/20">
                    <p class="text-sm font-semibold text-[#1C6B45] dark:text-[#9FD3AE]">Facility Details</p>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            Facility Name <span class="text-red-500">*</span>
                        </label>
                        <select wire:model.live="facility_name"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
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

                {{-- Materials — OPTIONAL add-on to a facility reservation --}}
                <div class="border-t border-gray-200 dark:border-neutral-700 pt-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">
                            Materials Needed <span class="text-xs font-normal text-gray-400">(optional — e.g. bench for court)</span>
                        </label>
                        <button type="button"
                            wire:click="addMaterial"
                            class="text-xs px-3 py-1.5 bg-[#1C6B45]/10 text-[#1C6B45] dark:text-green-300 border border-[#1C6B45]/30 rounded-lg hover:bg-[#1C6B45]/15 transition font-medium">
                            + Add Material
                        </button>
                    </div>

                    @if(empty($materials))
                        <p class="text-xs text-gray-400 italic">No materials added.</p>
                    @endif

                    <div class="space-y-3">
                        @foreach($materials as $index => $material)
                            @php
                                $available = $this->getAvailableStock($material['resource_id'] ?? null);
                                $breakdown = $this->getQuantityBreakdown($material['resource_id'] ?? null, $material['quantity'] ?? null);
                                $warning   = $this->getStockWarning($material['resource_id'] ?? null, $material['quantity'] ?? null);
                                $rowOptions = $this->getResourcesForRow($index);
                            @endphp
                            <div wire:key="fac-material-{{ $index }}" class="flex items-start gap-2 bg-gray-50 dark:bg-neutral-700/40 border border-gray-200 dark:border-neutral-700 rounded-lg p-3">
                                <div class="flex-1">
                                    <select wire:model.live="materials.{{ $index }}.resource_id"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                        <option value="">Select material</option>
                                        {{-- ✅ Same as the faculty form: grouped into Material (returned) / Supplies (used up) --}}
                                        @foreach([
                                            'Material — must be returned'           => collect($rowOptions)->filter(fn ($o) => ! empty($o->is_returnable)),
                                            'Supplies — used up, no need to return' => collect($rowOptions)->reject(fn ($o) => ! empty($o->is_returnable)),
                                        ] as $groupLabel => $groupOptions)
                                            @if($groupOptions->isNotEmpty())
                                            <optgroup label="{{ $groupLabel }}">
                                                @foreach($groupOptions as $resource)
                                                    <option wire:key="row-{{ $index }}--resource--{{$resource->id}}" value="{{$resource->id}}">
                                                        {{ $resource->resource_name }} ({{ $resource->quantity_available }} {{ $resource->unit ?: 'Ream'}} available)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('materials.' . $index . '.resource_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="w-32">
                                    <input type="number" min="1"
                                        wire:model.live="materials.{{ $index }}.quantity"
                                        placeholder="Qty {{$this->getMaterialResource($material['resource_id'] ?? null)->unit ?? 'ream'}}"
                                        class="w-full px-3 py-2 text-sm rounded-lg border {{ $warning ? 'border-red-400' : 'border-gray-300 dark:border-neutral-600' }} bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                    @if($breakdown)
                                        <p class="text-[10px] text-[#1C6B45] dark:text-green-400 font-medium mt-1">Requesting: {{ $breakdown }}</p>
                                    @endif
                                    @if($warning)
                                        <p class="text-[10px] text-red-500 mt-1 font-medium">{{ $warning }}</p>
                                    @endif
                                    @error('materials.' . $index . '.quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <button type="button" wire:click="removeMaterial({{ $index }})"
                                    class="mt-2 text-gray-400 hover:text-red-600 transition" title="Remove">✕</button>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Material Request (standalone type) --}}
                @if($request_type_id == \App\Models\RequestType::MATERIAL)
                <div class="space-y-4 p-4 bg-gray-50 dark:bg-neutral-900/30 rounded-xl border border-gray-200 dark:border-neutral-700">
                    <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Materials Needed <span class="text-red-500">*</span></p>

                    <div class="space-y-3">
                        @foreach($materials as $index => $material)
                            @php
                                $available = $this->getAvailableStock($material['resource_id'] ?? null);
                                $breakdown = $this->getQuantityBreakdown($material['resource_id'] ?? null, $material['quantity'] ?? null);
                                $warning   = $this->getStockWarning($material['resource_id'] ?? null, $material['quantity'] ?? null);
                                $rowOptions = $this->getResourcesForRow($index);
                            @endphp
                            <div wire:key="mat-material-{{ $index }}" class="flex items-start gap-2">
                                <div class="flex-1">
                                    <select wire:model.live="materials.{{ $index }}.resource_id"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                        <option value="">Select material</option>
                                        {{-- ✅ Same as the faculty form: grouped into Material (returned) / Supplies (used up) --}}
                                        @foreach([
                                            'Material — must be returned'           => collect($rowOptions)->filter(fn ($o) => ! empty($o->is_returnable)),
                                            'Supplies — used up, no need to return' => collect($rowOptions)->reject(fn ($o) => ! empty($o->is_returnable)),
                                        ] as $groupLabel => $groupOptions)
                                            @if($groupOptions->isNotEmpty())
                                            <optgroup label="{{ $groupLabel }}">
                                                @foreach($groupOptions as $resource)
                                                    <option wire:key="row-{{ $index }}--resource--{{$resource->id}}" value="{{$resource->id}}">
                                                        {{ $resource->resource_name }} ({{ $resource->quantity_available }} {{ $resource->unit ?: 'Ream'}} available)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('materials.' . $index . '.resource_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="w-32">
                                    <input type="number" min="1"
                                        wire:model.live="materials.{{ $index }}.quantity"
                                        placeholder="Qty {{$this->getMaterialResource($material['resource_id'] ?? null)->unit ?? 'ream'}}"
                                        class="w-full px-3 py-2 text-sm rounded-lg border {{ $warning ? 'border-red-400' : 'border-gray-300 dark:border-neutral-600' }} bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                    @if($breakdown)
                                        <p class="text-[10px] text-[#1C6B45] dark:text-green-400 font-medium mt-1">Requesting: {{ $breakdown }}</p>
                                    @endif
                                    @if($warning)
                                        <p class="text-[10px] text-red-500 mt-1 font-medium">{{ $warning }}</p>
                                    @endif
                                    @error('materials.' . $index . '.quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                @if(count($materials) > 1)
                                    <button type="button" wire:click="removeMaterial({{ $index }})"
                                        class="mt-2 text-red-500 hover:text-red-700 font-bold text-lg px-2">&times;</button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <button type="button" wire:click="addMaterial"
                        class="mt-2 px-4 py-2 text-sm bg-[#123524] hover:bg-[#0B2418] text-white rounded-lg transition">
                        + Add Item
                    </button>

                    @error('materials')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                {{-- Purpose (after the details, same order as the faculty form) --}}
                @if($request_type_id)
                @include('partials.purpose-field', ['placeholder' => 'What is it for? (at least 10 characters)'])
                @endif

                @endif {{-- end: normal (not endorsing) form --}}

                {{-- ✅ Return deadline — ONLY when borrowing Pcs items (chairs…). Not for Ream / Set. --}}
                @if($request_type_id && $this->hasBorrowItems())
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 inline-flex items-center justify-center size-9 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-800/40 dark:text-amber-300">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">You are borrowing Material items that must be returned</p>
                            <p class="text-xs text-amber-800/80 dark:text-amber-300/80">
                                Items counted in pieces (like chairs) go back to the Admin. You can keep them for
                                <strong>1 month at most</strong> — until {{ \Carbon\Carbon::parse($this->returnByMax())->format('M d, Y') }}.
                            </p>
                        </div>
                    </div>
                    <div>
                        <label for="return_by" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            Return by <span class="text-red-500">*</span>
                        </label>
                        <input id="return_by" type="date" wire:model.live="return_by"
                            min="{{ $this->returnByMin() }}" max="{{ $this->returnByMax() }}"
                            class="w-full sm:w-64 px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                        @error('return_by')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                @endif

                {{-- ✅ Where the Admin will see it --}}
                @if($request_type_id)
                <p class="flex items-start gap-2 text-xs text-gray-600 dark:text-neutral-400 bg-gray-50 dark:bg-neutral-900/40 border border-gray-200 dark:border-neutral-700 rounded-lg px-3 py-2">
                    <svg class="size-4 shrink-0 mt-px text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $this->destinationHint() }}</span>
                </p>
                @endif

                {{-- Buttons --}}
                @if($request_type_id)
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        wire:loading.attr="disabled" wire:target="submit"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-6 py-2 bg-[#123524] hover:bg-[#0B2418] text-white rounded-lg font-medium transition">
                        <span wire:loading.remove wire:target="submit">Submit Request</span>
                        <span wire:loading wire:target="submit">Submitting...</span>
                    </button>
                    <button type="button" wire:click="cancel"
                        class="px-6 py-2 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-600 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
                @endif

            </form>
            @endcan

        </div>
    </div>
</div>
</div>
