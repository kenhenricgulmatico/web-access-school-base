{{--
    Faculty portal — the same page title on every page.
    <x-portal.page-header title="My Reservations" subtitle="Track your facility requests" :back="route('portal.reservation')">
        <x-slot:actions> buttons </x-slot:actions>
        (optional extra text under the subtitle)
    </x-portal.page-header>
--}}
@props(['title', 'subtitle' => null, 'back' => null, 'backLabel' => 'Back'])

<div {{ $attributes->merge(['class' => 'mb-5 sm:mb-6']) }}>
    @if ($back)
        <a href="{{ $back }}" wire:navigate
           class="inline-flex items-center gap-1 mb-2 text-xs font-semibold text-[#1C6B45] dark:text-[#7FBF8E] hover:underline">
            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
            {{ $backLabel }}
        </a>
    @endif

    <div class="flex flex-wrap items-end justify-between gap-3">
        <div class="min-w-0">
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">{{ $title }}</h1>
            @if ($subtitle)
                <p class="mt-1 text-sm text-gray-500 dark:text-neutral-400">{{ $subtitle }}</p>
            @endif
            @if ($slot->isNotEmpty())
                <div class="mt-1 text-sm text-gray-500 dark:text-neutral-400">{{ $slot }}</div>
            @endif
        </div>

        @isset($actions)
            <div class="flex flex-wrap items-center gap-2 shrink-0">{{ $actions }}</div>
        @endisset
    </div>
</div>
