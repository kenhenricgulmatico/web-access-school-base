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

            {{-- Home --}}
            <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
                <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">Home</span>
                <ul class="flex flex-col gap-y-1">
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.dashboard')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/dashboard">
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

            {{-- Manage Request --}}
            @canany(['program-head-requests.view', 'borrowed-items.view'])
            <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
                <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">Manage Request</span>
                <ul class="flex flex-col gap-y-1">
                    @can('program-head-requests.view')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.manage-coordinator')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/manage-coordinator">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                <polyline points="14 2 14 8 20 8"/>
                                <line x1="16" y1="13" x2="8" y2="13"/>
                                <line x1="16" y1="17" x2="8" y2="17"/>
                            </svg>
                            Program Head Requests
                        </a>
                    </li>
                    @endcan
                                        {{-- ✅ NEW: Borrowed Items (Pcs) --}}
                    @can('borrowed-items.view')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.borrowed-items')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/borrowed-items">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <polyline points="9 14 4 9 9 4"/>
                                <path d="M20 20v-7a4 4 0 0 0-4-4H4"/>
                            </svg>
                            Borrowed Items
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

            {{-- Inventory / Stocks --}}
            @canany(['stock-materials.view', 'stock-history.view'])
            <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
                <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">Inventory</span>
                <ul class="flex flex-col gap-y-1">
                    @can('stock-materials.view')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.inventory-stock-material')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/inventory-stock-material">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"/>
                                <line x1="12" y1="22.08" x2="12" y2="12"/>
                            </svg>
                            Stock Materials
                        </a>
                    </li>
                    @endcan
                    @can('stock-history.view')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.inventory-stock-history')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/inventory-stock-history">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M3 3h18v18H3z"/>
                                <path d="M8 12h8M8 8h8M8 16h5"/>
                            </svg>
                            Stock History
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

            {{-- Schedule --}}
            @canany(['calendar.view', 'facility-schedule.view'])
            <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
                <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">Schedule</span>
                <ul class="flex flex-col gap-y-1">
                    @can('calendar.view')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.calendar')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/calendar">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Calendar
                        </a>
                    </li>
                    @endcan
                    @can('facility-schedule.view')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.schedule')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/schedule">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                <line x1="16" y1="2" x2="16" y2="6"/>
                                <line x1="8" y1="2" x2="8" y2="6"/>
                                <line x1="3" y1="10" x2="21" y2="10"/>
                            </svg>
                            Facility Room
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

            {{-- Management --}}
            @canany(['users.view'])
            <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
                <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">Management</span>
                <ul class="flex flex-col gap-y-1">
                    @can('users.view')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.users')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/users">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                                <circle cx="9" cy="7" r="4"/>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                            </svg>
                            Users
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

            {{-- System --}}
            @canany(['audit.view-all'])
            <div class="pt-3 mt-3 flex flex-col border-t border-gray-200 dark:border-neutral-700 first:border-t-0 first:pt-0 first:mt-0">
                <span class="block ps-2.5 mb-2 font-medium text-[10px] tracking-wider uppercase text-[#1C6B45]/80 dark:text-[#7FBF8E]">System</span>
                <ul class="flex flex-col gap-y-1">
                    @can('audit.view-all')
                    <li>
                        <a class="w-full flex items-center gap-x-2.5 py-2 px-2.5 text-sm rounded-lg transition
                            {{ request()->routeIs('admin.audit-logs')
                                ? 'bg-[#E8F3EC] text-[#123524] font-semibold dark:bg-[#1C6B45]/25 dark:text-[#9FD3AE]'
                                : 'text-gray-700 dark:text-neutral-300 hover:bg-[#F3F8F5] dark:hover:bg-neutral-800' }}
                            focus:outline-hidden focus:bg-[#F3F8F5] dark:focus:bg-neutral-800"
                            href="/admin/audit-logs">
                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                <polyline points="9 12 11 14 15 10"/>
                            </svg>
                            Audit Logs
                        </a>
                    </li>
                    @endcan
                </ul>
            </div>
            @endcanany

        </nav>
    </div>
</div>
<!-- End Sidebar -->
<!-- ========== END MAIN SIDEBAR ========== -->
</div>
