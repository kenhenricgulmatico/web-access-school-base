<div>
     <div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">

        @include('partials.users-tabs')

        @if (session()->has('success'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">{{ session('success') }}</div>
        @endif
        @if (session()->has('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                {{ session('error') }}
                @if (session('handover_user'))
                    <a href="{{ route('admin.borrowed-items', ['filter' => 'assigned', 'holder' => session('handover_user')]) }}" class="ms-1 font-semibold underline">Hand over now →</a>
                @endif
            </div>
        @endif

        <div class="@container bg-white border border-gray-200 rounded-xl shadow-2xs overflow-hidden dark:bg-neutral-800 dark:border-neutral-700">
            <!-- Header -->
            <div class="px-4 sm:px-6 py-4 grid gap-3 border-b border-gray-200 dark:border-neutral-700">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Users</h2>
                        <p class="text-sm text-gray-600 dark:text-neutral-400">Admin, program head and faculty accounts</p>
                    </div>
                    @can('users.create')
                        <a class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-blue-600 text-white hover:bg-blue-700"
                            href="{{ route('admin.user.create') }}">
                            <svg class="shrink-0 size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                            Add User
                        </a>
                    @endcan
                </div>

                {{-- ✅ Active / Deactivated --}}
                <div class="flex flex-wrap items-center gap-2">
                    @foreach (['active' => 'Active', 'deactivated' => 'Deactivated'] as $key => $label)
                        <button type="button" wire:click="$set('show', '{{ $key }}')"
                            class="px-3 py-1 text-xs font-semibold rounded-full border transition
                            {{ $show === $key ? 'bg-gray-800 border-gray-800 text-white dark:bg-neutral-200 dark:border-neutral-200 dark:text-neutral-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700' }}">
                            {{ $label }} ({{ $this->counts[$key] }})
                        </button>
                    @endforeach
                </div>

                {{-- ✅ Search + filters --}}
                <div class="grid gap-2 @xl:grid-cols-2 @4xl:grid-cols-[1fr_200px_240px_auto] @4xl:items-center">
                    <div class="relative">
                        <svg class="size-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/></svg>
                        <label for="u-search" class="sr-only">Search users</label>
                        <input id="u-search" type="text" wire:model.live.debounce.300ms="search" placeholder="Search name or email…"
                            class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-900 dark:text-neutral-200 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div>
                        <label for="u-role" class="sr-only">Role</label>
                        <select id="u-role" wire:model.live="roleFilter"
                            class="w-full py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-900 dark:text-neutral-200">
                            <option value="">All roles</option>
                            <option value="program head" @selected($roleFilter === 'program head')>Program Head</option>
                            <option value="faculty" @selected($roleFilter === 'faculty')>Faculty</option>
                            <option value="admin" @selected($roleFilter === 'admin')>Admin</option>
                        </select>
                    </div>
                    <div>
                        <label for="u-dept" class="sr-only">Department</label>
                        <select id="u-dept" wire:model.live="deptFilter"
                            class="w-full py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-900 dark:text-neutral-200">
                            <option value="">All departments</option>
                            @foreach ($this->departments as $d)
                                <option value="{{ $d->id }}" @selected((string) $d->id === $deptFilter)>{{ $d->department_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if ($search !== '' || $roleFilter !== '' || $deptFilter !== '')
                        <button type="button" wire:click="clearFilters" class="justify-self-start px-2.5 py-1 text-xs font-medium rounded-full text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30">
                            ✕ Clear
                        </button>
                    @endif
                </div>
            </div>

            <!-- Table (cards on narrow screens) -->
            <table class="min-w-full block @3xl:table divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="hidden @3xl:table-header-group bg-gray-50 dark:bg-neutral-800">
                    <tr>
                        <th class="ps-6 pe-3 py-3 text-left text-sm font-semibold text-gray-800 dark:text-neutral-200">Name</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-800 dark:text-neutral-200">Role</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-800 dark:text-neutral-200">Department</th>
                        <th class="px-3 py-3 text-left text-sm font-semibold text-gray-800 dark:text-neutral-200">Open items</th>
                        <th class="px-6 py-3 text-end text-sm font-semibold text-gray-800 dark:text-neutral-200">Actions</th>
                    </tr>
                </thead>
                <tbody class="block @3xl:table-row-group divide-y divide-gray-200 dark:divide-neutral-700">
                    @forelse($this->users as $user)
                        @php
                            $role    = $user->roles->first()?->name;
                            $isAdmin = $role === 'admin';
                            $open    = $this->openItems[$user->id] ?? ['pending' => 0, 'borrowed' => 0, 'assigned' => 0];
                            $roleTone = match ($role) {
                                'admin'        => 'bg-gray-800 text-white dark:bg-neutral-200 dark:text-neutral-900',
                                'program head' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
                                default        => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300',
                            };
                        @endphp
                        <tr wire:key="u-{{ $user->id }}" class="block @3xl:table-row px-4 py-3 @3xl:p-0 {{ $show === 'deactivated' ? 'opacity-75' : '' }}">
                            <td class="block @3xl:table-cell @3xl:ps-6 @3xl:pe-3 py-1 @3xl:py-3 min-w-0">
                                <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $user->name }}</p>
                                <p class="text-xs text-gray-500 dark:text-neutral-400 break-all">{{ $user->email }}</p>
                                @if ($user->must_change_password)
                                    <p class="text-[11px] text-amber-700 dark:text-amber-400">Still has to choose their own password</p>
                                @endif
                            </td>
                            <td class="inline-block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3">
                                <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $roleTone }}">{{ $role ? ucwords($role) : 'No role' }}</span>
                            </td>
                            <td class="inline-block @3xl:table-cell px-2 @3xl:px-3 py-1 @3xl:py-3 text-sm text-gray-600 dark:text-neutral-400">
                                {{ $isAdmin ? '—' : ($user->department->department_name ?? '—') }}
                            </td>
                            <td class="block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3 text-xs">
                                @if ($open['pending'] || $open['borrowed'] || ($open['assigned'] ?? 0))
                                    @if ($open['pending'])
                                        <span class="inline-block me-1 px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">{{ $open['pending'] }} pending</span>
                                    @endif
                                    @if ($open['borrowed'])
                                        <span class="inline-block px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900/40 dark:text-purple-300">{{ $open['borrowed'] }} borrowed</span>
                                    @endif
                                    @if ($open['assigned'] ?? 0)
                                        <a href="{{ route('admin.borrowed-items', ['filter' => 'assigned', 'holder' => $user->id]) }}"
                                           class="inline-block px-2 py-0.5 rounded-full bg-green-100 text-green-800 hover:underline dark:bg-green-900/40 dark:text-green-300">{{ $open['assigned'] }} kept</a>
                                    @endif
                                @else
                                    <span class="text-gray-400 dark:text-neutral-500">—</span>
                                @endif
                            </td>
                            <td class="block @3xl:table-cell @3xl:px-6 pt-2 @3xl:py-3 @3xl:text-end @3xl:whitespace-nowrap">
                                @can('users.update')
                                    <a href="{{ route('admin.user.edit', $user->id) }}" class="text-blue-600 hover:underline text-sm font-medium dark:text-blue-400">Edit</a>
                                    @if (! $isAdmin && auth()->user()->can('users.deactivate'))
                                        <span class="text-gray-300 dark:text-neutral-600 mx-1">·</span>
                                        @if ($show === 'deactivated')
                                            <button type="button" wire:click="reactivate({{ $user->id }})" wire:confirm="Let {{ $user->name }} sign in again?"
                                                class="text-green-700 hover:underline text-sm font-medium dark:text-green-400">Reactivate</button>
                                        @else
                                            <button type="button" wire:click="deactivate({{ $user->id }})" wire:confirm="{{ $this->deactivateConfirm($user) }}"
                                                class="text-red-600 hover:underline text-sm font-medium dark:text-red-400">Deactivate</button>
                                        @endif
                                    @endif
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr class="block @3xl:table-row">
                            <td colspan="5" class="block @3xl:table-cell text-center py-8 text-sm text-gray-500 dark:text-neutral-400">
                                {{ $show === 'deactivated' ? 'No deactivated accounts.' : 'No users match.' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            <!-- Footer -->
            <div class="px-4 sm:px-6 py-4 flex flex-wrap gap-3 justify-between items-center border-t border-gray-200 dark:border-neutral-700">
                <p class="text-sm text-gray-600 dark:text-neutral-400">
                    <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $this->users->total() }}</span> {{ \Illuminate\Support\Str::plural('user', $this->users->total()) }}
                </p>
                {{ $this->users->links() }}
            </div>
        </div>
    </div>
</div>
