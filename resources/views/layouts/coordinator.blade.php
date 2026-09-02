<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <link rel="icon" type="image/png" href="https://upload.wikimedia.org/wikipedia/commons/5/55/LogoCSAV.png">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

         @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <style>
                {{-- (unchanged Tailwind compiled CSS fallback — kept as-is) --}}
            </style>
        @endif

        @livewireStyles
    </head>
    <body class="bg-[#fdfdfc] dark:bg-[#0a0a0a] text-[#1b1b18] flex flex-col items-center justify-center p-2 sm:p-4 lg:p-8">
    <livewire:coordinator.header />
    <livewire:coordinator.sidebar />

    <main class="lg:hs-overlay-layout-open:ps-60 bg-gray-100 transition-all duration-300 lg:fixed lg:inset-0 pt-13 px-2 sm:px-3 pb-2 sm:pb-3 dark:bg-neutral-900 w-full">
        <div class="h-[calc(100dvh-62px)] lg:h-full overflow-hidden flex flex-col bg-white border border-gray-200 shadow-xs rounded-lg dark:bg-neutral-800 dark:border-neutral-700">
            <div class="flex-1 flex flex-col overflow-y-auto [&::-webkit-scrollbar]:w-0">
                {{ $slot }}
            </div>
        </div>
    </main>

    @livewireScripts
</body>
</html>
