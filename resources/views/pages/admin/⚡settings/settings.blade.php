<div>
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

        <x-portal.page-header title="Settings" subtitle="Manage your account security" :back="route('admin.dashboard')" back-label="Dashboard" />

        {{-- Success message --}}
        @if (session()->has('password_message'))
            <div class="mb-6 flex items-center gap-2 p-3 sm:p-4 rounded-xl bg-green-50 dark:bg-green-500/10 text-[#123524] dark:text-green-300 border border-green-200 dark:border-green-500/30">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-xs sm:text-sm">{{ session('password_message') }}</span>
            </div>
        @endif

        <div class="bg-white dark:bg-neutral-900 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3.5 sm:px-6 sm:py-4 border-b border-gray-100 dark:border-neutral-800">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-white">Change password</h3>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">Update your account password</p>
            </div>

            <form wire:submit="updatePassword">
                <div class="p-4 sm:p-6 space-y-4 sm:space-y-5">
                    <div>
                        <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                            Current password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" wire:model="current_password"
                            class="w-full px-3 py-2.5 text-xs sm:text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#123524] focus:border-[#123524] dark:focus:ring-[#7FBF8E] dark:focus:border-[#7FBF8E] transition">
                        @error('current_password')
                            <p class="text-xs text-red-500 dark:text-red-400 mt-1.5">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                New password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" wire:model="new_password"
                                class="w-full px-3 py-2.5 text-xs sm:text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#123524] focus:border-[#123524] dark:focus:ring-[#7FBF8E] dark:focus:border-[#7FBF8E] transition">
                            @error('new_password')
                                <p class="text-xs text-red-500 dark:text-red-400 mt-1.5">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5">
                                Confirm new password <span class="text-red-500">*</span>
                            </label>
                            <input type="password" wire:model="new_password_confirmation"
                                class="w-full px-3 py-2.5 text-xs sm:text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-[#123524] focus:border-[#123524] dark:focus:ring-[#7FBF8E] dark:focus:border-[#7FBF8E] transition">
                        </div>
                    </div>
                </div>

                <div class="px-4 sm:px-6 pb-4 sm:pb-6 flex justify-end">
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="w-full sm:w-auto px-6 py-2.5 bg-[#123524] hover:bg-[#0B2418] dark:bg-[#1C6B45] dark:hover:bg-[#23855A] text-white rounded-lg font-medium text-sm shadow-sm transition text-center">
                        <span wire:loading.remove wire:target="updatePassword">Update Password</span>
                        <span wire:loading wire:target="updatePassword">Updating...</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
