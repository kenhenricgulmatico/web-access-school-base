<div>
<div class="max-w-[85rem] px-4 py-8 sm:px-6 lg:px-8 mx-auto space-y-5">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-neutral-200">Students & Faculty</h2>
            <p class="text-sm text-gray-500 dark:text-neutral-400 mt-0.5">
                Department of
                <span class="font-medium text-[#123524] dark:text-green-400">{{ $this->departmentName }}</span>
            </p>
        </div>
        <button wire:click="openCreate"
            class="inline-flex items-center justify-center gap-2 px-4 py-2 text-sm font-medium bg-[#123524] text-white rounded-lg hover:bg-[#0C2418] transition w-full sm:w-auto">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M12 5v14M5 12h14"/>
            </svg>
            Add Member
        </button>
    </div>

    {{-- Flash --}}
    @if(session()->has('success'))
        <div class="p-3 bg-green-50 border border-green-200 text-green-700 dark:bg-green-900/20 dark:border-green-800 dark:text-green-400 rounded-lg text-sm flex items-center gap-2">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <p class="text-xs text-gray-500 dark:text-neutral-400 mb-1">Total Members</p>
            <p class="text-xl sm:text-2xl font-semibold text-gray-800 dark:text-neutral-200">{{ $this->totalCount }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <p class="text-xs text-gray-500 dark:text-neutral-400 mb-1">Students</p>
            <p class="text-xl sm:text-2xl font-semibold text-purple-700 dark:text-purple-300">{{ $this->studentCount }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <p class="text-xs text-gray-500 dark:text-neutral-400 mb-1">Faculty</p>
            <p class="text-xl sm:text-2xl font-semibold text-amber-600 dark:text-amber-400">{{ $this->facultyCount }}</p>
        </div>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl p-4">
            <p class="text-xs text-gray-500 dark:text-neutral-400 mb-1">Pending Approval</p>
            <p class="text-xl sm:text-2xl font-semibold text-yellow-600 dark:text-yellow-400">{{ $this->pendingCount }}</p>
        </div>

    </div>

    {{-- Pending notice banner --}}
    @if($this->pendingCount > 0)
        <div class="p-3 bg-yellow-50 border border-yellow-200 dark:bg-yellow-900/20 dark:border-yellow-800 rounded-lg text-sm text-yellow-700 dark:text-yellow-400 flex items-start sm:items-center gap-2">
            <svg class="w-4 h-4 shrink-0 mt-0.5 sm:mt-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
            </svg>
            <span>
                <strong>{{ $this->pendingCount }}</strong>
                {{ $this->pendingCount === 1 ? 'member is' : 'members are' }}
                waiting for admin approval before they can log in.
            </span>
        </div>
    @endif

    {{-- Table --}}
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

        {{-- Filters --}}
        <div class="px-4 sm:px-5 py-4 border-b border-gray-200 dark:border-neutral-700 flex flex-col sm:flex-row flex-wrap items-start sm:items-center justify-between gap-3">
            <div>
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Members list</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $this->totalCount }} total in {{ $this->departmentName }}</p>
            </div>
            <div class="flex flex-col sm:flex-row flex-wrap gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-auto">
                    <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="M21 21l-4.35-4.35"/>
                    </svg>
                    <input type="text" wire:model.live="search"
                        placeholder="Search name or email..."
                        class="pl-9 pr-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45] w-full sm:w-52">
                </div>
                <select wire:model.live="roleFilter"
                    class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45] w-full sm:w-auto">
                    <option value="">All roles</option>
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                </select>
                <select wire:model.live="statusFilter"
                    class="py-2 px-3 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45] w-full sm:w-auto">
                    <option value="">All status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>
        </div>

        {{-- Responsive Table Wrapper --}}
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                <thead class="bg-gray-50 dark:bg-neutral-900/30">
                    <tr>
                        <th class="px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Name</th>
                        <th class="hidden sm:table-cell px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Role</th>
                        <th class="px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Status</th>
                        <th class="hidden md:table-cell px-4 sm:px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Registered</th>
                        <th class="px-4 sm:px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                    @forelse($this->students as $user)
                        <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700/30 transition
                            {{ $user->status === 'pending' ? 'bg-yellow-50/40 dark:bg-yellow-900/5' : '' }}">

                            {{-- Name --}}
                            <td class="px-4 sm:px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-[#123524] text-white flex items-center justify-center text-sm font-bold shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-neutral-200 truncate">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-400 dark:text-neutral-500 truncate">{{ $user->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Role (hidden on mobile) --}}
                            <td class="hidden sm:table-cell px-4 sm:px-5 py-3">
                                @php $role = $user->roles->first()?->name ?? 'N/A'; @endphp
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full whitespace-nowrap
                                    @if($role === 'student') bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300
                                    @elseif($role === 'faculty') bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300
                                    @else bg-gray-100 text-gray-500 @endif">
                                    {{ ucfirst($role) }}
                                </span>
                            </td>

                            {{-- Status --}}
                            <td class="px-4 sm:px-5 py-3">
                                <span class="px-2 py-0.5 text-xs font-medium rounded-full inline-flex items-center gap-1 whitespace-nowrap
                                    @if($user->status === 'approved') bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300
                                    @elseif($user->status === 'pending') bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300
                                    @elseif($user->status === 'rejected') bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300
                                    @endif">
                                    @if($user->status === 'pending')
                                        <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                    <span class="hidden xs:inline">
                                        @if($user->status === 'pending') Waiting for admin
                                        @elseif($user->status === 'approved') Approved
                                        @elseif($user->status === 'rejected') Rejected
                                        @endif
                                    </span>
                                    <span class="xs:hidden">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </span>
                            </td>

                            {{-- Registered (hidden on mobile) --}}
                            <td class="hidden md:table-cell px-4 sm:px-5 py-3 text-sm text-gray-400 dark:text-neutral-500 whitespace-nowrap">
                                {{ $user->created_at->format('M d, Y') }}
                            </td>

                            {{-- Actions --}}
                            <td class="px-4 sm:px-5 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <button wire:click="openEdit({{ $user->id }})"
                                        class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded-md transition"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button wire:click="delete({{ $user->id }})"
                                        wire:confirm="Delete {{ $user->name }}? This cannot be undone."
                                        class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-md transition"
                                        title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 sm:px-5 py-14 text-center">
                                <div class="flex flex-col items-center gap-2">
                                    <svg class="w-10 h-10 text-gray-300 dark:text-neutral-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/>
                                    </svg>
                                    <p class="text-sm font-medium text-gray-500 dark:text-neutral-400">No members found</p>
                                    <p class="text-xs text-gray-400 dark:text-neutral-500">
                                        {{ $search || $roleFilter || $statusFilter ? 'Try adjusting your filters.' : 'No students or faculty in your department yet.' }}
                                    </p>
                                    <button wire:click="openCreate"
                                        class="mt-2 px-4 py-2 text-sm bg-[#123524] text-white rounded-lg hover:bg-[#0C2418] transition">
                                        + Add First Member
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($this->students->hasPages())
            <div class="px-4 sm:px-5 py-4 border-t border-gray-200 dark:border-neutral-700">
                {{ $this->students->links() }}
            </div>
        @endif

    </div>

</div>

{{-- ── Create Modal ──────────────────────────────────── --}}
@if($showCreateModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <div>
                <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-200">Add Member</h3>
                <p class="text-xs text-gray-400 mt-0.5">
                    Auto-assigned to
                    <span class="text-[#123524] dark:text-green-400 font-medium">{{ $this->departmentName }}</span>
                </p>
            </div>
            <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="createName" placeholder="Juan Dela Cruz"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                @error('createName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" wire:model="createEmail" placeholder="user@csav.edu.ph"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                @error('createEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div x-data="{ show: false }">
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                    Password <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input :type="show ? 'text' : 'password'"
                        wire:model="createPassword"
                        placeholder="Min. 6 characters"
                        class="w-full px-3 py-2 pr-10 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                    <button type="button" @click="show = !show"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300 transition">
                        <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                            <path stroke-linecap="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                        </svg>
                        <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" x-cloak>
                            <path stroke-linecap="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/>
                        </svg>
                    </button>
                </div>
                @error('createPassword') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">
                    Role <span class="text-red-500">*</span>
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" wire:click="$set('createRole', 'student')"
                        class="py-2 px-3 text-sm font-medium rounded-lg border-2 transition
                            {{ $createRole === 'student'
                                ? 'bg-[#123524] text-white border-[#123524]'
                                : 'bg-white dark:bg-neutral-700 text-gray-600 dark:text-neutral-300 border-gray-300 dark:border-neutral-600 hover:border-[#123524]' }}">
                        Student
                    </button>
                    <button type="button" wire:click="$set('createRole', 'faculty')"
                        class="py-2 px-3 text-sm font-medium rounded-lg border-2 transition
                            {{ $createRole === 'faculty'
                                ? 'bg-[#123524] text-white border-[#123524]'
                                : 'bg-white dark:bg-neutral-700 text-gray-600 dark:text-neutral-300 border-gray-300 dark:border-neutral-600 hover:border-[#123524]' }}">
                        Faculty
                    </button>
                </div>
                @error('createRole') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Pending notice --}}
            <div class="flex items-start gap-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <svg class="w-4 h-4 text-yellow-600 dark:text-yellow-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                </svg>
                <p class="text-xs text-yellow-700 dark:text-yellow-400">
                    This member will be assigned to <strong>{{ $this->departmentName }}</strong> with status
                    <strong>Pending</strong> — an admin must approve the account before they can log in.
                </p>
            </div>

        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
            <button wire:click="closeModals"
                class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg transition w-full sm:w-auto">
                Cancel
            </button>
            <button wire:click="create"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="px-4 py-2 text-sm bg-[#123524] hover:bg-[#0C2418] text-white rounded-lg font-medium transition w-full sm:w-auto">
                <span wire:loading.remove wire:target="create">Create Member</span>
                <span wire:loading wire:target="create">Creating...</span>
            </button>
        </div>

    </div>
</div>
@endif

{{-- ── Edit Modal ────────────────────────────────────── --}}
@if($showEditModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4 py-6">
    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl w-full max-w-md max-h-[90vh] overflow-y-auto">

        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <h3 class="text-base font-semibold text-gray-800 dark:text-neutral-200">Edit Member</h3>
            <button wire:click="closeModals" class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="px-6 py-5 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text" wire:model="editName"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                @error('editName') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" wire:model="editEmail"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                @error('editEmail') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-2">Role</label>
                <div class="grid grid-cols-2 gap-3">
                    <button type="button" wire:click="$set('editRole', 'student')"
                        class="py-2 px-3 text-sm font-medium rounded-lg border-2 transition
                            {{ $editRole === 'student'
                                ? 'bg-[#123524] text-white border-[#123524]'
                                : 'bg-white dark:bg-neutral-700 text-gray-600 dark:text-neutral-300 border-gray-300 dark:border-neutral-600 hover:border-[#123524]' }}">
                        Student
                    </button>
                    <button type="button" wire:click="$set('editRole', 'faculty')"
                        class="py-2 px-3 text-sm font-medium rounded-lg border-2 transition
                            {{ $editRole === 'faculty'
                                ? 'bg-[#123524] text-white border-[#123524]'
                                : 'bg-white dark:bg-neutral-700 text-gray-600 dark:text-neutral-300 border-gray-300 dark:border-neutral-600 hover:border-[#123524]' }}">
                        Faculty
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">Status</label>
                <select wire:model="editStatus"
                    class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 dark:bg-neutral-700 dark:text-neutral-200 focus:outline-none focus:ring-2 focus:ring-[#1C6B45]">
                    <option value="pending">Pending — waiting for admin</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
                @error('editStatus') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
            <button wire:click="closeModals"
                class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 dark:bg-neutral-700 dark:hover:bg-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg transition w-full sm:w-auto">
                Cancel
            </button>
            <button wire:click="update"
                wire:loading.attr="disabled"
                wire:loading.class="opacity-50 cursor-not-allowed"
                class="px-4 py-2 text-sm bg-[#123524] hover:bg-[#0C2418] text-white rounded-lg font-medium transition w-full sm:w-auto">
                <span wire:loading.remove wire:target="update">Save Changes</span>
                <span wire:loading wire:target="update">Saving...</span>
            </button>
        </div>

    </div>
</div>
@endif

</div>
