{{-- My Material Requests: buttons for one request. Needs $request and $info (rowInfo()). --}}
@can('material-requests.view')
    <a href="{{ route('portal.view-material-reservation', $request->id) }}" wire:navigate
       class="text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 text-sm font-medium">
        View
    </a>
@endcan

@if($request->status === 'pending')
    {{-- Edit — not once it's endorsed to the Admin --}}
    @can('material-requests.update')
        @if(! $info['endorsed'])
            <a href="{{ route('portal.edit-material', $request->id) }}"
               class="text-[#1C6B45] hover:text-[#123524] dark:text-green-300 dark:hover:text-green-200 text-sm font-medium">
                Edit
            </a>
        @else
            <span class="text-xs text-gray-400" title="The Admin is reviewing this exact request. Cancel it and make a new one if you need changes.">Can't edit — sent to Admin</span>
        @endif
    @endcan

    {{-- Cancel — also withdraws the endorsed copy at the Admin --}}
    @can('material-requests.cancel')
        <button wire:click="cancelRequest({{ $request->id }})"
                wire:confirm="{{ $info['endorsed'] ? 'Cancel this request? It was already sent to the Admin — that request will be withdrawn too.' : 'Cancel this request? Your Program Head will be told.' }}"
                wire:loading.attr="disabled" wire:target="cancelRequest({{ $request->id }})"
                class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 text-sm font-medium disabled:opacity-60">
            Cancel
        </button>
    @endcan
@endif
