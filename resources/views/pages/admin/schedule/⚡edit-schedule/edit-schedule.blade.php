<div>
<div class="max-w-3xl mx-auto px-4 py-10 sm:px-6 lg:px-8 lg:py-14">
    <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow overflow-hidden">

        {{-- Header --}}
        <div class="px-6 py-4 border-b border-gray-200 dark:border-neutral-700 flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">Edit Facility</h2>
                <p class="text-sm text-gray-600 dark:text-neutral-400">Update this facility</p>
            </div>
            <a href="{{ route('admin.schedule') }}"
                class="text-sm text-gray-500 hover:text-gray-700 dark:text-neutral-400 dark:hover:text-neutral-200 transition">
                ← Back
            </a>
        </div>

        <div class="p-6">

            @if(session()->has('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-800 rounded-xl text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="submit" class="space-y-5">

                @include('partials.facility-form')

                @if ($this->upcomingCount > 0)
                    <p class="text-xs text-gray-500 dark:text-neutral-400">
                        This room has {{ $this->upcomingCount }} upcoming {{ \Illuminate\Support\Str::plural('booking', $this->upcomingCount) }}.
                        If you rename it, they move to the new name automatically.
                    </p>
                @endif

                {{-- Buttons --}}
                <div class="flex gap-3 pt-2">
                    <button type="submit" wire:loading.attr="disabled" wire:target="submit"
                        class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition disabled:opacity-60">
                        <span wire:loading.remove wire:target="submit">Update Facility</span>
                        <span wire:loading wire:target="submit">Saving…</span>
                    </button>
                    <button type="button" wire:click="cancel"
                        class="px-6 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-medium text-sm transition">
                        Cancel
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
</div>
