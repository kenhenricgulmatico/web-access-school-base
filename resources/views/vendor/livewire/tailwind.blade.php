{{--
    ✅ App-wide Livewire pagination (replaces livewire::tailwind for every ->links()).
       Showing 1–5 of 58      ‹ Prev   1  2  3  …  12   Next ›
       - compact page list with "…" gaps (first, last, current ± 1)
       - smooth scroll back to the top of the list, only when it is above the screen
       - dims while the next page loads
--}}
@php
    if (! isset($scrollTo)) {
        $scrollTo = 'body';
    }

    $scrollIntoViewJsSnippet = ($scrollTo !== false)
        ? <<<JS
            (() => {
                const box = \$el.closest('.rounded-2xl, .rounded-xl') || \$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}');
                if (box && box.getBoundingClientRect().top < 0) box.scrollIntoView({ behavior: 'smooth', block: 'start' });
            })()
        JS
        : '';

    $current  = $paginator->currentPage();
    $last     = $paginator->lastPage();
    $pageName = $paginator->getPageName();
    $duskName = $pageName == 'page' ? '' : '.' . $pageName;

    // 1 … 4 5 6 … 12   (always first + last, current ± 1, "…" for gaps; short lists show every page)
    $pages = [];
    if ($last <= 7) {
        $pages = range(1, $last);
    } else {
        $window = array_unique(array_filter(
            [1, 2, $current - 1, $current, $current + 1, $last - 1, $last],
            fn ($p) => $p >= 1 && $p <= $last
        ));
        // keep 1–5 / last 5 together near the edges so the bar doesn't jump around
        if ($current <= 4) {
            $window = array_merge($window, range(1, 5));
        } elseif ($current >= $last - 3) {
            $window = array_merge($window, range($last - 4, $last));
        }
        $window = array_values(array_unique($window));
        sort($window);

        $prev = 0;
        foreach ($window as $p) {
            if ($prev && $p - $prev > 1) {
                $pages[] = $p - $prev === 2 ? $prev + 1 : '…';
            }
            $pages[] = $p;
            $prev = $p;
        }
    }

    $btn  = 'inline-flex items-center justify-center min-w-9 h-9 px-3 text-sm font-medium rounded-lg transition select-none';
    $idle = 'text-gray-600 hover:bg-gray-100 hover:text-gray-900 dark:text-neutral-300 dark:hover:bg-neutral-700 dark:hover:text-white';
    $off  = 'text-gray-300 cursor-not-allowed dark:text-neutral-600';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation"
             class="flex flex-col sm:flex-row items-center justify-between gap-3"
             wire:loading.class="opacity-60 pointer-events-none">

            {{-- Showing 1–5 of 58 --}}
            <p class="text-sm text-gray-500 dark:text-neutral-400">
                Showing
                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $paginator->firstItem() }}–{{ $paginator->lastItem() }}</span>
                of
                <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ number_format($paginator->total()) }}</span>
            </p>

            <div class="flex items-center gap-1">
                {{-- Previous --}}
                @if ($paginator->onFirstPage())
                    <span class="{{ $btn }} {{ $off }}" aria-disabled="true" aria-label="{{ __('pagination.previous') }}">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        <span class="hidden sm:inline ms-1">Prev</span>
                    </span>
                @else
                    <button type="button" wire:click="previousPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        dusk="previousPage{{ $duskName }}" aria-label="{{ __('pagination.previous') }}"
                        class="{{ $btn }} {{ $idle }}">
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        <span class="hidden sm:inline ms-1">Prev</span>
                    </button>
                @endif

                {{-- Pages (phones: "3 / 12") --}}
                <span class="sm:hidden px-2 text-sm text-gray-500 dark:text-neutral-400">
                    <span class="font-semibold text-gray-800 dark:text-neutral-200">{{ $current }}</span> / {{ $last }}
                </span>

                <div class="hidden sm:flex items-center gap-1">
                    @foreach ($pages as $page)
                        @if ($page === '…')
                            <span class="inline-flex items-center justify-center w-7 h-9 text-sm text-gray-400" aria-hidden="true">…</span>
                        @elseif ($page == $current)
                            <span wire:key="paginator-{{ $pageName }}-page{{ $page }}" aria-current="page"
                                class="{{ $btn }} bg-[#123524] text-white shadow-sm dark:bg-green-600">{{ $page }}</span>
                        @else
                            <button type="button" wire:key="paginator-{{ $pageName }}-page{{ $page }}"
                                wire:click="gotoPage({{ $page }}, '{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                                aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                                class="{{ $btn }} {{ $idle }}">{{ $page }}</button>
                        @endif
                    @endforeach
                </div>

                {{-- Next --}}
                @if ($paginator->hasMorePages())
                    <button type="button" wire:click="nextPage('{{ $pageName }}')" x-on:click="{{ $scrollIntoViewJsSnippet }}"
                        dusk="nextPage{{ $duskName }}" aria-label="{{ __('pagination.next') }}"
                        class="{{ $btn }} {{ $idle }}">
                        <span class="hidden sm:inline me-1">Next</span>
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </button>
                @else
                    <span class="{{ $btn }} {{ $off }}" aria-disabled="true" aria-label="{{ __('pagination.next') }}">
                        <span class="hidden sm:inline me-1">Next</span>
                        <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                    </span>
                @endif
            </div>
        </nav>
    @endif
</div>
