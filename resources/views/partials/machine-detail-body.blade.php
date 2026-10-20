@php($detailMetrics = $detail->productionMetrics())
@php($detailOrder = $detailMetrics->currentOrder() ?? $detailMetrics->nextActivatedOrder())
@php($detailNextOrder = $detailMetrics->nextActivatedOrder())
@php($detailNextOrder = $detailNextOrder?->is($detailOrder) ? null : $detailNextOrder)
@php($detailMould = $detailOrder?->mould)
<div @class(['space-y-4' => ! ($inline ?? false), 'grid gap-4 lg:grid-cols-5' => ($inline ?? false)])>
<div @class(['grid gap-3 lg:grid-cols-2' => ! ($inline ?? false), 'space-y-3 lg:col-span-2' => ($inline ?? false)])>
                    <section class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-bold text-slate-900">{{ $this->t('machine_details') }}</h3>
                            <span @class(['rounded-full px-2 py-0.5 text-xs font-bold', $this->statusClass($detail->status)])>{{ $this->statusLabel($detail->status) }}</span>
                        </div>
                        <dl class="mt-1.5 grid grid-cols-3 gap-2 text-xs">
                            <div><dt class="text-slate-500">{{ $this->t('opening') }}</dt><dd class="font-semibold">{{ $detail->open_mm ?? '-' }} mm</dd></div>
                            <div><dt class="text-slate-500">{{ $this->t('screw_diameter') }}</dt><dd class="font-semibold">{{ $detail->screw_diameter_cm ?? '-' }} cm</dd></div>
                        </dl>
                        @if ($detail->problem_description)
                            <p class="mt-2 rounded-md bg-red-50 p-2 text-xs text-red-700"><strong>{{ $this->t('problem') }}:</strong> {{ $detail->problem_description }}</p>
                        @endif
                    </section>

                    <section class="rounded-lg border border-slate-200 px-3 py-2">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-bold text-slate-900">{{ $this->t('mounted_mould') }}</h3>
                            @if ($detailMould)
                                <span @class(['rounded-full px-2 py-0.5 text-xs font-bold', $this->statusClass($detailMould->status)])>{{ $this->statusLabel($detailMould->status) }}</span>
                            @endif
                        </div>
                        @if ($detailMould)
                            <dl class="mt-1.5 grid grid-cols-4 gap-2 text-xs">
                                <div class="col-span-2"><dt class="text-slate-500">{{ $this->t('moulds') }}</dt><dd class="font-semibold">{{ $detailMould->name }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('number') }}</dt><dd class="font-semibold">{{ $detailMould->number ?? '-' }}</dd></div>
                            </dl>
                            @if ($detailMould->problem_description)
                                <p class="mt-2 rounded-md bg-red-50 p-2 text-xs text-red-700"><strong>{{ $this->t('problem') }}:</strong> {{ $detailMould->problem_description }}</p>
                            @endif
                        @else
                            <p class="mt-2 rounded-md bg-slate-50 p-2 text-xs text-slate-500">{{ $this->t('no_mounted_mould') }}</p>
                        @endif
                        @if ($detail->moulds->isNotEmpty())
                            <div class="mt-2 flex flex-wrap items-center gap-1.5">
                                <span class="text-[10px] font-semibold uppercase tracking-wide text-slate-500">{{ $this->t('compatible_moulds') }}:</span>
                                @foreach ($detail->moulds as $compatible)
                                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] text-slate-700">{{ $compatible->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </section>

</div>
<div @class(['lg:col-span-3' => ($inline ?? false)])>
                    <section class="rounded-lg border border-teal-100 bg-teal-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-teal-950">{{ $this->t('order_details') }}</h3>
                            @if ($detailOrder)
                                <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($detailOrder->status)])>{{ $this->statusLabel($detailOrder->status) }}</span>
                            @endif
                        </div>
                        @if ($detailOrder)
                            @php($lastQualityCheck = $this->lastQualityCheckAt($detailOrder))
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                                <div><dt class="text-slate-500">{{ $this->t('customer') }}</dt><dd class="font-semibold">{{ $detailOrder->customer }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('actual_cycle') }} ({{ $this->t('theoretical') }} / {{ $this->t('measured') }})</dt><dd class="font-semibold">{{ $detailOrder->cycle_time ? number_format($detailOrder->cycle_time / 1000, 2, ',', ' ').' s' : '-' }} / <span @class(['text-red-600' => $detailOrder->measuredDeviates('cycle_time')])>{{ $detailOrder->measured_cycle_time ? number_format($detailOrder->measured_cycle_time / 1000, 2, ',', ' ').' s' : '-' }}</span></dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('net_weight') }} ({{ $this->t('theoretical') }} / {{ $this->t('measured') }})</dt><dd class="font-semibold">{{ $detailOrder->net_weight !== null ? $detailOrder->net_weight.' g' : '-' }} / <span @class(['text-red-600' => $detailOrder->measuredDeviates('net_weight')])>{{ $detailOrder->measured_net_weight !== null ? $detailOrder->measured_net_weight.' g' : '-' }}</span></dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('gross_weight') }} ({{ $this->t('theoretical') }} / {{ $this->t('measured') }})</dt><dd class="font-semibold">{{ $detailOrder->gross_weight !== null ? $detailOrder->gross_weight.' g' : '-' }} / <span @class(['text-red-600' => $detailOrder->measuredDeviates('gross_weight')])>{{ $detailOrder->measured_gross_weight !== null ? $detailOrder->measured_gross_weight.' g' : '-' }}</span></dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('color_percentage') }} ({{ $this->t('theoretical') }} / {{ $this->t('measured') }})</dt><dd class="font-semibold">{{ $detailOrder->color_percentage !== null ? number_format((float) $detailOrder->color_percentage, 2, ',', ' ').'%' : '-' }} / <span @class(['text-red-600' => $detailOrder->measuredDeviates('color_percentage')])>{{ $detailOrder->measured_color_percentage !== null ? number_format((float) $detailOrder->measured_color_percentage, 2, ',', ' ').'%' : '-' }}</span></dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('planned_start') }}</dt><dd class="font-semibold">{{ $detailOrder->date?->format('Y-m-d') ?? '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('actual_start') }}</dt><dd class="font-semibold">{{ $this->productionStartedAt($detailOrder)?->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i') ?? '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('planned_end') }}</dt><dd class="font-semibold">{{ $detailOrder->planned_end_date?->format('Y-m-d') ?? '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('actual_end') }}</dt><dd class="font-semibold">{{ $detailOrder->ended_at?->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i') ?? '-' }}</dd></div>
                            </dl>
                            @if ($detailOrder->product?->material || $detailOrder->product?->color || $detailOrder->product?->additives?->isNotEmpty())
                                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-slate-500">{{ $this->t('material') }}</dt><dd class="font-semibold">{{ $detailOrder->product?->material?->name ?? '-' }}</dd></div>
                                    <div><dt class="text-slate-500">{{ $this->t('color') }}</dt><dd class="font-semibold">{{ $detailOrder->product?->color?->name ?? '-' }}</dd></div>
                                    <div class="col-span-2"><dt class="text-slate-500">{{ $this->t('additives') }}</dt><dd class="font-semibold">{{ $detailOrder->product?->additives?->pluck('name')->join(', ') ?: '-' }}</dd></div>
                                </dl>
                            @endif
                            @include('partials.production-progress-bar', ['order' => $detailOrder])
                            <div class="mt-3">
                                <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($detailOrder))])>{{ $this->t('quality') }}: {{ $this->statusLabel($this->qualityStatus($detailOrder)) }}</span>
                                <p @class(['mt-2 text-xs font-medium', 'text-red-700' => $this->qualityCheckOverdue($detailOrder), 'text-slate-600' => ! $this->qualityCheckOverdue($detailOrder)])>
                                    {{ $lastQualityCheck ? $this->t('last_quality_check_at', ['time' => $lastQualityCheck->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s')]) : $this->t('no_quality_check_yet') }}
                                </p>
                                @if ($this->qualityCheckOverdue($detailOrder))
                                    <p class="mt-1 text-xs font-bold text-red-700">{{ $this->t('quality_check_overdue') }}</p>
                                @endif
                            </div>
                            @if ($detailOrder->pause_description && $detailOrder->status === 'paused')
                                <p class="mt-3 rounded-md bg-amber-50 p-3 text-sm text-amber-800"><strong>{{ $this->t('pause_reason') }}:</strong> {{ $detailOrder->pause_description }}</p>
                            @endif
                            <div class="mt-4 flex flex-wrap gap-2">
                                @if ($this->canManageOrders())
                                    <button wire:click="openOrderModal({{ $detailOrder->id }})" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('manage_order') }}</button>
                                @endif
                                @if ($this->canEnterMeasuredData())
                                    <button wire:click="openMeasuredModal({{ $detailOrder->id }})" class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100">{{ $this->t('measured_data') }}</button>
                                @endif
                                @if ($this->canCheckQuality() && ! $detailOrder->hasMeasuredData())
                                    <p class="w-full rounded-md bg-amber-50 p-2 text-xs font-semibold text-amber-800">{{ $this->t('measured_data_required') }}</p>
                                @elseif ($this->canCheckQuality())
                                    <button wire:click="openQualityModal({{ $detailOrder->id }})" class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100">{{ $this->t($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY) ? 'color_check' : 'quality_check') }}</button>
                                @endif
                            </div>
                        @else
                            <p class="mt-3 rounded-lg bg-white p-3 text-sm text-slate-500">{{ $this->t('no_running_order') }}</p>
                        @endif
                        @if ($detailNextOrder)
                            <div class="mt-4 rounded-lg bg-white/80 p-3 text-sm">
                                <h4 class="font-semibold">{{ $this->t('next_activated_order') }}</h4>
                                <p class="mt-1">{{ $detailNextOrder->order_number }} · {{ $detailNextOrder->customer }} · {{ $detailNextOrder->product?->code ?? $detailNextOrder->product?->name }}</p>
                            </div>
                        @endif
                        @if ($detailMetrics->waitingOrders()->isNotEmpty())
                            <div class="mt-3 rounded-lg bg-white/80 p-3 text-sm">
                                <h4 class="font-semibold">{{ $this->t('waiting_orders') }}</h4>
                                <ul class="mt-1 list-inside list-disc">
                                    @foreach ($detailMetrics->waitingOrders() as $waitingOrder)
                                        <li>{{ $waitingOrder->order_number }} · {{ $waitingOrder->customer }} · {{ $waitingOrder->product?->code ?? $waitingOrder->product?->name }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                    </section>
</div>
@if ($detailOrder && ! ($inline ?? false))
<div class="flex justify-end">
    <button wire:click="openProductionProtocol({{ $detailOrder->id }})" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100">{{ $this->t('production_protocol') }}</button>
</div>
@endif
</div>
