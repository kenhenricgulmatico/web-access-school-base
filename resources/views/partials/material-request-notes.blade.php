{{-- My Material Requests: extra lines under the status. Needs $request and $info (rowInfo()). --}}
@if($info['reason'])
    {{-- ✅ Why it was rejected --}}
    <p class="mt-1.5 max-w-xs text-xs text-red-700 dark:text-red-300 whitespace-normal"><span class="font-semibold">Reason:</span> {{ $info['reason'] }}</p>
@endif

@if($info['toReturn'] > 0)
    {{-- ✅ Approved borrowed items still with me → where to return them --}}
    <a href="{{ route('portal.borrowed-items') }}" wire:navigate
       class="mt-1.5 inline-flex items-center gap-1 text-xs font-medium text-amber-700 dark:text-amber-300 hover:underline whitespace-normal">
        <svg class="size-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $info['toReturn'] }} {{ \Illuminate\Support\Str::plural('item', $info['toReturn']) }} to return by {{ $info['returnBy'] }} → Borrowed Items
    </a>
@endif
