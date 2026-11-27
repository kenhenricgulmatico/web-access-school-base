{{--
    Faculty portal — tabs that stay on one line (they scroll sideways on a small phone).
    <x-portal.tabs label="Filter reservations">
        <x-portal.tab :active="$tab === 'upcoming'" :count="3" wire:click="$set('tab','upcoming')">Upcoming</x-portal.tab>
    </x-portal.tabs>
--}}
@props(['label' => 'Tabs'])

<div class="max-w-full overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
    <div {{ $attributes->merge(['class' => 'inline-flex flex-nowrap gap-1 p-1 rounded-xl bg-[#F3F8F5] ring-1 ring-inset ring-[#1C6B45]/10 dark:bg-neutral-900/60 dark:ring-neutral-700']) }}
         role="tablist" aria-label="{{ $label }}">
        {{ $slot }}
    </div>
</div>
