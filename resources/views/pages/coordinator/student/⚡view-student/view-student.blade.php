<div>
<div class="max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">Faculty</h1>
            <p class="text-sm text-gray-500 dark:text-neutral-400">{{ $this->departmentName }}</p>
        </div>

        {{-- ✅ Only users with `members.create` can add --}}
        @can('members.create')
            <button wire:click="openCreate"
                class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-[#123524] text-white rounded-lg hover:bg-[#0C2418] transition">
                + Add Member
            </button>
        @endcan
    </div>

    {{-- Flash --}}
    @if(session()->has('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400 rounded-lg text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats: simple text row --}}
    <div class="flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-neutral-400 border-b border-gray-200 dark:border-neutral-700 pb-4">
        <span><strong class="text-gray-800 dark:text-neutral-200">{{ $this->counts['total'] }}</strong> total</span>
        <span><strong class="text-gray-800 dark:text-neutral-200">{{ $this->counts['faculty'] }}</strong> faculty</span>
        @if($this->counts['pending'] > 0)
            <span class="text-amber-700 dark:text-amber-400">
                <strong>{{ $this->counts['pending'] }}</strong> pending approval
            </span>
        @endif
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-2">
        {{-- Search with spinner --}}
        <div class="relative flex-1">
            <input type="text"
                wire:model.live.debounce.500ms="search"
                placeholder="Search name or email..."
                class="w-full px-3 py-2 pr-10 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">

            <div wire:loading wire:target="search" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
            </div>
        </div>

        <select wire:model.live="roleFilter"
            class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
            <option value="">All roles</option>
            <option value="faculty">Faculty</option>
        </select>

        <select wire:model.live="statusFilter"
            class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
            <option value="">All status</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
        </select>
    </div>

    {{-- Table --}}
    <div class="border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700 text-sm">
                <thead class="bg-gray-50 dark:bg-neutral-900/30 text-xs uppercase text-gray-500 dark:text-neutral-400">
                    <tr>
                        <th class="px-4 py-3 text-left">Name</th>
                        <th class="hidden sm:table-cell px-4 py-3 text-left">Role</th>
                        <th class="px-4 py-3 text-left">Status</th>
                        <th class="hidden md:table-cell px-4 py-3 text-left">Registered</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse($this->students as $user)
                        <tr wire:key="member-{{ $user->id }}" class="hover:bg-gray-50 dark:hover:bg-neutral-700/30">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">

                                    {{-- Avatar (photo if uploaded, otherwise initial) --}}
                                    @if ($user->avatar)
                                        <img src="{{ asset($user->avatar) }}"
                                             alt="{{ $user->name }}"
                                             class="size-9 rounded-full object-cover shrink-0 ring-1 ring-gray-200 dark:ring-neutral-600">
                                    @else
                                        <div class="size-9 rounded-full bg-[#123524] dark:bg-[#1C6B45] text-white flex items-center justify-center text-sm font-semibold shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                    @endif

                                    <div class="min-w-0">
                                        <p class="font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-neutral-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden sm:table-cell px-4 py-3 text-gray-600 dark:text-neutral-400">
                                {{ ucfirst($user->roles->first()?->name ?? 'N/A') }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-xs font-medium
                                    @if($user->status === 'approved') text-[#1C6B45] dark:text-[#7FBF8E]
                                    @elseif($user->status === 'pending') text-amber-700 dark:text-amber-400
                                    @else text-red-700 dark:text-red-400 @endif">
                                    {{ ucfirst($user->status) }}
                                </span>
                            </td>
                            <td class="hidden md:table-cell px-4 py-3 text-gray-400 dark:text-neutral-500">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>
                            <td class="px-4 py-3 text-right space-x-3">

                                {{-- ✅ Edit — requires `members.update` --}}
                                @can('members.update')
                                    <button wire:click="openEdit({{ $user->id }})" class="text-gray-500 hover:text-[#123524] dark:hover:text-green-400 text-xs font-medium">
                                        Edit
                                    </button>
                                @endcan

                                {{-- ✅ Delete — requires `members.delete` --}}
                                @can('members.delete')
                                    <button wire:click="delete({{ $user->id }})"
                                        wire:confirm="Delete {{ $user->name }}? This cannot be undone."
                                        class="text-gray-500 hover:text-red-600 text-xs font-medium">
                                        Delete
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-sm text-gray-400 dark:text-neutral-500">
                                No members found.
                                @can('members.create')
                                    <button wire:click="openCreate" class="block mx-auto mt-2 text-[#123524] dark:text-green-400 font-medium">
                                        + Add First Member
                                    </button>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($this->students->hasPages())
            <div class="px-4 py-3 border-t border-gray-200 dark:border-neutral-700">
                {{ $this->students->links() }}
            </div>
        @endif
    </div>

</div>

{{-- ── Create Modal ──────────────────────────────────── --}}
@can('members.create')
    @if($showCreateModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-xl w-full max-w-sm max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Add Member</h3>
                <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300">✕</button>
            </div>

            <div class="px-5 py-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Full Name</label>
                    <input type="text" wire:model="createName" placeholder="Juan Dela Cruz"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                    @error('createName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Email</label>
                    <input type="email" wire:model="createEmail" placeholder="user@csav.edu.ph"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                    @error('createEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div x-data="{ showPass: false }">
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Password</label>
                    <div class="relative">
                        <input :type="showPass ? 'text' : 'password'" wire:model="createPassword" placeholder="Min. 6 characters"
                            class="w-full px-3 py-2 pr-10 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                        <button type="button" @click="showPass = !showPass"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300">
                            <svg x-show="!showPass" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            <svg x-show="showPass" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12c1.292 4.338 5.31 7.5 10.066 7.5.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.774 3.162 10.066 7.5a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88"/>
                            </svg>
                        </button>
                    </div>
                    @error('createPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Role</label>
                    <select wire:model="createRole"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                        <option value="faculty">Faculty</option>
                    </select>
                    @error('createRole') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <p class="text-xs text-gray-400 dark:text-neutral-500">
                    Assigned to {{ $this->departmentName }} with status <strong>Pending</strong> until an admin approves.
                </p>
            </div>

            <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                <button wire:click="closeModals"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg">
                    Cancel
                </button>
                <button wire:click="create" wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm bg-[#123524] hover:bg-[#0C2418] text-white rounded-lg font-medium">
                    <span wire:loading.remove wire:target="create">Create</span>
                    <span wire:loading wire:target="create">Creating...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
@endcan

{{-- ── Edit Modal ────────────────────────────────────── --}}
@can('members.update')
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
        <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-xl w-full max-w-sm max-h-[90vh] overflow-y-auto">

            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-neutral-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Edit Member</h3>
                <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300">✕</button>
            </div>

            <div class="px-5 py-4 space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Full Name</label>
                    <input type="text" wire:model="editName"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                    @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Email</label>
                    <input type="email" wire:model="editEmail"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                    @error('editEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Role</label>
                    <select wire:model="editRole"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                        <option value="faculty">Faculty</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-neutral-400 mb-1">Status</label>
                    <select wire:model="editStatus"
                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-1 focus:ring-[#1C6B45]">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                    @error('editStatus') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex justify-end gap-2 px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                <button wire:click="closeModals"
                    class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg">
                    Cancel
                </button>
                <button wire:click="update" wire:loading.attr="disabled"
                    class="px-4 py-2 text-sm bg-[#123524] hover:bg-[#0C2418] text-white rounded-lg font-medium">
                    <span wire:loading.remove wire:target="update">Save</span>
                    <span wire:loading wire:target="update">Saving...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
@endcan

</div>
