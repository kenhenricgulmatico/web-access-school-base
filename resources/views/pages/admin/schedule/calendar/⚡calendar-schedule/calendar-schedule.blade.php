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

          {{-- Request Type Tabs (facility & material only) --}}
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
                    class="relative w-7 h-7 sm:w-8 sm:h-8 md:w-9 md:h-9 flex items-center justify-center text-[11px] sm:text-sm rounded-xl sm:rounded-2xl transition font-semibold
                      {{ $isSelected
                        ? 'bg-teal-500 text-white shadow-md shadow-teal-200 dark:shadow-none scale-105'
                        : ($isToday
                          ? 'bg-teal-50 text-teal-600 dark:bg-teal-900/40 dark:text-teal-300 ring-1 ring-teal-200 dark:ring-teal-800'
                          : 'text-gray-600 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-700') }}">
                    {{ $day }}
                  </button>
                  {{-- Event dots --}}
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
            <span class="text-[8px] sm:text-[10px] font-bold text-gray-300 dark:text-neutral-600 uppercase tracking-widest">Events</span>
          </div>

          {{-- Events List --}}
          <div class="overflow-y-auto max-h-[400px] sm:max-h-[500px] md:max-h-[600px] p-2 sm:p-3 space-y-1.5 sm:space-y-2">

            @php
              $events       = $this->selectedDateEvents;
              $reservations = $events['reservations'] ?? collect();
              $materials    = $events['materials'] ?? collect();
            @endphp

            {{-- Facility Reservations --}}
            @if($activeTab === 'reservation')
              @forelse($reservations as $reservation)
                @php
                  $facilityItem = $reservation->items->first(fn($i) => $i->start_time && $i->end_time)
                    ?? $reservation->items->first();
                  $materialItems = $reservation->items->reject(
                    fn($i) => $facilityItem && $i->id === $facilityItem->id
                  );
                @endphp

                <div class="flex gap-3 sm:gap-4 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-100 dark:border-neutral-700 hover:border-green-200 dark:hover:border-green-800 hover:bg-green-50/40 dark:hover:bg-green-900/10 transition">

                  <div class="w-1 rounded-full bg-green-400 dark:bg-green-500 shrink-0"></div>

                  <div class="flex-1 min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-2">
                      <div class="flex-1 min-w-0">
                        <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mb-1 sm:mb-1.5">
                          <span class="inline-flex items-center py-0.5 px-2 sm:px-2.5 text-[8px] sm:text-[10px] font-bold bg-green-100 text-green-700 rounded-full dark:bg-green-900 dark:text-green-300">
                            FACILITY
                          </span>
                          <span class="inline-flex items-center py-0.5 px-2 sm:px-2.5 text-[8px] sm:text-[10px] font-medium bg-gray-100 text-gray-600 rounded-full dark:bg-neutral-700 dark:text-neutral-300">
                            {{ $reservation->user->department->department_name ?? 'N/A' }}
                          </span>
                        </div>

                        <p class="text-xs sm:text-sm font-bold text-gray-800 dark:text-neutral-100 truncate">
                          {{ $facilityItem->item_name ?? 'N/A' }}
                        </p>

                        <p class="text-[10px] sm:text-xs text-gray-500 dark:text-neutral-400 mt-0.5 sm:mt-1 flex items-center gap-1">
                          <svg class="size-2.5 sm:size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                          </svg>
                          {{ $reservation->user->name }}
                        </p>

                        @if($materialItems->isNotEmpty())
                          <div class="mt-1.5 sm:mt-2 flex flex-wrap gap-1 sm:gap-1.5">
                            @foreach($materialItems as $mat)
                              <span class="inline-flex items-center gap-0.5 sm:gap-1 py-0.5 sm:py-1 px-1.5 sm:px-2.5 text-[8px] sm:text-[10px] font-semibold bg-purple-50 text-purple-700 rounded-full dark:bg-purple-900/40 dark:text-purple-300">
                                <svg class="size-2 sm:size-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                                {{ $mat->item_name }} (x{{ $mat->quantity }})
                              </span>
                            @endforeach
                          </div>
                        @endif

                        @if($reservation->purpose)
                          <p class="text-[10px] sm:text-xs text-gray-400 dark:text-neutral-500 mt-1 sm:mt-1.5 italic">
                            "{{ Str::limit($reservation->purpose, 60) }}"
                          </p>
                        @endif
                      </div>

                      @if($facilityItem && $facilityItem->start_time && $facilityItem->end_time)
                        <div class="shrink-0 text-left sm:text-right">
                          <p class="text-[10px] sm:text-xs font-bold text-gray-600 dark:text-neutral-300 bg-gray-50 dark:bg-neutral-700 px-2 sm:px-2.5 py-0.5 sm:py-1 rounded-lg">
                            {{ \Carbon\Carbon::parse($facilityItem->start_time)->format('g:i') }} – {{ \Carbon\Carbon::parse($facilityItem->end_time)->format('g:i A') }}
                          </p>
                        </div>
                      @endif
                    </div>
                  </div>

                </div>
              @empty
                <div class="px-4 sm:px-6 py-10 sm:py-14 text-center">
                  <p class="text-xs sm:text-sm text-gray-400 dark:text-neutral-500">No facility reservations on this day.</p>
                </div>
              @endforelse
            @endif

            {{-- Material Requests --}}
            @if($activeTab === 'material')
              @forelse($materials as $material)
                <div class="flex gap-3 sm:gap-4 p-3 sm:p-4 rounded-xl sm:rounded-2xl border border-gray-100 dark:border-neutral-700 hover:border-purple-200 dark:hover:border-purple-800 hover:bg-purple-50/40 dark:hover:bg-purple-900/10 transition">

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
                <div class="px-4 sm:px-6 py-10 sm:py-14 text-center">
                  <p class="text-xs sm:text-sm text-gray-400 dark:text-neutral-500">No material requests on this day.</p>
                </div>
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
