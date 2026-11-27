{{-- Faculty Borrowed Items: empty message for the current tab. --}}
@if($filter === 'returned')
    <x-portal.empty message="No returned items yet" hint="Items you give back will show here once your Program Head confirms them." />
@else
    <x-portal.empty message="Nothing to return" hint="You have no borrowed items right now — you're all set." />
@endif
