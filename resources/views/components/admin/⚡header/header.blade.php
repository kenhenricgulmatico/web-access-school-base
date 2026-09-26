<div class="select-none">
<!-- ========== HEADER ========== -->
<header class="fixed top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-48 lg:z-61 w-full bg-white dark:bg-neutral-900 border-b border-gray-200 dark:border-neutral-700 text-sm py-2.5">
    <nav class="px-4 sm:px-5.5 flex basis-full items-center w-full mx-auto">
        <div class="w-full flex items-center gap-x-1.5">

            {{-- Left: Logo + Sidebar Toggle --}}
            <ul class="flex items-center gap-1.5">
                <li class="inline-flex items-center gap-1 relative pe-1.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:w-px after:h-3.5 after:bg-gray-200 dark:after:bg-neutral-700 after:rounded-full after:-translate-y-1/2 after:rotate-12">

                    <a href="/admin/dashboard"
                        class="shrink-0 inline-flex justify-center items-center rounded-full bg-white ring-2 ring-[#D4A537]/60 p-1 shadow-sm hover:ring-[#D4A537] transition focus:outline-hidden focus:opacity-80"
                        aria-label="CSAV">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png"
                            alt="CSAV Logo" class="h-5 w-5 sm:h-6 sm:w-6 object-contain" />
                    </a>

                    <span class="hidden sm:block text-sm font-semibold text-gray-900 dark:text-white ms-1">
                        Colegio de Sta. Ana de Victorias
                    </span>

                    <button type="button"
                        class="p-1.5 size-7.5 inline-flex items-center gap-x-1 text-xs rounded-md border border-transparent text-gray-600 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-gray-100 dark:focus:bg-neutral-800"
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
            </ul>

            {{-- Right: Notifications + User Dropdown --}}
            <ul class="flex flex-row items-center gap-x-2 ms-auto">

                {{-- Notification Bell --}}
                <li x-data="{
                        open: false,
                        get unreadCount() { return $wire.unreadCount },
                        get totalCount() { return $wire.totalCount },
                        get showAll() { return $wire.showAll },
                        get notifications() { return $wire.notifications },
                        markAllAsRead() { $wire.markAllAsRead() },
                        openNotification(id) { $wire.openNotification(id) },
                        toggleAll() { $wire.toggleShowAll() }
                    }"
                    class="inline-flex items-center relative">

                    {{-- Bell Button --}}
                    <button @click="open = !open"
                        class="relative flex justify-center items-center size-9 text-sm text-gray-600 dark:text-neutral-300 rounded-full hover:bg-gray-100 dark:hover:bg-neutral-800 focus:outline-hidden focus:bg-gray-100 dark:focus:bg-neutral-800 transition"
                        aria-label="Notifications">
                        <svg class="shrink-0 size-5 dark:text-yellow-400" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        {{-- Unread badge --}}
                        <span x-show="unreadCount > 0" x-cloak
                            class="absolute -top-0.5 -end-0.5 flex items-center justify-center min-w-[18px] h-[18px] px-1 text-[10px] font-bold text-white bg-red-500 rounded-full border-2 border-white dark:border-neutral-900">
                            <span x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                        </span>
                    </button>

                    {{-- Dropdown Panel --}}
                    <div x-show="open"
                        @click.outside="open = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                        x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                        class="absolute right-0 top-full mt-2 w-96 bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-2xl z-50 overflow-hidden"
                        style="display: none;">

                        {{-- Header --}}
                        <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-neutral-800 border-b border-gray-200 dark:border-neutral-700">
                            <div class="flex items-center gap-2">
                                <svg class="size-4 text-gray-600 dark:text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Notifications</h3>
                                <span x-show="unreadCount > 0"
                                    class="inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-xs font-bold text-white bg-red-500 rounded-full"
                                    x-text="unreadCount"></span>
                            </div>
                            <button x-show="unreadCount > 0"
                                @click="markAllAsRead()"
                                class="text-xs text-blue-600 hover:text-blue-700 dark:text-blue-400 font-medium hover:underline transition">
                                Mark all read
                            </button>
                        </div>

                        {{-- Notifications List --}}
                        <div class="max-h-80 overflow-y-auto divide-y divide-gray-100 dark:divide-neutral-800">

                            <template x-for="notification in notifications" :key="notification.id">
                                {{-- Whole row is the click target (no buttons inside) --}}
                                <div @click="openNotification(notification.id)"
                                    class="px-4 py-3 flex items-start gap-3 transition-colors cursor-pointer"
                                    :class="notification.status === 'pending'
                                        ? 'bg-blue-50 dark:bg-blue-900/20 hover:bg-blue-100 dark:hover:bg-blue-900/30'
                                        : 'hover:bg-gray-50 dark:hover:bg-neutral-800/50'">

                                    {{-- Avatar --}}
                                    <div class="shrink-0 mt-0.5">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-sm text-white"
                                            :class="notification.status === 'unread'
                                                ? 'bg-green-700'
                                                : 'bg-gray-400'">
                                            <span x-text="notification.user ? notification.user.charAt(0).toUpperCase() : '?'"></span>
                                        </div>
                                    </div>

                                    {{-- Content --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-0.5">
                                            <span class="text-xs font-medium px-1.5 py-0.5 rounded-md"
                                                :class="notification.status === 'unread'
                                                    ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-300'
                                                    : 'bg-gray-100 text-gray-500 dark:bg-neutral-800 dark:text-neutral-400'"
                                                x-text="notification.status === 'unread' ? 'New' : 'Read'">
                                            </span>
                                            <span class="text-xs text-gray-400 dark:text-neutral-500"
                                                x-text="notification.type"></span>
                                        </div>
                                        <p class="text-sm text-gray-800 dark:text-neutral-200 leading-snug break-words"
                                            x-text="notification.message"></p>
                                        <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1"
                                            x-text="notification.time_ago"></p>
                                    </div>

                                </div>
                            </template>

                            {{-- Empty State --}}
                            <div x-show="notifications.length === 0" class="py-12 text-center">
                                <div class="w-14 h-14 rounded-full bg-gray-100 dark:bg-neutral-800 flex items-center justify-center mx-auto mb-3">
                                    <svg class="size-7 text-gray-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                </div>
                                <p class="text-sm font-medium text-gray-600 dark:text-neutral-400">All caught up!</p>
                                <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">No notifications yet</p>
                            </div>

                        </div>

                        {{-- Footer: View all / Show less (only when there are more than 10) --}}
                        <div x-show="totalCount > 10"
                            class="px-4 py-3 bg-gray-50 dark:bg-neutral-800 border-t border-gray-200 dark:border-neutral-700 text-center">
                            <button type="button" @click="toggleAll()"
                                class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 hover:underline transition"
                                x-text="showAll ? 'Show less ↑' : 'View all notifications →'">
                            </button>
                        </div>

                    </div>
                </li>

                {{-- User Dropdown --}}
                <li class="inline-flex items-center">
                    <div class="hs-dropdown inline-flex [--strategy:absolute] [--auto-close:inside] [--placement:bottom-right] relative text-start">

                         {{-- Avatar Button --}}
                        <button id="hs-admin-user-dropdown" type="button"
                            class="p-0.5 inline-flex shrink-0 items-center gap-x-1.5 sm:gap-x-2 text-start rounded-full hover:bg-navbar-nav-hover focus:outline-hidden focus:bg-navbar-nav-focus"
                            aria-haspopup="menu" aria-expanded="false" aria-label="User Dropdown">

                            {{-- Avatar Circle --}}
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#123524] dark:bg-green-700 text-white flex items-center justify-center text-xs sm:text-sm font-bold ring-3 ring-[#D4A537]/50 dark:ring-[#D4A537]/50 shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>

                            {{-- Name - Hidden on Mobile --}}
                            <div class="hidden sm:block text-left">
                                <span class="text-xs font-medium text-gray-900 dark:text-white">{{ Auth::user()->name }}</span>
                            </div>

                            <svg class="shrink-0 size-3 text-gray-600 dark:text-neutral-400 hidden sm:block"
                                xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        <div class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-60 transition-[opacity,margin] duration opacity-0 hidden z-20 bg-white dark:bg-neutral-900 border border-gray-300 dark:border-neutral-700 rounded-xl shadow-xl"
                            role="menu" aria-orientation="vertical" aria-labelledby="hs-admin-user-dropdown">

                            <div class="py-3 px-3.5 border-b border-gray-200 dark:border-neutral-700">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-green-700 text-white flex items-center justify-center text-sm font-bold">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-gray-500 dark:text-neutral-400">{{ Auth::user()->email }}</p>
                                        <p class="text-xs text-gray-500 dark:text-neutral-400 capitalize">
                                            {{ Auth::user()->roles->first()->name ?? 'No Role' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="px-4 py-2 border-b border-gray-200 dark:border-neutral-700">
                                <div class="flex flex-wrap justify-between items-center gap-2">
                                    <span class="text-sm text-gray-900 dark:text-white">Theme</span>
                                    <div class="p-0.5 inline-flex cursor-pointer bg-gray-100 dark:bg-neutral-800 rounded-full">
                                        <button type="button"
                                            class="size-7 flex justify-center items-center bg-white dark:bg-neutral-700 shadow-sm text-gray-700 dark:text-white rounded-full hs-auto-mode-active:bg-transparent hs-auto-mode-active:shadow-none hs-dark-mode-active:bg-transparent hs-dark-mode-active:shadow-none"
                                            data-hs-theme-click-value="default">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="4"/>
                                                <path d="M12 3v1M12 20v1M3 12h1M20 12h1m-2.636-6.364-.707.707M6.343 17.657l-.707.707M5.636 5.636l.707.707m12.021 12.021.707.707"/>
                                            </svg>
                                            <span class="sr-only">Light</span>
                                        </button>
                                        <button type="button"
                                            class="size-7 flex justify-center items-center text-gray-700 dark:text-neutral-300 rounded-full hs-dark-mode-active:bg-neutral-700 hs-dark-mode-active:text-white hs-dark-mode-active:shadow-sm"
                                            data-hs-theme-click-value="dark">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>
                                            </svg>
                                            <span class="sr-only">Dark</span>
                                        </button>
                                        <button type="button"
                                            class="size-7 flex justify-center items-center text-gray-700 dark:text-neutral-300 rounded-full hs-auto-light-mode-active:bg-white hs-auto-mode-active:shadow-sm"
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

                            <div class="p-1">
                                <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-gray-700 dark:text-neutral-300 hover:bg-gray-100 dark:hover:bg-neutral-800 focus:outline-hidden focus:bg-gray-100 dark:focus:bg-neutral-800"
                                    href="/admin/settings">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/>
                                        <circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    Settings
                                </a>

                                <div class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-red-600 hover:bg-gray-100 dark:hover:bg-neutral-800 focus:outline-hidden focus:bg-gray-100 dark:focus:bg-neutral-800 cursor-pointer">
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
