<div class="select-none">
    <div class="max-w-3xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

        {{-- Back --}}
        <a href="{{ route('admin.roles') }}" wire:navigate
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-200 transition mb-6">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Roles
        </a>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm p-6 sm:p-8">

            {{-- Header --}}
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    Edit Role — <span class="text-blue-600 dark:text-blue-400">{{ ucwords($role->name) }}</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                    Update the role's name or change what it's allowed to do.
                </p>
            </div>

            {{-- Flash --}}
            @if (session('success'))
                <div class="mb-5 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="save" class="space-y-6">

                {{-- Role name --}}
                <div>
                    <label for="role-name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-neutral-300">
                        Role Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model.defer="name"
                        type="text"
                        id="role-name"
                        placeholder="e.g. registrar, librarian, staff"
                        class="w-full py-2.5 px-4 border border-gray-300 dark:border-neutral-600 rounded-lg text-sm
                               bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200
                               focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 focus:outline-none
                               transition">
                    @error('name')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Permissions --}}
                <div>
                    <div class="mb-3">
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-white">
                            What can this role do?
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                            Check each permission you want this role to have. Uncheck to remove.
                        </p>
                    </div>

                    @error('selectedPermissions')
                        <div class="mb-3 p-2.5 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                            <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
                        </div>
                    @enderror

                    @if ($this->permissions->isEmpty())
                        <p class="text-sm text-gray-400 dark:text-neutral-500">No permissions available.</p>
                    @else
                        @php
                            $grouped = [];
                            foreach ($this->permissions as $permission) {
                                $parts  = explode('.', $permission->name, 2);
                                $prefix = $parts[0] ?? 'other';
                                $grouped[$prefix][] = $permission;
                            }
                            ksort($grouped);

                            $groupTitles = [
                                'members'                => 'View Faculty',
                                'requests'               => 'Reservation & Request',
                                'facility-requests'      => 'Facility',       // ✅ new
                                'material-requests'      => 'Material',       // ✅ new
                                'program-head-requests'  => 'Request to Admin',
                                'stock-materials'        => 'Stock Materials',
                                'facility-schedule'      => 'Facility Schedule',
                                'users'                  => 'Users',
                                'allocations'            => 'Resource Allocation',
                                'audit'                  => 'Audit Logs',
                                'reports'                => 'Reports',
                            ];

                            $permissionLabels = [
                                // Generic requests
                                'requests.view-own'        => 'View Own',
                                'requests.create'          => 'Create',
                                'requests.update-own'      => 'Edit Own',
                                'requests.delete-own'      => 'Delete Own',
                                'requests.cancel-own'      => 'Cancel Own',
                                'requests.view-department' => 'View',
                                'requests.view-all'        => 'View All',
                                'requests.approve'         => 'Approve',
                                'requests.reject'          => 'Reject',

                                // Facility Requests
                                'facility-requests.view'       => 'View',
                                'facility-requests.view-all'   => 'View All',
                                'facility-requests.create'     => 'Create',
                                'facility-requests.update'     => 'Edit',
                                'facility-requests.cancel'     => 'Cancel',

                                // Material Requests
                                'material-requests.view'       => 'View',
                                'material-requests.view-all'   => 'View All',
                                'material-requests.create'     => 'Create',
                                'material-requests.update'     => 'Edit',
                                'material-requests.cancel'     => 'Cancel',

                                // Members
                                'members.view'             => 'View',
                                'members.create'           => 'Create',
                                'members.update'           => 'Edit',
                                'members.delete'           => 'Delete',

                                // Program Head Requests
                                'program-head-requests.view'       => 'View',
                                'program-head-requests.view-all'   => 'View All',
                                'program-head-requests.create'     => 'Create',
                                'program-head-requests.update'     => 'Edit',
                                'program-head-requests.delete'     => 'Delete',
                                'program-head-requests.approve'    => 'Approve',
                                'program-head-requests.reject'     => 'Reject',

                                // Stock Materials
                                'stock-materials.view'       => 'View',
                                'stock-materials.view-all'   => 'View All',
                                'stock-materials.create'     => 'Create',
                                'stock-materials.update'     => 'Edit',
                                'stock-materials.delete'     => 'Delete',

                                // Facility Schedule
                                'facility-schedule.view'     => 'View',
                                'facility-schedule.view-all' => 'View All',
                                'facility-schedule.create'   => 'Create',
                                'facility-schedule.update'   => 'Edit',
                                'facility-schedule.delete'   => 'Delete',

                                // Users
                                'users.view'                 => 'View',
                                'users.create'               => 'Create',
                                'users.update'               => 'Edit',
                                'users.delete'               => 'Delete',
                                'users.approve'              => 'Approve',
                                'users.reset-password'       => 'Reset Password',
                            ];
                        @endphp

                        <div class="space-y-5">
                            @foreach ($grouped as $group => $permissions)
                                <div class="border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden">

                                    <div class="px-4 py-2 bg-gray-50 dark:bg-neutral-900/60 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-3">
                                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-600 dark:text-neutral-300">
                                            {{ $groupTitles[$group] ?? \Illuminate\Support\Str::headline($group) }}
                                        </p>
                                        @php
                                            $groupNames = collect($permissions)->pluck('name');
                                            $selectedInGroup = collect($selectedPermissions)->intersect($groupNames)->count();
                                        @endphp
                                        <span class="text-[10px] font-medium text-gray-400 dark:text-neutral-500">
                                            {{ $selectedInGroup }} / {{ count($permissions) }}
                                        </span>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-0 divide-y sm:divide-y-0 sm:divide-x divide-gray-100 dark:divide-neutral-700">
                                        @foreach ($permissions as $permission)
                                            <label for="perm-{{ $permission->id }}"
                                                class="flex items-start gap-3 p-3 cursor-pointer
                                                       hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition
                                                       border-b border-gray-100 dark:border-neutral-700 last:border-b-0 sm:border-b-0 sm:[&:nth-last-child(-n+2)]:border-b-0">
                                                <input
                                                    type="checkbox"
                                                    id="perm-{{ $permission->id }}"
                                                    wire:model="selectedPermissions"
                                                    value="{{ $permission->name }}"
                                                    class="mt-0.5 shrink-0 size-4 rounded border-gray-300 text-blue-600
                                                           focus:ring-2 focus:ring-blue-500/30 focus:ring-offset-0
                                                           dark:bg-neutral-800 dark:border-neutral-600
                                                           dark:checked:bg-blue-600 dark:checked:border-blue-600">
                                                <span class="text-sm text-gray-700 dark:text-neutral-300 leading-snug">
                                                    {{ $permissionLabels[$permission->name] ?? \Illuminate\Support\Str::headline(str_replace('.', ' ', $permission->name)) }}
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-neutral-700">
                    <a href="{{ route('admin.roles') }}" wire:navigate
                       class="px-5 py-2.5 text-sm font-medium rounded-lg
                              border border-gray-300 dark:border-neutral-600
                              bg-white dark:bg-neutral-800 text-gray-700 dark:text-neutral-300
                              hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                        Cancel
                    </a>
                    <button type="submit"
                        wire:loading.attr="disabled"
                        wire:loading.class="opacity-50 cursor-not-allowed"
                        class="px-5 py-2.5 text-sm font-medium rounded-lg
                               bg-blue-600 text-white hover:bg-blue-700
                               focus:outline-none focus:ring-2 focus:ring-blue-500/30
                               disabled:opacity-50 disabled:pointer-events-none transition">
                        <span wire:loading.remove wire:target="save">Save Changes</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
