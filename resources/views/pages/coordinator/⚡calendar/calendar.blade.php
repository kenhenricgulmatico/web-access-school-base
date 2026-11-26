@php
  $isFacility = $activeTab === 'facility';
  $today      = now()->format('Y-m-d');
  $days       = $this->monthDays;
  $cells      = $this->calendarCells;
@endphp

{{-- ✅ tab + sel live in the browser, so Facility / Material and picking a day are instant.
     They're sent back with the next request (month / department change) so nothing resets. --}}
<div x-data="{ tab: $wire.entangle('activeTab'), sel: $wire.entangle('selectedDate') }"
     class="min-h-screen bg-[#F4F6F3] dark:bg-[#0E1A14] text-[#1F2A24] dark:text-neutral-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex flex-col gap-6">

    {{-- ===== Header ===== --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div class="flex flex-col gap-1.5">
        <p class="text-xs font-semibold tracking-widest uppercase text-[#8A6A1F] dark:text-[#E8C877]">Program Head Portal</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123524] dark:text-green-200">Calendar</h1>
        <p class="text-sm text-[#4A564F] dark:text-neutral-400">Approved facility reservations of every department and your department's material requests.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        {{-- Facility / Material --}}
        <div class="inline-flex p-1 bg-[#E6EAE6] dark:bg-[#0E1A14] rounded-2xl gap-1" role="tablist" aria-label="Request type">
          @foreach(['facility' => 'Facility', 'material' => 'Material'] as $key => $label)
            <button type="button" role="tab" @click="tab = '{{ $key }}'"
              aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}" :aria-selected="tab === '{{ $key }}'"
              class="min-h-10 px-4 rounded-xl text-sm font-semibold transition inline-flex items-center gap-2
                {{ $activeTab === $key ? 'bg-white dark:bg-[#16281F] text-[#123524] dark:text-green-200 shadow-sm' : 'text-[#4A564F] dark:text-neutral-400 hover:text-[#123524] dark:hover:text-green-200' }}"
              :class="{ 'bg-white dark:bg-[#16281F] text-[#123524] dark:text-green-200 shadow-sm': tab === '{{ $key }}', 'text-[#4A564F] dark:text-neutral-400 hover:text-[#123524] dark:hover:text-green-200': tab !== '{{ $key }}' }">
              <span class="size-2 rounded-full {{ $key === 'facility' ? 'bg-[#1C7A4A]' : 'bg-[#B8862A]' }}"></span>
              {{ $label }}
            </button>
          @endforeach
        </div>

        {{-- Department --}}
        {{-- Facility: pick any department · Material: always your own department --}}
        <label class="sr-only" for="cal-department">Department</label>
        <select id="cal-department" wire:model.live="department"
          x-show="tab === 'facility'" @if(! $isFacility) style="display: none" @endif
          class="w-full sm:w-auto min-h-11 ps-3 pe-9 rounded-xl border border-[#D9DED9] dark:border-[#2A4B3A] bg-white dark:bg-[#16281F] text-sm font-medium text-[#1F2A24] dark:text-neutral-100 focus:border-[#123524] dark:focus:border-[#7FBF8E] focus:ring-[#123524] dark:focus:ring-[#7FBF8E]">
          <option value="">All departments</option>
          @foreach($this->departments as $id => $name)
            <option value="{{ $id }}">{{ $name }}{{ (int) $id === (int) $this->myDepartmentId ? ' (yours)' : '' }}</option>
          @endforeach
        </select>
        <span x-show="tab === 'material'" @if($isFacility) style="display: none" @endif
          class="inline-flex items-center gap-2 min-h-11 px-4 rounded-xl border border-[#D9DED9] dark:border-[#2A4B3A] bg-white dark:bg-[#16281F] text-sm font-medium text-[#1F2A24] dark:text-neutral-100">
          <span class="size-2 rounded-full bg-[#B8862A]"></span>
          {{ $this->departments[(int) $this->myDepartmentId] ?? 'No department' }} <span class="text-[#8A968F] dark:text-neutral-500 font-normal">(your department)</span>
        </span>

      </div>
    </div>

    {{-- Fades while a new month / department is loading --}}
    <div class="flex flex-col lg:flex-row gap-6 items-start transition-opacity"
         wire:loading.class="opacity-50 pointer-events-none" wire:target="previousMonth, nextMonth, goToToday, department">

      {{-- ===== Month grid (compact, same size as the Admin calendar) ===== --}}
      <section aria-label="Month view" class="w-full lg:w-[340px] xl:w-[380px] shrink-0 bg-white dark:bg-[#16281F] border border-[#E1E6E1] dark:border-[#2A4B3A] rounded-2xl sm:rounded-3xl overflow-hidden">

        <div class="flex items-center justify-between gap-2 px-4 sm:px-5 py-3.5 border-b border-[#E6EAE6] dark:border-[#2A4B3A]">
          <button type="button" wire:click="previousMonth" aria-label="Previous month"
            class="size-9 inline-flex items-center justify-center rounded-full border border-[#D9DED9] dark:border-[#2A4B3A] bg-white dark:bg-[#16281F] hover:bg-[#EEF1EE] dark:hover:bg-[#1F3A2C] transition">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
          </button>
          <div class="flex flex-col items-center">
            <h2 class="text-sm sm:text-base font-bold text-[#123524] dark:text-green-200">
              {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </h2>
            <button type="button" wire:click="goToToday" class="text-[11px] font-semibold text-[#8A6A1F] dark:text-[#E8C877] hover:underline">
              Today
            </button>
          </div>
          <button type="button" wire:click="nextMonth" aria-label="Next month"
            @disabled(! $this->canGoNext) title="{{ $this->canGoNext ? 'Next month' : 'Bookings only go up to ' . \Carbon\Carbon::parse(\App\Support\BookingDate::max())->format('M d, Y') }}"
            class="disabled:opacity-40 disabled:cursor-not-allowed size-9 inline-flex items-center justify-center rounded-full border border-[#D9DED9] dark:border-[#2A4B3A] bg-white dark:bg-[#16281F] hover:bg-[#EEF1EE] dark:hover:bg-[#1F3A2C] transition">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 18l6-6-6-6"/></svg>
          </button>
        </div>

        {{-- Weekdays --}}
        <div class="grid grid-cols-7 px-2 sm:px-4 pt-3">
          @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $wd)
            <div class="text-center py-1 text-[10px] font-bold tracking-widest text-[#8A968F] dark:text-neutral-500">{{ $wd }}</div>
          @endforeach
        </div>

        {{-- Days --}}
        <div class="grid grid-cols-7 px-2 sm:px-4 pb-3 gap-y-0.5">
          @foreach($cells as $cell)
            @if(! $cell)
              <div aria-hidden="true"></div>
            @else
              @php
                $isSel   = $cell['date'] === $selectedDate;
                $isToday = $cell['date'] === $today;
                $label   = \Carbon\Carbon::parse($cell['date'])->format('F j')
                         . ($cell['facility'] ? ', ' . $cell['facility'] . ' ' . \Illuminate\Support\Str::plural('reservation', $cell['facility']) : '')
                         . ($cell['material'] ? ', ' . $cell['material'] . ' material ' . \Illuminate\Support\Str::plural('request', $cell['material']) : '');
                $selOn   = 'bg-[#123524] dark:bg-[#1C6B45] text-white shadow-sm';
                $selOff  = $isToday ? 'bg-[#F3E9CC] dark:bg-[#D4A537]/20 text-[#6B5216] dark:text-[#E8C877] ring-1 ring-[#D4A537]' : 'text-[#1F2A24] dark:text-neutral-100 hover:bg-[#EEF1EE] dark:hover:bg-[#1F3A2C]';
              @endphp
              <div class="flex flex-col items-center py-0.5">
                <button type="button" @click="sel = '{{ $cell['date'] }}'" wire:key="cell-{{ $cell['date'] }}"
                  aria-pressed="{{ $isSel ? 'true' : 'false' }}" :aria-pressed="sel === '{{ $cell['date'] }}'"
                  aria-label="{{ $label }}" title="{{ $label }}"
                  class="size-8 sm:size-9 inline-flex items-center justify-center rounded-xl text-[13px] font-semibold transition
                    focus:outline-none focus-visible:ring-2 focus-visible:ring-[#123524] dark:focus-visible:ring-[#7FBF8E]
                    {{ $isSel ? $selOn : $selOff }}"
                  :class="{ '{{ $selOn }}': sel === '{{ $cell['date'] }}', '{{ $selOff }}': sel !== '{{ $cell['date'] }}' }">
                  {{ $cell['day'] }}
                </button>
                {{-- Dot for days that have something (for the open tab) --}}
                <span class="h-1.5 mt-0.5 flex items-center">
                  @if($cell['facility'])
                    <span x-show="tab === 'facility'" @if(! $isFacility) style="display: none" @endif class="size-1.5 rounded-full bg-[#1C7A4A]"></span>
                  @endif
                  @if($cell['material'])
                    <span x-show="tab === 'material'" @if($isFacility) style="display: none" @endif class="size-1.5 rounded-full bg-[#B8862A]"></span>
                  @endif
                </span>
              </div>
            @endif
          @endforeach
        </div>

        {{-- Legend --}}
        <div class="mx-2 sm:mx-4 mb-4 px-3 py-2 bg-[#FAFBFA] dark:bg-[#0E1A14]/60 rounded-xl flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] font-medium text-[#4A564F] dark:text-neutral-400">
          <span x-show="tab === 'facility'" @if(! $isFacility) style="display: none" @endif class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#1C7A4A]"></span>Approved facility reservations</span>
          <span x-show="tab === 'material'" @if($isFacility) style="display: none" @endif class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#B8862A]"></span>Approved material requests</span>
          <span class="text-[#8A968F] dark:text-neutral-500">·</span>
          <span x-show="tab === 'facility'" @if(! $isFacility) style="display: none" @endif>{{ $department !== '' ? ($this->departments[(int) $department] ?? '') : 'All departments' }}</span>
          <span x-show="tab === 'material'" @if($isFacility) style="display: none" @endif>{{ $this->departments[(int) $this->myDepartmentId] ?? 'No department' }}</span>
        </div>
      </section>

      {{-- ===== Selected day panel (every day of the month is ready; only the picked one shows) ===== --}}
      <aside aria-label="Selected day" class="w-full lg:flex-1 min-w-0">
        @foreach($cells as $cell)
          @continue(! $cell)
          @php
            $date      = $cell['date'];
            $selected  = \Carbon\Carbon::parse($date);
            $rooms     = $days[$date]['rooms'] ?? [];
            $materials = $days[$date]['materials'] ?? [];
            $bookings  = $cell['facility'];
          @endphp

          <div x-show="sel === '{{ $date }}'" @if($date !== $selectedDate) style="display: none" @endif
               wire:key="day-{{ $date }}-{{ $department }}" class="flex flex-col gap-4">

            <div class="flex items-center gap-3.5 px-5 py-4 bg-[#123524] dark:bg-[#1C6B45] rounded-2xl sm:rounded-3xl text-white">
              <div class="flex flex-col items-center justify-center size-[60px] rounded-2xl bg-[#D4A537] text-[#1A1405] shrink-0">
                <span class="text-2xl font-extrabold leading-none">{{ $selected->format('d') }}</span>
                <span class="text-[10px] font-bold tracking-widest uppercase">{{ $selected->format('M') }}</span>
              </div>
              <div class="flex flex-col gap-1">
                <p class="text-lg font-bold leading-tight">{{ $selected->format('l') }}</p>
                <p class="text-[13px] text-[#CFE0D5]">
                  {{ $bookings }} {{ \Illuminate\Support\Str::plural('reservation', $bookings) }}
                  · {{ count($materials) }} material {{ \Illuminate\Support\Str::plural('request', count($materials)) }}
                </p>
              </div>
            </div>

            {{-- ===== Rooms booked that day ===== --}}
            <div x-show="tab === 'facility'" @if(! $isFacility) style="display: none" @endif
                 x-data="{ open: null }" class="flex flex-col gap-3 overflow-y-auto overscroll-contain max-h-[400px] sm:max-h-[500px] md:max-h-[600px] pe-1">
              @forelse($rooms as $i => $room)
                @php
                  $roomDeptCounts = collect($room['slots'])->countBy('department')->sortKeys();
                  $dateLabel      = $selected->format('l, F j, Y');
                  $deptCount      = collect($room['slots'])->pluck('department')->unique()->count();
                @endphp
                <div x-data="{ dept: 'all', room: @js($room) }" class="bg-white dark:bg-[#16281F] border border-[#E6EAE6] dark:border-[#2A4B3A] rounded-2xl overflow-hidden">
                  <button type="button" @click="open = (open === {{ $i }}) ? null : {{ $i }}"
                    class="w-full flex items-center justify-between gap-3 px-4 py-3.5 text-left hover:bg-[#FAFCFA] dark:hover:bg-[#1F3A2C] transition"
                    :class="open === {{ $i }} ? 'bg-[#EEF4EF] dark:bg-[#1F3A2C]' : ''">
                    <span class="min-w-0">
                      <span class="block text-[15px] font-bold text-[#1F2A24] dark:text-neutral-100 truncate">{{ $room['room'] }}</span>
                      <span class="block text-[12.5px] text-[#5C6862] dark:text-neutral-400">
                        {{ count($room['slots']) }} {{ \Illuminate\Support\Str::plural('booking', count($room['slots'])) }}
                        · {{ $deptCount }} {{ \Illuminate\Support\Str::plural('department', $deptCount) }}
                      </span>
                    </span>
                    <svg class="size-4 shrink-0 text-[#8A968F] dark:text-neutral-500 transition-transform" :class="open === {{ $i }} ? 'rotate-90' : ''"
                      viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                  </button>

                  <div x-show="open === {{ $i }}" x-cloak x-transition.opacity class="border-t border-[#EEF1EE] dark:border-[#2A4B3A] divide-y divide-[#EEF1EE] dark:divide-[#2A4B3A]">

                    {{-- 🖨 Print bar (this room only) — same as the Admin calendar --}}
                    <div class="flex flex-wrap items-center gap-2 px-4 py-2.5 bg-[#FAFBFA] dark:bg-[#0E1A14]/60">
                      <label class="sr-only" for="print-dept-{{ $date }}-{{ $i }}">Department to print</label>
                      <select id="print-dept-{{ $date }}-{{ $i }}" x-model="dept"
                        class="flex-1 min-w-0 py-1.5 px-2.5 text-[13px] rounded-lg border border-[#D9DED9] dark:border-[#2A4B3A] bg-white dark:bg-[#16281F] text-[#1F2A24] dark:text-neutral-100 focus:border-[#123524] dark:focus:border-[#7FBF8E] focus:ring-[#123524] dark:focus:ring-[#7FBF8E]">
                        <option value="all">All departments ({{ $roomDeptCounts->sum() }})</option>
                        @foreach($roomDeptCounts as $deptName => $count)
                          <option value="{{ $deptName }}">{{ $deptName }} ({{ $count }})</option>
                        @endforeach
                      </select>
                      <button type="button" @click="printRoomSchedule([room], dept, @js($dateLabel))"
                        class="inline-flex items-center gap-1.5 py-1.5 px-3 text-[13px] font-semibold rounded-lg bg-[#123524] dark:bg-[#1C6B45] text-white hover:bg-[#0B2418] dark:hover:bg-[#15573A] transition">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                          <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                        </svg>
                        Print
                      </button>
                    </div>

                    @foreach($room['slots'] as $slot)
                      <div class="flex gap-3 px-4 py-3">
                        <span class="w-[92px] shrink-0 text-[12.5px] font-bold text-[#33403A] dark:text-neutral-200 leading-snug">
                          @if($slot['start'] && $slot['end'])
                            {{ $slot['start'] }}<br><span class="font-normal text-[#5C6862] dark:text-neutral-400">to {{ $slot['end'] }}</span>
                          @else
                            No time
                          @endif
                        </span>
                        <span class="flex flex-col gap-1 min-w-0">
                          <span class="flex flex-wrap items-center gap-1.5">
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#E3F1E8] dark:bg-[#1C6B45]/25 text-[#145A37] dark:text-green-300">{{ $slot['department'] }}</span>
                            @if($slot['mine'])
                              <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#F3E9CC] dark:bg-[#D4A537]/20 text-[#6B5216] dark:text-[#E8C877]">Your dept</span>
                            @endif
                          </span>
                          <span class="text-[13px] text-[#1F2A24] dark:text-neutral-100">{{ $slot['user'] }}</span>
                          @if($slot['extras'])
                            <span class="text-[12.5px] text-[#4A564F] dark:text-neutral-400">With: {{ $slot['extras'] }}</span>
                          @endif
                          @if($slot['purpose'])
                            <span class="text-[12.5px] text-[#5C6862] dark:text-neutral-400 italic">"{{ \Illuminate\Support\Str::limit($slot['purpose'], 80) }}"</span>
                          @endif
                        </span>
                      </div>
                    @endforeach
                  </div>
                </div>
              @empty
                <div class="flex flex-col items-center gap-2.5 px-5 py-9 bg-white dark:bg-[#16281F] border border-dashed border-[#CBD3CC] dark:border-[#2A4B3A] rounded-2xl text-center">
                  <svg class="size-8 text-[#8A968F] dark:text-neutral-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                  <p class="text-sm font-semibold text-[#33403A] dark:text-neutral-200">No facility reservations on this day</p>
                  <p class="text-[13px] text-[#5C6862] dark:text-neutral-400">Pick another date{{ count($materials) ? ', or check the Material tab' : '' }}.</p>
                </div>
              @endforelse
            </div>

            {{-- ===== Material requests that day: 2 across, 2 rows tall, then it scrolls ===== --}}
            {{-- Every card is 160px tall → 2 rows = 2×160 + 12 (gap) = 332px --}}
            <div x-show="tab === 'material'" @if($isFacility) style="display: none" @endif
                 class="grid grid-cols-1 sm:grid-cols-2 auto-rows-[160px] gap-3 content-start overflow-y-auto overscroll-contain max-h-[332px] pe-1">
              @forelse($materials as $m)
                <div wire:key="mat-{{ $date }}-{{ $m['id'] }}" class="flex flex-col gap-1.5 min-w-0 min-h-0 p-3 bg-white dark:bg-[#16281F] border border-[#E6EAE6] dark:border-[#2A4B3A] rounded-2xl break-words">
                  <span class="flex flex-wrap items-center gap-1">
                    <span class="max-w-full truncate px-2 py-0.5 rounded-full text-[10.5px] font-semibold bg-[#F3E9CC] dark:bg-[#D4A537]/20 text-[#6B5216] dark:text-[#E8C877]" title="{{ $m['department'] }}">{{ $m['department'] }}</span>
                  </span>
                  <span class="text-[13.5px] font-bold text-[#1F2A24] dark:text-neutral-100 truncate" title="{{ $m['user'] }}">{{ $m['user'] }}</span>
                  {{-- Items: scroll inside the card if there are many --}}
                  <span class="flex flex-wrap content-start gap-1 flex-1 min-h-0 overflow-y-auto">
                    @foreach($m['items'] as $it)
                      <span class="max-w-full px-1.5 py-0.5 rounded-md text-[11.5px] bg-[#EEF1EE] dark:bg-[#0E1A14] text-[#33403A] dark:text-neutral-200 break-words">{{ $it }}</span>
                    @endforeach
                  </span>
                  @if($m['purpose'])
                    <span class="text-[11.5px] text-[#5C6862] dark:text-neutral-400 italic truncate" title="{{ $m['purpose'] }}">"{{ $m['purpose'] }}"</span>
                  @endif
                </div>
              @empty
                <div class="col-span-full flex flex-col items-center justify-center gap-2 px-5 bg-white dark:bg-[#16281F] border border-dashed border-[#CBD3CC] dark:border-[#2A4B3A] rounded-2xl text-center">
                  <svg class="size-8 text-[#8A968F] dark:text-neutral-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                  <p class="text-sm font-semibold text-[#33403A] dark:text-neutral-200">No material requests on this day</p>
                  <p class="text-[13px] text-[#5C6862] dark:text-neutral-400">Pick another date{{ $bookings ? ', or check the Facility tab' : '' }}.</p>
                </div>
              @endforelse
            </div>
          </div>
        @endforeach
      </aside>
    </div>
  </div>
</div>

@script
<script>
  /**
   * 🖨 Print a room's reservations for the selected day.
   * dept = 'all' → every department, otherwise only that department.
   */
  window.printRoomSchedule = function (rooms, dept, dateLabel) {
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    }[c]));

    const list = rooms
      .map((r) => ({
        room:  r.room,
        slots: r.slots.filter((s) => dept === 'all' || s.department === dept),
      }))
      .filter((r) => r.slots.length > 0);

    const total = list.reduce((n, r) => n + r.slots.length, 0);

    if (total === 0) {
      alert('Nothing to print for this department.');
      return;
    }

    const roomName  = list.length === 1 ? list[0].room : 'All rooms';
    const deptLabel = dept === 'all' ? 'All departments' : dept;

    const tables = list.map((r) => `
      <table>
        <thead>
          <tr><th style="width:6%">#</th><th style="width:24%">Time</th><th style="width:24%">Department</th><th style="width:18%">Reserved by</th><th>Purpose</th></tr>
        </thead>
        <tbody>
          ${r.slots.map((s, n) => `
            <tr>
              <td>${n + 1}</td>
              <td>${s.start && s.end ? esc(s.start + ' – ' + s.end) : 'No time'}</td>
              <td>${esc(s.department)}</td>
              <td>${esc(s.user)}</td>
              <td>${esc(s.purpose || '—')}</td>
            </tr>`).join('')}
        </tbody>
      </table>`).join('');

    const w = window.open('', '_blank', 'width=900,height=700');
    if (!w) {
      alert('Please allow pop-ups for this site to print.');
      return;
    }

    w.document.write(`<!doctype html>
      <html>
      <head>
        <meta charset="utf-8">
        <title>${esc(roomName)} - ${esc(dateLabel)}</title>
        <style>
          * { box-sizing: border-box; }
          body { font-family: Arial, Helvetica, sans-serif; color: #111; margin: 32px; font-size: 13px; }
          h1 { font-size: 20px; margin: 0 0 4px; }
          .meta { color: #555; margin: 0 0 18px; line-height: 1.7; }
          .meta b { color: #111; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; vertical-align: top; }
          th { background: #f2f2f2; font-size: 12px; }
          .foot { margin-top: 28px; color: #888; font-size: 11px; }
          @page { margin: 14mm; }
        </style>
      </head>
      <body>
        <h1>${esc(roomName)}</h1>
        <p class="meta">
          <b>Date:</b> ${esc(dateLabel)}<br>
          <b>Department:</b> ${esc(deptLabel)}<br>
          <b>Total:</b> ${total} reservation${total > 1 ? 's' : ''}
        </p>
        ${tables}
        <p class="foot">Printed on ${esc(new Date().toLocaleString())}</p>
      </body>
      </html>`);
    w.document.close();
    w.focus();

    w.onafterprint = () => w.close();
    setTimeout(() => w.print(), 300);
  };
</script>
@endscript
