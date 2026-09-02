<div class="select-none">
<!-- ========== HEADER ========== -->
<header class="fixed top-0 inset-x-0 flex flex-wrap md:justify-start md:flex-nowrap z-48 lg:z-61 w-full bg-navbar-2 text-sm">
    <nav class="px-3 sm:px-5 flex basis-full items-center w-full mx-auto py-2.5">
        <div class="w-full flex items-center gap-x-1.5">

            {{-- Left: Logo + Sidebar Toggle + Dept Badge --}}
            <ul class="flex items-center gap-1.5 sm:gap-2.5 min-w-0">
                <li class="inline-flex items-center gap-1.5 sm:gap-2.5 relative pe-1.5 sm:pe-2.5 last:pe-0 last:after:hidden after:absolute after:top-1/2 after:end-0 after:inline-block after:w-px after:h-3.5 after:bg-navbar-2-divider after:rounded-full after:-translate-y-1/2 after:rotate-12">

                    {{-- Logo --}}
                    <a href="/admin/dashboard"
                        class="shrink-0 inline-flex justify-center items-center rounded-full bg-white ring-2 ring-[#D4A537]/60 p-0.5 sm:p-1 shadow-sm hover:ring-[#D4A537] transition focus:outline-hidden focus:opacity-80"
                        aria-label="CSAV">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png"
                            alt="CSAV Logo" class="h-5 w-5 sm:h-6 sm:w-6 object-contain" />
                    </a>

                    {{-- School Name — hidden on mobile, short on tablet, full on desktop --}}
                    <div class="hidden sm:flex flex-col leading-tight ms-0.5 min-w-0">
                        <span class="text-xs sm:text-sm font-semibold text-foreground truncate max-w-[140px] md:max-w-none"
                            style="font-family: 'Fraunces', serif;">
                            <span class="hidden md:inline">Colegio de Sta. Ana de Victorias</span>
                            <span class="md:hidden">CSAV</span>
                        </span>
                        <span class="text-[9px] sm:text-[10px] uppercase tracking-[0.15em] text-[#D4A537] font-medium">
                            Campus Portal
                        </span>
                    </div>

                    {{-- Sidebar Toggle --}}
                    <button type="button"
                        class="p-1.5 size-7 sm:size-7.5 inline-flex items-center gap-x-1 text-xs rounded-md border border-transparent text-foreground hover:bg-surface-hover disabled:opacity-50 disabled:pointer-events-none focus:outline-hidden focus:bg-surface-focus ms-0.5 sm:ms-1"
                        aria-haspopup="dialog" aria-expanded="false" aria-controls="hs-pro-sidebar"
                        data-hs-overlay="#hs-pro-sidebar">
                        <svg class="shrink-0 size-3 sm:size-3.5" xmlns="http://www.w3.org/2000/svg" width="24"
                            height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <rect width="18" height="18" x="3" y="3" rx="2" />
                            <path d="M15 3v18" />
                            <path d="m10 15-3-3 3-3" />
                        </svg>
                        <span class="sr-only">Sidebar Toggle</span>
                    </button>

                </li>

                {{-- Department Badge — hidden on mobile, shown on md+ --}}
                @if(Auth::user()->department)
                    <li class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 bg-[#123524]/5 border border-[#123524]/10 rounded-full">
                        <svg class="size-3 sm:size-3.5 text-[#123524]/60 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/>
                        </svg>
                        <span class="text-xs font-medium text-[#123524] truncate max-w-[120px] lg:max-w-none">
                            {{ Auth::user()->department->department_name }}
                        </span>
                    </li>
                @endif
            </ul>

            {{-- Right: Notifications + User Dropdown --}}
            <ul class="flex flex-row items-center gap-x-1 sm:gap-x-2 ms-auto shrink-0">

                {{-- Notification Bell --}}
                <li class="inline-flex items-center">
                    <a href="/admin/notifications"
                        class="relative flex justify-center items-center size-8 sm:size-9 text-sm text-navbar-2-nav-foreground rounded-full hover:bg-navbar-2-nav-hover focus:outline-hidden focus:bg-navbar-2-nav-focus transition">
                        <svg class="shrink-0 size-4 sm:size-4.5" xmlns="http://www.w3.org/2000/svg" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9" />
                            <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                        </svg>
                        @php
                            $unread = \App\Models\Notification::where('status', 'unread')->count();
                        @endphp
                        @if($unread > 0)
                            <span class="absolute top-0.5 end-0.5 flex items-center justify-center min-w-[16px] h-4 px-1 text-[9px] sm:text-[10px] font-bold text-white bg-[#B8352A] rounded-full ring-2 ring-white dark:ring-[#16281F] animate-pulse">
                                {{ $unread > 9 ? '9+' : $unread }}
                            </span>
                        @endif
                    </a>
                </li>

                {{-- User Dropdown --}}
                <li class="inline-flex items-center">
                    <div class="hs-dropdown inline-flex [--strategy:absolute] [--auto-close:inside] [--placement:bottom-right] relative text-start">

                        {{-- Avatar Button --}}
                        <button id="hs-admin-user-dropdown" type="button"
                            class="p-0.5 inline-flex shrink-0 items-center gap-x-1.5 sm:gap-x-2 text-start rounded-full hover:bg-navbar-nav-hover focus:outline-hidden focus:bg-navbar-nav-focus"
                            aria-haspopup="menu" aria-expanded="false" aria-label="User Dropdown">

                            {{-- Avatar circle --}}
                            <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-xs sm:text-sm font-bold ring-2 ring-[#D4A537]/50 shrink-0">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>

                            {{-- Name — hidden on mobile --}}
                            <div class="hidden sm:block text-left">
                                <span class="text-xs font-medium text-foreground truncate max-w-[100px] lg:max-w-[160px] block">
                                    {{ Auth::user()->name }}
                                </span>
                            </div>

                            <svg class="shrink-0 size-3 text-muted-foreground-1 hidden sm:block"
                                xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor"
                                stroke-width="2" viewBox="0 0 24 24">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                        </button>

                        {{-- Dropdown Menu --}}
                        <div class="hs-dropdown-menu hs-dropdown-open:opacity-100 w-60 sm:w-64 transition-[opacity,margin] duration opacity-0 hidden z-20 bg-gray-50 border border-dropdown-line rounded-xl shadow-xl overflow-hidden"
                            role="menu" aria-orientation="vertical" aria-labelledby="hs-admin-user-dropdown">

                            {{-- User Info --}}
                            <div class="py-3 px-3.5 border-b border-dropdown-divider bg-gradient-to-br from-[#123524]/[0.04] to-[#D4A537]/[0.04]">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold ring-2 ring-[#D4A537]/50 shrink-0">
                                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-foreground truncate">{{ Auth::user()->name }}</p>
                                        <p class="text-xs text-muted-foreground-1 truncate">{{ Auth::user()->email }}</p>
                                        <p class="text-[10px] uppercase tracking-wide text-[#B8862A] font-semibold mt-0.5">
                                            {{ Auth::user()->roles->first()->name ?? 'No Role' }}
                                        </p>
                                        {{-- Show dept in dropdown on mobile since badge is hidden --}}
                                        @if(Auth::user()->department)
                                            <p class="text-[10px] text-muted-foreground-1 mt-0.5 md:hidden">
                                                {{ Auth::user()->department->department_name }}
                                            </p>
                                        @endif
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
                                                <circle cx="12" cy="12" r="4" />
                                                <path d="M12 3v1M12 20v1M3 12h1M20 12h1m-2.636-6.364-.707.707M6.343 17.657l-.707.707M5.636 5.636l.707.707m12.021 12.021.707.707" />
                                            </svg>
                                            <span class="sr-only">Light</span>
                                        </button>
                                        <button type="button"
                                            class="size-7 flex justify-center items-center text-layer-foreground rounded-full hs-dark-mode-active:bg-secondary-active hs-dark-mode-active:text-secondary-foreground hs-dark-mode-active:shadow-sm"
                                            data-hs-theme-click-value="dark">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                                            </svg>
                                            <span class="sr-only">Dark</span>
                                        </button>
                                        <button type="button"
                                            class="size-7 flex justify-center items-center text-layer-foreground rounded-full hs-auto-light-mode-active:bg-layer hs-auto-mode-active:shadow-sm"
                                            data-hs-theme-click-value="auto">
                                            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <rect width="20" height="14" x="2" y="3" rx="2" />
                                                <line x1="8" x2="16" y1="21" y2="21" />
                                                <line x1="12" x2="12" y1="17" y2="21" />
                                            </svg>
                                            <span class="sr-only">Auto</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Menu Items --}}
                            <div class="p-1">
                                <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus"
                                    href="/admin/profile">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                        <circle cx="12" cy="7" r="4" />
                                    </svg>
                                    Profile
                                </a>
                                <a class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-dropdown-item-foreground hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus"
                                    href="/admin/settings">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                                        <circle cx="12" cy="12" r="3" />
                                    </svg>
                                    Settings
                                </a>

                                {{-- Logout --}}
                                <div class="flex items-center gap-x-3 py-2 px-3 rounded-lg text-sm text-red-600 hover:bg-dropdown-item-hover focus:outline-hidden focus:bg-dropdown-item-focus cursor-pointer">
                                    <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                        <polyline points="16 17 21 12 16 7" />
                                        <line x1="21" y1="12" x2="9" y2="12" />
                                    </svg>
                                    <livewire:auth::logout />
                                </div>
                            </div>

                        </div>
                        {{-- End Dropdown Menu --}}

                    </div>
                </li>

            </ul>
        </div>
    </nav>
</header>
<!-- ========== END HEADER ========== -->
</div>
