<div>
    <div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">
        <div class="max-w-full mx-auto">
            <div class="flex flex-col border border-gray-200 rounded-xl p-4 sm:p-6 lg:p-8 dark:border-neutral-700">

                {{-- Header with Back button --}}
                <div class="mb-8 flex items-center justify-between gap-x-2">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                            Create User
                        </h2>
                        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                            Add a program head or faculty account
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

                {{-- ✅ Only users with `users.create` can see & use this form --}}
                @can('users.create')
                <form wire:submit.prevent="save">
                    <div class="grid gap-4 lg:gap-6">

                        {{-- ✅ Department FIRST --}}
                        <div>
                            <label for="department-select" class="block mb-2 text-sm text-gray-700 font-medium dark:text-white">
                                Department <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                {{-- Building icon on the left --}}
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                    <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9v.01M9 12v.01M9 15v.01"/>
                                    </svg>
                                </div>

                                <select
                                    id="department-select"
                                    wire:model="department_id"
                                    class="py-2.5 ps-10 pe-10 block w-full border-gray-200 rounded-lg sm:text-sm appearance-none
                                           focus:border-[#1C6B45] focus:ring-[#1C6B45]
                                           dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300
                                           @error('department_id') border-red-300 dark:border-red-700 @enderror">
                                <option value="" disabled>Select Department</option>
                                    @foreach($this->departments as $department)
                                        <option value="{{ $department->id }}">
                                            {{ $department->department_name }}
                                        </option>
                                    @endforeach
                                </select>

                                {{-- Chevron icon on the right --}}
                                <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                    <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </div>
                            </div>

                            @error('department_id')
                                <span class="block text-red-600 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- ✅ Role — now a dropdown --}}
                        <div>
                            <label for="role-select" class="block mb-2 text-sm text-gray-700 font-medium dark:text-white">
                                Role <span class="text-red-500">*</span>
                            </label>

                            <div class="relative">
                                {{-- User icon on the left --}}
                                <div class="absolute inset-y-0 start-0 flex items-center ps-3.5 pointer-events-none">
                                    <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                                    </svg>
                                </div>

                                <select
                                    id="role-select"
                                    wire:model.live="role"
                                    class="py-2.5 ps-10 pe-10 block w-full border-gray-200 rounded-lg sm:text-sm appearance-none
                                           focus:border-[#1C6B45] focus:ring-[#1C6B45]
                                           dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-300
                                           @error('role') border-red-300 dark:border-red-700 @enderror">

                                    <option value="" disabled>Select Role</option>

                                    @foreach ($this->roles as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>

                                {{-- Chevron icon on the right --}}
                                <div class="absolute inset-y-0 end-0 flex items-center pe-3.5 pointer-events-none">
                                    <svg class="size-4 text-gray-400 dark:text-neutral-500" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6"/>
                                    </svg>
                                </div>
                            </div>

                            @error('role')
                                <span class="block text-red-600 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>

                        {{-- Name + Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                            <div>
                                <label class="block mb-2 text-sm text-gray-700 font-medium dark:text-white">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input wire:model.defer="name" type="text" placeholder="Juan Dela Cruz"
                                    class="py-2.5 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                                @error('name') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block mb-2 text-sm text-gray-700 font-medium dark:text-white">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <input wire:model.defer="email" type="email" placeholder="user@csav.edu.ph"
                                    class="py-2.5 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                                @error('email') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- Password + Confirm --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 lg:gap-6">
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <label class="text-sm text-gray-700 font-medium dark:text-white">
                                        Password <span class="text-red-500">*</span>
                                    </label>
                                    <button type="button"
                                        wire:click="useDefaultPassword"
                                        class="text-[11px] font-semibold text-[#1C6B45] hover:text-[#123524] dark:text-[#7FBF8E] dark:hover:text-[#9FD3AE] hover:underline">
                                        Use default ({{ $this->defaultPassword }})
                                    </button>
                                </div>
                                <div class="relative" x-data="{ show: false }">
                                    <input :type="show ? 'text' : 'password'" wire:model.defer="password"
                                        placeholder="Min. 6 characters"
                                        class="py-2.5 px-4 pr-12 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                                    <button type="button" @click="show = !show"
                                        class="absolute inset-y-0 right-0 px-3 flex items-center text-xs text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200"
                                        :aria-label="show ? 'Hide password' : 'Show password'">
                                        <span x-text="show ? 'Hide' : 'Show'"></span>
                                    </button>
                                </div>
                                @error('password') <span class="text-red-600 text-sm">{{ $message }}</span> @enderror
                            </div>

                            <div>
                                <label class="block mb-2 text-sm text-gray-700 font-medium dark:text-white">
                                    Confirm Password <span class="text-red-500">*</span>
                                </label>
                                <input type="password" wire:model.defer="password_confirmation"
                                    placeholder="Re-enter password"
                                    class="py-2.5 px-4 block w-full border-gray-200 rounded-lg sm:text-sm focus:border-[#1C6B45] focus:ring-[#1C6B45] dark:bg-neutral-900 dark:border-neutral-700 dark:text-neutral-400 dark:placeholder-neutral-500">
                            </div>
                        </div>

                        {{-- Info note --}}
                        <div class="flex items-start gap-2.5 rounded-lg border border-[#1C6B45/25] dark:border-[#1C6B45]/50 bg-[#F3F8F5] dark:bg-[#1C6B45]/20 px-3.5 py-3">
                            <svg class="size-4 shrink-0 mt-0.5 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" />
                                <path stroke-linecap="round" d="M12 16v-4m0-4h.01" />
                            </svg>
                            <p class="text-xs text-[#123524] dark:text-[#9FD3AE]">
                                The account will be created with status <strong>Approved</strong> and can log in immediately with the password you set.
                            </p>
                        </div>
                    </div>

                    {{-- Save Button --}}
                    <div class="mt-6 flex gap-3">
                        <button type="submit"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50 cursor-not-allowed"
                            class="py-3 px-6 inline-flex justify-center items-center gap-x-2 text-sm font-medium rounded-lg bg-[#123524] text-white hover:bg-[#0B2418] focus:outline-hidden transition">
                            <span wire:loading.remove wire:target="save">Create User</span>
                            <span wire:loading wire:target="save">Creating...</span>
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
