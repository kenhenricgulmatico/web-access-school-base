<div>
<div class="max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-6">

    {{-- Back --}}
    <a href="{{ route('admin.roles') }}" wire:navigate
       class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-200 transition">
        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Roles
    </a>

    {{-- Flash --}}
    @if (session('success'))
        <div class="p-3 rounded-lg bg-green-50 dark:bg-[#1C6B45]/20 border border-green-200 dark:border-[#1C6B45/40] text-[#1C6B45] dark:text-green-400 text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if (session('error'))
        <div class="p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Role header --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">Role</p>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">
                    {{ ucwords($role->name) }}
                </h1>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                    Can do <strong>{{ $role->permissions->count() }}</strong> things ·
                    Assigned to <strong>{{ $this->usersCount }}</strong> {{ \Illuminate\Support\Str::plural('user', $this->usersCount) }}
                </p>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('admin.roles.edit', $role->id) }}" wire:navigate
                   class="px-3.5 py-2 text-sm font-medium bg-[#123524] text-white rounded-lg hover:bg-[#0B2418] transition">
                    Edit
                </a>
                <button type="button"
                        wire:click="delete"
                        wire:confirm="Delete this role? This cannot be undone."
                        class="px-3.5 py-2 text-sm font-medium bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    Delete
                </button>
            </div>
        </div>
    </div>

    {{-- What this role can do --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">What this role can do</h2>

        @if ($role->permissions->isEmpty())
            <p class="text-sm text-gray-400 dark:text-neutral-500">This role can't do anything yet.</p>
        @else
            <div class="grid gap-6 sm:grid-cols-2">
                @foreach ($this->groupedPermissions as $group => $names)
                    <div>
                        <p class="text-sm font-semibold text-gray-700 dark:text-neutral-300">
                            {{ \App\Support\PermissionCatalog::title($group, $role->name) }}
                        </p>
                        @if ($desc = \App\Support\PermissionCatalog::description($group, $role->name))
                            <p class="text-xs text-gray-500 dark:text-neutral-400 mb-2">{{ $desc }}</p>
                        @endif
                        <ul class="space-y-1.5">
                            @foreach ($names as $name)
                                <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-neutral-400">
                                    <svg class="size-4 mt-0.5 shrink-0 text-[#1C6B45] dark:text-green-400" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ \App\Support\PermissionCatalog::label($name) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Users with this role --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm p-6">
        <h2 class="text-base font-semibold text-gray-800 dark:text-white mb-4">Who has this role</h2>

        @if ($this->users->isEmpty())
            <p class="text-sm text-gray-400 dark:text-neutral-500">Nobody has this role yet.</p>
        @else
            <ul class="divide-y divide-gray-100 dark:divide-neutral-700">
                @foreach ($this->users as $user)
                    <li class="py-2.5 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm text-gray-800 dark:text-neutral-200 truncate">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-neutral-400 truncate">{{ $user->email }}</p>
                        </div>
                        @if ($user->department)
                            <span class="text-xs text-gray-500 dark:text-neutral-400 shrink-0">
                                {{ $user->department->department_name }}
                            </span>
                        @endif
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

</div>
</div>
