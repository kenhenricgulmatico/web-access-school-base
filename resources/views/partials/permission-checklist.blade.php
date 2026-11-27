{{-- ✅ Permission checklist, grouped by feature (Create Role / Edit Role). Needs $this->grouped. --}}
@php
    $canLock  = method_exists($this, 'isLocked');
    $roleName = isset($this->role) ? $this->role->name : null;
@endphp

@if (empty($this->grouped))
    <p class="text-sm text-gray-400 dark:text-neutral-500">No permissions available.</p>
@else
    <div class="grid gap-4 @container">
        @foreach ($this->grouped as $group => $permissions)
            @php
                $groupNames      = collect($permissions)->pluck('name');
                $selectedInGroup = collect($selectedPermissions)->intersect($groupNames)->count();
                $freeNames       = $groupNames->reject(fn ($n) => $canLock && $this->isLocked($n))->values();
            @endphp
            <div wire:key="grp-{{ $group }}" class="border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden">

                <div class="px-4 py-2.5 bg-gray-50 dark:bg-neutral-900/60 border-b border-gray-200 dark:border-neutral-700 flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-700 dark:text-neutral-200">
                            {{ \App\Support\PermissionCatalog::title($group, $roleName) }}
                        </p>
                        @if ($desc = \App\Support\PermissionCatalog::description($group, $roleName))
                            <p class="mt-0.5 text-[11px] text-gray-500 dark:text-neutral-400">{{ $desc }}</p>
                        @endif
                    </div>
                    <div class="shrink-0 flex items-center gap-2">
                        <span class="text-[10px] font-medium text-gray-400 dark:text-neutral-500">{{ $selectedInGroup }} / {{ count($permissions) }}</span>
                        @if ($freeNames->isNotEmpty())
                            {{-- tick / untick the whole group --}}
                            @php $allOn = $freeNames->diff($selectedPermissions)->isEmpty(); @endphp
                            <button type="button" wire:click="toggleGroup('{{ $group }}')"
                                class="text-[11px] font-semibold text-[#1C6B45] hover:underline dark:text-[#7FBF8E]">
                                {{ $allOn ? 'None' : 'All' }}
                            </button>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-1 @md:grid-cols-2 divide-y @md:divide-y-0 divide-gray-100 dark:divide-neutral-700">
                    @foreach ($permissions as $permission)
                        @php $locked = $canLock && $this->isLocked($permission->name); @endphp
                        <label for="perm-{{ $permission->id }}"
                            class="flex items-start gap-3 p-3 transition {{ $locked ? 'cursor-not-allowed opacity-75' : 'cursor-pointer hover:bg-gray-50 dark:hover:bg-neutral-700/30' }}"
                            @if ($locked) title="Always on for this role, so nobody gets locked out" @endif>
                            <input
                                type="checkbox"
                                id="perm-{{ $permission->id }}"
                                @if ($locked) checked disabled @else wire:model.live="selectedPermissions" @checked(in_array($permission->name, $selectedPermissions, true)) @endif
                                value="{{ $permission->name }}"
                                class="mt-0.5 shrink-0 size-4 rounded border-gray-300 text-[#1C6B45] focus:ring-2 focus:ring-[#1C6B45]/30 focus:ring-offset-0 dark:bg-neutral-800 dark:border-neutral-600 dark:checked:bg-[#123524] dark:checked:border-[#1C6B45]">
                            <span class="text-sm text-gray-700 dark:text-neutral-300 leading-snug">
                                {{ \App\Support\PermissionCatalog::label($permission->name) }}
                                @if ($locked)
                                    <span class="block text-[11px] text-gray-400 dark:text-neutral-500">Always on (lockout protection)</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
@endif
