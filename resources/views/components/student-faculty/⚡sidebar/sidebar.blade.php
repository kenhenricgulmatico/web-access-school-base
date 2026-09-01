<div class="select-none">
<!-- ========== MAIN SIDEBAR ========== -->
<div id="hs-pro-sidebar"
    class="hs-overlay [--body-scroll:true] lg:[--overlay-backdrop:false] [--is-layout-affect:true] [--opened:lg] [--auto-close:lg]
    hs-overlay-open:translate-x-0 lg:hs-overlay-layout-open:translate-x-0
    -translate-x-full transition-all duration-300 transform
    w-[260px] sm:w-60
    hidden
    fixed inset-y-0 z-60 start-0
    bg-sidebar-2
    lg:block lg:-translate-x-full lg:end-auto lg:bottom-0"
    role="dialog" tabindex="-1" aria-label="Sidebar">

  <div class="lg:pt-13 relative flex flex-col h-full max-h-full">
    <nav class="p-3 size-full flex flex-col overflow-y-auto
        [&::-webkit-scrollbar]:w-1.5
        [&::-webkit-scrollbar-thumb]:rounded-none
        [&::-webkit-scrollbar-track]:bg-scrollbar-track
        [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb">

      {{-- Mobile: close button + user info strip --}}
      <div class="lg:hidden mb-3">

        {{-- Top row: close + user --}}
        <div class="flex items-center justify-between mb-3">
          <div class="flex items-center gap-2 min-w-0">
            <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#D4A537]/50 shrink-0">
              {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
              <p class="text-sm font-semibold text-foreground truncate">{{ Auth::user()->name }}</p>
              <p class="text-[10px] uppercase tracking-wide text-[#B8862A] font-medium">
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

      {{-- Search --}}
      <button type="button"
        class="p-1.5 ps-2.5 w-full inline-flex items-center gap-x-2 text-sm rounded-lg bg-layer border border-layer-line text-muted-foreground-2 shadow-xs focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none hover:border-[#D4A537]/40 transition"
        aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-cmsssm"
        data-hs-overlay="#hs-pro-cmsssm">
        <svg class="shrink-0 size-3.5 text-muted-foreground-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
        </svg>
        <span class="text-sm">Search</span>
        <span class="ms-auto flex items-center gap-x-1 py-px px-1.5 border border-line-2 rounded-md">
          <svg class="shrink-0 size-2.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
              viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
              stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"/>
          </svg>
          <span class="text-[11px] uppercase">k</span>
        </span>
      </button>

      {{-- Home --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Home
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.dashboard')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus"
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
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Requests
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.reservation') || request()->routeIs('portal.create-reservation')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus"
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
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.material')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus"
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
        </ul>
      </div>

      {{-- Notifications --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Notifications
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.notification')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus"
                href="#!">
              <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
              </svg>
              Notifications
              {{-- Unread badge in sidebar --}}
              @php $unread = \App\Models\Notification::where('user_id', Auth::id())->where('status', 'unread')->count(); @endphp
              @if($unread > 0)
                <span class="ms-auto inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full">
                  {{ $unread > 9 ? '9+' : $unread }}
                </span>
              @endif
            </a>
          </li>
        </ul>
      </div>

      {{-- Account --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Account
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                {{ request()->routeIs('portal.profile')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus"
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

    {{-- Footer: CSAV mini badge --}}
    <footer class="mt-auto p-3 border-t border-sidebar-2-divider">
      <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg bg-gradient-to-r from-[#123524]/5 to-[#D4A537]/5">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png"
            alt="CSAV"
            class="w-6 h-6 rounded-full bg-white p-0.5 ring-1 ring-[#D4A537]/40 object-contain shrink-0">
        <div class="leading-tight min-w-0">
          <p class="text-[11px] font-semibold text-sidebar-2-nav-foreground">CSAV Portal</p>
          <p class="text-[10px] text-muted-foreground-1 truncate">
            {{ Auth::user()->department?->department_name ?? 'Student & Faculty' }}
          </p>
        </div>
      </div>
    </footer>

  </div>
</div>
<!-- End Sidebar -->
<!-- ========== END MAIN SIDEBAR ========== -->
</div>
