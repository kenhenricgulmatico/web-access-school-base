<div class="select-none">
    <div class="max-w-2xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">

        {{-- Back --}}
        <a href="{{ route('admin.schedule') }}" wire:navigate
           class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-200 transition mb-6">
            <svg class="size-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
            Back to Facilities
        </a>

        <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm p-6 sm:p-8">

            {{-- Header --}}
            <div class="mb-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white">
                    Add Facility
                </h2>
                <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
                    Add a new facility that faculty and program heads can reserve.
                </p>
            </div>

            {{-- Flash --}}
            @if(session()->has('success'))
                <div class="mb-5 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session()->has('error'))
                <div class="mb-5 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-400 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form wire:submit="submit" class="space-y-6">

                {{-- Facility Name --}}
                <div>
                    <label for="facility-name" class="block mb-2 text-sm font-medium text-gray-700 dark:text-neutral-300">
                        Facility Name <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="facility-name"
                        wire:model="facility_name"
                        placeholder="e.g. RM 207, LAB 1, IS FIELD"
                        class="w-full py-2.5 px-4 border border-gray-300 dark:border-neutral-600 rounded-lg text-sm
                               bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200
                               focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none
                               transition">
                    @error('facility_name')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Status --}}
                <div>
                    <label for="facility-status" class="block mb-2 text-sm font-medium text-gray-700 dark:text-neutral-300">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select
                        id="facility-status"
                        wire:model="status"
                        class="w-full py-2.5 px-4 border border-gray-300 dark:border-neutral-600 rounded-lg text-sm
                               bg-white dark:bg-neutral-900 text-gray-800 dark:text-neutral-200
                               focus:border-green-500 focus:ring-2 focus:ring-green-500/20 focus:outline-none
                               transition">
                        <option value="available">Available</option>
                        <option value="maintenance">Under Maintenance</option>
                    </select>
                    @error('status')
                        <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Buttons --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 dark:border-neutral-700">
                    <a href="{{ route('admin.schedule') }}" wire:navigate
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
                               bg-green-600 text-white hover:bg-green-700
                               focus:outline-none focus:ring-2 focus:ring-green-500/30
                               disabled:opacity-50 disabled:pointer-events-none transition">
                        <span wire:loading.remove wire:target="submit">Save Facility</span>
                        <span wire:loading wire:target="submit">Saving...</span>
                    </button>
                </div>

            </form>
        </div>

    </div>
</div>
