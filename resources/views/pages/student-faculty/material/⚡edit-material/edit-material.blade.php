<div class="select-none">
    <div class="max-w-4xl mx-auto px-3 py-6 sm:px-6 lg:px-8 lg:py-14">
        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-xl shadow-sm overflow-hidden">

            <div class="px-4 py-4 sm:px-6 border-b border-gray-200 dark:border-neutral-700">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 dark:text-neutral-200">Edit Material Request</h2>
                <p class="text-xs sm:text-sm text-gray-600 dark:text-neutral-400">Update your request details</p>
            </div>

            <div class="p-4 sm:p-6">
                @if (session()->has('success'))
                    <div class="mb-4 p-3 sm:p-4 rounded-lg bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 border border-green-200 dark:border-green-800 text-xs sm:text-sm">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mb-4 p-3 sm:p-4 rounded-lg bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800 text-xs sm:text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                <form wire:submit="update" class="space-y-6">
                    {{-- Purpose --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            Purpose <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="purpose" rows="3"
                            placeholder="State your purpose for requesting these materials..."
                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-blue-500 focus:outline-none"></textarea>
                        @error('purpose')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Dynamic Items --}}
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300">
                                Materials <span class="text-red-500">*</span>
                            </label>
                            <button type="button" wire:click="addItem"
                                class="px-3 py-1.5 text-xs sm:text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                                + Add Item
                            </button>
                        </div>

                        @foreach($items as $index => $item)
                            <div class="p-3 sm:p-0 bg-gray-50 sm:bg-transparent dark:bg-neutral-700/40 sm:dark:bg-transparent rounded-lg sm:rounded-none border sm:border-0 border-gray-200 dark:border-neutral-700 flex flex-col sm:flex-row gap-3 sm:items-start relative">
                                <div class="flex-1">
                                    <label class="block sm:hidden text-xs text-gray-500 dark:text-neutral-400 mb-1">Item Name</label>
                                    <input type="text" wire:model="items.{{ $index }}.name"
                                        placeholder="Material name"
                                        class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    @error("items.{$index}.name")
                                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div class="flex items-center gap-3 sm:w-28">
                                    <div class="w-full">
                                        <label class="block sm:hidden text-xs text-gray-500 dark:text-neutral-400 mb-1">Quantity</label>
                                        <input type="number" wire:model="items.{{ $index }}.quantity"
                                            placeholder="Qty" min="1"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    </div>
                                    @if(count($items) > 1)
                                        <button type="button" wire:click="removeItem({{ $index }})"
                                            class="sm:hidden text-red-500 hover:text-red-700 p-2 mt-4 self-end">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                                @if(count($items) > 1)
                                    <button type="button" wire:click="removeItem({{ $index }})"
                                        class="hidden sm:block mt-2 text-red-500 hover:text-red-700 transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    {{-- Buttons --}}
                    <div class="flex flex-col-reverse sm:flex-row gap-3 pt-4 border-t border-gray-100 dark:border-neutral-700/50">
                        <button type="button" wire:click="cancel"
                            class="w-full sm:w-auto px-6 py-2.5 bg-gray-500 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition text-center">
                            Cancel
                        </button>
                        <button type="submit"
                            class="w-full sm:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-medium transition text-center">
                            Update Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
