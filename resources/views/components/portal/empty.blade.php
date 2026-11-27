{{--
    Faculty portal — friendly "nothing here yet" message with an optional button.
    <x-portal.empty message="No reservations yet" hint="Book a room for your class." :href="route('portal.create-reservation')" action="Create reservation" />
--}}
@props(['message', 'hint' => null, 'href' => null, 'action' => null])

<div {{ $attributes->merge(['class' => 'px-5 py-10 sm:py-12 text-center']) }}>
    <div class="mx-auto mb-3 flex size-12 items-center justify-center rounded-full bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]">
        <svg class="size-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m6 4.125l2.25 2.25m0 0l2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" /></svg>
    </div>
    <p class="text-sm font-semibold text-gray-700 dark:text-neutral-200">{{ $message }}</p>
    @if ($hint)
        <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400">{{ $hint }}</p>
    @endif
    @if ($href && $action)
        <a href="{{ $href }}" wire:navigate
           class="mt-4 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-[#123524] text-white hover:bg-[#0B2418] transition">
            {{ $action }}
        </a>
    @endif
    {{ $slot }}
</div>
