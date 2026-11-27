<div class="min-h-screen bg-gradient-to-br from-green-100 via-white to-yellow-100 flex items-center justify-center px-6 py-10">
    <div class="max-w-md w-full">

        <div class="flex justify-center mb-5">
            <div class="w-20 h-20 rounded-full bg-green-100 border-4 border-green-600 flex items-center justify-center">
                <svg class="w-10 h-10 text-green-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
            </div>
        </div>

        <h1 class="text-2xl font-extrabold text-green-900 text-center mb-1">Choose your own password</h1>
        <p class="text-gray-600 text-sm text-center mb-6">
            Hi <span class="font-semibold text-green-700">{{ auth()->user()->name }}</span>, you signed in with a password the Admin gave you.
            Please pick a new one that only you know before you continue.
        </p>

        <form wire:submit="save" class="bg-white rounded-2xl shadow-lg border border-green-200 p-6 space-y-4">
            <div>
                <label for="new-password" class="block text-sm font-medium text-gray-700 mb-1.5">New password</label>
                <input id="new-password" type="password" wire:model="new_password" autocomplete="new-password" minlength="8" required
                    class="w-full py-2.5 px-3.5 rounded-lg border border-gray-300 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/20 focus:outline-none">
                <p class="mt-1 text-xs text-gray-500">At least 8 characters.</p>
                @error('new_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="new-password-confirm" class="block text-sm font-medium text-gray-700 mb-1.5">Type it again</label>
                <input id="new-password-confirm" type="password" wire:model="new_password_confirmation" autocomplete="new-password" required
                    class="w-full py-2.5 px-3.5 rounded-lg border border-gray-300 text-sm focus:border-green-600 focus:ring-2 focus:ring-green-600/20 focus:outline-none">
            </div>

            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                class="w-full py-2.5 rounded-lg bg-green-700 text-white text-sm font-semibold hover:bg-green-800 disabled:opacity-60 transition">
                <span wire:loading.remove wire:target="save">Save and continue</span>
                <span wire:loading wire:target="save">Saving…</span>
            </button>
        </form>

        <div class="mt-4 text-center text-sm text-gray-500">
            Not you? <livewire:auth::logout />
        </div>
    </div>
</div>
