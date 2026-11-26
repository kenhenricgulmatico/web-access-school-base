{{-- My Material Requests: empty message for the current tab. --}}
@if($tab === 'pending')
    No pending material requests.
    @can('material-requests.create')
        <a href="{{ route('portal.create-material') }}" wire:navigate class="text-[#1C6B45] dark:text-green-300 font-semibold hover:underline ml-1">Create one now</a>
    @endcan
@elseif($tab === 'approved')
    No approved material requests yet.
@else
    No cancelled or rejected material requests.
@endif
