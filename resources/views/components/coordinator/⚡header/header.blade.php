<div class="select-none">
<!-- ========== HEADER ========== -->
<header class="fixed top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-[48] lg:z-[61] w-full bg-navbar-2 text-sm py-2.5"
    wire:poll.5s="loadNotifications">
    <nav class="px-3 sm:px-5.5 flex basis-full items-center w-full mx-auto">
        <div class="w-full flex items-center gap-x-1 sm:gap-x-1.5">

            {{-- Left: Logo + Sidebar Toggle --}}
            <ul class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                <li class="inline-flex items-center gap-1.5 sm:gap-2.5 relative pe-1.5 sm:pe-2.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:w-px after:h-3.5 after:bg-navbar-2-divider after:rounded-full after:-translate-y-1/2 after:rotate-12 min-w-0">

                    {{-- CSAV Logo, seal-badge treatment --}}
                    <a href="/coordinator/dashboard"
                        class="shrink-0 inline-flex justify-center items-center rounded-full bg-white ring-2 ring-[#D4A537]/60 p-1 shadow-sm hover:ring-[#D4A537] transition focus:outline-hidden focus:opacity-80"
                        aria-label="CSAV">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png"
                            alt="CSAV Logo" class="h-5 w-5 sm:h-6 sm:w-6 object-contain" />
                    </a>

                    <div class="hidden md:flex flex-col leading-tight ms-0.5 min-w-0">
                        <span class="text-sm font-semibold text-foreground truncate" style="font-family: 'Fraunces', serif;">
                            Colegio de Sta. Ana de Victorias
                        </span>
                        <span class="text-[10px] uppercase tracking-[0.15em] text-[#D4A537] font-medium truncate">
                            Program Head Portal
                        </span>
                    </div>

                    <button type="button"
                        class="p-1.5 size-7.5 inline-flex items-center gap-x-1 text-xs rounded-md border border-transparent text-foreground hover:bg-surface-hover disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-surface-focus ms-0.5 sm:ms-1 shrink-0"
                        aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-sidebar"
                        data-hs-overlay="#hs-pro-sidebar">
                        <svg class="shrink-0 size-3.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="M15 3v18" />
                            <path d="m10 15-3-3 3-3" />
                        </svg>
                        <span class="sr-only">Sidebar Toggle</span>
                    </button>

                </li>

                {{-- Department Badge (coordinator's assigned department) --}}
                @if (Auth::user()->department)
                    <li class="hidden lg:inline-flex items-center gap-1.5 px-3 py-1 bg-[#123524]/5 border border-[#123524]/10 rounded-full shrink-0">
                        <svg class="size-3.5 text-[#123524]/60" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
                        </svg>
                        <span class="text-xs font-medium text-[#123524] whitespace-nowrap">
                            {{ Auth::user()->department->department_name }}
                        </span>
                    </li>
                @endif
            </ul>

            {{-- Right: Notifications + User Dropdown --}}
            <ul class="flex flex-row items-center gap-x-1 sm:gap-x-2 ms-auto shrink-0">

                {{-- Notification Bell --}}
                <li x-data="{
                        open: false,
                        get unreadCount() { return $wire.unreadCount; },
                        get notifications() { return $wire.notifications; },
                        markAllAsRead() { $wire.markAllAsRead(); }
                    }"
                    class="inline-flex items-center relative">

                    {{-- Bell Button --}}
                    <button @click="open = !open"
                        class="relative flex justify-center items-center size-8 sm:size-9 text-sm text-navbar-2-nav-foreground rounded-full hover:bg-navbar-2-nav-hover focus:outline-hidden focus:bg-navbar-2-nav-focus transition"
                        aria-label="Notifications">
                        <svg class="shrink-0 size-4.5 sm:size-5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        <span x-show="unreadCount > 0" x-cloak
                            class="absolute -top-0.5 -end-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-[#B8352A] rounded-full border-2 border-white animate-pulse">
                            <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </span>
                    </button>

                    {{-- Dropdown Panel — fixed & centered on mobile, anchored on sm+ --}}
                    <div x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                        class="fixed sm:absolute left-3 right-3 sm:left-auto sm:right-0 top-16 sm:top-full sm:mt-2 w-auto sm:w-96 max-w-full bg-white dark:bg-[#16281F] border border-[#E4E1D8] dark:border-[#2A4B3A] rounded-2xl shadow-2xl z-50 overflow-hidden"
                        style="display: none;">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-4 py-3 bg-[#FAF7EF] dark:bg-[#0E1A14] border-b border-[#E4E1D8] dark:border-[#2A4B3A]">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="size-4 text-[#1C6B45] dark:text-[#7FBF8E] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <h3 class="text-sm font-semibold text-[#123524] dark:text-white truncate" style="font-family: 'Fraunces', serif;">Pending Requests</h3>
                                <span x-show="unreadCount > 0"
                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-[#B8352A] rounded-full shrink-0"
                                    x-text="unreadCount"></span>
                            </div>
                            <button x-show="unreadCount > 0"
                                @click="markAllAsRead()"
                                class="text-xs text-[#1C6B45] hover:text-[#123524] dark:text-[#7FBF8E] font-medium hover:underline transition shrink-0 whitespace-nowrap ms-2">
                                Mark all read
                            </button>
                        </div>

                        {{-- Notifications List --}}
                        <div class="max-h-[60vh] sm:max-h-80 overflow-y-auto divide-y divide-[#E4E1D8] dark:divide-[#2A4B3A]">

                            <template x-for="notification in notifications" :key="notification.id">
                                <div class="px-4 py-3 flex items-start gap-3 transition-colors"
                                    :class="notification.status === 'pending'
                                        ? 'bg-[#D4A537]/[0.06] hover:bg-[#D4A537]/[0.1]'
                                        : 'hover:bg-[#FAF7EF] dark:hover:bg-[#0E1A14]/50'">

                                    {{-- Icon --}}
                                    <div class="shrink-0 mt-0.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center"
                                            :class="notification.is_facility
                                                ? 'bg-[#123524]/10 dark:bg-[#123524]/30 text-[#1C6B45] dark:text-[#7FBF8E]'
                                                : 'bg-[#D4A537]/15 dark:bg-[#D4A537]/25 text-[#B8862A]'">
                                            <template x-if="notification.is_facility">
                                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21m4.5 0v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21" />
                                                </svg>
                                            </template>
                                            <template x-if="!notification.is_facility">
                                                <svg class="size-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                                                </svg>
                                            </template>
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5 flex-wrap">
                                            <span class="text-xs font-medium px-1.5 py-0.5 rounded-md"
                                                :class="notification.is_facility
                                                    ? 'bg-[#123524]/10 text-[#1C6B45] dark:bg-[#123524]/30 dark:text-[#7FBF8E]'
                                                    : 'bg-[#D4A537]/15 text-[#B8862A]'"
                                                x-text="notification.type">
                                            </span>
                                            <span class="text-xs font-medium px-1.5 py-0.5 rounded-md bg-[#D4A537]/15 text-[#B8862A]">
                                                Pending
                                            </span>
                                        </div>
                                        <p class="text-sm font-medium text-gray-800 dark:text-gray-200 truncate"
                                            x-text="notification.requester"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                            x-text="'Dept: ' + notification.department"></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate"
                                            x-text="notification.purpose"></p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1"
                                            x-text="notification.time_ago"></p>
                                    </div>

                                </div>
                            </template>

                            {{-- Empty State --}}
                            <div x-show="notifications.length === 0" class="py-10 sm:py-12 text-center px-4">
                                <div class="w-14 h-14 rounded-full bg-[#FAF7EF] dark:bg-[#0E1A14] flex items-center justify-center mx-auto mb-3">
                                    <svg class="size-7 text-[#B8862A]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-[#123524] dark:text-gray-300">No pending requests</p>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">All requests have been handled</p>
                            </div>

                        </div>

                        {{-- Footer --}}
                        <div x-show="notifications.length > 0"
                            class="px-4 py-3 bg-[#FAF7EF] dark:bg-[#0E1A14] border-t border-[#E4E1D8] dark:border-[#2A4B3A] flex flex-wrap gap-3 justify-center">
                            <a href="/coordinator/facility"
                                class="text-xs font-medium text-[#1C6B45] hover:text-[#123524] dark:text-[#7FBF8E] hover:underline transition">
                                View Facility →
                            </a>
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <a href="/coordinator/material"
                                class="text-xs font-medium text-[#B8862A] hover:text-[#96701F] hover:underline transition">
                                View Materials →
                            </a>
                        </div>

                    </div>
                </li>

                {{-- User Dropdown --}}
                <li class="inline-flex items-center">
                    <div class="hs-dropdown inline-flex [--strategy:absolute] [--auto-close:inside] [--placement:bottom-right] relative text-start">

                        <button id="hs-coordinator-user-dropdown" type="button"
                            class="p-0.5 inline-flex shrink-0 items-center gap-x-1.5 sm:gap-x-2 text-start rounded-full hover:bg-navbar-nav-hover focus:outline-hidden focus:bg-navbar-nav-focus"
                            aria-haspopup="menu" aria-expanded="false" aria-label="User Dropdown">
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-xs sm:text-sm font-bold ring-2 ring-[#D4A537]/50 shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left min-w-0">
                                <span class="text-xs font-medium text-foreground block leading-tight truncate max-w-[120px]">{{ Auth::user()->name }}</span>
                            </div>
                            <svg class="shrink-0 size-3 text-muted-foreground-1 hidden md:block"
                                xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-64 max-w-[calc(100vw-1.5rem)] transition-[opacity,margin] duration opacity-0 hidden z-20 bg-gray-50 border border-dropdown-line rounded-xl shadow-xl overflow-hidden"
                            role="menu" aria-orientation="vertical" aria-labelledby="hs-coordinator-user-dropdown">

                            {{-- User Info --}}
                            <div class="py-3.5 px-3.5 border-b border-dropdown-divider bg-gradient-to-br from-[#123524]/[0.04] to-[#D4A537]/[0.04]">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#D4A537]/50 shrink-0">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-foreground truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-muted-foreground-1 truncate">{{ Auth::user()->email }}</p>
                                        <div class="flex items-center gap-1.5 mt-1">
                                            <span class="text-[10px] uppercase tracking-wide text-[#B8862A] font-semibold">
                                                {{ Auth::user()->roles->first()->name ?? 'No Role' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Theme Toggle --}}
                            <div class="px-4 py-2 border-b border-dropdown-divider">
                                <div class="flex flex-wrap justify-between items-center gap-2">
                                    <span class="text-sm text-foreground">Theme</span>
                                    <div class="p-0.5 inline-flex cursor-pointer bg-surface rounded-full">
                                        <button type="button"
                                            class="size-7 flex justify-center items-center bg-layer shadow-sm text-layer-foreground rounded-full hs-auto-mode-active:bg-transparent hs-auto-mode-active:shadow-none hs-dark-mode-active:bg-transparent hs-dark-mode-active:shadow-none"
                                            data-hs-theme-click-value="default">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="4"/>
                                                <path d="M12 3v1M12 20v1M3 12h1M20 12h1m-2.636-6.364-.707.707M6.343 17.657l-.707.707M5.636 5.636l.707.707m12.021 12.021.707.707"/>
                                            </svg>
                                            <span class="sr-only">Light</span>
                                        </button>
                                        <button type="button"
                                            class="size-7 flex justify-center items-center text-layer-foreground rounded-full hs-dark-mode-active:bg-secondary-active hs-dark-mode-active:text-secondary-foreground hs-dark-mode-active:shadow-sm"
                                            data-hs-theme-click-value="dark">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                                            </svg>
                                            <span class="sr-only">Dark</span>
                                        </button>
                                        <button type="button"
                                            class="size-7 flex justify-center items-center text-layer-foreground rounded-full hs-auto-light-mode-active:bg-layer hs-auto-mode-active:shadow-sm"
                                            data-hs-theme-click-value="auto">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <rect width="20" height="14" x="2" y="3" rx="2"/>
                                                <line x1="8" x2="16" y1="21" y2="21"/>
                                                <line x1="12" x2="12" y1="17" y2="21"/>
                                            </svg>
                                            <span class="sr-only">Auto</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Menu Items --}}
                            <div class="p-1">
                                <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus"
                                    href="/coordinator/profile">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/>
                                        <circle cx="12" cy="7" r="4"/>
                                    </svg>
                                    Profile
                                </a>
                                <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus"
                                    href="/coordinator/settings">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Settings
                                </a>

                                {{-- Logout --}}
                                <div class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-red-600 hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus cursor-pointer">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                        <polyline points="16 17 21 12 16 7"/>
                                        <line x1="21" y1="12" x2="9" y2="12"/>
                                    </svg>
                                    <livewire:auth::logout />
                                </div>
                            </div>

                        </div>

                    </div>
                </li>

            </ul>
        </div>
    </nav>
</header>
<!-- ========== END HEADER ========== -->

<style>
    [x-cloak] { display: none !important; }
</style>
</div>
