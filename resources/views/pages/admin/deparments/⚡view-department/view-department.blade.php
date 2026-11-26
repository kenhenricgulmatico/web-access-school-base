<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">

    {{-- Quick Nav --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('admin.users') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.users')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            Users
        </a>

        <a href="{{ route('admin.students') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.students')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/>
                <path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
            </svg>
            Account Approvals
        </a>

        <a href="{{ route('admin.roles') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.roles*')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            Roles &amp; Permissions
        </a>

        <a href="{{ route('admin.departments') }}"
            class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border
                {{ request()->routeIs('admin.departments*')
                    ? 'border-transparent bg-blue-600 text-white'
                    : 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
            <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9h.01M9 13h.01M9 17h.01"/>
            </svg>
            Departments
        </a>
    </div>

    {{-- Flash --}}
    @if(session()->has('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session()->has('error'))
        <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 border-b border-gray-200 dark:border-neutral-700">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Departments</h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">List of all school departments</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300 rounded-full">
                    {{ $this->departments->count() }} Total
                </span>

                @can('users.create')
                    <a href="{{ route('admin.departments.create') }}" wire:navigate
                       class="py-2 px-3.5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg
                              bg-blue-600 text-white hover:bg-blue-700 transition">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                        </svg>
                        Add Department
                    </a>
                @endcan
            </div>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-900/40">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400 w-16">#</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Department Name</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Members</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse($this->departments as $index => $dept)
                        <tr wire:key="dept-{{ $dept->id }}" class="hover:bg-gray-50 dark:hover:bg-neutral-700/40 transition">

                            {{-- # --}}
                            <td class="px-6 py-3.5 text-sm text-gray-500 dark:text-neutral-400">
                                {{ $index + 1 }}
                            </td>

                            {{-- Name --}}
                            <td class="px-6 py-3.5">
                                <span class="text-sm font-semibold text-gray-800 dark:text-neutral-200">
                                    {{ $dept->department_name }}
                                </span>
                            </td>

                            {{-- Members --}}
                            <td class="px-6 py-3.5">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-medium rounded-full
                                             bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300">
                                    {{ $dept->users_count }} {{ \Illuminate\Support\Str::plural('member', $dept->users_count) }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-3.5 text-right">
                                <div class="inline-flex items-center gap-3">
                                    <a href="#" wire:navigate
                                       class="text-sm font-medium text-gray-600 hover:underline dark:text-neutral-400">
                                        View
                                    </a>
                                    @can('users.update')
                                        <a href="{{ route('admin.departments.edit', $dept->id) }}" wire:navigate
                                           class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-500">
                                            Edit
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <div class="size-12 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center">
                                        <svg class="size-6 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9h.01M9 13h.01M9 17h.01"/>
                                        </svg>
                                    </div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No departments found</p>
                                    @can('users.create')
                                        <a href="{{ route('admin.departments.create') }}" wire:navigate
                                           class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                            <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
                                            </svg>
                                            Add First Department
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
</div>
