@props(['active' => false, 'count' => null])

<button type="button" role="tab" aria-selected="{{ $active ? 'true' : 'false' }}"
    {{ $attributes->merge(['class' => 'shrink-0 whitespace-nowrap min-h-9 px-3 rounded-lg text-sm font-semibold transition inline-flex items-center gap-2 ' . ($active
        ? 'bg-white text-[#123524] shadow-sm ring-1 ring-[#1C6B45]/15 dark:bg-neutral-700 dark:text-white dark:ring-0'
        : 'text-gray-600 hover:text-[#123524] hover:bg-white/60 dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800')]) }}>
    {{ $slot }}
    @if (! is_null($count))
        <span class="text-xs px-1.5 py-0.5 rounded-full {{ $active ? 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-[#9FD3AE]' : 'bg-gray-200/70 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300' }}">{{ $count }}</span>
    @endif
</button>
