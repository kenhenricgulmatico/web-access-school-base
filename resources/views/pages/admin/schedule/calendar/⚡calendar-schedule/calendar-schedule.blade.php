<div>
  <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

    {{-- ✅ @container: side-by-side only when THIS area is wide enough (the sidebar takes 240px) --}}
    <div class="@container">
    <div class="flex flex-col @4xl:flex-row gap-4 md:gap-6">

      {{-- LEFT: Calendar --}}
      <div class="w-full @4xl:w-[340px] @6xl:w-[380px] shrink-0 space-y-3">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 dark:border-neutral-700 overflow-hidden">

          {{-- Calendar Header: ‹ [Month ▾] [Year ▾] › --}}
          <div class="flex items-center justify-between gap-2 px-3 sm:px-5 py-4 bg-gradient-to-br from-[#F3F8F5] to-white dark:from-neutral-900 dark:to-neutral-800 border-b border-gray-100 dark:border-neutral-700">
            <button wire:click="previousMonth" aria-label="Previous month"
              class="p-1.5 sm:p-2 rounded-full bg-white dark:bg-neutral-700 shadow-sm hover:shadow transition text-gray-500 dark:text-neutral-300">
              <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <div class="flex items-center gap-1.5">
              <label for="cal-month" class="sr-only">Month</label>
              <select id="cal-month" wire:model.live="month"
                class="py-1 ps-2 pe-7 text-sm font-bold rounded-lg border-transparent bg-transparent text-gray-800 hover:bg-white focus:ring-[#1C6B45] dark:text-neutral-100 dark:hover:bg-neutral-700">
                @foreach (range(1, 12) as $m)
                  <option value="{{ $m }}" @selected($m === $month)>{{ \Carbon\Carbon::create(2000, $m, 1)->format('F') }}</option>
                @endforeach
              </select>
              <label for="cal-year" class="sr-only">Year</label>
              <select id="cal-year" wire:model.live="year"
                class="py-1 ps-2 pe-7 text-sm font-bold rounded-lg border-transparent bg-transparent text-gray-800 hover:bg-white focus:ring-[#1C6B45] dark:text-neutral-100 dark:hover:bg-neutral-700">
                @foreach ($this->years() as $y)
                  <option value="{{ $y }}" @selected($y === $year)>{{ $y }}</option>
                @endforeach
              </select>
            </div>
            <button wire:click="nextMonth" aria-label="Next month"
              class="p-1.5 sm:p-2 rounded-full bg-white dark:bg-neutral-700 shadow-sm hover:shadow transition text-gray-500 dark:text-neutral-300">
              <svg class="size-3.5 sm:size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>

          {{-- Tabs: Facility / Material / Returns --}}
          <div class="grid grid-cols-3 gap-1.5 sm:gap-2 px-3 sm:px-4 pt-3 sm:pt-4">
            @foreach (['reservation' => ['FACILITY', 'bg-[#123524]'], 'material' => ['MATERIAL', 'bg-[#2F8A5B]'], 'returns' => ['RETURNS', 'bg-red-500']] as $tab => [$label, $on])
              <button wire:click="$set('activeTab', '{{ $tab }}')"
                class="relative py-1.5 sm:py-2 text-[10px] sm:text-xs font-bold rounded-full transition
                  {{ $activeTab === $tab ? $on . ' text-white shadow-sm' : 'bg-gray-100 dark:bg-neutral-700 text-gray-500 dark:text-neutral-400 hover:bg-gray-200 dark:hover:bg-neutral-600' }}">
                {{ $label }}
                @if ($tab === 'returns' && $this->overdueCount > 0)
                  <span class="absolute -top-1.5 -right-1 min-w-4 h-4 px-1 inline-flex items-center justify-center rounded-full bg-red-600 text-white text-[9px] ring-2 ring-white dark:ring-neutral-800"
                    title="{{ $this->overdueCount }} overdue">{{ $this->overdueCount }}</span>
                @endif
              </button>
            @endforeach
          </div>

          {{-- Filters --}}
          <div class="flex flex-wrap items-center gap-2 px-3 sm:px-4 pt-3">
            @if ($activeTab === 'reservation')
              <label for="cal-room" class="sr-only">Room</label>
              <select id="cal-room" wire:model.live="roomFilter"
                class="flex-1 min-w-0 py-1.5 px-2.5 text-xs rounded-lg border border-gray-200 bg-white text-gray-700 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 focus:border-[#1C6B45] focus:ring-[#1C6B45]">
                <option value="">All rooms</option>
                @foreach ($this->rooms as $r)
                  <option value="{{ $r->resource_name }}" @selected($r->resource_name === $roomFilter)>{{ $r->resource_name }}{{ $r->status === 'maintenance' ? ' (maintenance)' : '' }}</option>
                @endforeach
              </select>
            @endif
            @if ($activeTab !== 'returns')
              <label class="inline-flex items-center gap-1.5 text-xs text-gray-600 dark:text-neutral-300 cursor-pointer">
                <input type="checkbox" wire:model.live="showPending" @checked($showPending) class="size-3.5 rounded border-gray-300 text-amber-500 focus:ring-amber-400">
                Show pending
              </label>
            @endif
          </div>

          {{-- Day Headers --}}
          <div class="grid grid-cols-7 px-2 sm:px-4 pt-3 sm:pt-4">
            @foreach(['S', 'M', 'T', 'W', 'T', 'F', 'S'] as $d)
              <div class="text-center text-[8px] sm:text-[10px] font-bold text-gray-300 dark:text-neutral-600 py-1 tracking-widest">
                {{ $d }}
              </div>
            @endforeach
          </div>

          {{-- Calendar Days: the number of things on each day (for the open tab) --}}
          <div class="grid grid-cols-7 px-2 sm:px-4 pb-4 sm:pb-5 gap-y-0.5 sm:gap-y-1">
            @foreach($this->calendarDays as $day)
              @php
                $dateKey    = $day ? \Carbon\Carbon::create($year, $month, $day)->format('Y-m-d') : null;
                $isToday    = $dateKey === now()->format('Y-m-d');
                $isSelected = $dateKey === $selectedDate;
                $c          = $day ? ($this->dayCounts[$dateKey] ?? null) : null;
                [$n, $p, $tone] = match ($activeTab) {
                    'material' => [$c['materials'] ?? 0, $c['materialsPending'] ?? 0, 'text-[#2F8A5B] dark:text-[#7FBF8E]'],
                    'returns'  => [$c['due'] ?? 0, 0, ($c['overdue'] ?? false) ? 'text-red-600 dark:text-red-400' : 'text-amber-600 dark:text-amber-300'],
                    default    => [$c['rooms'] ?? 0, $c['roomsPending'] ?? 0, 'text-[#1C6B45] dark:text-green-400'],
                };
                $tip = collect([
                    $n ? $n . ' ' . match ($activeTab) { 'material' => 'material ' . \Illuminate\Support\Str::plural('request', $n), 'returns' => 'due back', default => \Illuminate\Support\Str::plural('booking', $n) } : null,
                    $p ? $p . ' pending' : null,
                ])->filter()->implode(' · ');
              @endphp

              <div class="flex flex-col items-center py-0.5">
                @if($day)
                  <button wire:click="selectDate('{{ $dateKey }}')"
                    title="{{ trim($this->relativeDayLabel($dateKey) . ($tip ? ' — ' . $tip : '')) }}"
                    class="relative w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 flex items-center justify-center text-[11px] sm:text-sm rounded-xl sm:rounded-2xl transition font-semibold
                      {{ $isSelected
                        ? 'bg-[#1C6B45] text-white shadow-md shadow-[#1C6B45/25] dark:shadow-none scale-105'
                        : ($isToday
                          ? 'bg-[#F3F8F5] text-[#1C6B45] dark:bg-[#1C6B45]/40 dark:text-[#9FD3AE] ring-1 ring-[#1C6B45/25] dark:ring-[#1C6B45/40]'
                          : 'text-gray-600 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700') }}">
                    {{ $day }}
                  </button>
                  <div class="h-3 mt-0.5 flex items-center gap-0.5 text-[9px] font-bold leading-none">
                    @if($n)
                      <span class="{{ $tone }}">{{ $n }}</span>
                    @endif
                    @if($p)
                      <span class="text-amber-600 dark:text-amber-400" aria-label="{{ $p }} pending">+{{ $p }}</span>
                    @endif
                  </div>
                @endif
              </div>
            @endforeach
          </div>

          {{-- Legend --}}
          <div class="mx-2 sm:mx-4 mb-4 sm:mb-5 px-3 sm:px-4 py-2 sm:py-3 bg-gray-50 dark:bg-neutral-900/50 rounded-xl sm:rounded-2xl flex flex-wrap items-center gap-x-4 gap-y-1 text-[10px] sm:text-[11px] font-medium text-gray-500 dark:text-neutral-400">
            @if ($activeTab === 'returns')
              <span><b class="text-amber-600 dark:text-amber-300">2</b> due back that day</span>
              <span><b class="text-red-600 dark:text-red-400">2</b> already overdue</span>
            @else
              <span><b class="{{ $activeTab === 'material' ? 'text-[#2F8A5B] dark:text-[#7FBF8E]' : 'text-[#1C6B45] dark:text-green-400' }}">3</b> approved</span>
              @if ($showPending)
                <span><b class="text-amber-600 dark:text-amber-400">+1</b> pending (waiting for you)</span>
              @endif
            @endif
          </div>

        </div>

        @if ($showPending && $this->pendingThisMonth > 0 && $activeTab !== 'returns')
          <a href="{{ route('admin.manage-coordinator') }}"
            class="flex items-center justify-between gap-2 px-4 py-3 rounded-2xl border border-amber-200 bg-amber-50 text-sm text-amber-800 hover:bg-amber-100 dark:bg-amber-950/30 dark:border-amber-900 dark:text-amber-300">
            <span><b>{{ $this->pendingThisMonth }}</b> pending in {{ \Carbon\Carbon::create($year, $month, 1)->format('F') }} waiting for your decision</span>
            <span class="font-semibold">Review →</span>
          </a>
        @endif
      </div>

      {{-- RIGHT: Events Panel --}}
      <div class="flex-1 min-w-0">
        <div class="bg-white dark:bg-neutral-800 rounded-2xl sm:rounded-3xl shadow-sm border border-gray-100 dark:border-neutral-700 overflow-hidden h-full">

          {{-- Panel Header --}}
          <div class="flex flex-wrap items-center justify-between gap-3 px-4 sm:px-6 md:px-7 py-4 sm:py-5 bg-gradient-to-r from-gray-50 to-white dark:from-neutral-900 dark:to-neutral-800 border-b border-gray-100 dark:border-neutral-700">
            <div class="flex items-center gap-3 sm:gap-4">
              <div class="flex flex-col items-center justify-center bg-[#1C6B45] text-white rounded-xl sm:rounded-2xl size-11 sm:size-12 md:size-14 shadow-sm shrink-0">
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
                $relLabel === 'Today'             => 'bg-[#1C6B45] text-white',
                $relLabel === 'Tomorrow',
                str_starts_with($relLabel, 'In ') => 'bg-[#F3F8F5] text-[#1C6B45] ring-1 ring-[#1C6B45/25] dark:bg-[#1C6B45]/30 dark:text-[#9FD3AE] dark:ring-[#1C6B45/40]',
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
                  class="inline-flex items-center py-1 px-2.5 sm:px-3 text-[10px] sm:text-xs font-semibold rounded-full border border-[#1C6B45/25] text-[#1C6B45] hover:bg-[#F3F8F5] dark:border-[#1C6B45/40] dark:text-[#9FD3AE] dark:hover:bg-[#1C6B45]/30 transition">
                  Go to Today
                </button>
              @endif
            </div>
          </div>

          {{-- Events List --}}
          <div class="overflow-y-auto max-h-[480px] sm:max-h-[560px] md:max-h-[640px] p-2 sm:p-3 space-y-1.5 sm:space-y-2">

            {{-- ============ FACILITY: every room, booked or free ============ --}}
            @if($activeTab === 'reservation')
              @php
                $rooms     = $this->roomDay;
                $marks     = $this->hourMarks();
                $dateLabel = $selectedDate ? \Carbon\Carbon::parse($selectedDate)->format('l, F j, Y') : '';
                $booked    = collect($rooms)->where('busy', true)->count();
                // ✅ Only booked rooms get a full row; free rooms become small chips below
                //    (one room picked in the filter → always its full row)
                $fullRows  = $roomFilter !== '' ? $rooms : array_values(array_filter($rooms, fn ($r) => $r['busy']));
                $freeRooms = $roomFilter !== '' ? [] : array_values(array_filter($rooms, fn ($r) => ! $r['busy']));
                $chipLimit = 12;
              @endphp

              {{-- Search (shown when there are many rooms) --}}
              @if ($this->rooms->count() > 8 || $roomSearch !== '')
                <div class="relative px-1 pt-1">
                  <label for="cal-room-search" class="sr-only">Find a room</label>
                  <svg class="absolute left-3.5 top-1/2 mt-0.5 -translate-y-1/2 size-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                  <input id="cal-room-search" type="text" wire:model.live.debounce.300ms="roomSearch" placeholder="Find a room…"
                    class="w-full py-1.5 ps-8 pe-3 text-xs sm:text-sm rounded-lg border border-gray-200 bg-white text-gray-700 dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-200 focus:border-[#1C6B45] focus:ring-[#1C6B45]">
                </div>
              @endif

              <p class="px-2 pt-1 text-[11px] sm:text-xs text-gray-500 dark:text-neutral-400">
                <b class="text-gray-700 dark:text-neutral-200">{{ $booked }}</b> of {{ count($rooms) }} {{ \Illuminate\Support\Str::plural('room', count($rooms)) }} booked
                · times shown 7 AM – 8 PM
              </p>

              @if (empty($rooms))
                <p class="px-2 py-6 text-center text-xs text-gray-500 dark:text-neutral-400">No room matches "{{ $roomSearch }}".</p>
              @elseif (empty($fullRows))
                <p class="px-2 py-4 text-center text-xs sm:text-sm font-semibold text-gray-500 dark:text-neutral-400">No room is booked on this day.</p>
              @endif

              <div x-data="{ open: null }" wire:key="rooms-{{ $selectedDate }}-{{ $roomFilter }}-{{ (int) $showPending }}-{{ md5($roomSearch) }}" class="space-y-1.5 sm:space-y-2">
                @foreach($fullRows as $i => $room)
                  @php
                    $approvedSlots  = array_values(array_filter($room['slots'], fn ($x) => $x['status'] === 'approved'));
                    $pendingN       = count($room['slots']) - count($approvedSlots);
                    $printable      = ['room' => $room['room'], 'slots' => $approvedSlots];
                    $roomDeptCounts = collect($approvedSlots)->countBy('department')->sortKeys();
                    $hasClash       = collect($room['slots'])->contains('clash', true);
                  @endphp

                  <div x-data="{ dept: 'all', room: @js($printable) }" wire:key="room-{{ $i }}-{{ md5($room['room']) }}"
                    class="rounded-xl border overflow-hidden {{ $hasClash ? 'border-red-300 dark:border-red-800' : 'border-gray-100 dark:border-neutral-700' }}">

                    {{-- Room row: name, counts, time bar --}}
                    <button type="button"
                      @click="open = (open === {{ $i }}) ? null : {{ $i }}"
                      class="w-full px-3 sm:px-4 py-3 text-left hover:bg-gray-50 dark:hover:bg-neutral-700/50 transition"
                      :class="open === {{ $i }} ? 'bg-green-50 dark:bg-[#1C6B45]/20' : ''">
                      <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2 min-w-0">
                          <span class="size-2 rounded-full shrink-0 {{ $room['status'] === 'maintenance' ? 'bg-amber-500' : ($room['busy'] ? 'bg-[#1C6B45]' : 'bg-gray-300 dark:bg-neutral-600') }}"></span>
                          <span class="text-xs sm:text-sm font-bold text-gray-800 dark:text-neutral-100 truncate">{{ $room['room'] }}</span>
                          @if ($room['status'] === 'maintenance')
                            <span class="shrink-0 px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300" title="{{ $room['note'] }}">MAINTENANCE{{ $room['note'] ? ' · ' . $room['note'] : '' }}</span>
                          @elseif ($room['status'] === 'removed')
                            <span class="shrink-0 px-1.5 py-0.5 text-[9px] font-bold rounded bg-gray-100 text-gray-600 dark:bg-neutral-700 dark:text-neutral-300">NOT A ROOM ANYMORE</span>
                          @endif
                          @if ($hasClash)
                            <span class="shrink-0 px-1.5 py-0.5 text-[9px] font-bold rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300">CLASH</span>
                          @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0 text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400">
                          @if ($room['busy'])
                            <span>{{ count($approvedSlots) }} booked</span>
                            @if ($pendingN)
                              <span class="text-amber-600 dark:text-amber-400">+{{ $pendingN }} pending</span>
                            @endif
                          @else
                            <span class="text-[#1C6B45] dark:text-green-400 font-semibold">Free all day</span>
                          @endif
                          <svg class="size-3.5 text-gray-400 transition-transform" :class="open === {{ $i }} ? 'rotate-90' : ''"
                            fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                          </svg>
                        </div>
                      </div>

                      {{-- ✅ 7 AM – 8 PM bar --}}
                      <div class="relative mt-2 h-2.5 rounded-full bg-gray-100 dark:bg-neutral-700 overflow-hidden" aria-hidden="true">
                        @foreach ($room['slots'] as $slot)
                          <span class="absolute inset-y-0 rounded-full
                            {{ $slot['clash'] ? 'bg-red-500' : ($slot['status'] === 'pending' ? 'bg-amber-300 dark:bg-amber-500/70' : 'bg-[#1C6B45]') }}"
                            style="left: {{ $slot['left'] }}%; width: {{ $slot['width'] }}%"></span>
                        @endforeach
                      </div>
                      <div class="relative h-3 mt-0.5 text-[8px] text-gray-400 dark:text-neutral-500" aria-hidden="true">
                        @foreach ($marks as $mk)
                          <span class="absolute -translate-x-1/2 first:translate-x-0 last:-translate-x-full" style="left: {{ $mk['left'] }}%">{{ $mk['label'] }}</span>
                        @endforeach
                      </div>
                      @if ($room['busy'] && $room['free'])
                        <p class="mt-1 text-[10px] sm:text-[11px] text-[#1C6B45] dark:text-green-400 truncate">Free: {{ implode(', ', $room['free']) }}</p>
                      @elseif ($room['busy'])
                        <p class="mt-1 text-[10px] sm:text-[11px] text-red-600 dark:text-red-400">Fully booked</p>
                      @endif
                    </button>

                    {{-- Inside the room --}}
                    <div x-show="open === {{ $i }}" x-cloak x-transition.opacity
                      class="border-t border-gray-100 dark:border-neutral-700">

                      @if (count($approvedSlots))
                        {{-- 🖨 Print bar (approved bookings of this room) --}}
                        <div class="flex flex-wrap items-center gap-2 px-3 sm:px-4 py-2.5 bg-gray-50 dark:bg-neutral-900/50 border-b border-gray-100 dark:border-neutral-700">
                          <select x-model="dept" aria-label="Department to print"
                            class="flex-1 min-w-0 py-1.5 px-2.5 text-xs sm:text-sm rounded-lg border border-gray-200 bg-white text-gray-700 dark:bg-neutral-800 dark:border-neutral-600 dark:text-neutral-200 focus:border-[#1C6B45] focus:ring-[#1C6B45]">
                            <option value="all">All departments ({{ $roomDeptCounts->sum() }})</option>
                            @foreach($roomDeptCounts as $deptName => $count)
                              <option value="{{ $deptName }}">{{ $deptName }} ({{ $count }})</option>
                            @endforeach
                          </select>

                          <button type="button"
                            @click="printRoomSchedule([room], dept, @js($dateLabel))"
                            class="inline-flex items-center gap-1.5 py-1.5 px-3 text-xs sm:text-sm font-semibold rounded-lg bg-[#1C6B45] text-white hover:bg-[#123524] transition">
                            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6z"/>
                            </svg>
                            Print
                          </button>
                        </div>
                      @endif

                      @if (count($room['slots']))
                        <div class="divide-y divide-gray-100 dark:divide-neutral-700">
                          @foreach($room['slots'] as $slot)
                            <div class="flex items-start gap-3 px-3 sm:px-4 py-2.5 {{ $slot['status'] === 'pending' ? 'bg-amber-50/50 dark:bg-amber-950/10' : '' }}">
                              <span class="w-28 sm:w-36 shrink-0 text-[11px] sm:text-xs font-bold text-gray-700 dark:text-neutral-200">
                                {{ $slot['start'] && $slot['end'] ? $slot['start'] . ' – ' . $slot['end'] : 'No time' }}
                              </span>
                              <div class="min-w-0 flex-1">
                                <p class="text-[11px] sm:text-xs font-semibold text-gray-800 dark:text-neutral-100">
                                  {{ $slot['department'] }}
                                  <span class="font-normal text-gray-400 dark:text-neutral-500">· {{ $slot['user'] }}</span>
                                  @if ($slot['status'] === 'pending')
                                    <span class="ms-1 px-1.5 py-0.5 text-[9px] font-bold rounded bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">PENDING</span>
                                  @endif
                                </p>
                                @if($slot['purpose'])
                                  <p class="text-[10px] sm:text-xs text-gray-400 dark:text-neutral-500 italic truncate">
                                    "{{ \Illuminate\Support\Str::limit($slot['purpose'], 60) }}"
                                  </p>
                                @endif
                                @if ($slot['clash'])
                                  <p class="text-[10px] sm:text-xs font-semibold text-red-600 dark:text-red-400">Overlaps an approved booking — approving it would double-book the room.</p>
                                @endif
                              </div>
                              <a href="{{ route('admin.view-request', $slot['request_id']) }}"
                                class="shrink-0 text-[11px] sm:text-xs font-semibold hover:underline {{ $slot['status'] === 'pending' ? 'text-amber-700 dark:text-amber-300' : 'text-[#1C6B45] dark:text-green-300' }}">
                                {{ $slot['status'] === 'pending' ? 'Review →' : 'View' }}
                              </a>
                            </div>
                          @endforeach
                        </div>
                      @else
                        <p class="px-4 py-3 text-xs text-gray-500 dark:text-neutral-400">
                          No bookings — free 7:00 AM – 8:00 PM{{ $room['status'] === 'maintenance' ? ', but the room is under maintenance' : '' }}.
                        </p>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>

              {{-- ✅ Free all day: compact chips (click one to see that room's month) --}}
              @if (count($freeRooms))
                <div class="mt-1 px-2 sm:px-3 py-3 rounded-xl bg-gray-50 dark:bg-neutral-900/50">
                  <p class="mb-2 text-[11px] sm:text-xs font-semibold text-[#1C6B45] dark:text-green-400">
                    Free all day · {{ count($freeRooms) }} {{ \Illuminate\Support\Str::plural('room', count($freeRooms)) }}
                  </p>
                  <div class="flex flex-wrap gap-1.5">
                    @foreach ($showAllFree ? $freeRooms : array_slice($freeRooms, 0, $chipLimit) as $free)
                      <button type="button" wire:click="$set('roomFilter', @js($free['room']))"
                        title="{{ $free['status'] === 'maintenance' ? 'Under maintenance' . ($free['note'] ? ': ' . $free['note'] : '') : 'Free 7:00 AM – 8:00 PM' }} · click to see this room's month"
                        class="px-2 py-1 text-[11px] font-medium rounded-full border transition
                        {{ $free['status'] === 'maintenance'
                          ? 'border-amber-300 bg-amber-50 text-amber-800 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-300'
                          : 'border-gray-200 bg-white text-gray-700 hover:border-green-400 hover:text-[#1C6B45] dark:border-neutral-700 dark:bg-neutral-800 dark:text-neutral-200' }}">
                        {{ $free['room'] }}{{ $free['status'] === 'maintenance' ? ' · maintenance' : '' }}
                      </button>
                    @endforeach
                    @if (count($freeRooms) > $chipLimit)
                      <button type="button" wire:click="$set('showAllFree', {{ $showAllFree ? 'false' : 'true' }})"
                        class="px-2 py-1 text-[11px] font-semibold rounded-full text-[#1C6B45] hover:bg-[#F3F8F5] dark:text-[#9FD3AE] dark:hover:bg-[#1C6B45]/30">
                        {{ $showAllFree ? 'Show less' : '+' . (count($freeRooms) - $chipLimit) . ' more' }}
                      </button>
                    @endif
                  </div>
                </div>
              @endif
            @endif

            {{-- ============ MATERIAL requests needed that day ============ --}}
            @if($activeTab === 'material')
              @forelse($this->selectedMaterials as $material)
                @php $isPending = $material->status === 'pending'; @endphp
                <div wire:key="mat-{{ $material->id }}" class="flex gap-3 sm:gap-4 p-3 sm:p-4 rounded-xl sm:rounded-2xl border transition
                  {{ $isPending ? 'border-amber-200 bg-amber-50/40 dark:border-amber-900 dark:bg-amber-950/10' : 'border-gray-100 dark:border-neutral-700 hover:border-[#1C6B45]/20 hover:bg-[#F3F8F5]/60 dark:hover:bg-[#1C6B45]/10' }}">

                  <div class="w-1 rounded-full shrink-0 {{ $isPending ? 'bg-amber-400' : 'bg-[#7FBF8E]' }}"></div>

                  <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2">
                      <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-1 sm:mb-1.5">
                          <span class="inline-flex items-center py-0.5 px-2 sm:px-2.5 text-[8px] sm:text-[10px] font-bold rounded-full
                            {{ $isPending ? 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300' : 'bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]' }}">
                            {{ $isPending ? 'PENDING' : 'APPROVED' }}
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
                        <p class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400 mt-0.5 sm:mt-1.5">{{ $material->user->name }}</p>
                        @if($material->purpose)
                          <p class="text-[10px] sm:text-xs text-gray-400 dark:text-neutral-500 mt-0.5 sm:mt-1 italic">
                            "{{ \Illuminate\Support\Str::limit($material->purpose, 60) }}"
                          </p>
                        @endif
                      </div>
                      <a href="{{ route('admin.view-request', $material->id) }}"
                        class="shrink-0 self-start text-[11px] sm:text-xs font-semibold hover:underline {{ $isPending ? 'text-amber-700 dark:text-amber-300' : 'text-[#1C6B45] dark:text-green-300' }}">
                        {{ $isPending ? 'Review →' : 'View' }}
                      </a>
                    </div>
                  </div>
                </div>
              @empty
                <div class="px-4 sm:px-6 py-14 text-center">
                  <p class="text-xs sm:text-sm font-semibold text-gray-500 dark:text-neutral-400">No material requests needed on this day</p>
                </div>
              @endforelse
            @endif

            {{-- ============ RETURNS: borrowed items due back ============ --}}
            @if($activeTab === 'returns')
              @php $ret = $this->selectedReturns; @endphp

              @foreach (['overdue' => 'Overdue (all)', 'due' => 'Due back this day'] as $key => $title)
                @if ($ret[$key]->isNotEmpty())
                  <p class="px-2 pt-2 text-[11px] font-bold uppercase tracking-wide {{ $key === 'overdue' ? 'text-red-600 dark:text-red-400' : 'text-gray-500 dark:text-neutral-400' }}">
                    {{ $title }} · {{ $ret[$key]->count() }}
                  </p>
                  @foreach ($ret[$key] as $loan)
                    @php $late = (int) \Carbon\Carbon::parse($loan->due_back_at)->diffInDays(today()); @endphp
                    <div wire:key="loan-{{ $key }}-{{ $loan->id }}" class="flex items-start justify-between gap-3 p-3 rounded-xl border
                      {{ $key === 'overdue' ? 'border-red-200 bg-red-50/50 dark:border-red-900 dark:bg-red-950/20' : 'border-gray-100 dark:border-neutral-700' }}">
                      <div class="min-w-0">
                        <p class="text-xs sm:text-sm font-semibold text-gray-800 dark:text-neutral-100">{{ $loan->item_name }} <span class="font-normal text-gray-400">×{{ $loan->quantity }}</span></p>
                        <p class="text-[11px] sm:text-xs text-gray-500 dark:text-neutral-400">
                          {{ $loan->request?->user?->name ?? 'N/A' }} · {{ $loan->request?->user?->department?->department_name ?? 'N/A' }}
                        </p>
                        <p class="text-[11px] sm:text-xs {{ $key === 'overdue' ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-gray-500 dark:text-neutral-400' }}">
                          Due {{ \Carbon\Carbon::parse($loan->due_back_at)->format('M d, Y') }}{{ $key === 'overdue' ? ' · ' . $late . ' ' . \Illuminate\Support\Str::plural('day', $late) . ' late' : '' }}
                          @if ($loan->return_requested_at)
                            · <span class="text-[#1C6B45] dark:text-[#9FD3AE]">sent back, waiting for you to confirm</span>
                          @endif
                        </p>
                      </div>
                      <a href="{{ route('admin.borrowed-items') }}?filter={{ $key === 'overdue' ? 'overdue' : 'outstanding' }}"
                        class="shrink-0 text-[11px] sm:text-xs font-semibold text-[#1C6B45] dark:text-green-300 hover:underline">Open →</a>
                    </div>
                  @endforeach
                @endif
              @endforeach

              @if ($ret['due']->isEmpty() && $ret['overdue']->isEmpty())
                <div class="px-4 sm:px-6 py-14 text-center">
                  <p class="text-xs sm:text-sm font-semibold text-gray-500 dark:text-neutral-400">Nothing due back on this day</p>
                  @if ($this->overdueCount > 0 && $selectedDate !== today()->toDateString())
                    <button type="button" wire:click="goToday" class="mt-2 text-xs font-semibold text-red-600 hover:underline dark:text-red-400">
                      {{ $this->overdueCount }} overdue — see them on Today
                    </button>
                  @endif
                </div>
              @endif
            @endif

          </div>
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
