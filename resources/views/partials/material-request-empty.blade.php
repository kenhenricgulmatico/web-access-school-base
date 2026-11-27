{{-- My Material Requests: empty message for the current tab. --}}
@if($tab === 'pending')
    @can('material-requests.create')
        <x-portal.empty message="No pending material requests" hint="Need chairs, a projector or supplies? Ask for them here." :href="route('portal.create-material')" action="+ Request Materials" />
    @else
        <x-portal.empty message="No pending material requests" />
    @endcan
@elseif($tab === 'approved')
    <x-portal.empty message="No approved material requests yet" hint="Approved requests will show here." />
@else
    <x-portal.empty message="Nothing cancelled or rejected" hint="Good news — every request went through." />
@endif
