@php
    $actualPercent = $this->progress($order);
    $expectedPercent = $this->expectedProgress($order);
    $expectedQuantity = $this->expectedQuantity($order);
@endphp
<div class="mt-2">
    <div class="relative h-3 overflow-hidden rounded-full bg-teal-100">
        @if ($expectedPercent !== null)
            <div class="absolute inset-y-0 left-0 rounded-full bg-red-400" style="width: {{ $expectedPercent }}%" title="{{ $this->t('expected_quantity') }}: {{ $expectedQuantity }} db"></div>
        @endif
        <div class="absolute left-0 top-0 h-2 rounded-full bg-teal-600" style="width: {{ $actualPercent }}%" title="{{ $this->t('current_quantity') }}: {{ $order->current_quantity }} db"></div>
    </div>
    @if ($expectedQuantity !== null)
        <p class="mt-1 flex items-center gap-1 text-xs text-red-700">
            <span class="inline-block h-1.5 w-4 rounded-full bg-red-400"></span>
            {{ $this->t('expected_quantity') }}: {{ number_format($expectedQuantity, 0, ',', ' ') }} db
        </p>
    @endif
</div>
