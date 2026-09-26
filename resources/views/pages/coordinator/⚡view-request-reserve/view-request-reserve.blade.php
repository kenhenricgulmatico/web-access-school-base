<div class="select-none">
<div class="max-w-3xl px-4 py-10 sm:px-6 lg:px-8 lg:py-14 mx-auto">

  {{-- Back --}}
  <a href="{{ route('coordinator.facility') }}" wire:navigate
     class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 hover:text-gray-800 dark:text-neutral-400 dark:hover:text-neutral-200 transition mb-5">
    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
      <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
    </svg>
    Back to Reservations
  </a>

  <div class="bg-white dark:bg-neutral-800 border border-gray-200 dark:border-neutral-700 rounded-2xl shadow-sm overflow-hidden">

    {{-- Header --}}
    <div class="px-6 py-5 flex justify-between items-start gap-4 border-b border-gray-100 dark:border-neutral-700">
      <div>
        <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">Request</p>
        <h2 class="text-xl font-semibold text-gray-900 dark:text-neutral-100">{{ $requestModel->purpose }}</h2>
        <p class="text-sm text-gray-500 dark:text-neutral-400 mt-1">
          Submitted {{ $requestModel->created_at->format('M d, Y · h:i A') }}
        </p>
      </div>

      <span class="shrink-0 inline-flex items-center gap-1.5 px-3 py-1 text-xs font-semibold rounded-full
        @if($requestModel->status === 'pending') bg-yellow-100 text-yellow-800
        @elseif($requestModel->status === 'approved') bg-green-100 text-green-800
        @elseif($requestModel->status === 'rejected') bg-red-100 text-red-800
        @endif">
        <span class="w-1.5 h-1.5 rounded-full
          @if($requestModel->status === 'pending') bg-yellow-500
          @elseif($requestModel->status === 'approved') bg-green-500
          @elseif($requestModel->status === 'rejected') bg-red-500
          @endif"></span>
        @if($requestModel->status === 'pending') Pending
        @elseif($requestModel->status === 'approved') Approved
        @elseif($requestModel->status === 'rejected') Rejected
        @endif
      </span>
    </div>

    <div class="px-6 py-6 space-y-6">

      {{-- Requestor / Department --}}
      <div class="grid grid-cols-2 gap-6">
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 shrink-0 rounded-full bg-gray-100 dark:bg-neutral-700 flex items-center justify-center text-sm font-semibold text-gray-600 dark:text-neutral-300">
            {{ strtoupper(substr($requestModel->user->name, 0, 1)) }}
          </div>
          <div>
            <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Requestor</p>
            <p class="text-sm font-medium text-gray-800 dark:text-neutral-200">{{ $requestModel->user->name }}</p>
            <p class="text-xs text-gray-500 dark:text-neutral-500">{{ $requestModel->user->email }}</p>
          </div>
        </div>
        <div>
          <p class="text-xs font-medium text-gray-400 uppercase tracking-wide">Department</p>
          <p class="text-sm text-gray-700 dark:text-neutral-300 mt-1.5">{{ $requestModel->user->department->department_name ?? 'N/A' }}</p>
        </div>
      </div>

      {{-- Facility / Materials --}}
      @php
        $facilityItem  = $requestModel->items->firstWhere('resource_id', null);
        $materialItems = $requestModel->items->whereNotNull('resource_id');
      @endphp

      @if($facilityItem || $materialItems->isNotEmpty())
      <div class="border-t border-gray-100 dark:border-neutral-700 pt-6 space-y-5">

        @if($facilityItem)
        <div>
          <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V7l8-4v18M13 21V11l6 3v7M9 9v.01M9 12v.01M9 15v.01" />
            </svg>
            Facility
          </p>
          <div class="bg-gray-50 dark:bg-neutral-900/40 rounded-lg px-4 py-3 flex items-center justify-between gap-4">
            <p class="text-sm font-semibold text-gray-800 dark:text-neutral-200">{{ $facilityItem->item_name }}</p>
            <p class="text-sm text-gray-600 dark:text-neutral-400 text-right shrink-0">
              {{ \Carbon\Carbon::parse($facilityItem->request_date)->format('M d, Y') }}
              @if($facilityItem->start_time && $facilityItem->end_time)
                <br class="sm:hidden">
                <span class="text-gray-400">·</span>
                {{ \Carbon\Carbon::parse($facilityItem->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($facilityItem->end_time)->format('h:i A') }}
              @endif
            </p>
          </div>
        </div>
        @endif

        @if($materialItems->isNotEmpty())
        <div>
          <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-2 flex items-center gap-1.5">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
            Materials
          </p>
          <div class="bg-gray-50 dark:bg-neutral-900/40 rounded-lg divide-y divide-gray-200 dark:divide-neutral-700">
            @foreach($materialItems as $material)
              <div class="px-4 py-2.5 flex items-center justify-between">
                <p class="text-sm text-gray-700 dark:text-neutral-300">{{ $material->item_name }}</p>
                <span class="text-xs font-medium text-red-600 bg-red-50 dark:bg-red-950/30 px-2 py-0.5 rounded-full">x{{ $material->quantity }}</span>
              </div>
            @endforeach
          </div>
        </div>
        @endif

      </div>
      @endif

    </div>

    {{-- Actions --}}
    @if($requestModel->status === 'pending')
    <div class="px-6 py-4 bg-gray-50 dark:bg-neutral-900/40 border-t border-gray-100 dark:border-neutral-700 flex justify-end gap-2">
      <button wire:click="reject"
              wire:confirm="Reject this request?"
              class="px-4 py-2 text-sm font-medium bg-white dark:bg-neutral-800 text-red-600 border border-red-200 dark:border-red-900 rounded-lg hover:bg-red-50 dark:hover:bg-red-950/30 transition">
        Reject
      </button>
      <button wire:click="accept"
              wire:confirm="Accept this request?"
              class="px-4 py-2 text-sm font-medium bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
        Accept
      </button>
    </div>
    @endif

  </div>
</div>
</div>
