<div>
<!-- ========== MAIN SIDEBAR ========== -->
<div id="hs-pro-sidebar" class="hs-overlay [--body-scroll:true] lg:[--overlay-backdrop:false] [--is-layout-affect:true] [--opened:lg] [--auto-close:lg]
hs-overlay-open:translate-x-0 lg:hs-overlay-layout-open:translate-x-0
-translate-x-full transition-all duration-300 transform
w-60
hidden
fixed inset-y-0 z-60 start-0
bg-white dark:bg-neutral-900
lg:block lg:-translate-x-full lg:end-auto lg:bottom-0"
    role="dialog" tabindex="-1" aria-label="Sidebar">

    <div class="lg:pt-13 relative flex flex-col h-full max-h-full border-e border-gray-200 dark:border-neutral-700 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-xl">
        <nav class="p-3 size-full flex flex-col overflow-y-auto [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-gray-100 dark:[&::-webkit-scrollbar-track]:bg-neutral-800 [&::-webkit-scrollbar-thumb]:bg-gray-300 dark:[&::-webkit-scrollbar-thumb]:bg-neutral-600">

      {{-- Mobile: close button + user info strip --}}
      <div class="lg:hidden mb-3">

        {{-- Top row: close + user --}}
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#7FBF8E]/60 shrink-0">
              {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">{{ Auth::user()->name }}</p>
              <p class="text-[10px] uppercase tracking-wide text-[#1C6B45] dark:text-[#7FBF8E] font-medium">
                {{ Auth::user()->roles->first()?->name ?? 'User' }}
              </p>
            </div>
          </div>
          <button type="button"
            class="p-1.5 size-8 inline-flex items-center justify-center text-xs rounded-md text-muted-foreground-1 hover:bg-sidebar-2-nav-hover disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden shrink-0"
            aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-sidebar"
            data-hs-overlay="#hs-pro-sidebar">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
            </svg>
            <span class="sr-only">Close Sidebar</span>
          </button>
        </div>

        {{-- Department pill on mobile --}}
        @if(Auth::user()->department)
          <div class="flex items-center gap-1.5 px-2.5 py-1.5 bg-[#123524]/5 border border-[#123524]/10 rounded-lg">
            <svg class="size-3.5 text-[#123524]/60 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
            </svg>
            <span class="text-xs font-medium text-[#123524] truncate">
              {{ Auth::user()->department->department_name }}
            </span>
          </div>
        @endif

      </div>

      {{-- Home --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
                <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">Home</span>
                <ul class="flex flex-col gap-y-1">
                    <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.dashboard')
                    ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                    : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/portal/dashboard">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
              </svg>
              Dashboard
            </a>
          </li>
        </ul>
      </div>

      {{-- Requests --}}
      @canany(['facility-requests.view', 'material-requests.view', 'borrowed-items.view', 'calendar.view'])
      <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">
          Requests
        </span>
        <ul class="flex flex-col gap-y-1">
          @can('facility-requests.view')
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.reservation') || request()->routeIs('portal.create-reservation')
                    ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                    : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                href="{{ route('portal.reservation') }}">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                <polyline points="9 22 9 12 15 12 15 22"/>
              </svg>
              Reserve Facility
              {{-- Active indicator dot --}}
              @if(request()->routeIs('portal.reservation') || request()->routeIs('portal.create-reservation'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/60"></span>
              @endif
            </a>
          </li>
          @endcan
          @can('material-requests.view')
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.material')
                    ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                    : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                href="{{ route('portal.material') }}">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
              </svg>
              Request Materials
              @if(request()->routeIs('portal.material'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/60"></span>
              @endif
            </a>
          </li>
          @endcan
          @can('borrowed-items.view')
          <li>
            @php
              $overdueBorrowed = \App\Models\RequestItem::overdue()
                  ->whereNull('return_requested_at')
                  ->whereHas('request', fn ($q) => $q->where('user_id', auth()->id())->where('status', 'approved'))
                  ->count();
            @endphp
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.borrowed-items')
                    ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                    : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                href="{{ route('portal.borrowed-items') }}">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3"/>
              </svg>
              Borrowed Items
              @if($overdueBorrowed > 0)
                <span class="ms-auto px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-red-600 text-white">{{ $overdueBorrowed }}</span>
              @elseif(request()->routeIs('portal.borrowed-items'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/60"></span>
              @endif
            </a>
          </li>
          @endcan
          @can('calendar.view')
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.calendar')
                    ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                    : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                href="{{ route('portal.calendar') }}">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
              </svg>
              Calendar
              @if(request()->routeIs('portal.calendar'))
                <span class="ms-auto w-1.5 h-1.5 rounded-full bg-white/60"></span>
              @endif
            </a>
          </li>
          @endcan
        </ul>
      </div>
      @endcanany

      {{-- Account --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">
          Account
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.profile')
                    ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                    : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                href="/portal/profile">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
              </svg>
              Profile
            </a>
          </li>

          {{-- Logout directly in sidebar for mobile convenience --}}
          <li class="lg:hidden">
            <div class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 cursor-pointer focus:outline-hidden">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                <polyline points="16 17 21 12 16 7"/>
                <line x1="21" y1="12" x2="9" y2="12"/>
              </svg>
              <livewire:auth::logout />
            </div>
          </li>
        </ul>
      </div>

    </nav>


  </div>
</div>
<!-- End Sidebar -->
<!-- ========== END MAIN SIDEBAR ========== -->
</div>
