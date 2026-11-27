<div>
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
        <div class="max-w-full mx-auto">
            <div class="flex flex-col border border-gray-200 rounded-xl p-4 sm:p-6 lg:p-8 dark:border-neutral-700">

                {{-- Header --}}
                <div class="mb-8 flex items-center justify-between gap-x-2">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                            Edit User
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                            Update the account details or change the user's role
                        </p>
                    </div>

                    <a href="{{ route('admin.users') }}"
                        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 focus:outline-hidden dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m12 19-7-7 7-7"/>
                            <path d="M19 12H5"/>
                        </svg>
                        Back
                    </a>
                </div>

                @if (session('success'))
                    <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-[#1C6B45]/20 border border-green-200 dark:border-[#1C6B45/40] text-[#1C6B45] dark:text-green-400 text-sm font-medium">
                        {{ session('success') }}
                    </div>
                @endif

                {{-- User info strip --}}
                <div class="mb-6 p-4 rounded-xl border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-800/50 flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#0B2418] text-white flex items-center justify-center text-sm font-bold shrink-0">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 truncate">{{ $user->email }}</p>
                    </div>
                    @if ($this->isAdmin)
                        <span class="px-2.5 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 shrink-0">
                            Admin — read only
                        </span>
                    @endif
                </div>

                {{-- ✅ Only users with `users.update` can edit --}}
                @can('users.update')
                <form wire:submit.prevent="save">
                    <div class="grid gap-4 lg:gap-6">

                        {{-- ✅ Department (moved first, hidden for admin) --}}
                        @unless ($this->isAdmin)
                            <div>
                                <label for="department-select" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                                    Department <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                        <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9v.01M9 12v.01M9 15v.01"/>
                                        </svg>
                                    </div>

                                    <select id="department-select"
                                        wire:model.live="department_id"
                                        class="py-2.5 ps-10 pe-10 block w-full border-gray-200 rounded-lg sm:text-sm appearance-none
                                               focus:border-[#1C6B45] focus:ring-[#1C6B45]
                                               dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300
                                               @error('department_id') border-red-300 dark:border-red-700 @enderror">
                                        <option value="">Select Department</option>
                                        @foreach($this->departments as $department)
                                            <option value="{{ $department->id }}">{{ $department->department_name }}</option>
                                        @endforeach
                                    </select>

                                    <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                        <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                        </svg>
                                    </div>
                                </div>

                                @error('department_id') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endunless

                        {{-- ✅ Role dropdown (hidden for admin) --}}
                        @unless ($this->isAdmin)
                            <div>
                                <label for="role-select" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                                    Role <span class="text-red-500">*</span>
                                </label>

                                <div class="relative">
                                    <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                        <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                                        </svg>
                                    </div>

                                    <select id="role-select"
                                        wire:model.live="role"
                                        class="py-2.5 ps-10 pe-10 block w-full border-gray-200 rounded-lg sm:text-sm appearance-none
                                               focus:border-[#1C6B45] focus:ring-[#1C6B45]
                                               dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300
                                               @error('role') border-red-300 dark:border-red-700 @enderror">
                                        <option value="">Select Role</option>
                                        @foreach ($this->roles as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>

                                    <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                        <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                        </svg>
                                    </div>
                                </div>

                                @error('role') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                            </div>
                        @endunless

                        {{-- ✅ Changing role / department while requests or borrowed items are still open --}}
                        @if ($warning = $this->changeWarning())
                            <div class="flex items-start gap-2.5 p-3 rounded-lg border border-amber-200 bg-amber-50 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-300">
                                <svg class="size-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                                <span>{{ $warning }}</span>
                            </div>
                        @endif

                        {{-- Name + Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                            <div>
                                <label for="hs-name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="hs-name" wire:model="name"
                                    class="py-2.5 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="hs-email" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input type="email" id="hs-email" wire:model="email"
                                    class="py-2.5 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400">
                                @unless ($this->isAdmin)
                                    <p class="mt-1 text-xs text-gray-500 dark:text-neutral-400">Must end with @csav.edu.ph</p>
                                @endunless
                                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Password --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                            <div>
                                <label for="hs-password" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                                    New Password
                                </label>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" id="hs-password" wire:model="password"
                                        placeholder="Leave blank to keep current"
                                        class="py-2.5 px-4 pr-12 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-xs text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200">
                                        <span x-text="show ? 'Hide' : 'Show'"></span>
                                    </button>
                                </div>
                                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label for="hs-password-confirmation" class="block mb-2 text-sm font-medium text-gray-700 dark:text-white">
                                    Confirm New Password
                                </label>
                                <input type="password" id="hs-password-confirmation" wire:model="password_confirmation"
                                    placeholder="Re-enter new password"
                                    class="py-2.5 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                            </div>
                        </div>

                        {{-- Quick Password Reset --}}
                        @unless ($this->isAdmin)
                            @can('users.reset-password')
                                <div class="flex items-start gap-3 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                                    <svg class="size-5 shrink-0 mt-0.5 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-semibold text-amber-900 dark:text-amber-200">
                                            Quick Password Reset
                                        </p>
                                        <p class="text-xs text-amber-800 dark:text-amber-300 mt-1">
                                            Reset this user's password to the default value
                                            (<span class="font-mono font-semibold">{{ $this->defaultPassword }}</span>)
                                            and send them a reset notification.
                                        </p>
                                        <button type="button"
                                            wire:click="resetToDefaultPassword"
                                            wire:confirm="Reset this user's password to the default? An email will be sent to them."
                                            wire:loading.attr="disabled"
                                            wire:loading.class="opacity-50 cursor-not-allowed"
                                            class="mt-3 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-lg bg-amber-600 text-white hover:bg-amber-700 transition">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                                            </svg>
                                            <span wire:loading.remove wire:target="resetToDefaultPassword">Reset to Default Password</span>
                                            <span wire:loading wire:target="resetToDefaultPassword">Resetting...</span>
                                        </button>
                                    </div>
                                </div>
                            @endcan
                        @endunless

                        {{-- Info note --}}
                        <div class="flex items-start gap-2.5 rounded-lg border border-[#1C6B45/25] dark:border-[#1C6B45]/50 bg-[#F3F8F5] dark:bg-[#1C6B45]/20 px-3.5 py-3">
                            <svg class="size-4 shrink-0 mt-0.5 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path stroke-linecap="round" d="M12 16v-4m0-4h.01" />
                            </svg>
                            <p class="text-xs text-[#123524] dark:text-[#9FD3AE]">
                                Leave the password fields blank to keep the user's current password.
                            </p>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="py-3 px-6 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-[#123524] text-white hover:bg-[#0B2418] focus:outline-hidden transition">
                            <span wire:loading.remove wire:target="save">Save Changes</span>
                            <span wire:loading wire:target="save">Saving...</span>
                        </button>
                        <a href="{{ route('admin.users') }}"
                            class="py-3 px-6 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700 transition">
                            Cancel
                        </a>
                    </div>
                </form>
                @endcan
            </div>
        </div>
    </div>
</div>
