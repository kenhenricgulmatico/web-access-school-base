<div class="max-w-xl mx-auto px-3 sm:px-4 py-6 sm:py-8 print:py-0 print:px-0">

    @if($notFound)

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm p-8 sm:p-10 text-center">
            <div class="size-14 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center mx-auto mb-4">
                <svg class="size-7 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-600 dark:text-neutral-300">Receipt not available</p>
            <p class="text-xs text-gray-400 dark:text-neutral-500 mt-1">
                This request either doesn't exist or hasn't been approved yet.
            </p>
        </div>

    @else

        {{-- Print button --}}
        <div class="flex justify-end mb-4 sm:mb-5 print:hidden">
            <button onclick="window.print()"
                class="py-2.5 px-4 inline-flex items-center gap-x-2 text-sm font-medium rounded-lg border border-transparent bg-green-700 text-white hover:bg-green-800 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shadow-sm transition">
                <svg class="shrink-0 size-4" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                </svg>
                Print Receipt
            </button>
        </div>

        {{-- Receipt --}}
        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm print:shadow-none print:border print:border-gray-300 print:rounded-none overflow-hidden">

            {{-- Header --}}
            <div class="px-4 sm:px-7 pt-6 sm:pt-7 pb-4 sm:pb-5 border-b border-gray-200 dark:border-neutral-700">
                <div class="flex items-start gap-3 sm:gap-4">
                    <img src="{{ asset('images/csav-logo.png') }}"
                         alt="CSAV Logo"
                         class="size-10 sm:size-12 rounded-lg shrink-0 object-contain">
                    <div class="min-w-0 flex-1">
                        <h1 class="text-sm sm:text-base font-bold text-gray-900 dark:text-white leading-tight">
                            Colegio de Sta. Ana de Victorias
                        </h1>
                        <p class="text-xs text-gray-500 dark:text-neutral-400 mt-0.5">
                            Materials Request &amp; Facility Reservation
                        </p>
                        <p class="text-[11px] font-semibold text-green-700 dark:text-green-400 uppercase tracking-wider mt-1.5">
                            Official Receipt
                        </p>
                    </div>
                </div>
            </div>

            <div class="px-4 sm:px-7 py-5 sm:py-6">

                {{-- Meta row --}}
                <div class="flex flex-wrap items-center justify-between gap-3 mb-5 sm:mb-6">
                    <span class="inline-flex items-center gap-x-1.5 py-1 px-2.5 rounded-full text-[11px] font-semibold uppercase tracking-wide bg-teal-100 text-teal-800 dark:bg-teal-900/50 dark:text-teal-300">
                        <svg class="size-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                        </svg>
                        Approved
                    </span>

                    <div class="text-right">
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 uppercase tracking-wide">Receipt No.</p>
                        <p class="text-sm font-bold text-gray-900 dark:text-white">#{{ $printRequest->id }}</p>
                    </div>
                </div>

                {{-- Details --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 sm:gap-x-8 gap-y-4 text-sm mb-6 sm:mb-7">
                    <div>
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 uppercase tracking-wide mb-0.5">Requested By</p>
                        <p class="font-medium text-gray-900 dark:text-neutral-100">{{ $printRequest->user->name }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 uppercase tracking-wide mb-0.5">Department</p>
                        <p class="font-medium text-gray-900 dark:text-neutral-100">{{ $printRequest->user->department->department_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 uppercase tracking-wide mb-0.5">Date Approved</p>
                        <p class="font-medium text-gray-900 dark:text-neutral-100">
                            {{ $approval?->approved_at
                                ? \Carbon\Carbon::parse($approval->approved_at)->format('M d, Y • g:i A')
                                : $printRequest->updated_at->format('M d, Y • g:i A') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-[11px] text-gray-400 dark:text-neutral-500 uppercase tracking-wide mb-0.5">Purpose</p>
                        <p class="font-medium text-gray-900 dark:text-neutral-100 break-words">{{ $printRequest->purpose }}</p>
                    </div>
                </div>

                {{-- Items table - responsive --}}
                <div class="border border-gray-200 dark:border-neutral-700 rounded-lg overflow-hidden mb-8 sm:mb-10 print:border-gray-300">
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-neutral-900/60">
                                <tr>
                                    <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400 whitespace-nowrap">
                                        Type
                                    </th>
                                    <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400">
                                        Item / Facility
                                    </th>
                                    <th scope="col" class="px-3 sm:px-4 py-2.5 text-center text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400 whitespace-nowrap">
                                        Qty
                                    </th>
                                    <th scope="col" class="px-3 sm:px-4 py-2.5 text-left text-[11px] font-semibold uppercase tracking-wider text-gray-500 dark:text-neutral-400 whitespace-nowrap">
                                        Schedule
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-neutral-700">
                                @foreach($printRequest->items as $item)
                                    @php
                                        $isFacility = is_null($item->resource_id);
                                    @endphp
                                    <tr>
                                        <td class="px-3 sm:px-4 py-3 whitespace-nowrap">
                                            @if($isFacility)
                                                <span class="inline-flex items-center py-0.5 px-2 text-[10px] font-semibold uppercase rounded-full bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-400">
                                                    Facility
                                                </span>
                                            @else
                                                <span class="inline-flex items-center py-0.5 px-2 text-[10px] font-semibold uppercase rounded-full bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-400">
                                                    Material
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-3 sm:px-4 py-3 font-medium text-gray-900 dark:text-neutral-100">
                                            {{ $item->item_name }}
                                        </td>
                                        <td class="px-3 sm:px-4 py-3 text-center text-gray-600 dark:text-neutral-400">
                                            {{ $isFacility ? ' ' : $item->quantity }}
                                        </td>
                                        <td class="px-3 sm:px-4 py-3 text-gray-600 dark:text-neutral-400 whitespace-nowrap">
                                            @if($item->request_date)
                                                <span class="block">{{ \Carbon\Carbon::parse($item->request_date)->format('M d, Y') }}</span>
                                            @endif
                                            @if($item->start_time && $item->end_time)
                                                <span class="block text-xs text-gray-500 dark:text-neutral-500 mt-0.5">
                                                    {{ \Carbon\Carbon::parse($item->start_time)->format('g:i A') }} – {{ \Carbon\Carbon::parse($item->end_time)->format('g:i A') }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Signatures --}}
                <div class="grid grid-cols-2 gap-6 sm:gap-10 mt-10 sm:mt-12">
                    <div class="text-center">
                        <div class="h-8 sm:h-10"></div>
                        <div class="border-t border-gray-300 dark:border-neutral-600 pt-2">
                            <p class="text-xs font-medium text-gray-700 dark:text-neutral-300">Released By</p>
                            <p class="text-[11px] text-gray-400 dark:text-neutral-500 mt-0.5">Property Custodian</p>
                        </div>
                    </div>
                    <div class="text-center">
                        <div class="h-8 sm:h-10"></div>
                        <div class="border-t border-gray-300 dark:border-neutral-600 pt-2">
                            <p class="text-xs font-medium text-gray-700 dark:text-neutral-300">Received By</p>
                            <p class="text-[11px] text-gray-400 dark:text-neutral-500 mt-0.5">{{ $printRequest->user->name }}</p>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="px-4 sm:px-7 py-3.5 sm:py-4 bg-gray-50 dark:bg-neutral-900/40 border-t border-gray-200 dark:border-neutral-700">
                <p class="text-[11px] leading-relaxed text-center text-gray-400 dark:text-neutral-500">
                    This is an official receipt from the CSAV Resource Management System.<br>
                    Please present this document at pickup or on the reserved date as proof of approval.
                </p>
            </div>

        </div>

    @endif

</div>
