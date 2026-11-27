<div class="space-y-3">
    @if (session()->has('message'))
        <div class="p-3 rounded-lg bg-green-50 border border-green-200 text-green-700 text-sm dark:bg-green-900/20 dark:border-green-800 dark:text-green-400">{{ session('message') }}</div>
    @endif
    @if (session()->has('error'))
        <div class="p-3 rounded-lg bg-red-50 border border-red-200 text-red-700 text-sm dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">{{ session('error') }}</div>
    @endif

    {{-- What this list is --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-gray-600 dark:text-neutral-400 max-w-2xl">
            @if ($scope === 'me')
                Items you <strong>keep</strong> — no return date (for example furniture for your faculty room). If you stop teaching, they are handed over to the next teacher.
            @else
                Items people <strong>keep</strong> — no return date, never overdue (for example a table and chair for a faculty room).
                When someone stops teaching, <strong>hand them over</strong> to the next teacher.
            @endif
        </p>
        @if ($scope !== 'me')
            <div class="flex items-center gap-2">
                @if ($this->holderName)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium rounded-full bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">
                        Only {{ $this->holderName }}
                        <button type="button" wire:click="clearHolder" class="hover:underline" aria-label="Show everyone">✕</button>
                    </span>
                @endif
                <label for="ai-search-{{ $scope }}" class="sr-only">Search assigned items</label>
                <input id="ai-search-{{ $scope }}" type="search" wire:model.live.debounce.300ms="search" placeholder="Search item, person, department…"
                    class="w-full sm:w-64 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
            </div>
        @endif
    </div>

    <div class="@container bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl overflow-hidden">
        <table class="w-full block @3xl:table text-sm">
            <thead class="hidden @3xl:table-header-group bg-gray-50 dark:bg-neutral-900/50 text-left">
                <tr>
                    <th class="ps-4 pe-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Item</th>
                    @if ($scope !== 'me')
                        <th class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Kept by</th>
                    @endif
                    <th class="px-3 py-2.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400">Since</th>
                    <th class="pe-4 ps-3 py-2.5 text-right text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-neutral-400"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="block @3xl:table-row-group divide-y divide-gray-100 dark:divide-neutral-700">
                @forelse ($this->items as $item)
                    @php
                        $returnsTo = \App\Support\Assignments::returnsTo($item);
                        $last      = $item->handovers->first();
                        $role      = $item->holder?->roles->first()?->name;
                    @endphp
                    <tr wire:key="as-{{ $item->id }}" class="block @3xl:table-row px-4 py-3 @3xl:p-0 align-top">
                        <td class="block @3xl:table-cell @3xl:ps-4 @3xl:pe-3 py-1 @3xl:py-3">
                            <p class="font-semibold text-gray-800 dark:text-neutral-100">{{ $item->item_name }} <span class="font-normal text-gray-500 dark:text-neutral-400">×{{ $item->quantity }}</span></p>
                            <p class="text-xs text-gray-500 dark:text-neutral-400">
                                <span class="inline-block px-1.5 py-0.5 rounded bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300 font-semibold text-[10px]">KEPT · NO RETURN DATE</span>
                                @if ($scope !== 'me')
                                    <span class="ms-1">from the {{ $returnsTo === 'main' ? 'main stock' : 'department stock' }}</span>
                                @endif
                            </p>
                        </td>
                        @if ($scope !== 'me')
                            <td class="block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3">
                                <p class="font-medium text-gray-800 dark:text-neutral-100">{{ $item->holder?->name ?? '—' }}</p>
                                <p class="text-xs text-gray-500 dark:text-neutral-400">
                                    {{ $role ? ucwords($role) : '' }}{{ $item->holder?->department ? ' · ' . $item->holder->department->department_name : '' }}
                                    @if ($item->holder && $item->holder->status !== 'approved')
                                        <span class="ms-1 px-1.5 py-0.5 rounded bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300 font-semibold text-[10px]">{{ strtoupper($item->holder->status) }} — HAND OVER</span>
                                    @endif
                                </p>
                            </td>
                        @endif
                        <td class="block @3xl:table-cell @3xl:px-3 py-1 @3xl:py-3 text-gray-700 dark:text-neutral-300">
                            {{ $item->assigned_at?->format('M d, Y') }}
                            @if ($last && $last->kind === 'handed_over')
                                <p class="text-xs text-gray-500 dark:text-neutral-400">Handed over from {{ $last->from?->name ?? 'someone' }} · {{ $last->created_at->format('M d, Y') }}</p>
                            @endif
                            @if ($item->handovers->count() > 1)
                                <details class="mt-0.5 text-xs text-gray-500 dark:text-neutral-400">
                                    <summary class="cursor-pointer hover:underline">History ({{ $item->handovers->count() }})</summary>
                                    <ul class="mt-1 space-y-0.5">
                                        @foreach ($item->handovers as $h)
                                            <li>{{ $h->created_at->format('M d, Y') }} —
                                                {{ match ($h->kind) { 'assigned' => 'assigned to ' . ($h->to?->name ?? '?'), 'handed_over' => ($h->from?->name ?? '?') . ' → ' . ($h->to?->name ?? '?'), default => 'returned' } }}
                                                @if ($h->note) <span class="italic">“{{ $h->note }}”</span> @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        </td>
                        <td class="block @3xl:table-cell @3xl:pe-4 @3xl:ps-3 pt-2 @3xl:py-3 @3xl:text-right @3xl:whitespace-nowrap">
                            @if ($this->canManage())
                                <button type="button" wire:click="openHandOver({{ $item->id }})"
                                    class="min-h-9 px-3 rounded-lg bg-[#123524] text-white text-xs font-semibold hover:bg-[#0B2418] transition">Hand over</button>
                                @if ($this->canTakeBack($item))
                                    <button type="button" wire:click="openReturn({{ $item->id }})"
                                        class="min-h-9 px-3 rounded-lg border border-gray-300 dark:border-neutral-600 text-xs font-semibold text-gray-700 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-neutral-700">Take back</button>
                                @elseif ($scope === 'department')
                                    <p class="mt-1 text-[11px] text-gray-500 dark:text-neutral-400">From the main stock — only the Admin can take it back.</p>
                                @endif
                            @endif
                        </td>
                    </tr>

                    {{-- Hand over form --}}
                    @if ($handingId === $item->id)
                        <tr class="block @3xl:table-row bg-gray-50 dark:bg-neutral-900/50">
                            <td colspan="4" class="block @3xl:table-cell px-4 py-3">
                                <form wire:submit="saveHandOver" class="flex flex-col @2xl:flex-row @2xl:items-end gap-3">
                                    <div class="flex-1">
                                        <label for="ai-to-{{ $item->id }}" class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">Hand over to (same department)</label>
                                        <select id="ai-to-{{ $item->id }}" wire:model="toUserId" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
                                            <option value="">Choose a teacher…</option>
                                            @foreach ($this->recipients as $u)
                                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('toUserId') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                                        @if ($this->recipients->isEmpty())
                                            <p class="text-xs text-amber-700 dark:text-amber-400 mt-1">Nobody else is active in this department.</p>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <label for="ai-note-{{ $item->id }}" class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">Note (optional)</label>
                                        <input id="ai-note-{{ $item->id }}" type="text" wire:model="handNote" maxlength="255" placeholder="e.g. Ms. Reyes retired; same faculty room"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="cancelForms" class="min-h-10 px-3 rounded-lg border border-gray-300 dark:border-neutral-600 text-sm dark:text-neutral-200">Cancel</button>
                                        <button type="submit" wire:loading.attr="disabled" wire:target="saveHandOver" class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418] disabled:opacity-60">Hand over</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif

                    {{-- Take back form --}}
                    @if ($returningId === $item->id)
                        <tr class="block @3xl:table-row bg-gray-50 dark:bg-neutral-900/50">
                            <td colspan="4" class="block @3xl:table-cell px-4 py-3">
                                <form wire:submit="saveReturn" class="flex flex-col @2xl:flex-row @2xl:items-end gap-3">
                                    <div>
                                        <label for="ai-cond-{{ $item->id }}" class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">Condition</label>
                                        <select id="ai-cond-{{ $item->id }}" wire:model="returnCondition" class="px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
                                            @foreach (\App\Models\RequestItem::CONDITIONS as $v => $l)
                                                <option value="{{ $v }}">{{ $l }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="flex-1">
                                        <label for="ai-rn-{{ $item->id }}" class="block text-xs font-medium text-gray-600 dark:text-neutral-300 mb-1">Notes (optional)</label>
                                        <input id="ai-rn-{{ $item->id }}" type="text" wire:model="returnNotes" maxlength="255"
                                            class="w-full px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-800 text-gray-800 dark:text-neutral-200">
                                    </div>
                                    <div class="flex gap-2">
                                        <button type="button" wire:click="cancelForms" class="min-h-10 px-3 rounded-lg border border-gray-300 dark:border-neutral-600 text-sm dark:text-neutral-200">Cancel</button>
                                        <button type="submit" wire:loading.attr="disabled" wire:target="saveReturn"
                                            wire:confirm="Take {{ $item->item_name }} ×{{ $item->quantity }} back from {{ $item->holder?->name }}? Good condition goes back into the {{ $returnsTo === 'main' ? 'main' : 'department' }} stock."
                                            class="min-h-10 px-3 rounded-lg bg-[#123524] text-white text-sm font-semibold hover:bg-[#0B2418] disabled:opacity-60">Take back</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr class="block @3xl:table-row">
                        <td colspan="4" class="block @3xl:table-cell px-4 py-10 text-center text-sm text-gray-500 dark:text-neutral-400">
                            {{ $scope === 'me' ? 'You do not keep any assigned items.' : 'No assigned items.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($this->items->hasPages())
        <div>{{ $this->items->links() }}</div>
    @endif
</div>
