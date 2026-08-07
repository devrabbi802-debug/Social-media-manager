@php
    $badge = match ($status ?? '') {
        'draft' => 'bg-gray-100 text-gray-700',
        'ordered' => 'bg-blue-100 text-blue-700',
        'partially_received' => 'bg-yellow-100 text-yellow-800',
        'received' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-red-100 text-red-700',
        default => 'bg-gray-100 text-gray-700',
    };
    $label = match ($status ?? '') {
        'draft' => 'Draft',
        'ordered' => 'Ordered',
        'partially_received' => 'Partially Received',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
        default => ucfirst($status ?? ''),
    };
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $label }}</span>
