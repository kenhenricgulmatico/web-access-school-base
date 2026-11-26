{{--
    ✅ Purpose box with a live character counter (counted in the browser — no server trip per key).
    Optional: ['placeholder' => '...']. Needs a `purpose` property on the Livewire component.
--}}
@php $min = 10; $max = 500; @endphp
<div x-data="{ len: ($wire.purpose ?? '').length }">
    <div class="flex items-end justify-between gap-3 mb-1">
        <label for="purpose" class="block text-sm font-medium text-gray-700 dark:text-neutral-300">
            Purpose <span class="text-red-500">*</span>
        </label>
        <span class="text-[11px] tabular-nums"
              :class="len < {{ $min }} ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-neutral-500'"
              x-text="len < {{ $min }} ? (len + ' / {{ $min }} characters minimum') : (len + ' / {{ $max }}')">0 / {{ $min }} characters minimum</span>
    </div>
    <textarea id="purpose" wire:model="purpose" rows="3" maxlength="{{ $max }}"
        x-on:input="len = $event.target.value.length"
        placeholder="{{ $placeholder ?? 'What is it for? (at least 10 characters)' }}"
        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-[#1C6B45]/30 focus:border-[#1C6B45] focus:outline-none"></textarea>
    @error('purpose') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
</div>
