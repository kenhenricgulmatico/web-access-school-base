<div>
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Edit Reservation</h2>
            <p class="text-sm text-gray-600 dark:text-neutral-400">Update your facility reservation</p>
        </div>

        <div class="p-6">
            @if (session()->has('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-100 text-green-800 border border-green-200">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="mb-4 p-4 rounded-lg bg-red-100 text-red-800 border border-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="update" class="space-y-6">
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
                    @error('facility_name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Reservation Date <span class="text-red-500">*</span>
                    </label>
                    <input type="date"
                        wire:model.live="used_date"
                        min="{{ \App\Support\BookingDate::min() }}"
                        max="{{ \App\Support\BookingDate::max() }}"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                    @error('used_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- ✅ What is already booked for this facility on this day (not counting this reservation) --}}
                @if($facility_name && $used_date)
                    @php $bookings = $this->dayBookings; @endphp
                    <div class="rounded-lg border p-3 text-sm {{ count($bookings) ? 'border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800' : 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800' }}">
                        @if(count($bookings))
                            <p class="font-medium text-amber-900 dark:text-amber-200 mb-1.5">Already booked on {{ \Carbon\Carbon::parse($used_date)->format('M d, Y') }}:</p>
                            <ul class="space-y-1">
                                @foreach($bookings as $bk)
                                    <li class="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                                        <span class="size-1.5 rounded-full bg-amber-500 shrink-0"></span>
                                        <span class="font-semibold">{{ $bk['label'] }}</span>
                                        <span class="text-xs text-amber-700/80 dark:text-amber-400/80">· {{ $bk['department'] }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <p class="text-green-800 dark:text-green-300"><span class="font-semibold">{{ $facility_name }}</span> is free all day on {{ \Carbon\Carbon::parse($used_date)->format('M d, Y') }} — no other approved bookings.</p>
                        @endif
                    </div>
                @endif

                @include('partials.reservation-time-pickers')

                {{-- MATERIALS PICKER --}}
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
                            <div wire:key="material-{{ $material['item_id'] ?? 'new-' . $index }}" class="flex items-start gap-2 bg-gray-50 dark:bg-neutral-700/40 border border-gray-200 dark:border-neutral-700 rounded-lg p-3">
                                <div class="flex-1">
                                    <select wire:model.live="materials.{{ $index }}.resource_id"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                        <option value="">Select material</option>
                                        @php
                                            $__opts    = collect($availableResources);
                                            $__borrow  = $__opts->filter(fn ($o) => ! empty($o->is_returnable));
                                            $__supply  = $__opts->reject(fn ($o) => ! empty($o->is_returnable));
                                        @endphp
                                        @if($__borrow->isNotEmpty())
                                            <optgroup label="Material — must be returned">
                                                @foreach($__borrow as $o)
                                                    <option value="{{ $o->id }}">{{ $o->resource_name }}</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if($__supply->isNotEmpty())
                                            <optgroup label="Supplies — used up, no need to return">
                                                @foreach($__supply as $o)
                                                    <option value="{{ $o->id }}">{{ $o->resource_name }} ({{ ($o->unit ?? null) ?: 'Ream' }})</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                    </select>
                                    {{-- ✅ Same as Material Request: what is available, in its unit --}}
                                    @php
                                        $__pick  = collect($availableResources)->firstWhere('id', (int) ($material['resource_id'] ?? 0));
                                        $__unit  = $__pick ? (($__pick->unit ?? null) ?: 'Ream') : null;
                                        $__qty   = (int) ($material['quantity'] ?? 0);
                                        $__over  = $__pick && $__qty > (int) $__pick->quantity_available;
                                    @endphp
                                    @error('materials.' . $index . '.resource_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <div class="w-28">
                                    <input type="number"
                                        min="1"
                                        wire:model.live="materials.{{ $index }}.quantity"
                                        placeholder="Qty{{ $__unit ? ' (' . $__unit . ')' : '' }}"
                                        class="w-full px-3 py-2 text-sm rounded-lg border {{ $__over ? 'border-red-400' : 'border-gray-300 dark:border-neutral-600' }} bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                    {{-- ✅ "Requesting: 1 Ream" / "1 Set" / "10 Pcs" --}}
                                    @if($__pick && $__qty > 0)
                                        <p class="text-[10px] text-[#1C6B45] dark:text-green-400 font-medium mt-1">Requesting: {{ $__qty }} {{ $__unit }}</p>
                                    @endif
                                    @if($__over)
                                        <p class="text-[10px] text-red-500 mt-1 font-medium">More than what is in stock — please request less</p>
                                    @endif
                                    @error('materials.' . $index . '.quantity') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                </div>

                                <button type="button"
                                    wire:click="removeMaterial({{ $index }})"
                                    class="mt-2 text-gray-400 hover:text-red-600 transition"
                                    title="Remove">
                                    ✕
                                </button>
                            </div>
                        @endforeach
                    </div>
                </div>

                @include('partials.purpose-field', ['placeholder' => 'What will you use the facility for? (at least 10 characters)'])

                {{-- ✅ Return date — ONLY when borrowing Pcs items (chairs…). Not for Ream / Set. --}}
                @if($this->hasBorrowItems())
                <div class="p-4 rounded-xl border border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-700 space-y-3">
                    <div class="flex items-start gap-3">
                        <span class="shrink-0 inline-flex items-center justify-center size-9 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-800/40 dark:text-amber-300">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 2m6-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">You are borrowing items that must be returned</p>
                            <p class="text-xs text-amber-800/80 dark:text-amber-300/80">
                                Items counted in pieces (like chairs) go back to your Program Head. You can keep them for
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
                            class="w-full sm:w-64 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                        @error('return_by')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                @endif

                <div class="flex gap-3 pt-4">
                    {{-- ✅ Disabled while saving, so a double click can't save twice --}}
                    <button type="submit"
                        wire:loading.attr="disabled" wire:target="update"
                        class="px-6 py-2 bg-[#123524] hover:bg-[#0B2418] text-white rounded-lg font-medium transition disabled:opacity-60 disabled:cursor-wait">
                        <span wire:loading.remove wire:target="update">Update Reservation</span>
                        <span wire:loading wire:target="update">Saving…</span>
                    </button>
                    <button type="button"
                        wire:click="cancel"
                        class="px-6 py-2 bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-600 rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
