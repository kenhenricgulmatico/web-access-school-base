<div>
    <div class="max-w-3xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-5 sm:py-8">

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
                <h2 class="text-xl sm:text-2xl font-bold tracking-tight text-[#123524] dark:text-white">
                    Edit Role — <span class="text-[#1C6B45] dark:text-[#7FBF8E]">{{ ucwords($role->name) }}</span>
                </h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                    Update the role's name or change what it's allowed to do.
                </p>
            </div>

            {{-- Flash --}}
            @if (session('success'))
                <div class="mb-5 p-3 rounded-lg bg-green-50 dark:bg-[#1C6B45]/20 border border-green-200 dark:border-[#1C6B45/40] text-[#1C6B45] dark:text-green-400 text-sm">
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
                        @readonly(in_array($role->name, ['admin', 'program head', 'faculty'], true))
                        @if (in_array($role->name, ['admin', 'program head', 'faculty'], true)) title="Built-in role — its name can't be changed" @endif
                        placeholder="e.g. registrar, librarian, staff"
                        class="w-full py-2.5 px-4 border border-gray-300 dark:border-neutral-600 rounded-lg text-sm
                               bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200
                               focus:border-[#1C6B45] focus:ring-2 focus:ring-[#1C6B45]/20 focus:outline-none
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

                    @include('partials.permission-checklist')
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
                               bg-[#123524] text-white hover:bg-[#0B2418]
                               focus:outline-none focus:ring-2 focus:ring-[#1C6B45]/30
                               disabled:opacity-50 disabled:pointer-events-none transition">
                        <span wire:loading.remove wire:target="save">Save Changes</span>
                        <span wire:loading wire:target="save">Saving...</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
