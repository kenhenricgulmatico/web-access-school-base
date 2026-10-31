@php
  $statusChip = [
    'approved' => 'bg-[#E3F1E8] text-[#145A37]',
    'pending'  => 'bg-[#FCF0D6] text-[#7A4D00]',
    'rejected' => 'bg-[#FCE9E7] text-[#A3271B]',
  ];
  $statusDot = [
    'approved' => 'bg-[#1C7A4A]',
    'pending'  => 'bg-[#C98A10]',
    'rejected' => 'bg-[#C2372A]',
  ];
  $isMine   = $activeTab === 'mine';
  $selected = \Carbon\Carbon::parse($selectedDate);
  $today    = now()->format('Y-m-d');
@endphp

<div class="min-h-screen bg-[#F4F6F3] text-[#1F2A24]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex flex-col gap-6">

    {{-- ===== Header ===== --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div class="flex flex-col gap-1.5">
        <p class="text-xs font-semibold tracking-widest uppercase text-[#8A6A1F]">Student &amp; Faculty Portal</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123524]">Calendar</h1>
        <p class="text-sm text-[#4A564F]">Track your requests by date and see which facilities are already booked.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        <div class="inline-flex p-1 bg-[#E6EAE6] rounded-2xl gap-1" role="tablist" aria-label="Calendar view">
          <button type="button" role="tab" wire:click="setTab('mine')" aria-selected="{{ $isMine ? 'true' : 'false' }}"
            class="min-h-10 px-4 rounded-xl text-sm font-semibold transition
              {{ $isMine ? 'bg-white text-[#123524] shadow-sm' : 'text-[#4A564F] hover:text-[#123524]' }}">
            My Requests
          </button>
          <button type="button" role="tab" wire:click="setTab('availability')" aria-selected="{{ $isMine ? 'false' : 'true' }}"
            class="min-h-10 px-4 rounded-xl text-sm font-semibold transition
              {{ ! $isMine ? 'bg-white text-[#123524] shadow-sm' : 'text-[#4A564F] hover:text-[#123524]' }}">
            Facility Availability
          </button>
        </div>

        <a href="{{ route('portal.create-reservation') }}"
           class="inline-flex items-center gap-2 min-h-11 px-4 rounded-xl bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418] transition">
          <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>
          New reservation
        </a>
      </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-6 items-start">

      {{-- ===== Month grid ===== --}}
      <section aria-label="Month view" class="w-full lg:flex-1 min-w-0 bg-white border border-[#E1E6E1] rounded-2xl sm:rounded-3xl overflow-hidden">

        <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-5 py-4 border-b border-[#E6EAE6]">
          <div class="flex items-center gap-2.5">
            <button type="button" wire:click="previousMonth" aria-label="Previous month"
              class="size-11 inline-flex items-center justify-center rounded-xl border border-[#D9DED9] bg-white hover:bg-[#EEF1EE] transition">
              <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
            </button>
            <button type="button" wire:click="nextMonth" aria-label="Next month"
              class="size-11 inline-flex items-center justify-center rounded-xl border border-[#D9DED9] bg-white hover:bg-[#EEF1EE] transition">
              <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
            </button>
            <h2 class="ms-1.5 text-lg sm:text-xl font-bold text-[#123524]">
              {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </h2>
          </div>
          <button type="button" wire:click="goToToday"
            class="min-h-11 px-4 rounded-xl border border-[#D9DED9] bg-white text-sm font-semibold hover:bg-[#EEF1EE] transition">
            Today
          </button>
        </div>

        {{-- Weekdays --}}
        <div class="grid grid-cols-7 bg-[#FAFBFA] border-b border-[#E6EAE6]">
          @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $wd)
            <div class="px-2 py-2.5 text-[11px] font-semibold tracking-widest uppercase text-[#5C6862]">{{ $wd }}</div>
          @endforeach
        </div>

        {{-- Days --}}
        <div class="grid grid-cols-7">
          @foreach($this->calendarCells as $cell)
            @if(! $cell)
              <div class="min-h-16 sm:min-h-28 bg-[#FAFBFA] border-r border-b border-[#E6EAE6]" aria-hidden="true"></div>
            @else
              @php
                $isSel   = $cell['date'] === $selectedDate;
                $isToday = $cell['date'] === $today;
                $rows    = $cell['rows'];
                $count   = $isMine ? $rows->count() : $cell['booked'];
              @endphp
              <button type="button" wire:click="selectDate('{{ $cell['date'] }}')" wire:key="cell-{{ $cell['date'] }}"
                aria-pressed="{{ $isSel ? 'true' : 'false' }}"
                aria-label="{{ \Carbon\Carbon::parse($cell['date'])->format('F j') }}{{ $count ? ', '.$count.($isMine ? ' request' : ' booking').($count === 1 ? '' : 's') : '' }}"
                class="relative flex flex-col items-stretch gap-1 min-h-16 sm:min-h-28 p-1 sm:p-2 min-w-0 text-left border-r border-b border-[#E6EAE6] transition
                  focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#123524]
                  {{ $isSel ? 'bg-[#EEF4EF] ring-2 ring-inset ring-[#123524]' : 'bg-white hover:bg-[#F7F9F6]' }}">

                <span class="flex items-center justify-between">
                  <span class="inline-flex items-center justify-center size-7 rounded-full text-[13px] font-semibold
                    {{ $isToday ? 'bg-[#D4A537] text-[#1A1405]' : 'text-[#1F2A24]' }}">
                    {{ $cell['day'] }}
                  </span>

                  {{-- Phone: dots instead of labels --}}
                  <span class="flex sm:hidden gap-0.5 pe-0.5">
                    @if($isMine)
                      @foreach($rows as $r)
                        <span class="size-1.5 rounded-full {{ $statusDot[$r['status']] ?? 'bg-gray-400' }}"></span>
                      @endforeach
                    @elseif($cell['booked'])
                      <span class="size-1.5 rounded-full bg-[#B8862A]"></span>
                    @endif
                  </span>
                </span>

                {{-- Tablet / desktop: labels --}}
                @if($isMine)
                  @foreach($rows->take(2) as $r)
                    <span class="hidden sm:block truncate px-1.5 py-0.5 rounded-md text-[11.5px] font-medium {{ $statusChip[$r['status']] ?? '' }}">
                      {{ $r['title'] }}
                    </span>
                  @endforeach
                  @if($rows->count() > 2)
                    <span class="hidden sm:block px-1.5 text-[11px] font-semibold text-[#4A564F]">+{{ $rows->count() - 2 }} more</span>
                  @endif
                @elseif($cell['booked'])
                  <span class="hidden sm:block truncate px-1.5 py-0.5 rounded-md text-[11.5px] font-medium bg-[#F3E9CC] text-[#6B5216]">
                    {{ $cell['booked'] }} {{ $cell['booked'] === 1 ? 'room' : 'rooms' }} booked
                  </span>
                @endif
              </button>
            @endif
          @endforeach
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 px-4 sm:px-5 py-3.5 border-t border-[#E6EAE6] text-[12.5px] text-[#4A564F]">
          @if($isMine)
            <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#1C7A4A]"></span>Approved</span>
            <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#C98A10]"></span>Pending</span>
            <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#C2372A]"></span>Rejected</span>
          @else
            <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#B8862A]"></span>Facilities booked that day — pick a date to see free times</span>
          @endif
        </div>
      </section>

      {{-- ===== Selected day panel ===== --}}
      <aside aria-label="Selected day" class="w-full lg:w-[360px] shrink-0 flex flex-col gap-4">

        <div class="flex items-center gap-3.5 px-5 py-4 bg-[#123524] rounded-2xl sm:rounded-3xl text-white">
          <div class="flex flex-col items-center justify-center size-[60px] rounded-2xl bg-[#D4A537] text-[#1A1405] shrink-0">
            <span class="text-2xl font-extrabold leading-none">{{ $selected->format('d') }}</span>
            <span class="text-[10px] font-bold tracking-widest uppercase">{{ $selected->format('M') }}</span>
          </div>
          <div class="flex flex-col gap-1">
            <p class="text-lg font-bold leading-tight">{{ $selected->format('l') }}</p>
            <p class="text-[13px] text-[#CFE0D5]">
              @if($isMine)
                {{ $this->myRequests->count() ? $this->myRequests->count().' '.\Illuminate\Support\Str::plural('request', $this->myRequests->count()) : 'No requests' }}
              @else
                {{ $this->bookedCount ? $this->bookedCount.' '.\Illuminate\Support\Str::plural('booking', $this->bookedCount).' · other times free' : 'All facilities free' }}
              @endif
            </p>
          </div>
        </div>

        {{-- My requests on the selected day --}}
        @if($isMine)
          <div class="flex flex-col gap-3">
            @forelse($this->myRequests as $req)
              <a href="{{ route('portal.view-material-reservation', $req['id']) }}" wire:key="req-{{ $req['id'] }}"
                 class="flex flex-col gap-2 p-4 bg-white border border-[#E6EAE6] rounded-2xl hover:border-[#B9C6BD] hover:bg-[#FAFCFA] transition">
                <span class="flex flex-wrap gap-1.5">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wide bg-[#EEF1EE] text-[#33403A]">
                    {{ $req['isFacility'] ? 'Facility' : 'Material' }}
                  </span>
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold uppercase tracking-wide {{ $statusChip[$req['status']] ?? '' }}">
                    {{ $req['status'] }}
                  </span>
                </span>

                <span class="text-base font-bold text-[#1F2A24]">{{ $req['title'] }}</span>

                @if($req['time'])
                  <span class="inline-flex items-center gap-1.5 text-[13px] text-[#4A564F]">
                    <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                    {{ $req['time'] }}
                  </span>
                @endif

                @if($req['materials'])
                  <span class="text-[13px] text-[#4A564F]">With: {{ $req['materials'] }}</span>
                @endif

                @if($req['purpose'])
                  <span class="text-[13px] leading-relaxed text-[#5C6862]">{{ \Illuminate\Support\Str::limit($req['purpose'], 100) }}</span>
                @endif

                @if($req['reason'])
                  <span class="px-3 py-2.5 rounded-xl bg-[#FCE9E7] text-[#A3271B] text-[13px] leading-snug">
                    <strong>Reason:</strong> {{ $req['reason'] }}
                  </span>
                @endif
              </a>
            @empty
              <div class="flex flex-col items-center gap-2.5 px-5 py-9 bg-white border border-dashed border-[#CBD3CC] rounded-2xl text-center">
                <svg class="size-8 text-[#8A968F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <p class="text-sm font-semibold text-[#33403A]">No requests on this day</p>
                <p class="text-[13px] text-[#5C6862]">Pick another date, or check which rooms are free.</p>
                <button type="button" wire:click="setTab('availability')"
                  class="min-h-11 px-4 rounded-xl border border-[#D9DED9] bg-white text-sm font-semibold hover:bg-[#EEF1EE] transition">
                  See facility availability
                </button>
              </div>
            @endforelse
          </div>

        {{-- Room schedule (timeline) --}}
        @else
          <div class="flex flex-col gap-3.5 p-5 bg-white border border-[#E1E6E1] rounded-2xl sm:rounded-3xl">
            <div class="flex items-baseline justify-between gap-2">
              <h3 class="text-base font-bold text-[#123524]">Room schedule</h3>
              <span class="text-xs text-[#5C6862]">7 AM – 7 PM</span>
            </div>

            <div class="flex justify-between ms-[112px] text-[10.5px] font-medium text-[#5C6862]">
              <span>7a</span><span>9a</span><span>11a</span><span>1p</span><span>3p</span><span>5p</span><span>7p</span>
            </div>

            @forelse($this->roomSchedule as $room)
              <div class="flex items-center gap-3" wire:key="room-{{ md5($room['name']) }}">
                <span class="w-[100px] shrink-0 text-[12.5px] font-semibold leading-tight text-[#33403A]">{{ $room['name'] }}</span>
                <div class="relative flex-1 h-[34px] rounded-lg bg-[#F4F6F3]"
                     style="background-image: repeating-linear-gradient(90deg, transparent 0, transparent calc(100%/12 - 1px), #E6EAE6 calc(100%/12 - 1px), #E6EAE6 calc(100%/12));"
                     role="img"
                     aria-label="{{ $room['name'] }}: {{ count($room['bars']) ? 'booked '.collect($room['bars'])->pluck('label')->implode(', ') : 'free all day' }}">
                  @foreach($room['bars'] as $bar)
                    <span class="absolute top-1 bottom-1 flex items-center px-2 rounded-md bg-[#D4A537] text-[#1A1405] text-[11px] font-semibold whitespace-nowrap overflow-hidden"
                          style="left: {{ $bar['left'] }}%; width: {{ $bar['width'] }}%;"
                          title="{{ $bar['label'] }}">
                      {{ $bar['label'] }}
                    </span>
                  @endforeach
                </div>
              </div>
            @empty
              <p class="text-[13px] text-[#5C6862]">No facilities have been added yet.</p>
            @endforelse

            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-[#EEF1EE]">
              <span class="inline-flex items-center gap-2 text-[12.5px] text-[#4A564F]">
                <span class="w-3.5 h-2.5 rounded-sm bg-[#D4A537]"></span>Booked
                <span class="w-3.5 h-2.5 rounded-sm bg-[#F4F6F3] border border-[#E6EAE6] ms-2"></span>Free
              </span>
              <a href="{{ route('portal.create-reservation') }}"
                 class="inline-flex items-center min-h-11 px-4 rounded-xl bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418] transition">
                Reserve a free slot
              </a>
            </div>
          </div>
        @endif

      </aside>
    </div>
  </div>
</div>
