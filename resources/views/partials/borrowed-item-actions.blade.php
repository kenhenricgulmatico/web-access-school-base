@can('borrowed-items.manage')
{{-- Faculty Borrowed Items: buttons for one item. Needs $item and $info (rowInfo()). --}}
@if(! $item->returned_at && ! $item->return_requested_at)
    <button type="button" wire:click="requestReturn({{ $item->id }})"
        wire:confirm="Return {{ $item->item_name }} (x{{ $item->quantity }}) to your Program Head?"
        wire:loading.attr="disabled" wire:target="requestReturn({{ $item->id }})"
        class="w-full sm:w-auto min-h-11 sm:min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm sm:text-xs font-semibold hover:bg-[#0B2418] disabled:opacity-60 transition">
        Return to Program Head
    </button>

    {{-- ✅ Several items from the same request still out → send them all back at once --}}
    @if($info['returnAll'] > 1)
        <button type="button" wire:click="requestReturnAll({{ $item->request_id }})"
            wire:confirm="Return all {{ $info['returnAll'] }} items from this request to your Program Head?"
            wire:loading.attr="disabled" wire:target="requestReturnAll({{ $item->request_id }})"
            class="w-full sm:w-auto mt-2 sm:mt-1.5 min-h-11 sm:min-h-0 sm:py-1 px-3 rounded-lg text-sm sm:text-xs font-medium text-[#1C6B45] dark:text-green-300 hover:underline disabled:opacity-60 transition">
            Return all {{ $info['returnAll'] }} from this request
        </button>
    @endif
@elseif(! $item->returned_at)
    <button type="button" wire:click="cancelRequestReturn({{ $item->id }})"
        wire:confirm="Cancel the return of {{ $item->item_name }}? Your Program Head will be told it is still with you."
        wire:loading.attr="disabled" wire:target="cancelRequestReturn({{ $item->id }})"
        class="w-full sm:w-auto min-h-11 sm:min-h-10 px-3 rounded-lg border border-gray-300 text-sm sm:text-xs font-medium bg-white hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200 dark:hover:bg-neutral-700 disabled:opacity-60 transition">
        Cancel return
    </button>
@endif
@endcan
