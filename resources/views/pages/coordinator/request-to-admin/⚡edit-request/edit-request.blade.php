<div class="select-none">
<div class="max-w-3xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Edit Request to Admin</h2>
            <p class="text-sm text-gray-600 dark:text-neutral-400">Update your facility reservation or material request</p>
        </div>

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

            {{-- ✅ Only users with `program-head-requests.update` can see & submit this form --}}
            @can('program-head-requests.update')
            <form wire:submit="update" class="space-y-6">

                {{-- Request Type (locked — cannot change after creation) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Request Type
                    </label>
                    <select disabled
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-gray-100 dark:bg-neutral-800 text-gray-500 dark:text-neutral-400 cursor-not-allowed">
                        @foreach($this->requestTypes as $type)
                            <option value="{{ $type->id }}" {{ $type->id == $request_type_id ? 'selected' : '' }}>
                                {{ $type->type_name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-400 mt-1">Request type cannot be changed after submission.</p>
                </div>


                {{-- Shared: Date --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date" wire:model="request_date"
                        min="{{ \App\Support\BookingDate::min() }}"
                        max="{{ \App\Support\BookingDate::max() }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                    @error('request_date')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Facility Fields --}}
                @if($request_type_id == \App\Models\RequestType::FACILITY)
                <div class="space-y-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-700">
                    <p class="text-sm font-semibold text-green-700 dark:text-green-300">Facility Details</p>

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
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
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
                                $available = $this->getAvailableStock($material['resource_id'] ?? null, $material['id'] ?? null);
                                $breakdown = $this->getQuantityBreakdown($material['resource_id'] ?? null, $material['quantity'] ?? null);
                                $warning   = $this->getStockWarning($material['resource_id'] ?? null, $material['quantity'] ?? null, $material['id'] ?? null);
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
                                                    <option value="{{ $resource->id }}">
                                                        {{ $resource->resource_name }} ({{ $resource->available_formatted }} available)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    @if($available)
                                        <p class="text-[10px] text-gray-500 mt-1">Available: {{ $available }}</p>
                                    @endif
                                    @error('materials.' . $index . '.resource_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="w-32">
                                    <input type="number" min="1"
                                        wire:model.live="materials.{{ $index }}.quantity"
                                        placeholder="Qty (pcs)"
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
                                    class="mt-2 text-gray-400 hover:text-red-600 transition" title="Remove">
                                    <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
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
                                $available = $this->getAvailableStock($material['resource_id'] ?? null, $material['id'] ?? null);
                                $breakdown = $this->getQuantityBreakdown($material['resource_id'] ?? null, $material['quantity'] ?? null);
                                $warning   = $this->getStockWarning($material['resource_id'] ?? null, $material['quantity'] ?? null, $material['id'] ?? null);
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
                                                    <option value="{{ $resource->id }}">
                                                        {{ $resource->resource_name }} ({{ $resource->available_formatted }} available)
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    @if($available)
                                        <p class="text-[10px] text-gray-500 mt-1">Available: {{ $available }}</p>
                                    @endif
                                    @error('materials.' . $index . '.resource_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div class="w-32">
                                    <input type="number" min="1"
                                        wire:model.live="materials.{{ $index }}.quantity"
                                        placeholder="Qty (pcs)"
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
                                        class="mt-2 text-red-500 hover:text-red-700 px-2" title="Remove">
                                        <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
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

                {{-- Purpose (after the details, same order as Create) --}}
                @include('partials.purpose-field', ['placeholder' => 'What is it for? (at least 10 characters)'])

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

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit"
                        wire:loading.attr="disabled" wire:target="update"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-6 py-2 bg-[#123524] hover:bg-[#0B2418] text-white rounded-lg font-medium transition">
                        <span wire:loading.remove wire:target="update">Save Changes</span>
                        <span wire:loading wire:target="update">Saving...</span>
                    </button>
                    <button type="button" wire:click="cancel"
                        class="px-6 py-2 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-600 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>

            </form>
            @endcan
        </div>
    </div>
</div>
</div>
