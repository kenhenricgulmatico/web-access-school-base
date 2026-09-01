<div class="select-none">
<div class="max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">
    <div class="flex flex-col">
        <div class="overflow-x-auto">
            <div class="min-w-full inline-block align-middle">
                <div class="bg-white dark:bg-neutral-800 border rounded-xl shadow overflow-hidden">

                    {{-- Header --}}
                    <div class="px-6 py-4 flex justify-between items-center border-b">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-neutral-200">My Requests to Admin</h2>
                            <p class="text-sm text-gray-600 dark:text-neutral-400">Track the status of your own requests sent to admin</p>
                        </div>
                        <a href="{{ route('coordinator.request-to-admin.create-request') }}"
                            class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                            + New Request
                        </a>
                    </div>

                    {{-- Flash Messages --}}
                    @if(session()->has('success'))
                        <div class="mx-6 mt-4 p-3 rounded-lg bg-green-100 text-green-800 border border-green-200 text-sm">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session()->has('error'))
                        <div class="mx-6 mt-4 p-3 rounded-lg bg-red-100 text-red-800 border border-red-200 text-sm">
                            {{ session('error') }}
                        </div>
                    @endif

                    {{-- Table --}}
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-neutral-700">
                        <thead class="bg-gray-50 dark:bg-neutral-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">#</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Items</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Purpose</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold uppercase text-gray-500">Admin Status</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-neutral-700">
                            @forelse($this->requests as $index => $request)
                                @php
                                    $isMaterial = $request->request_type_id == 2;
                                @endphp
                                <tr class="hover:bg-gray-50 dark:hover:bg-neutral-700 transition align-top">

                                    <td class="px-6 py-4 text-sm text-gray-500">{{ $index + 1 }}</td>

                                    {{-- Type --}}
                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs font-medium rounded-full
                                            @if($request->request_type_id == 1) bg-green-100 text-green-700
                                            @else bg-red-100 text-red-700 @endif">
                                            {{ $request->requestType->type_name ?? 'N/A' }}
                                        </span>
                                    </td>

                                    {{-- Items --}}
                                    <td class="px-6 py-4">
                                        <ul class="text-xs space-y-1 {{ $isMaterial ? 'text-red-600' : 'text-gray-600 dark:text-neutral-400' }}">
                                            @foreach($request->items as $item)
                                                <li class="flex items-center gap-1.5 flex-wrap">
                                                    @if($isMaterial)
                                                        <span class="w-1 h-1 rounded-full bg-red-500 inline-block"></span>
                                                    @else
                                                        <span>•</span>
                                                    @endif
                                                    <span class="font-medium">{{ $item->item_name ?? 'N/A' }}</span>
                                                    @if($isMaterial)
                                                        <span class="text-red-400">x{{ $item->quantity }}</span>
                                                    @endif
                                                    @if($item->request_date)
                                                        <span class="text-gray-400">({{ \Carbon\Carbon::parse($item->request_date)->format('M d, Y') }})</span>
                                                    @endif
                                                    @if($item->start_time && $item->end_time)
                                                        <span class="text-gray-400">{{ \Carbon\Carbon::parse($item->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($item->end_time)->format('h:i A') }}</span>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>

                                    {{-- Purpose --}}
                                    <td class="px-6 py-4 text-sm text-gray-600 dark:text-neutral-400">
                                        {{ Str::limit($request->purpose, 40) }}
                                    </td>

                                    {{-- Date --}}
                                    <td class="px-6 py-4 text-sm text-gray-400">
                                        {{ $request->created_at->format('M d, Y') }}
                                    </td>

                                    {{-- Admin Status --}}
                                    <td class="px-6 py-4">
                                        @if($request->status === 'pending')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-700"> Pending</span>
                                        @elseif($request->status === 'admin_review')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-700"> Admin Reviewing</span>
                                        @elseif($request->status === 'coordinator_review')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-700">Admin Accepted</span>
                                        @elseif($request->status === 'approved')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-700">Approved</span>
                                        @elseif($request->status === 'rejected')
                                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-700">Rejected</span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4 text-right space-x-2">
                                        @if($request->status === 'pending')
                                            <button wire:click="openEdit({{ $request->id }})"
                                                class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                                                Edit
                                            </button>
                                            <button wire:click="delete({{ $request->id }})"
                                                wire:confirm="Are you sure you want to delete this request?"
                                                class="px-3 py-1 text-xs bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                                                Delete
                                            </button>
                                        @else
                                            <span class="text-xs text-gray-400">No actions</span>
                                        @endif
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-10 text-center text-gray-500">
                                        You have not submitted any requests to admin yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Edit Modal --}}
@if($showEditModal)
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 px-4">
    <div class="bg-white dark:bg-neutral-800 rounded-2xl shadow-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 dark:border-neutral-700">
            <h2 class="text-lg font-semibold text-gray-800 dark:text-neutral-200">Edit Request</h2>
            <button wire:click="closeEdit"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-neutral-300 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="px-6 py-5 space-y-5">

            {{-- Purpose --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                    Purpose <span class="text-red-500">*</span>
                </label>
                <textarea wire:model="purpose" rows="3"
                    placeholder="State your purpose..."
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm"></textarea>
                @error('purpose')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Date --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                    Date <span class="text-red-500">*</span>
                </label>
                <input type="date" wire:model="request_date"
                    min="{{ date('Y-m-d') }}"
                    class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                @error('request_date')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Facility Fields --}}
            @if($request_type_id == 1)
            <div class="space-y-4 p-4 bg-green-50 dark:bg-green-900/20 rounded-xl border border-green-200 dark:border-green-700">
                <p class="text-sm font-semibold text-green-700 dark:text-green-300">Facility Details</p>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                        Facility Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" wire:model="facility_name"
                        placeholder="e.g. Room 101, Computer Lab"
                        class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                    @error('facility_name')
                        <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            Start Time <span class="text-red-500">*</span>
                        </label>
                        <input type="time" wire:model="start_time"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                        @error('start_time')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-neutral-300 mb-1">
                            End Time <span class="text-red-500">*</span>
                        </label>
                        <input type="time" wire:model="end_time"
                            class="w-full px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                        @error('end_time')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
            @endif

            {{-- Material Fields --}}
            @if($request_type_id == 2)
            <div class="space-y-4 p-4 bg-red-50 dark:bg-red-900/20 rounded-xl border border-red-200 dark:border-red-700">
                <p class="text-sm font-semibold text-red-700 dark:text-red-300">Materials Needed</p>

                <div class="space-y-3">
                    @foreach($items as $index => $item)
                        <div class="flex gap-3 items-center">
                            <input type="text"
                                wire:model="items.{{ $index }}.name"
                                placeholder="Material name"
                                class="flex-1 px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                            <input type="number"
                                wire:model="items.{{ $index }}.quantity"
                                placeholder="Qty" min="1"
                                class="w-20 px-3 py-2 rounded-lg border border-gray-300 dark:border-neutral-600 bg-white dark:bg-neutral-700 text-gray-800 dark:text-neutral-200 text-sm">
                            @if(count($items) > 1)
                                <button type="button" wire:click="removeItem({{ $index }})"
                                    class="text-red-500 hover:text-red-700 font-bold text-lg px-2">
                                    &times;
                                </button>
                            @endif
                        </div>
                        @error("items.{$index}.name")
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                        @error("items.{$index}.quantity")
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    @endforeach
                </div>

                <button type="button" wire:click="addItem"
                    class="mt-2 px-4 py-2 text-sm bg-red-600 text-white rounded-lg hover:bg-red-700 transition">
                    + Add Item
                </button>
            </div>
            @endif

        </div>

        {{-- Modal Footer --}}
        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-gray-200 dark:border-neutral-700">
            <button wire:click="closeEdit"
                class="px-4 py-2 text-sm bg-gray-500 hover:bg-gray-600 text-white rounded-lg transition">
                Cancel
            </button>
            <button wire:click="saveEdit"
                class="px-4 py-2 text-sm bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition">
                Save Changes
            </button>
        </div>

    </div>
</div>
@endif

</div>
