<div class="select-none" style="color-shema: ligh;">
    <div class="relative min-h-screen flex items-center justify-center overflow-hidden bg-[#FAF7EF] dark:bg-[#0E1A14] px-6 py-10" style="font-family: 'Inter', sans-serif;">

        <!-- Subtle grille pattern, echoes the building's iron railings -->
        <svg class="absolute inset-0 w-full h-full opacity-[0.04] pointer-events-none" xmlns="http://www.w3.org/2000/svg">
            <defs>
                <pattern id="grille" width="28" height="28" patternUnits="userSpaceOnUse">
                    <path d="M0 0 L0 28 M14 0 L14 28" stroke="#123524" stroke-width="1"/>
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#grille)"/>
        </svg>

        <div class="relative max-w-6xl w-full grid md:grid-cols-2 items-stretch shadow-2xl rounded-3xl overflow-hidden border border-[#E4DFCE] dark:border-[#1C3B2E]">

            <!-- Left: Diagonal photo panel -->
            <div class="relative hidden md:block bg-[#123524]">
                <img src="{{ asset('images/csav_building.jpg') }}"
                     alt="Colegio De Sta. Ana de Victorias, Inc. campus"
                     class="absolute inset-0 w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-[#0E1A14] via-[#0E1A14]/40 to-[#0E1A14]/10"></div>

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
                        <p class="text-sm text-white/75 max-w-xs">
                            Reserve facilities, request materials, and track approvals — one system for every faculty member and program head on campus.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mobile-only header -->
            <div class="md:hidden text-center pt-10 px-8">
                <img src="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png"
                     alt="CSAV logo"
                     class="w-14 h-14 object-contain mx-auto mb-3">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#1C6B45] dark:text-[#7FBF8E] mb-2">Est. Campus Portal</p>
                <h1 class="text-[#123524] dark:text-green-200 mb-1" style="font-family: 'Fraunces', serif; font-size: 1.9rem; font-weight: 700;">Colegio De Sta. Ana de Victorias, Inc.</h1>
            </div>

            <!-- Right: Login form -->
            <div class="relative bg-white dark:bg-[#16281F] p-10 sm:p-12 flex flex-col justify-center">

                <h2 class="text-[#123524] dark:text-green-200 mb-1" style="font-family: 'Fraunces', serif; font-size: 1.75rem; font-weight: 700;">Welcome back</h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mb-8">Sign in to continue to your portal</p>

                <form wire:submit.prevent="login" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-[#123524] dark:text-[#7FBF8E]">Email or Mobile</label>
                        <input type="email" id="email" name="email" wire:model='email'
                               class="mt-2 w-full px-4 py-3 rounded-lg border border-[#D8D4C8] focus:ring-2 focus:ring-[#1C6B45] focus:border-[#1C6B45] dark:bg-[#0E1A14] dark:border-[#2A4B3A] dark:text-green-100 transition"
                               placeholder="you@example.com" required>
                               @error('email') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div x-data="{ showPassword: false }">
                        <label for="password" class="block text-sm font-medium text-[#123524] dark:text-[#7FBF8E]">Password</label>
                        <div class="relative mt-2">
                            <input :type="showPassword ? 'text' : 'password'" id="password" name="password" wire:model='password'
                                   class="w-full pl-4 pr-11 py-3 rounded-lg border border-[#D8D4C8] focus:ring-2 focus:ring-[#1C6B45] focus:border-[#1C6B45] dark:bg-[#0E1A14] dark:border-[#2A4B3A] dark:text-green-100 transition"
                                   placeholder="••••••••" required>

                            <button type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 flex items-center pr-3 text-[#123524]/50 dark:text-green-300/50 hover:text-[#123524] dark:hover:text-green-200 transition"
                                    tabindex="-1">
                                <!-- Eye (show) icon -->
                                <svg x-show="!showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <!-- Eye-slash (hide) icon -->
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                                </svg>
                            </button>
                        </div>
                        @error('password') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-between text-sm">
                        <label class="flex items-center gap-2 text-[#123524] dark:text-green-300">
                            <input type="checkbox" class="rounded border-[#D8D4C8] text-[#1C6B45] focus:ring-[#1C6B45]">
                            Remember me
                        </label>
                        <a href="/forgot-password" class="text-[#B8862A] hover:text-[#966E22] font-medium">Forgot password?</a>
                    </div>

                    {{-- Terms --}}
                    <div class="flex items-start gap-2">
                        <input type="checkbox" wire:model="terms"
                               class="mt-1 rounded border-[#D8D4C8] text-[#1C6B45] focus:ring-[#1C6B45]">
                        <label class="text-sm text-gray-600 dark:text-neutral-300">
                            I agree to the
                            <a href="/privacy" class="text-[#B8862A] hover:text-[#966E22] font-medium">Privacy Policy</a>
                        </label>
                    </div>
                    @error('terms')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror

                    <!-- Submit -->
                    <button type="submit"
                            class="w-full px-6 py-3.5 rounded-lg bg-[#123524] text-white font-semibold shadow-lg hover:bg-[#0C2418] transition">
                        Log In
                    </button>
                </form>
            </div>
        </div>
    </div>
    {{-- I have not failed. I've just found 10,000 ways that won't work. - Thomas Edison --}}
</div>
