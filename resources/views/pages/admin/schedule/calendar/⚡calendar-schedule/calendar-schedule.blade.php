<div class="select-none">
  <div class="max-w-7xl mx-auto px-3 sm:px-4 md:px-6 lg:px-8 py-4 sm:py-6 md:py-8">

    <div class="flex flex-col lg:flex-row gap-4 md:gap-6">

      {{-- LEFT: Calendar --}}
      <div class="w-full lg:w-[340px] xl:w-[380px] shrink-0">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 dark:border-neutral-700 overflow-hidden">

          {{-- Calendar Header --}}
          <div class="flex items-center justify-between px-4 sm:px-6 py-4 sm:py-5 bg-gradient-to-br from-teal-50 to-white dark:from-neutral-900 dark:to-neutral-800 border-b border-gray-100 dark:border-neutral-700">
            <button wire:click="previousMonth"
              class="p-1.5 sm:p-2 rounded-full bg-white dark:bg-neutral-700 shadow-sm hover:shadow transition text-gray-500 dark:text-neutral-300">
              <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <h2 class="text-sm sm:text-base font-bold text-gray-800 dark:text-neutral-100 tracking-tight">
              {{ \Carbon\Carbon::create($year, $month, 1)->format('F Y') }}
            </h2>
            <button wire:click="nextMonth"
              class="p-1.5 sm:p-2 rounded-full bg-white dark:bg-neutral-700 shadow-sm hover:shadow transition text-gray-500 dark:text-neutral-300">
              <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

          {{-- Request Type Tabs --}}
          <div class="flex gap-1.5 sm:gap-2 px-3 sm:px-4 pt-3 sm:pt-4">
            <button wire:click="$set('activeTab', 'reservation')"
              class="flex-1 py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold rounded-full transition
                {{ $activeTab === 'reservation'
                  ? 'bg-green-500 text-white shadow-sm'
                  : 'bg-gray-100 dark:bg-neutral-700 text-gray-500 dark:text-neutral-400 hover:bg-gray-200 dark:hover:bg-neutral-600' }}">
              FACILITY
            </button>
            <button wire:click="$set('activeTab', 'material')"
              class="flex-1 py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold rounded-full transition
                {{ $activeTab === 'material'
                  ? 'bg-purple-500 text-white shadow-sm'
                  : 'bg-gray-100 dark:bg-neutral-700 text-gray-500 dark:text-neutral-400 hover:bg-gray-200 dark:hover:bg-neutral-600' }}">
              MATERIAL
            </button>
          </div>

          {{-- Day Headers --}}
          <div class="grid grid-cols-7 px-2 sm:px-4 pt-3 sm:pt-4">
            @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $d)
              <div class="text-center text-[8px] sm:text-[10px] font-bold text-gray-300 dark:text-neutral-600 py-1 tracking-widest">
                {{ $d }}
              </div>
            @endforeach
          </div>

          {{-- Calendar Days --}}
          <div class="grid grid-cols-7 px-2 sm:px-4 pb-4 sm:pb-5 gap-y-0.5 sm:gap-y-1">
            @foreach($this->calendarDays as $day)
              @php
                $dateKey    = $day ? \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d') : null;
                $isToday    = $dateKey === now()->format('Y-m-d');
                $isSelected = $dateKey === $selectedDate;
                $events     = $day ? ($this->daysWithEvents[$dateKey] ?? null) : null;
              @endphp

              <div class="flex flex-col items-center py-0.5">
                @if($day)
                  <button wire:click="selectDate('{{ $dateKey }}')"
                    title="{{ $this->relativeDayLabel($dateKey) }}"
                    class="relative w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 flex items-center justify-center text-[11px] sm:text-sm rounded-xl sm:rounded-2xl transition font-semibold
                      {{ $isSelected
                        ? 'bg-teal-500 text-white shadow-md shadow-teal-200 dark:shadow-none scale-105'
                        : ($isToday
                          ? 'bg-teal-50 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300 ring-1 ring-teal-200 dark:ring-teal-800'
                          : 'text-gray-600 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700') }}">
                    {{ $day }}
                  </button>
                  @if($events)
                    <div class="flex gap-0.5 mt-0.5 sm:mt-1">
                      @if($events['reservation'] ?? false)
                        <span class="size-1 sm:size-1.5 rounded-full bg-green-500 inline-block"></span>
                      @endif
                      @if($events['material'] ?? false)
                        <span class="size-1 sm:size-1.5 rounded-full bg-purple-500 inline-block"></span>
                      @endif
                    </div>
                  @endif
                @endif
              </div>
            @endforeach
          </div>

          {{-- Legend --}}
          <div class="mx-2 sm:mx-4 mb-4 sm:mb-5 px-3 sm:px-4 py-2 sm:py-3 bg-gray-50 dark:bg-neutral-900/50 rounded-xl sm:rounded-2xl flex flex-wrap items-center justify-center sm:justify-start gap-2 sm:gap-4 text-[10px] sm:text-[11px] font-medium text-gray-500 dark:text-neutral-400">
            <div class="flex items-center gap-1 sm:gap-1.5">
              <span class="size-1.5 sm:size-2 rounded-full bg-green-500 inline-block"></span>
              Facility
            </div>
            <div class="flex items-center gap-1 sm:gap-1.5">
              <span class="size-1.5 sm:size-2 rounded-full bg-purple-500 inline-block"></span>
              Material
            </div>
          </div>

        </div>
      </div>

      {{-- RIGHT: Events Panel --}}
      <div class="flex-1 min-w-0">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 dark:border-neutral-700 overflow-hidden h-full">

          {{-- Panel Header --}}
          <div class="flex items-center justify-between px-4 sm:px-6 md:px-7 py-4 sm:py-5 bg-gradient-to-r from-gray-50 to-white dark:from-neutral-900 dark:to-neutral-800 border-b border-gray-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 sm:gap-4">
              <div class="flex flex-col items-center justify-center bg-teal-500 text-white rounded-xl sm:rounded-2xl size-11 sm:size-12 md:size-14 shadow-sm shrink-0">
                <span class="text-base sm:text-lg font-extrabold leading-none">
                  {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('d') : '--' }}
                </span>
                <span class="text-[7px] sm:text-[8px] md:text-[9px] font-semibold uppercase tracking-wide opacity-80">
                  {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('M') : '' }}
                </span>
              </div>
              <div>
                <p class="text-xs sm:text-sm font-bold text-gray-800 dark:text-neutral-100">
                  {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('l') : '' }}
                </p>
                <p class="text-[10px] sm:text-xs text-gray-400 dark:text-neutral-500">
                  {{ $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('F Y') : '' }}
                </p>
              </div>
            </div>

            @php
              $relLabel = $this->relativeDayLabel($selectedDate);
              $relClass = match (true) {
                $relLabel === 'Today'             => 'bg-teal-500 text-white',
                $relLabel === 'Tomorrow',
                str_starts_with($relLabel, 'In ') => 'bg-blue-50 text-blue-700 ring-1 ring-blue-200 dark:bg-blue-900/30 dark:text-blue-300 dark:ring-blue-800',
                default                           => 'bg-gray-100 text-gray-600 ring-1 ring-gray-200 dark:bg-neutral-700 dark:text-neutral-300 dark:ring-neutral-600',
              };
            @endphp
            <div class="flex items-center gap-2 shrink-0">
              @if($relLabel)
                <span class="inline-flex items-center py-1 px-2.5 sm:px-3 text-[10px] sm:text-xs font-bold rounded-full {{ $relClass }}">
                  {{ $relLabel }}
                </span>
              @endif
              @if($relLabel !== 'Today')
                <button wire:click="goToday"
                  class="inline-flex items-center py-1 px-2.5 sm:px-3 text-[10px] sm:text-xs font-semibold rounded-full border border-teal-200 text-teal-700 hover:bg-teal-50 dark:border-teal-800 dark:text-teal-300 dark:hover:bg-teal-900/30 transition">
                  Go to Today
                </button>
              @endif
            </div>
          </div>

          {{-- Events List --}}
          <div class="overflow-y-auto max-h-[400px] sm:max-h-[500px] md:max-h-[600px] p-2 sm:p-3 space-y-1.5 sm:space-y-2">

            @php
              $events       = $this->selectedDateEvents;
              $reservations = $events['reservations'] ?? collect();
              $materials    = $events['materials'] ?? collect();
            @endphp

            {{-- ✅ FACILITY: room list — click a room to see who reserved it + print --}}
            @if($activeTab === 'reservation')
              @php
                $rooms     = $this->roomSummary;
                $dateLabel = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') : '';
              @endphp

              @if(count($rooms) > 0)
                <div x-data="{ open: null }" wire:key="rooms-{{ $selectedDate }}" class="space-y-1.5 sm:space-y-2">
                  @foreach($rooms as $i => $room)
                    @php
                      $roomDeptCounts = collect($room['slots'])->countBy('department')->sortKeys();
                    @endphp

                    <div x-data="{ dept: 'all', room: @js($room) }"
                      class="rounded-xl border border-gray-100 dark:border-neutral-700 overflow-hidden">

                      {{-- Room row --}}
                      <button type="button"
                        @click="open = (open === {{ $i }}) ? null : {{ $i }}"
                        class="w-full flex items-center justify-between gap-3 px-3 sm:px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition"
                        :class="open === {{ $i }} ? 'bg-green-50 dark:bg-green-900/20' : ''">
                        <div class="flex items-center gap-2.5 min-w-0">
                          <span class="size-2 rounded-full bg-green-500 shrink-0"></span>
                          <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-neutral-100 truncate">
                            {{ $room['room'] }}
                          </span>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                          <span class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400">
                            {{ count($room['slots']) }} {{ Str::plural('reservation', count($room['slots'])) }}
                          </span>
                          <svg class="size-3.5 text-gray-400 transition-transform" :class="open === {{ $i }} ? 'rotate-90' : ''"
                            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                          </svg>
                        </div>
                      </button>

                      {{-- Inside the room --}}
                      <div x-show="open === {{ $i }}" x-cloak x-transition.opacity
                        class="border-t border-gray-100 dark:border-neutral-700">

                        {{-- 🖨 Print bar (this room only) --}}
                        <div class="flex flex-wrap items-center gap-2 px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-neutral-900/50 border-b border-gray-100 dark:border-neutral-700">
                          <select x-model="dept"
                            class="flex-1 min-w-0 py-1.5 px-2.5 text-xs sm:text-sm rounded-lg border border-gray-200 bg-white text-gray-700 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200 focus:border-teal-500 focus:ring-teal-500">
                            <option value="all">All departments ({{ $roomDeptCounts->sum() }})</option>
                            @foreach($roomDeptCounts as $deptName => $count)
                              <option value="{{ $deptName }}">{{ $deptName }} ({{ $count }})</option>
                            @endforeach
                          </select>

                          <button type="button"
                            @click="printRoomSchedule([room], dept, @js($dateLabel))"
                            class="inline-flex items-center gap-1.5 py-1.5 px-3 text-xs sm:text-sm font-semibold rounded-lg bg-teal-500 text-white hover:bg-teal-600 transition">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                            </svg>
                            Print
                          </button>
                        </div>

                        {{-- Reservations of this room --}}
                        <div class="divide-y divide-gray-100 dark:divide-neutral-700">
                          @foreach($room['slots'] as $slot)
                            <div class="flex items-start gap-3 px-3 sm:px-4 py-2.5">
                              <span class="w-32 sm:w-36 shrink-0 text-[11px] sm:text-xs font-bold text-gray-700 dark:text-neutral-200">
                                {{ $slot['start'] && $slot['end'] ? $slot['start'] . ' – ' . $slot['end'] : 'No time' }}
                              </span>
                              <div class="min-w-0">
                                <p class="text-[11px] sm:text-xs font-semibold text-gray-800 dark:text-neutral-100">
                                  {{ $slot['department'] }}
                                  <span class="font-normal text-gray-400 dark:text-neutral-500">· {{ $slot['user'] }}</span>
                                </p>
                                @if($slot['purpose'])
                                  <p class="text-[10px] sm:text-xs text-gray-400 dark:text-neutral-500 italic truncate">
                                    "{{ Str::limit($slot['purpose'], 60) }}"
                                  </p>
                                @endif
                              </div>
                            </div>
                          @endforeach
                        </div>

                      </div>
                    </div>
                  @endforeach
                </div>
              @elseif($materials->isNotEmpty())
                <div class="px-4 sm:px-6 py-10 sm:py-14 text-center">
                  <p class="text-xs sm:text-sm text-gray-400 dark:text-neutral-500">No facility reservations on this day.</p>
                </div>
              @endif
            @endif

            {{-- Material Requests --}}
            @if($activeTab === 'material')
              @forelse($materials as $material)
                <div wire:key="mat-{{ $material->id }}" class="flex gap-3 sm:gap-4 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-100 dark:border-neutral-700 hover:border-purple-200 dark:hover:border-purple-800 hover:bg-purple-50/40 dark:hover:bg-purple-900/10 transition">

                  <div class="w-1 rounded-full bg-purple-400 dark:bg-purple-500 shrink-0"></div>

                  <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-2">
                      <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-1 sm:mb-1.5">
                          <span class="inline-flex items-center py-0.5 px-2 sm:px-2.5 text-[8px] sm:text-[10px] font-bold bg-purple-100 text-purple-700 rounded-full dark:bg-purple-900 dark:text-purple-300">
                            MATERIAL
                          </span>
                          <span class="inline-flex items-center py-0.5 px-2 sm:px-2.5 text-[8px] sm:text-[10px] font-medium bg-gray-100 text-gray-600 rounded-full dark:bg-neutral-700 dark:text-neutral-300">
                            {{ $material->user->department->department_name ?? 'N/A' }}
                          </span>
                        </div>
                        <ul class="text-xs sm:text-sm text-gray-700 dark:text-neutral-300 space-y-0.5 sm:space-y-1">
                          @foreach($material->items as $item)
                            <li class="truncate flex items-center gap-1 sm:gap-1.5">
                              <span class="size-0.5 sm:size-1 rounded-full bg-gray-300 dark:bg-neutral-600 shrink-0"></span>
                              {{ $item->item_name }} <span class="text-gray-400">(x{{ $item->quantity }})</span>
                            </li>
                          @endforeach
                        </ul>
                        <p class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400 mt-0.5 sm:mt-1.5 flex items-center gap-1">
                          <svg class="size-2.5 sm:size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                          </svg>
                          {{ $material->user->name }}
                        </p>
                        @if($material->purpose)
                          <p class="text-[10px] sm:text-xs text-gray-400 dark:text-neutral-500 mt-0.5 sm:mt-1 italic">
                            "{{ Str::limit($material->purpose, 60) }}"
                          </p>
                        @endif
                      </div>
                      <div class="shrink-0 text-left sm:text-right">
                        <p class="text-[10px] sm:text-xs font-bold text-gray-500 dark:text-neutral-400 bg-gray-50 dark:bg-neutral-700 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg">
                          {{ \Carbon\Carbon::parse($material->created_at)->format('g:i A') }}
                        </p>
                      </div>
                    </div>
                  </div>

                </div>
              @empty
                @if($reservations->isNotEmpty())
                  <div class="px-4 sm:px-6 py-10 sm:py-14 text-center">
                    <p class="text-xs sm:text-sm text-gray-400 dark:text-neutral-500">No material requests on this day.</p>
                  </div>
                @endif
              @endforelse
            @endif

            {{-- Both empty --}}
            @if($reservations->isEmpty() && $materials->isEmpty())
              <div class="flex flex-col items-center justify-center py-14 sm:py-20 px-4 sm:px-6 text-center">
                <div class="size-12 sm:size-16 rounded-2xl sm:rounded-3xl bg-gray-50 dark:bg-neutral-700 flex items-center justify-center mb-3 sm:mb-4">
                  <svg class="size-6 sm:size-8 text-gray-300 dark:text-neutral-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                  </svg>
                </div>
                <p class="text-xs sm:text-sm font-semibold text-gray-500 dark:text-neutral-400">Nothing on this day</p>
                <p class="text-[10px] sm:text-xs text-gray-400 dark:text-neutral-500 mt-0.5 sm:mt-1">Select another date to see events</p>
              </div>
            @endif

          </div>
        </div>
      </div>

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
