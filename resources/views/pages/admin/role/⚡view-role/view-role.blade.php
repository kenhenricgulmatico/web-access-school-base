<div class="select-none">
    <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">

        @include('partials.users-tabs')

        <!-- Card -->
        <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">

            <!-- Header -->
            <div class="px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 border-b border-gray-200 dark:border-neutral-700">
                <div class="min-w-0">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">
                        Role &amp; Permissions
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">
                        Manage role and permissions
                    </p>

                    @if(session('success'))
                        <div class="mt-2 text-green-600 dark:text-green-400 text-sm font-medium">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mt-2 text-red-600 dark:text-red-400 text-sm font-medium">
                            {{ session('error') }}
                        </div>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2 shrink-0">
                    <button type="button"
                            wire:click="deleteSelected"
                            wire:confirm="Are you sure you want to delete selected roles?"
                            class="py-2 px-3.5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg
                                   border border-transparent bg-red-600 text-white
                                   hover:bg-red-700 focus:outline-none focus:bg-red-700
                                   disabled:opacity-50 disabled:pointer-events-none transition">
                        <svg class="shrink-0 size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete Selected
                    </button>

                    <a href="{{ route('admin.roles.create') }}"
                       wire:navigate
                       class="py-2 px-3.5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg
                              border border-transparent bg-blue-600 text-white
                              hover:bg-blue-700 focus:outline-none focus:bg-blue-700 transition">
                        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 5v14" />
                            <path d="M5 12h14" />
                        </svg>
                        Add Role
                    </a>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                    <thead class="bg-gray-50 dark:bg-neutral-900/50">
                        <tr>
                            {{-- Checkbox --}}
                            <th scope="col" class="w-12 ps-6 py-3 text-start">
                                <label for="roles-select-all" class="flex items-center">
                                    <input type="checkbox"
                                           wire:model.live="selectAll"
                                           id="roles-select-all"
                                           class="shrink-0 size-4 border-gray-300 rounded text-blue-600
                                                  focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-0
                                                  dark:bg-neutral-800 dark:border-neutral-600
                                                  dark:checked:bg-blue-600 dark:checked:border-blue-600">
                                    <span class="sr-only">Select all roles</span>
                                </label>
                            </th>

                            {{-- Role --}}
                            <th scope="col" class="px-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">
                                    Role
                                </span>
                            </th>

                            {{-- Created at --}}
                            <th scope="col" class="px-6 py-3 text-start">
                                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">
                                    Created at
                                </span>
                            </th>

                            {{-- Actions --}}
                            <th scope="col" class="px-6 py-3 text-end">
                                <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">
                                    Actions
                                </span>
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                        @forelse ($this->roles as $role)
                            <tr wire:key="role-{{ $role->id }}"
                                class="hover:bg-gray-50 dark:hover:bg-neutral-700/40 transition">

                                {{-- Checkbox --}}
                                <td class="ps-6 py-3.5 w-12">
                                    <label for="role-checkbox-{{ $role->id }}" class="flex items-center">
                                        <input type="checkbox"
                                               value="{{ $role->id }}"
                                               wire:model.live="selectedRoles"
                                               id="role-checkbox-{{ $role->id }}"
                                               class="shrink-0 size-4 border-gray-300 rounded text-blue-600
                                                      focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-0
                                                      dark:bg-neutral-800 dark:border-neutral-600
                                                      dark:checked:bg-blue-600 dark:checked:border-blue-600">
                                        <span class="sr-only">Select {{ $role->name }}</span>
                                    </label>
                                </td>

                                {{-- Role --}}
                                <td class="px-6 py-3.5">
                                    <span class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                        {{ $role->name }}
                                    </span>
                                </td>

                                {{-- Created at --}}
                                <td class="px-6 py-3.5">
                                    <span class="text-sm text-gray-500 dark:text-neutral-400">
                                        {{ $role->created_at->diffForHumans() }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="px-6 py-3.5 text-end">
                                    <div class="inline-flex items-center gap-3">
                                        <a href="{{ route('admin.roles.view', $role->id) }}"
                                           wire:navigate
                                           class="text-sm font-medium text-gray-600 hover:text-gray-900 hover:underline
                                                  dark:text-neutral-400 dark:hover:text-neutral-100">
                                            View
                                        </a>
                                        <a href="{{ route('admin.roles.edit', $role->id) }}"
                                           wire:navigate
                                           class="text-sm font-medium text-blue-600 hover:text-blue-800 hover:underline
                                                  dark:text-blue-400 dark:hover:text-blue-300">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-12 text-center">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="size-12 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center">
                                            <svg class="size-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-medium text-gray-600 dark:text-neutral-400">
                                            No roles found
                                        </p>
                                        <a href="{{ route('admin.roles.create') }}"
                                           wire:navigate
                                           class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                            </svg>
                                            Add First Role
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

        </div>
        <!-- End Card -->
    </div>
    {{-- If you do not have a consistent goal in life, you can not live it in a consistent way. - Marcus Aurelius --}}
</div>
