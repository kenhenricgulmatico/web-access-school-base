{{--
    ✅ Start / End time pickers for Create and Edit Reservation — every 30 minutes
    (App\Support\TimeSlots). Booked slots are greyed out; End only lists times after Start.
    Needs on the Livewire component: $start_time, $end_time, startIsTaken(), endIsTaken().
--}}
<div class="grid grid-cols-2 gap-4">
    <div>
        <label for="start_time" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
            Start Time <span class="text-red-500">*</span>
        </label>
        <select id="start_time" wire:model.live="start_time"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
            @foreach(\App\Support\TimeSlots::starts() as $value => $label)
                @php $taken = $this->startIsTaken($value); @endphp
                <option value="{{ $value }}" @disabled($taken && $start_time !== $value)>{{ $label }}{{ $taken ? ' — booked' : '' }}</option>
            @endforeach
        </select>
        @error('start_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    <div>
        <label for="end_time" class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
            End Time <span class="text-red-500">*</span>
        </label>
        <select id="end_time" wire:model.live="end_time" wire:key="end-time-{{ $start_time }}"
            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200">
            @foreach(\App\Support\TimeSlots::ends($start_time) as $value => $label)
                @php $taken = $this->endIsTaken($value); @endphp
                <option value="{{ $value }}" @disabled($taken && $end_time !== $value)>{{ $label }}{{ $taken ? ' — overlaps a booking' : '' }}</option>
            @endforeach
        </select>
        @error('end_time') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>
</div>
