<div class="select-none">
<div class="max-w-4xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <div class="bg-white dark:bg-neutral-800 border rounded-xl shadow overflow-hidden">

        <div class="px-6 py-4 border-b">
            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Create Reservation</h2>
            <p class="text-sm text-gray-600 dark:text-neutral-400">Fill out the form to reserve a facility</p>
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

            <form wire:submit="submit" class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Facility Name <span class="text-red-500">*</span>
                    </label>
                    <select wire:model="facility_name"
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

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            Start Time <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="start_time"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                            <option value="08:00">8:00 AM</option>
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="17:00">5:00 PM</option>
                        </select>
                        @error('start_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            End Time <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="end_time"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                            <option value="09:00">9:00 AM</option>
                            <option value="10:00">10:00 AM</option>
                            <option value="11:00">11:00 AM</option>
                            <option value="12:00">12:00 PM</option>
                            <option value="13:00">1:00 PM</option>
                            <option value="14:00">2:00 PM</option>
                            <option value="15:00">3:00 PM</option>
                            <option value="16:00">4:00 PM</option>
                            <option value="17:00">5:00 PM</option>
                            <option value="18:00">6:00 PM</option>
                        </select>
                        @error('end_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                {{-- MATERIALS PICKER --}}
                <div class="border-t pt-5">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">
                            Materials Needed <span class="text-xs font-normal text-gray-400">(optional — e.g. bench for court)</span>
                        </label>
                        <button type="button"
                            wire:click="addMaterial"
                            class="text-xs px-3 py-1.5 bg-red-50 text-red-600 border border-red-200 rounded-lg hover:bg-red-100 transition font-medium">
                            + Add Material
                        </button>
                    </div>

                    @if(empty($materials))
                        <p class="text-xs text-gray-400 italic">No materials added. Click "+ Add Material" if you need equipment or supplies (e.g. chairs, projector, bond paper).</p>
                    @endif

                    @if(empty($availableResources) || (is_countable($availableResources) && count($availableResources) === 0))
                        <p class="text-xs text-amber-600 italic mt-1">No materials are available for your department right now.</p>
                    @endif

                    <div class="space-y-3">
                        @foreach($materials as $index => $material)
                        @php $rowOptions = $this->getMaterialOptionsForRow($index); @endphp
                            <div wire:key="material-{{ $index }}" class="flex items-start gap-2 bg-red-50/50 border border-red-100 rounded-lg p-3">
                                <div class="flex-1">
                                    <select wire:model.live="materials.{{ $index }}.resource_id"
                                        wire:key="material-select-{{$index}}--{{$rowOptions->count()}}"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
                                        <option value="">Select material</option>
                                        @php
                                            $__opts    = collect($rowOptions);
                                            $__borrow  = $__opts->filter(fn ($o) => ($o->unit ?? null) === 'Pcs');
                                            $__supply  = $__opts->reject(fn ($o) => ($o->unit ?? null) === 'Pcs');
                                        @endphp
                                        @if($__borrow->isNotEmpty())
                                            <optgroup label="Borrow — must be returned (Pcs)">
                                                @foreach($__borrow as $o)
                                                    <option value="{{ $o->id }}">{{ $o->resource_name }} — {{ $o->quantity_available }} Pcs available</option>
                                                @endforeach
                                            </optgroup>
                                        @endif
                                        @if($__supply->isNotEmpty())
                                            <optgroup label="Supplies — no need to return (Ream / Set)">
                                                @foreach($__supply as $o)
                                                    <option value="{{ $o->id }}">{{ $o->resource_name }} — {{ $o->quantity_available }} {{ ($o->unit ?? null) ?: 'Ream' }} available</option>
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
                                        <p class="text-[10px] text-blue-600 font-medium mt-1">Requesting: {{ $__qty }} {{ $__unit }}</p>
                                    @endif
                                    @if($__over)
                                        <p class="text-[10px] text-red-500 mt-1 font-medium">Exceeds available stock ({{ $__pick->quantity_available }} {{ $__unit }})</p>
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

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Purpose <span class="text-red-500">*</span>
                    </label>
                    <textarea wire:model="purpose"
                        rows="4"
                        placeholder="State your purpose for using this facility..."
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200"></textarea>
                    @error('purpose') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

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
                    <button type="submit"
                        class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition">
                        Submit Reservation
                    </button>
                    <button type="button"
                        wire:click="cancel"
                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
