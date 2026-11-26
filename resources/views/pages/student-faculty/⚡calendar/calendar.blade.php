@php
  $isFacility = $activeTab === 'facility';
  $selected   = \Carbon\Carbon::parse($selectedDate);
  $today      = now()->format('Y-m-d');
  $rooms      = $this->rooms;
  $materials  = $this->materials;
  $bookings   = collect($rooms)->sum(fn ($r) => count($r['slots']));
@endphp

<div class="min-h-screen bg-[#F4F6F3] text-[#1F2A24]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8 flex flex-col gap-6">

    {{-- ===== Header ===== --}}
    <div class="flex flex-wrap items-end justify-between gap-4">
      <div class="flex flex-col gap-1.5">
        <p class="text-xs font-semibold tracking-widest uppercase text-[#8A6A1F]">Faculty Portal</p>
        <h1 class="text-2xl sm:text-3xl font-extrabold text-[#123524]">Calendar</h1>
        <p class="text-sm text-[#4A564F]">See which rooms are booked and what materials are requested — every department.</p>
      </div>

      <div class="flex flex-wrap items-center gap-3">
        {{-- Facility / Material --}}
        <div class="inline-flex p-1 bg-[#E6EAE6] rounded-2xl gap-1" role="tablist" aria-label="Request type">
          @foreach(['facility' => 'Facility', 'material' => 'Material'] as $key => $label)
            <button type="button" role="tab" wire:click="setTab('{{ $key }}')" aria-selected="{{ $activeTab === $key ? 'true' : 'false' }}"
              class="min-h-10 px-4 rounded-xl text-sm font-semibold transition inline-flex items-center gap-2
                {{ $activeTab === $key ? 'bg-white text-[#123524] shadow-sm' : 'text-[#4A564F] hover:text-[#123524]' }}">
              <span class="size-2 rounded-full {{ $key === 'facility' ? 'bg-[#1C7A4A]' : 'bg-[#B8862A]' }}"></span>
              {{ $label }}
            </button>
          @endforeach
        </div>

        {{-- Department --}}
        <label class="sr-only" for="cal-department">Department</label>
        <select id="cal-department" wire:model.live="department"
          class="w-full sm:w-auto min-h-11 ps-3 pe-9 rounded-xl border border-[#D9DED9] bg-white text-sm font-medium text-[#1F2A24] focus:border-[#123524] focus:ring-[#123524]">
          <option value="">All departments</option>
          @foreach($this->departments as $id => $name)
            <option value="{{ $id }}">{{ $name }}{{ (int) $id === (int) $this->myDepartmentId ? ' (yours)' : '' }}</option>
          @endforeach
        </select>

        {{-- New reservation (faculty) --}}
        <a href="{{ route('portal.create-reservation') }}" wire:navigate
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
                $count   = $isFacility ? $cell['facility'] : $cell['material'];
                $noun    = $isFacility ? 'reservation' : 'request';
              @endphp
              <button type="button" wire:click="selectDate('{{ $cell['date'] }}')" wire:key="cell-{{ $cell['date'] }}"
                aria-pressed="{{ $isSel ? 'true' : 'false' }}"
                aria-label="{{ \Carbon\Carbon::parse($cell['date'])->format('F j') }}{{ $count ? ', ' . $count . ' ' . \Illuminate\Support\Str::plural($noun, $count) : '' }}"
                class="relative flex flex-col items-stretch gap-1 min-h-16 sm:min-h-28 p-1 sm:p-2 min-w-0 text-left border-r border-b border-[#E6EAE6] transition
                  focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#123524]
                  {{ $isSel ? 'bg-[#EEF4EF] ring-2 ring-inset ring-[#123524]' : 'bg-white hover:bg-[#F7F9F6]' }}">

                <span class="flex items-center justify-between">
                  <span class="inline-flex items-center justify-center size-7 rounded-full text-[13px] font-semibold
                    {{ $isToday ? 'bg-[#D4A537] text-[#1A1405]' : 'text-[#1F2A24]' }}">
                    {{ $cell['day'] }}
                  </span>
                  {{-- Phone: a dot --}}
                  @if($count)
                    <span class="sm:hidden size-1.5 me-0.5 rounded-full {{ $isFacility ? 'bg-[#1C7A4A]' : 'bg-[#B8862A]' }}"></span>
                  @endif
                </span>

                {{-- Tablet / desktop: label --}}
                @if($count)
                  <span class="hidden sm:block truncate px-1.5 py-0.5 rounded-md text-[11.5px] font-medium
                    {{ $isFacility ? 'bg-[#E3F1E8] text-[#145A37]' : 'bg-[#F3E9CC] text-[#6B5216]' }}">
                    {{ $count }} {{ \Illuminate\Support\Str::plural($noun, $count) }}
                  </span>
                @endif
              </button>
            @endif
          @endforeach
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-4 px-4 sm:px-5 py-3.5 border-t border-[#E6EAE6] text-[12.5px] text-[#4A564F]">
          @if($isFacility)
            <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#1C7A4A]"></span>Approved facility reservations</span>
          @else
            <span class="inline-flex items-center gap-1.5"><span class="size-1.5 rounded-full bg-[#B8862A]"></span>Approved material requests</span>
          @endif
          <span class="text-[#8A968F]">·</span>
          <span>{{ $department !== '' ? ($this->departments[(int) $department] ?? '') : 'All departments' }}</span>
        </div>
      </section>

      {{-- ===== Selected day panel ===== --}}
      <aside aria-label="Selected day" class="w-full lg:w-[380px] shrink-0 flex flex-col gap-4">

        <div class="flex items-center gap-3.5 px-5 py-4 bg-[#123524] rounded-2xl sm:rounded-3xl text-white">
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

        @if($isFacility)
          {{-- ===== Rooms booked that day ===== --}}
          <div x-data="{ open: null }" wire:key="rooms-{{ $selectedDate }}-{{ $department }}" class="flex flex-col gap-3">
            @forelse($rooms as $i => $room)
              @php
                $roomDeptCounts = collect($room['slots'])->countBy('department')->sortKeys();
                $dateLabel      = $selected->format('l, F j, Y');
              @endphp
              <div x-data="{ dept: 'all', room: @js($room) }" class="bg-white border border-[#E6EAE6] rounded-2xl overflow-hidden">
                <button type="button" @click="open = (open === {{ $i }}) ? null : {{ $i }}"
                  class="w-full flex items-center justify-between gap-3 px-4 py-3.5 text-left hover:bg-[#FAFCFA] transition"
                  :class="open === {{ $i }} ? 'bg-[#EEF4EF]' : ''">
                  <span class="min-w-0">
                    <span class="block text-[15px] font-bold text-[#1F2A24] truncate">{{ $room['room'] }}</span>
                    <span class="block text-[12.5px] text-[#5C6862]">
                      {{ count($room['slots']) }} {{ \Illuminate\Support\Str::plural('booking', count($room['slots'])) }}
                      · {{ collect($room['slots'])->pluck('department')->unique()->count() }} {{ \Illuminate\Support\Str::plural('department', collect($room['slots'])->pluck('department')->unique()->count()) }}
                    </span>
                  </span>
                  <svg class="size-4 shrink-0 text-[#8A968F] transition-transform" :class="open === {{ $i }} ? 'rotate-90' : ''"
                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                </button>

                <div x-show="open === {{ $i }}" x-cloak x-transition.opacity class="border-t border-[#EEF1EE] divide-y divide-[#EEF1EE]">

                  {{-- 🖨 Print bar (this room only) — same as the Admin calendar --}}
                  <div class="flex flex-wrap items-center gap-2 px-4 py-2.5 bg-[#FAFBFA]">
                    <label class="sr-only" for="print-dept-{{ $i }}">Department to print</label>
                    <select id="print-dept-{{ $i }}" x-model="dept"
                      class="flex-1 min-w-0 py-1.5 px-2.5 text-[13px] rounded-lg border border-[#D9DED9] bg-white text-[#1F2A24] focus:border-[#123524] focus:ring-[#123524]">
                      <option value="all">All departments ({{ $roomDeptCounts->sum() }})</option>
                      @foreach($roomDeptCounts as $deptName => $count)
                        <option value="{{ $deptName }}">{{ $deptName }} ({{ $count }})</option>
                      @endforeach
                    </select>
                    <button type="button" @click="printRoomSchedule([room], dept, @js($dateLabel))"
                      class="inline-flex items-center gap-1.5 py-1.5 px-3 text-[13px] font-semibold rounded-lg bg-[#123524] text-white hover:bg-[#0B2418] transition">
                      <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                      </svg>
                      Print
                    </button>
                  </div>

                  @foreach($room['slots'] as $slot)
                    <div class="flex gap-3 px-4 py-3">
                      <span class="w-[92px] shrink-0 text-[12.5px] font-bold text-[#33403A] leading-snug">
                        @if($slot['start'] && $slot['end'])
                          {{ $slot['start'] }}<br><span class="font-normal text-[#5C6862]">to {{ $slot['end'] }}</span>
                        @else
                          No time
                        @endif
                      </span>
                      <span class="flex flex-col gap-1 min-w-0">
                        <span class="flex flex-wrap items-center gap-1.5">
                          <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#E3F1E8] text-[#145A37]">{{ $slot['department'] }}</span>
                          @if($slot['mine'])
                            <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#F3E9CC] text-[#6B5216]">Your dept</span>
                          @endif
                        </span>
                        <span class="text-[13px] text-[#1F2A24]">{{ $slot['user'] }}</span>
                        @if($slot['extras'])
                          <span class="text-[12.5px] text-[#4A564F]">With: {{ $slot['extras'] }}</span>
                        @endif
                        @if($slot['purpose'])
                          <span class="text-[12.5px] text-[#5C6862] italic">"{{ \Illuminate\Support\Str::limit($slot['purpose'], 80) }}"</span>
                        @endif
                      </span>
                    </div>
                  @endforeach
                </div>
              </div>
            @empty
              <div class="flex flex-col items-center gap-2.5 px-5 py-9 bg-white border border-dashed border-[#CBD3CC] rounded-2xl text-center">
                <svg class="size-8 text-[#8A968F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                <p class="text-sm font-semibold text-[#33403A]">No facility reservations on this day</p>
                <p class="text-[13px] text-[#5C6862]">Pick another date{{ count($materials) ? ', or check the Material tab' : '' }}.</p>
              </div>
            @endforelse
          </div>

        @else
          {{-- ===== Material requests that day ===== --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-1 gap-3">
            @forelse($materials as $m)
              <div wire:key="mat-{{ $m['id'] }}" class="flex flex-col gap-2 min-w-0 p-4 bg-white border border-[#E6EAE6] rounded-2xl break-words">
                <span class="flex flex-wrap items-center gap-1.5">
                  <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-[#F3E9CC] text-[#6B5216]">{{ $m['department'] }}</span>
                  @if($m['mine'])
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-[#E3F1E8] text-[#145A37]">Your dept</span>
                  @endif
                </span>
                <span class="text-[15px] font-bold text-[#1F2A24] truncate">{{ $m['user'] }}</span>
                <span class="flex flex-wrap gap-1">
                  @foreach($m['items'] as $it)
                    <span class="max-w-full px-2 py-0.5 rounded-md text-[12px] bg-[#EEF1EE] text-[#33403A] break-words">{{ $it }}</span>
                  @endforeach
                </span>
                @if($m['purpose'])
                  <span class="text-[13px] leading-relaxed text-[#5C6862] italic">"{{ \Illuminate\Support\Str::limit($m['purpose'], 100) }}"</span>
                @endif
              </div>
            @empty
              <div class="sm:col-span-2 lg:col-span-1 flex flex-col items-center gap-2.5 px-5 py-9 bg-white border border-dashed border-[#CBD3CC] rounded-2xl text-center">
                <svg class="size-8 text-[#8A968F]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" aria-hidden="true"><path d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <p class="text-sm font-semibold text-[#33403A]">No material requests on this day</p>
                <p class="text-[13px] text-[#5C6862]">Pick another date{{ $bookings ? ', or check the Facility tab' : '' }}.</p>
              </div>
            @endforelse
          </div>
        @endif

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
