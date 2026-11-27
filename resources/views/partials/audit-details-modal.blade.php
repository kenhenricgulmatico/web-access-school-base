{{-- ✅ "Record Details" pop-up for audit logs (Admin + Program Head). Open it with:
     $dispatch('open-modal', {title, rows: [{label, value}]}) --}}
<div x-data="{ showModal: false, details: { title: '', rows: [] } }"
     x-on:open-modal.window="showModal = true; details = $event.detail"
     x-show="showModal"
     x-cloak
     @keydown.escape.window="showModal = false"
     class="fixed inset-0 z-50 overflow-y-auto">

    {{-- Backdrop --}}
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"
         x-show="showModal"
         x-transition.opacity.duration.150ms
         @click="showModal = false"></div>

    {{-- Panel --}}
    <div class="flex items-center justify-center min-h-screen px-4 py-6">
        <div x-show="showModal"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 translate-y-2 scale-[0.98]"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative w-full max-w-2xl bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl border border-gray-200 dark:border-neutral-700 overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="shrink-0 size-10 rounded-full bg-[#E8F3EC] dark:bg-[#1C6B45]/40 flex items-center justify-center">
                        <svg class="size-5 text-[#1C6B45] dark:text-[#7FBF8E]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z"/>
                        </svg>
                    </div>
                    <div class="min-w-0">
                        <h3 class="text-base font-semibold text-gray-800 dark:text-white leading-tight">
                            Record Details
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-neutral-400" x-text="details.title"></p>
                    </div>
                </div>
                <button type="button" @click="showModal = false" title="Close (Esc)"
                    class="p-2 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 dark:hover:bg-neutral-700 dark:hover:text-neutral-200 transition">
                    <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Body --}}
            <div class="p-6 max-h-[70vh] overflow-y-auto">
                {{-- Readable details: label on the left, value on the right --}}
                <dl x-show="details.rows.length" class="divide-y divide-gray-100 dark:divide-neutral-700 rounded-lg border border-gray-200 dark:border-neutral-700 overflow-hidden">
                    <template x-for="(row, i) in details.rows" :key="i">
                        <div class="grid grid-cols-3 gap-4 px-4 py-2.5 bg-white dark:bg-neutral-800">
                            <dt class="text-xs font-medium text-gray-500 dark:text-neutral-400" x-text="row.label"></dt>
                            <dd class="col-span-2 text-sm text-gray-800 dark:text-neutral-200 whitespace-pre-line break-words" x-text="row.value"></dd>
                        </div>
                    </template>
                </dl>
                <p x-show="! details.rows.length" class="text-sm text-gray-500 dark:text-neutral-400 text-center py-6">
                    No details were recorded for this action.
                </p>
            </div>

            {{-- Footer --}}
            <div class="px-6 py-3.5 bg-gray-50 dark:bg-neutral-900/40 border-t border-gray-200 dark:border-neutral-700 flex justify-end">
                <button type="button" @click="showModal = false"
                    class="px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-700 border border-gray-300 dark:border-neutral-600 text-gray-700 dark:text-neutral-300 rounded-lg hover:bg-gray-50 dark:hover:bg-neutral-600 transition">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>
