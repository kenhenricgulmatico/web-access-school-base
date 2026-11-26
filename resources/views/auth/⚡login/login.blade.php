<div class="select-none">
    <div class="relative min-h-screen flex items-center justify-center overflow-hidden bg-[#FAF7EF] dark:bg-[#0E1A14] px-4 sm:px-6 py-8 sm:py-10" style="font-family: 'Inter', sans-serif;">

        <!-- Subtle grille pattern, echoes the building's iron railings -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.04] pointer-events-none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <defs>
                <pattern id="grille" width="28" height="28" patternUnits="userSpaceOnUse">
                    <path d="M0 0 L0 28 M14 0 L14 28" stroke="#123524" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grille)"/>
        </svg>

        <!-- Soft gold glow behind the card -->
        <div class="absolute -top-32 -right-32 size-[28rem] rounded-full bg-[#D4A537]/10 blur-3xl pointer-events-none" aria-hidden="true"></div>
        <div class="absolute -bottom-40 -left-32 size-[30rem] rounded-full bg-[#1C6B45]/10 blur-3xl pointer-events-none" aria-hidden="true"></div>

        <div class="relative max-w-5xl w-full grid md:grid-cols-2 items-stretch bg-white dark:bg-[#16281F] shadow-2xl shadow-[#123524]/10 rounded-3xl overflow-hidden border border-[#E4DFCE] dark:border-[#1C3B2E]">

            <!-- Left: photo panel (tablet / desktop) -->
            <div class="relative hidden md:block min-h-[620px] bg-[#123524]">
                <img src="{{ asset('images/csav_building.jpg') }}"
                     alt="Colegio De Sta. Ana de Victorias, Inc. campus"
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0E1A14] via-[#0E1A14]/55 to-[#0E1A14]/20"></div>

                <div class="relative h-full flex flex-col justify-between p-10">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 shrink-0 rounded-full bg-white ring-2 ring-[#D4A537]/50 p-1 shadow-lg">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png"
                                 alt="CSAV logo"
                                 class="w-full h-full object-contain">
                        </div>
                        <span class="text-white font-semibold leading-tight drop-shadow-md" style="font-family: 'Fraunces', serif; font-size: 1.1rem;">
                            Colegio De Sta. Ana<br>de Victorias, Inc.
                        </span>
                    </div>

                    <div>
                        <span class="inline-block mb-4 h-1 w-12 rounded-full bg-[#D4A537]"></span>
                        <h2 class="text-white leading-tight mb-3 drop-shadow-md" style="font-family: 'Fraunces', serif; font-size: 2rem; font-weight: 700;">
                            Campus Resource Portal
                        </h2>
                        <p class="text-sm text-white/80 max-w-sm mb-6">
                            Reserve facilities, request materials, and track approvals — one system for every faculty member and program head on campus.
                        </p>

                        <ul class="space-y-2.5 text-sm text-white/90">
                            <li class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center size-7 rounded-full bg-white/10 ring-1 ring-white/20 backdrop-blur-sm">
                                    <svg class="size-4 text-[#E8C877]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                                </span>
                                Book rooms and facilities
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center size-7 rounded-full bg-white/10 ring-1 ring-white/20 backdrop-blur-sm">
                                    <svg class="size-4 text-[#E8C877]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z"/></svg>
                                </span>
                                Request and borrow materials
                            </li>
                            <li class="flex items-center gap-2.5">
                                <span class="flex items-center justify-center size-7 rounded-full bg-white/10 ring-1 ring-white/20 backdrop-blur-sm">
                                    <svg class="size-4 text-[#E8C877]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                </span>
                                Follow every approval in real time
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Mobile: same photo as a banner, with the logo on top -->
            <div class="md:hidden relative h-44 bg-[#123524]">
                <img src="{{ asset('images/csav_building.jpg') }}"
                     alt="Colegio De Sta. Ana de Victorias, Inc. campus"
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0E1A14]/90 via-[#0E1A14]/40 to-transparent"></div>
                <div class="relative h-full flex items-end gap-3 p-5">
                    <div class="w-12 h-12 shrink-0 rounded-full bg-white ring-2 ring-[#D4A537]/50 p-1 shadow-lg">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png"
                             alt="CSAV logo"
                             class="w-full h-full object-contain">
                    </div>
                    <span class="text-white font-semibold leading-tight drop-shadow-md" style="font-family: 'Fraunces', serif; font-size: 1.05rem;">
                        Colegio De Sta. Ana<br>de Victorias, Inc.
                    </span>
                </div>
            </div>

            <!-- Right: Login form -->
            <div class="relative p-7 sm:p-12 flex flex-col justify-center">

                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-[#B8862A] mb-2">Campus Portal</p>
                <h1 class="text-[#123524] dark:text-green-200 mb-1" style="font-family: 'Fraunces', serif; font-size: 1.9rem; font-weight: 700;">Welcome back</h1>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mb-8">Sign in with your school email to continue.</p>

                <form wire:submit.prevent="login" class="space-y-5" novalidate>
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#123524] dark:text-[#7FBF8E]">Email</label>
                        <div class="relative mt-2">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#123524]/40 dark:text-green-300/40 pointer-events-none">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                            </span>
                            <input type="email" id="email" name="email" wire:model="email" autocomplete="username" autofocus
                                   class="w-full pl-11 pr-4 py-3 rounded-xl border bg-[#FCFBF7] dark:bg-[#0E1A14] dark:text-green-100 focus:bg-white focus:ring-2 focus:ring-[#1C6B45]/30 focus:border-[#1C6B45] transition
                                          @error('email') border-red-400 dark:border-red-500 @else border-[#D8D4C8] dark:border-[#2A4B3A] @enderror"
                                   placeholder="you@csav.edu.ph" required
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                        @error('email') <p id="email-error" class="flex items-center gap-1.5 text-red-600 dark:text-red-400 text-sm mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Password -->
                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-[#123524] dark:text-[#7FBF8E]">Password</label>
                        <div class="relative mt-2">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-[#123524]/40 dark:text-green-300/40 pointer-events-none">
                                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" type="password" id="password" name="password" wire:model="password" autocomplete="current-password"
                                   class="w-full pl-11 pr-11 py-3 rounded-xl border bg-[#FCFBF7] dark:bg-[#0E1A14] dark:text-green-100 focus:bg-white focus:ring-2 focus:ring-[#1C6B45]/30 focus:border-[#1C6B45] transition
                                          @error('password') border-red-400 dark:border-red-500 @else border-[#D8D4C8] dark:border-[#2A4B3A] @enderror"
                                   placeholder="••••••••" required
                                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>

                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    :aria-label="showPassword ? 'Hide password' : 'Show password'"
                                    aria-label="Show password"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-[#123524]/50 dark:text-green-300/50 hover:text-[#123524] dark:hover:text-green-200 transition">
                                <!-- Eye (show) icon -->
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <!-- Eye-slash (hide) icon -->
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <p id="password-error" class="text-red-600 dark:text-red-400 text-sm mt-1.5">{{ $message }}</p> @enderror
                    </div>

                    <!-- Remember me / Forgot password -->
                    <div class="flex items-center justify-between gap-3 text-sm">
                        <label for="remember" class="flex items-center gap-2 text-[#123524] dark:text-green-300 cursor-pointer">
                            <input type="checkbox" id="remember" wire:model="remember"
                                   class="rounded border-[#D8D4C8] text-[#1C6B45] focus:ring-[#1C6B45]">
                            Remember me
                        </label>
                        <a href="/forgot-password" class="text-[#B8862A] hover:text-[#966E22] font-medium">Forgot password?</a>
                    </div>

                    {{-- Privacy Policy --}}
                    <div>
                        <div class="flex items-start gap-2.5 p-3 rounded-xl bg-[#FAF7EF] dark:bg-[#0E1A14] border @error('terms') border-red-300 dark:border-red-500/60 @else border-[#EFEADB] dark:border-[#2A4B3A] @enderror">
                            <input type="checkbox" id="terms" wire:model="terms"
                                   class="mt-0.5 rounded border-[#D8D4C8] text-[#1C6B45] focus:ring-[#1C6B45]">
                            <label for="terms" class="text-sm text-gray-600 dark:text-neutral-300 cursor-pointer">
                                I agree to the
                                <a href="/privacy" class="text-[#B8862A] hover:text-[#966E22] font-medium underline-offset-2 hover:underline">Privacy Policy</a>
                            </label>
                        </div>
                        @error('terms')
                            <p class="text-red-600 dark:text-red-400 text-sm mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Submit -->
                    <button type="submit"
                            wire:loading.attr="disabled" wire:target="login"
                            class="group w-full inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-xl bg-[#123524] text-white font-semibold shadow-lg shadow-[#123524]/20 hover:bg-[#0C2418] focus:outline-none focus-visible:ring-2 focus-visible:ring-offset-2 focus-visible:ring-[#D4A537] disabled:opacity-70 disabled:cursor-wait transition">
                        <span wire:loading.remove wire:target="login" class="inline-flex items-center gap-2">
                            Log In
                            <svg class="size-4 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                        <span wire:loading.flex wire:target="login" class="items-center gap-2">
                            <svg class="size-4 animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4z"/></svg>
                            Signing in…
                        </span>
                    </button>
                </form>

                <p class="mt-8 text-center text-xs text-gray-400 dark:text-neutral-500">
                    © {{ now()->year }} Colegio De Sta. Ana de Victorias, Inc.
                </p>
            </div>
        </div>
    </div>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
</div>
