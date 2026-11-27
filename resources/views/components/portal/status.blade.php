{{--
    Faculty portal — one set of status colors everywhere.
      green (light) : approved, returned, done
      yellow        : pending, due soon  (only for things that need attention)
      red           : rejected, overdue
      gray          : cancelled
      slate         : kept / assigned
      green (dark)  : borrowed, endorsed, in use
      green outline : sent back, waiting for the Program Head to check
    <x-portal.status status="approved" />          → "Approved"
    <x-portal.status status="soon">Due today</x-portal.status>
--}}
@props(['status' => null])

@php
    $key = strtolower((string) $status);
    $tone = match (true) {
        in_array($key, ['approved', 'returned', 'ok', 'done', 'confirmed', 'completed'])       => 'green',
        in_array($key, ['pending', 'submitted', 'soon', 'due', 'damaged'])                     => 'yellow',
        in_array($key, ['sent', 'waiting', 'for-checking'])                                     => 'outline',
        in_array($key, ['rejected', 'overdue', 'denied'])                                       => 'red',
        in_array($key, ['kept', 'assigned'])                                                    => 'slate',
        in_array($key, ['borrowed', 'endorsed', 'in-use', 'active'])                            => 'deep',
        default                                                                                  => 'gray',
    };
    $classes = [
        'green'  => 'bg-[#E8F3EC] text-[#1C6B45] ring-[#1C6B45]/15 dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE] dark:ring-[#7FBF8E]/20',
        'yellow' => 'bg-amber-50 text-amber-800 ring-amber-600/20 dark:bg-amber-500/15 dark:text-amber-300 dark:ring-amber-400/20',
        'red'    => 'bg-red-50 text-red-700 ring-red-600/15 dark:bg-red-500/15 dark:text-red-300 dark:ring-red-400/20',
        'slate'  => 'bg-slate-100 text-slate-700 ring-slate-500/15 dark:bg-slate-500/20 dark:text-slate-300 dark:ring-slate-400/20',
        'deep'   => 'bg-[#123524] text-white ring-[#123524] dark:bg-[#1C6B45] dark:ring-[#1C6B45]',
        'outline'=> 'bg-white text-[#1C6B45] ring-[#1C6B45]/40 dark:bg-transparent dark:text-[#9FD3AE] dark:ring-[#7FBF8E]/40',
        'gray'   => 'bg-gray-100 text-gray-600 ring-gray-500/15 dark:bg-neutral-700 dark:text-neutral-300 dark:ring-neutral-500/20',
    ][$tone];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold whitespace-nowrap ring-1 ring-inset {$classes}"]) }}>
    @if ($slot->isNotEmpty()){{ $slot }}@else{{ ucfirst(str_replace('-', ' ', $key)) }}@endif
</span>
