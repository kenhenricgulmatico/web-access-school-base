{{-- ✅ Tabs shared by Users · Account Approvals · Roles & Permissions · Departments --}}
@php
    // ✅ Same look as the faculty / Program Head tabs — one line, scrolls sideways on a phone
    $tabOn  = 'bg-white text-[#123524] font-semibold shadow-sm ring-1 ring-[#1C6B45]/15 dark:bg-neutral-700 dark:text-white dark:ring-0';
    $tabOff = 'text-gray-600 hover:text-[#123524] hover:bg-white/60 dark:text-neutral-400 dark:hover:text-white dark:hover:bg-neutral-800';
    $pendingAccounts = \App\Models\User::where('status', 'pending')->count();
@endphp
<div class="mb-4 max-w-full overflow-x-auto [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
<div class="inline-flex flex-nowrap gap-1 p-1 rounded-xl bg-[#F3F8F5] ring-1 ring-inset ring-[#1C6B45]/10 dark:bg-neutral-900/60 dark:ring-neutral-700">
    @can('users.view')
    <a href="{{ route('admin.users') }}"
        class="shrink-0 whitespace-nowrap min-h-9 py-1.5 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.users', 'admin.user.*') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Users
    </a>
    @endcan

    @can('users.approve')
    <a href="{{ route('admin.students') }}"
        class="shrink-0 whitespace-nowrap min-h-9 py-1.5 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.students') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
        </svg>
        Account Approvals
        @if ($pendingAccounts > 0)
            {{-- ✅ sign-ups waiting for a decision --}}
            <span class="min-w-5 h-5 px-1.5 inline-flex items-center justify-center rounded-full text-[11px] font-bold
                {{ request()->routeIs('admin.students') ? 'bg-[#E8F3EC] text-[#1C6B45]' : 'bg-amber-500 text-white' }}"
                title="{{ $pendingAccounts }} waiting for approval">{{ $pendingAccounts }}</span>
        @endif
    </a>
    @endcan

    @can('roles.view')
    <a href="{{ route('admin.roles') }}"
        class="shrink-0 whitespace-nowrap min-h-9 py-1.5 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.roles*') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        Roles &amp; Permissions
    </a>
    @endcan

    @can('departments.view')
    <a href="{{ route('admin.departments') }}"
        class="shrink-0 whitespace-nowrap min-h-9 py-1.5 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg transition {{ request()->routeIs('admin.departments*') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9h.01M9 13h.01M9 17h.01"/>
        </svg>
        Departments
    </a>
    @endcan
</div>
</div>
