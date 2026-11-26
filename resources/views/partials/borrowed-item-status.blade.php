{{-- Faculty Borrowed Items: status badge + details for one item. Needs $item and $info (rowInfo()). --}}
<span class="inline-flex px-2.5 py-1 rounded-full text-xs font-semibold {{ $info['badge'] }}">{{ $info['label'] }}</span>

@if($item->returned_at)
    <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">
        {{ $item->returned_at->format('M d, Y') }}@if($item->receiver) · confirmed by {{ $item->receiver->name }}@endif
    </p>
    @if($item->return_notes)
        <p class="text-xs text-gray-500 dark:text-neutral-400 italic">"{{ $item->return_notes }}"</p>
    @endif
@elseif($item->return_requested_at)
    <p class="text-xs text-gray-500 dark:text-neutral-400 mt-1">Sent {{ \Carbon\Carbon::parse($item->return_requested_at)->format('M d, Y g:i A') }}</p>
@elseif($info['reminder'])
    {{-- Message from the Program Head --}}
    <div class="mt-2 max-w-sm px-3 py-2 rounded-lg bg-red-50 border border-red-100 dark:bg-red-950/30 dark:border-red-900">
        <p class="flex items-center gap-1 text-[11px] font-semibold text-red-700 dark:text-red-300">
            <svg class="size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0"/>
            </svg>
            Message from your Program Head · {{ $info['reminder']->created_at->diffForHumans() }}
        </p>
        <p class="text-xs text-red-800 dark:text-red-200 mt-0.5 whitespace-normal">{{ $info['reminder']->message }}</p>
    </div>
@endif
