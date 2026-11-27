<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

    @include('partials.users-tabs')

    {{-- Flash --}}
    @if(session()->has('success'))
        <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-[#1C6B45]/20 border border-green-200 dark:border-[#1C6B45/40] text-[#1C6B45] dark:text-green-400 text-sm">
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
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Departments</h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">List of all school departments</p>
            </div>

            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-xs font-medium bg-green-100 text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-green-300 rounded-full">
                    {{ $this->departments->count() }} Total
                </span>

                @can('users.create')
                    <a href="{{ route('admin.departments.create') }}" wire:navigate
                       class="py-2 px-3.5 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg
                              bg-[#123524] text-white hover:bg-[#0B2418] transition">
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
                                             bg-[#E8F3EC] text-[#1C6B45] dark:bg-[#1C6B45]/30 dark:text-[#9FD3AE]">
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
                                           class="text-sm font-medium text-[#1C6B45] hover:underline dark:text-[#1C6B45]">
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
                                           class="mt-2 inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition">
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
