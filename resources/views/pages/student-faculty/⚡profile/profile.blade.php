<div class="select-none">
    <div class="max-w-3xl mx-auto px-3 py-6 sm:px-6 lg:px-8 lg:py-14">

        {{-- Profile Header Card --}}
        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm p-4 sm:p-6 mb-6 flex items-center gap-4 text-left">
            <div class="w-16 h-16 rounded-full bg-green-700 text-white flex items-center justify-center text-2xl font-semibold shrink-0">
                {{ strtoupper(substr($name, 0, 1)) }}
            </div>
            <div>
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-neutral-200">{{ $name }}</h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">{{ ucfirst($role) }} · {{ $department }}</p>
            </div>
        </div>

        {{-- Success message --}}
        @if (session()->has('message'))
            <div class="mb-6 flex items-center gap-2 p-3 sm:p-4 rounded-xl bg-green-50 text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-xs sm:text-sm">{{ session('message') }}</span>
            </div>
        @endif

        <form wire:submit="updateProfile" class="space-y-6">

            {{-- Personal Info Section (now fully read-only) --}}
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3.5 sm:px-6 sm:py-4 border-b border-gray-100 dark:border-neutral-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Personal information</h3>
                    <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">Managed by the registrar and can't be edited here</p>
                </div>

                <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                    {{-- Name --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                            Full name
                        </label>
                        <div class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 text-gray-500 dark:text-gray-400 text-xs sm:text-sm flex items-center justify-between">
                            <span class="truncate pr-2">{{ $name }}</span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="10" rx="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                            Email address
                        </label>
                        <div class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 text-gray-500 dark:text-gray-400 text-xs sm:text-sm flex items-center justify-between">
                            <span class="truncate pr-2">{{ $email }}</span>
                            <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="10" rx="2"/>
                                <path d="M7 11V7a5 5 0 0110 0v4"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Department + Role (read-only, responsive grid) --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1 sm:pt-2">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                Department
                            </label>
                            <div class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 text-gray-500 dark:text-gray-400 text-xs sm:text-sm flex items-center justify-between">
                                <span class="truncate pr-2">{{ $department }}</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="10" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                Role
                            </label>
                            <div class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 text-gray-500 dark:text-gray-400 text-xs sm:text-sm flex items-center justify-between">
                                <span class="truncate pr-2">{{ ucfirst($role) }}</span>
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <rect x="3" y="11" width="18" height="10" rx="2"/>
                                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Change Password Section --}}
            <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
                <div class="px-4 py-3.5 sm:px-6 sm:py-4 border-b border-gray-100 dark:border-neutral-700">
                    <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Change password</h3>
                    <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">Leave blank if you don't want to change it</p>
                </div>

                <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                            Current password
                        </label>
                        <input type="password" wire:model="current_password"
                            class="w-full px-3 py-2.5 text-xs sm:text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-green-600 focus:border-green-600 transition">
                        @error('current_password')
                            <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                New password
                            </label>
                            <input type="password" wire:model="new_password"
                                class="w-full px-3 py-2.5 text-xs sm:text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-green-600 focus:border-green-600 transition">
                            @error('new_password')
                                <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                Confirm new password
                            </label>
                            <input type="password" wire:model="new_password_confirmation"
                                class="w-full px-3 py-2.5 text-xs sm:text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-green-600 focus:border-green-600 transition">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-end pt-2">
                <button type="submit"
                    class="w-full sm:w-auto px-6 py-2.5 bg-green-700 hover:bg-green-800 text-white rounded-lg font-medium text-sm shadow-sm transition text-center">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</div>
