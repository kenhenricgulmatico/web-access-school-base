<div class="select-none">
<!-- ========== MAIN SIDEBAR ========== -->
<div id="hs-pro-sidebar" class="hs-overlay [--body-scroll:true] lg:[--overlay-backdrop:false] [--is-layout-affect:true] [--opened:lg] [--auto-close:lg]
hs-overlay-open:translate-x-0 lg:hs-overlay-layout-open:translate-x-0
-translate-x-full transition-all duration-300 transform
w-[82vw] max-w-64 sm:w-60 sm:max-w-60
fixed inset-y-0 z-60 start-0
bg-sidebar-2
lg:block lg:-translate-x-full lg:end-auto lg:bottom-0" role="dialog" tabindex="-1" aria-label="Sidebar">

  <div class="lg:pt-13 relative flex flex-col h-full max-h-full">

    {{-- Scrollable nav --}}
    <nav class="p-2.5 sm:p-3 size-full flex flex-col overflow-y-auto overflow-x-hidden [&::-webkit-scrollbar]:w-2 [&::-webkit-scrollbar-thumb]:rounded-none [&::-webkit-scrollbar-track]:bg-scrollbar-track [&::-webkit-scrollbar-thumb]:bg-scrollbar-thumb">

      {{-- Mobile header with close button --}}
      <div class="lg:hidden mb-2 flex items-center justify-between">
        <span class="text-xs font-semibold text-sidebar-2-nav-foreground ps-1">Menu</span>
        <button type="button" class="p-1.5 size-8 sm:size-7.5 inline-flex items-center justify-center gap-x-1 text-xs rounded-md text-muted-foreground-1 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden" aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-sidebar" data-hs-overlay="#hs-pro-sidebar">
          <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
          <span class="sr-only">Sidebar Toggle</span>
        </button>
      </div>

      {{-- Search --}}
      <button type="button" class="p-1.5 ps-2.5 w-full inline-flex items-center gap-x-2 text-sm rounded-lg bg-layer border border-layer-line text-muted-foreground-2 shadow-xs focus:outline-hidden disabled:opacity-50 disabled:pointer-events-none hover:border-[#D4A537]/40 transition" aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-cmsssm" data-hs-overlay="#hs-pro-cmsssm">
        Search
        <span class="ms-auto flex items-center gap-x-1 py-px px-1.5 border border-line-2 rounded-md shrink-0">
          <svg class="shrink-0 size-2.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 6v12a3 3 0 1 0 3-3H6a3 3 0 1 0 3 3V6a3 3 0 1 0-3 3h12a3 3 0 1 0-3-3"></path></svg>
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
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition min-w-0
                {{ request()->routeIs('programHead.dashboard')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="/programHead/dashboard">
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/></svg>
              <span class="truncate">Dashboard</span>
            </a>
          </li>
        </ul>
      </div>

      {{-- Student Management --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Student Management
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition min-w-0
                {{ request()->routeIs('programHead.students')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="/programHead/view-student">
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
              </svg>
              <span class="truncate">View Students</span>
            </a>
          </li>
        </ul>
      </div>

      {{-- Manage Request --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Manage Request
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition min-w-0
                {{ request()->routeIs('programHead.facility')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="/programHead/facility">
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m4.5 0v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21"/></svg>
              <span class="truncate">Facility Requests</span>
            </a>
          </li>
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition min-w-0
                {{ request()->routeIs('programHead.material')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="/programHead/material">
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
              <span class="truncate">Material Requests</span>
            </a>
          </li>
        </ul>
      </div>

      {{-- Make Request (to Admin) --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Make Request
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition min-w-0
                {{ request()->routeIs('programHead.request-to-admin.*')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="/programHead/request-to-admin/view-request">
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/></svg>
              <span class="truncate">Request to Admin</span>
            </a>
          </li>
        </ul>
      </div>

      {{-- Resources --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Resources
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition min-w-0
                {{ request()->routeIs('programHead.resource-allocation')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="/programHead/resource-allocation">
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5"/></svg>
              <span class="truncate">Resource Allocation</span>
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
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition min-w-0
                {{ request()->routeIs('programHead.notifications')
                    ? 'bg-[#123524] text-white font-medium shadow-sm'
                    : 'text-sidebar-2-nav-foreground hover:bg-sidebar-2-nav-hover' }}
                focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="/programHead/notifications">
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
              <span class="truncate">Notifications</span>
            </a>
          </li>
        </ul>
      </div>

      {{-- System --}}
      <div class="pt-3 mt-3 flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          System
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm text-sidebar-2-nav-foreground rounded-lg hover:bg-sidebar-2-nav-hover focus:outline-hidden focus:bg-sidebar-2-nav-focus min-w-0" href='{{route('coordinator.audit')}}'>
              <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.286Z"/></svg>
              <span class="truncate">Audit Logs</span>
            </a>
          </li>
        </ul>
      </div>

      {{-- Mobile Others (visible only on small screens) --}}
      <div class="pt-3 mt-3 lg:hidden flex flex-col border-t border-sidebar-2-divider first:border-t-0 first:pt-0 first:mt-0">
        <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#B8862A]">
          Others
        </span>
        <ul class="flex flex-col gap-y-1">
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm text-sidebar-2-nav-foreground rounded-lg hover:bg-sidebar-2-nav-hover focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="#">
              Docs
            </a>
          </li>
          <li>
            <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm text-sidebar-2-nav-foreground rounded-lg hover:bg-sidebar-2-nav-hover focus:outline-hidden focus:bg-sidebar-2-nav-focus" href="#">
              API
            </a>
          </li>
        </ul>
      </div>

    </nav>

    {{-- Footer: CSAV mini badge --}}
    <footer class="mt-auto p-2.5 sm:p-3 border-t border-sidebar-2-divider">
      <div class="flex items-center gap-2.5 px-2 py-2 rounded-lg bg-gradient-to-r from-[#123524]/5 to-[#D4A537]/5 min-w-0">
        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png" alt="CSAV" class="w-6 h-6 rounded-full bg-white p-0.5 ring-1 ring-[#D4A537]/40 object-contain shrink-0">
        <div class="leading-tight min-w-0">
          <p class="text-[11px] font-semibold text-sidebar-2-nav-foreground truncate">CSAV Portal</p>
          <p class="text-[10px] text-muted-foreground-1 truncate">Program Head</p>
        </div>
      </div>
    </footer>

  </div>
</div>
<!-- End Sidebar -->
<!-- ========== END MAIN SIDEBAR ========== -->
</div>
