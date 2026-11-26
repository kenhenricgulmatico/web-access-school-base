{{--
    ✅ "Already booked on <day>" — approved bookings of the picked facility on the picked day.
    Pass ['date' => $request_date]. Needs $facility_name and $this->dayBookings on the component.
--}}
@if($facility_name && $date)
    @php $bookings = $this->dayBookings; @endphp
    <div class="rounded-lg border p-3 text-sm {{ count($bookings) ? 'border-amber-200 bg-amber-50 dark:bg-amber-900/20 dark:border-amber-800' : 'border-green-200 bg-green-50 dark:bg-green-900/20 dark:border-green-800' }}">
        @if(count($bookings))
            <p class="font-medium text-amber-900 dark:text-amber-200 mb-1.5">Already booked on {{ \Carbon\Carbon::parse($date)->format('M d, Y') }}:</p>
            <ul class="space-y-1">
                @foreach($bookings as $bk)
                    <li class="flex items-center gap-2 text-amber-800 dark:text-amber-300">
                        <span class="size-1.5 rounded-full bg-amber-500 shrink-0"></span>
                        <span class="font-semibold">{{ $bk['label'] }}</span>
                        <span class="text-xs text-amber-700/80 dark:text-amber-400/80">· {{ $bk['department'] }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="text-xs text-amber-700/80 dark:text-amber-400/80 mt-2">Booked times are greyed out in the time lists.</p>
        @else
            <p class="text-green-800 dark:text-green-300">
                <span class="font-semibold">{{ $facility_name }}</span> is free all day on {{ \Carbon\Carbon::parse($date)->format('M d, Y') }} — no approved bookings yet.
            </p>
        @endif
    </div>
@endif
