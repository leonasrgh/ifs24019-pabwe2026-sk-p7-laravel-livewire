@props(['stock'])
@php
    $status = $stock->statusEnum();
@endphp
{{-- Status disampaikan lewat teks + ikon + warna, tidak hanya warna (WCAG 1.4.1) --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-bold '.$status->badgeClass()]) }}>
    @if ($status === \App\Features\Stock\Enums\StockStatus::AVAILABLE)
        <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
    @elseif ($status === \App\Features\Stock\Enums\StockStatus::LOW_STOCK)
        <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.9L2.4 18a2 2 0 001.7 3h15.8a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z"/></svg>
    @else
        <svg class="h-3.5 w-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
    @endif
    {{ $status->label() }}
</span>
