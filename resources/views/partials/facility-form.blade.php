{{-- ✅ Room form fields shared by Add Facility and Edit Facility --}}
@php
    $fld = 'w-full py-2.5 px-3.5 border border-gray-300 dark:border-neutral-600 rounded-lg text-sm bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none transition';
    $lbl = 'block mb-1.5 text-sm font-medium text-gray-700 dark:text-neutral-300';
@endphp

{{-- Name --}}
<div>
    <label for="facility-name" class="{{ $lbl }}">Facility Name <span class="text-red-500">*</span></label>
    <input type="text" id="facility-name" wire:model.blur="facility_name" maxlength="100"
        placeholder="e.g. RM 207, LAB 1, IS FIELD" class="{{ $fld }}">
    @error('facility_name')
        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
    @enderror
</div>

{{-- Kind / Capacity / Location --}}
<div class="grid sm:grid-cols-3 gap-4">
    <div>
        <label for="facility-kind" class="{{ $lbl }}">Kind</label>
        <select id="facility-kind" wire:model="room_kind" class="{{ $fld }}">
            <option value="">—</option>
            @foreach (\App\Support\Facilities::KINDS as $kind)
                <option value="{{ $kind }}">{{ $kind }}</option>
            @endforeach
        </select>
        @error('room_kind') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="facility-capacity" class="{{ $lbl }}">Capacity <span class="font-normal text-gray-400">(people)</span></label>
        <input type="number" id="facility-capacity" wire:model="capacity" min="1" max="5000" placeholder="e.g. 40" class="{{ $fld }}">
        @error('capacity') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
    </div>
    <div>
        <label for="facility-location" class="{{ $lbl }}">Building / floor</label>
        <input type="text" id="facility-location" wire:model="location" maxlength="100" list="facility-locations"
            placeholder="e.g. Main Bldg, 2F" class="{{ $fld }}">
        <datalist id="facility-locations">
            @foreach ($this->locations as $loc)
                <option value="{{ $loc }}"></option>
            @endforeach
        </datalist>
        @error('location') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
    </div>
</div>

{{-- Status --}}
<div>
    <label for="facility-status" class="{{ $lbl }}">Status <span class="text-red-500">*</span></label>
    <select id="facility-status" wire:model.live="status" class="{{ $fld }}">
        <option value="available">Available</option>
        <option value="maintenance">Under Maintenance</option>
    </select>
    @error('status') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
</div>

{{-- Maintenance details --}}
@if ($status === 'maintenance')
    <div class="grid sm:grid-cols-3 gap-4 p-4 rounded-xl border border-amber-200 bg-amber-50 dark:border-amber-900 dark:bg-amber-950/20">
        <div class="sm:col-span-2">
            <label for="facility-reason" class="{{ $lbl }}">Reason</label>
            <input type="text" id="facility-reason" wire:model="maintenance_reason" maxlength="255"
                placeholder="e.g. Aircon repair" class="{{ $fld }}">
            @error('maintenance_reason') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="facility-until" class="{{ $lbl }}">Until</label>
            <input type="date" id="facility-until" wire:model="maintenance_until" min="{{ today()->toDateString() }}" class="{{ $fld }}">
            @error('maintenance_until') <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p> @enderror
        </div>
        <p class="sm:col-span-3 text-xs text-amber-800 dark:text-amber-300">
            The room can't be picked for new bookings. People with upcoming bookings are told, with this reason.
        </p>
    </div>
@endif
