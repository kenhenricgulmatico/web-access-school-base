{{-- ✅ Tabs shared by Users · Account Approvals · Roles & Permissions · Departments --}}
@php
    $tabOn  = 'border-transparent bg-blue-600 text-white';
    $tabOff = 'border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-neutral-800 dark:border-neutral-700 dark:text-neutral-300 dark:hover:bg-neutral-700';
    $pendingAccounts = \App\Models\User::where('status', 'pending')->count();
@endphp
<div class="flex flex-wrap gap-2 mb-4">
    @can('users.view')
    <a href="{{ route('admin.users') }}"
        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border {{ request()->routeIs('admin.users', 'admin.user.*') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>
        </svg>
        Users
    </a>
    @endcan

    @can('users.approve')
    <a href="{{ route('admin.students') }}"
        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border {{ request()->routeIs('admin.students') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M22 10v6M2 10l10-5 10 5-10 5-10-5z"/><path d="M6 12v5c0 1.1 2.7 2 6 2s6-.9 6-2v-5"/>
        </svg>
        Account Approvals
        @if ($pendingAccounts > 0)
            {{-- ✅ sign-ups waiting for a decision --}}
            <span class="min-w-5 h-5 px-1.5 inline-flex items-center justify-center rounded-full text-[11px] font-bold
                {{ request()->routeIs('admin.students') ? 'bg-white text-blue-700' : 'bg-red-600 text-white' }}"
                title="{{ $pendingAccounts }} waiting for approval">{{ $pendingAccounts }}</span>
        @endif
    </a>
    @endcan

    @can('roles.view')
    <a href="{{ route('admin.roles') }}"
        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border {{ request()->routeIs('admin.roles*') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
        </svg>
        Roles &amp; Permissions
    </a>
    @endcan

    @can('departments.view')
    <a href="{{ route('admin.departments') }}"
        class="py-2 px-3 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border {{ request()->routeIs('admin.departments*') ? $tabOn : $tabOff }}">
        <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M3 21h18M5 21V7l8-4v18M13 21V11h6v10M9 9h.01M9 13h.01M9 17h.01"/>
        </svg>
        Departments
    </a>
    @endcan
</div>
