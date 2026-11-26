<div>
    <div class="max-w-3xl mx-auto px-3 py-6 sm:px-6 lg:px-8 lg:py-14">

        {{-- Profile Header Card with Avatar Upload --}}
        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm p-4 sm:p-6 mb-6 flex items-center gap-4 text-left">

            {{-- Avatar --}}
            <div class="relative shrink-0 group w-16 h-16">
                @if ($currentAvatar)
                    <img src="{{ asset($currentAvatar) }}"
                         wire:key="avatar-{{ $currentAvatar }}"
                         alt="Profile photo"
                         class="w-16 h-16 rounded-full object-cover ring-2 ring-green-700/20">
                @else
                    <div class="w-16 h-16 rounded-full bg-green-700 text-white flex items-center justify-center text-2xl font-semibold">
                        {{ strtoupper(substr($savedName, 0, 1)) }}
                    </div>
                @endif

                {{-- Loading overlay (hidden when idle; .flex keeps the spinner centered) --}}
                <div wire:loading.flex wire:target="avatar"
                     class="absolute inset-0 rounded-full bg-black/60 items-center justify-center z-10">
                    <svg class="w-6 h-6 text-white animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </div>

                {{-- Hover overlay (only on devices that truly support hover) --}}
                <label for="avatar-upload"
                       class="absolute inset-0 rounded-full bg-black/40 opacity-0 [@media(hover:hover)]:group-hover:opacity-100 flex items-center justify-center cursor-pointer transition z-20">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                        <circle cx="12" cy="13" r="3"/>
                    </svg>
                </label>

                {{-- File input lives outside the label --}}
                <input id="avatar-upload" type="file" wire:model="avatar"
                       accept="image/jpeg,image/png,image/gif,image/webp" class="sr-only">
            </div>

            {{-- Name / role / actions --}}
            <div class="min-w-0 flex-1">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-neutral-200 truncate">{{ $savedName }}</h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-neutral-400">{{ ucfirst($role) }} · {{ $department }}</p>

                <div class="flex items-center gap-3 mt-1.5">
                    <label for="avatar-upload"
                           class="inline-block text-xs font-medium text-green-700 dark:text-green-400 hover:underline cursor-pointer">
                        <span wire:loading.remove wire:target="avatar">Change photo (JPG, PNG, GIF, WebP · max 5MB)</span>
                        <span wire:loading wire:target="avatar">Uploading...</span>
                    </label>

                    @if ($currentAvatar)
                        <button type="button"
                                wire:click="removeAvatar"
                                wire:confirm="Remove your profile photo?"
                                class="text-xs font-medium text-red-600 hover:text-red-800 hover:underline">
                            Remove
                        </button>
                    @endif
                </div>

                @error('avatar')
                    <div class="mt-3 flex items-start gap-2 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                        <svg class="size-4 shrink-0 mt-0.5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <path stroke-linecap="round" d="M12 8v4m0 4h.01"/>
                        </svg>
                        <p class="text-xs text-red-700 dark:text-red-300 font-medium">{{ $message }}</p>
                    </div>
                @enderror
            </div>
        </div>

        {{-- Success message --}}
        @if (session()->has('message'))
            <div class="mb-6 flex items-center gap-2 p-3 sm:p-4 rounded-xl bg-green-50 text-green-800 border border-green-200 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span class="text-xs sm:text-sm">{{ session('message') }}</span>
            </div>
        @endif

        {{-- Personal Info (name editable, the rest read-only) --}}
        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-4 py-3.5 sm:px-6 sm:py-4 border-b border-gray-100 dark:border-neutral-700">
                <h3 class="text-sm font-semibold text-gray-800 dark:text-neutral-200">Personal information</h3>
                <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">You can change your name. Email, department and role are managed by the registrar.</p>
            </div>

            <div class="p-4 sm:p-6 space-y-4 sm:space-y-5 select-none">
                @php
                    $lockIcon = '<svg class="w-4 h-4 text-gray-400 dark:text-neutral-500 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="10" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>';
                    $fieldClass = 'w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-neutral-700 bg-gray-50 dark:bg-neutral-900 text-gray-500 dark:text-gray-400 text-xs sm:text-sm flex items-center justify-between';
                    $labelClass = 'block text-xs sm:text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1.5';
                @endphp

                {{-- ✅ Full name — the only field you can change yourself --}}
                <div>
                    <label for="profile-name" class="{{ $labelClass }}">Full name</label>
                    <form wire:submit="saveName" class="flex flex-col sm:flex-row gap-2">
                        <input id="profile-name" type="text" wire:model="name" maxlength="100" autocomplete="name"
                               class="flex-1 min-w-0 px-3 py-2.5 rounded-lg border bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200 text-xs sm:text-sm focus:outline-none focus:ring-2 focus:ring-green-600/30 focus:border-green-600
                                      @error('name') border-red-400 @else border-gray-300 dark:border-neutral-600 @enderror">
                        <div class="flex gap-2 shrink-0" x-show="$wire.name !== $wire.savedName" x-cloak>
                            <button type="button" wire:click="cancelName"
                                    class="px-3 py-2 text-xs sm:text-sm font-medium rounded-lg border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-300 hover:bg-gray-50 dark:hover:bg-neutral-700 transition">
                                Cancel
                            </button>
                            <button type="submit" wire:loading.attr="disabled" wire:target="saveName"
                                    class="px-4 py-2 text-xs sm:text-sm font-semibold rounded-lg bg-green-700 text-white hover:bg-green-800 disabled:opacity-60 transition">
                                <span wire:loading.remove wire:target="saveName">Save</span>
                                <span wire:loading wire:target="saveName">Saving…</span>
                            </button>
                        </div>
                    </form>
                    @error('name') <p class="text-xs text-red-600 dark:text-red-400 mt-1.5">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="{{ $labelClass }}">Email address</label>
                    <div class="{{ $fieldClass }}">
                        <span class="truncate pr-2">{{ $email }}</span>
                        {!! $lockIcon !!}
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1 sm:pt-2">
                    <div>
                        <label class="{{ $labelClass }}">Department</label>
                        <div class="{{ $fieldClass }}">
                            <span class="truncate pr-2">{{ $department }}</span>
                            {!! $lockIcon !!}
                        </div>
                    </div>
                    <div>
                        <label class="{{ $labelClass }}">Role</label>
                        <div class="{{ $fieldClass }}">
                            <span class="truncate pr-2">{{ ucfirst($role) }}</span>
                            {!! $lockIcon !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Link to Settings --}}
        <div class="mt-6 flex justify-end">
            <a href="{{ route('portal.settings') }}"
               class="text-sm font-medium text-green-700 dark:text-green-400 hover:underline">
                Looking to change your password? Go to Settings →
            </a>
        </div>
    </div>
</div>
