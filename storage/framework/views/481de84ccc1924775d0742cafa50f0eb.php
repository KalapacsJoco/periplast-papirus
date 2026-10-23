<?php ($detailMetrics = $detail->productionMetrics()); ?>
<?php ($detailOrder = $detailMetrics->currentOrder() ?? $detailMetrics->nextActivatedOrder()); ?>
<?php ($detailNextOrder = $detailMetrics->nextActivatedOrder()); ?>
<?php ($detailNextOrder = $detailNextOrder?->is($detailOrder) ? null : $detailNextOrder); ?>
<?php ($detailMould = $detailOrder?->mould); ?>
<div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['space-y-4'=> ! ($inline ?? false), 'grid gap-4 lg:grid-cols-5' => ($inline ?? false)]); ?>">
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['grid gap-3 lg:grid-cols-2'=> ! ($inline ?? false), 'space-y-3 lg:col-span-2' => ($inline ?? false)]); ?>">
        <section class="rounded-lg border border-slate-200 px-3 py-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-bold text-slate-900"><?php echo e($this->t('machine_details')); ?></h3>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2 py-0.5 text-xs font-bold', $this->statusClass($detail->status)]); ?>"><?php echo e($this->statusLabel($detail->status)); ?></span>
            </div>
            <dl class="mt-1.5 grid grid-cols-2 gap-2 text-xs">
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('screw_diameter')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detail->screw_diameter_cm ?? '-'); ?> mm</dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('pressure')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detail->pressure ?? '-'); ?> bar</dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('opening')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detail->opening ?? '-'); ?> mm</dd>
                </div>

                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('closing')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detail->closing ?? '-'); ?> mm</dd>
                </div>
                <button
                    class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100">
                    <?php echo e($this->t('repair_log')); ?>

                </button>
            </dl>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->problem_description): ?>
            <p class="mt-2 rounded-md bg-red-50 p-2 text-xs text-red-700"><strong><?php echo e($this->t('problem')); ?>:</strong> <?php echo e($detail->problem_description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

        <section class="rounded-lg border border-slate-200 px-3 py-2">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="text-sm font-bold text-slate-900"><?php echo e($this->t('mounted_mould')); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMould): ?>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2 py-0.5 text-xs font-bold',
                    $this->statusClass($detailMould->status)]); ?>"><?php echo e($this->statusLabel($detailMould->status)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMould): ?>
            <dl class="mt-1.5 grid grid-cols-2 gap-2 text-xs">
                <div class="">
                    <dt class="text-slate-500"><?php echo e($this->t('moulds')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailMould->name); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('number')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailMould->number ?? '-'); ?></dd>
                </div>
                <button
                    class="mt-2 rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100">
                    <?php echo e($this->t('repair_log')); ?>

                </button>
            </dl>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMould->problem_description): ?>
            <p class="mt-2 rounded-md bg-red-50 p-2 text-xs text-red-700"><strong><?php echo e($this->t('problem')); ?>:</strong> <?php echo e($detailMould->problem_description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php else: ?>
            <p class="mt-2 rounded-md bg-slate-50 p-2 text-xs text-slate-500"><?php echo e($this->t('no_mounted_mould')); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->moulds->isNotEmpty()): ?>
            
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>

    </div>
    <div class="<?php echo \Illuminate\Support\Arr::toCssClasses(['lg:col-span-3'=> ($inline ?? false)]); ?>">
        <section class="rounded-lg border border-teal-100 bg-teal-50 p-4">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h3 class="font-bold text-teal-950"><?php echo e($this->t('order_details')); ?></h3>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder): ?>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold',
                    $this->statusClass($detailOrder->status)]); ?>"><?php echo e($this->statusLabel($detailOrder->status)); ?></span>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder): ?>
            <?php ($lastQualityCheck = $this->lastQualityCheckAt($detailOrder)); ?>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('customer')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->customer); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('actual_cycle')); ?> (<?php echo e($this->t('theoretical')); ?> / <?php echo e($this->t('measured')); ?>)</dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->cycle_time ? number_format($detailOrder->cycle_time /
                        1000, 2, ',', ' ').' s' : '-'); ?> / <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-red-600'=>
                            $detailOrder->measuredDeviates('cycle_time')]); ?>"><?php echo e($detailOrder->measured_cycle_time ?
                            number_format($detailOrder->measured_cycle_time / 1000, 2, ',', ' ').' s' : '-'); ?></span>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('net_weight')); ?> (<?php echo e($this->t('theoretical')); ?> / <?php echo e($this->t('measured')); ?>)</dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->net_weight !== null ? $detailOrder->net_weight.' g' : '-'); ?> / <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-red-600'=> $detailOrder->measuredDeviates('net_weight')]); ?>"><?php echo e($detailOrder->measured_net_weight !== null ? $detailOrder->measured_net_weight.' g' : '-'); ?></span></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('gross_weight')); ?> (<?php echo e($this->t('theoretical')); ?> / <?php echo e($this->t('measured')); ?>)</dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->gross_weight !== null ? $detailOrder->gross_weight.' g' :
                        '-'); ?> / <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-red-600'=> $detailOrder->measuredDeviates('gross_weight')]); ?>"><?php echo e($detailOrder->measured_gross_weight !== null ? $detailOrder->measured_gross_weight.' g' :
                            '-'); ?></span></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('color_percentage')); ?> (<?php echo e($this->t('theoretical')); ?> / <?php echo e($this->t('measured')); ?>)</dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->color_percentage !== null ? number_format((float)
                        $detailOrder->color_percentage, 2, ',', ' ').'%' : '-'); ?> / <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['text-red-600'=>
                            $detailOrder->measuredDeviates('color_percentage')]); ?>"><?php echo e($detailOrder->measured_color_percentage !== null ? number_format((float)
                            $detailOrder->measured_color_percentage, 2, ',', ' ').'%' : '-'); ?></span></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('planned_start')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->date?->format('Y-m-d') ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('actual_start')); ?></dt>
                    <dd class="font-semibold"><?php echo e($this->productionStartedAt($detailOrder)?->copy()->timezone(config('app.display_timezone'))->format('Y-m-d
                        H:i') ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('planned_end')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->planned_end_date?->format('Y-m-d') ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('actual_end')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->ended_at?->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i')
                        ?? '-'); ?></dd>
                </div>
            </dl>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder->product?->material || $detailOrder->product?->color ||
            $detailOrder->product?->additives?->isNotEmpty()): ?>
            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('material')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->product?->material?->name ?? '-'); ?></dd>
                </div>
                <div>
                    <dt class="text-slate-500"><?php echo e($this->t('color')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->product?->color?->name ?? '-'); ?></dd>
                </div>
                <div class="col-span-2">
                    <dt class="text-slate-500"><?php echo e($this->t('additives')); ?></dt>
                    <dd class="font-semibold"><?php echo e($detailOrder->product?->additives?->pluck('name')->join(', ') ?: '-'); ?>

                    </dd>
                </div>
            </dl>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php echo $__env->make('partials.production-progress-bar', ['order' => $detailOrder], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="mt-3">
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold',
                    $this->statusClass($this->qualityStatus($detailOrder))]); ?>"><?php echo e($this->t('quality')); ?>: <?php echo e($this->statusLabel($this->qualityStatus($detailOrder))); ?></span>
                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-2 text-xs font-medium', 'text-red-700'=> $this->qualityCheckOverdue($detailOrder),
                    'text-slate-600' => ! $this->qualityCheckOverdue($detailOrder)]); ?>">
                    <?php echo e($lastQualityCheck ? $this->t('last_quality_check_at', ['time' =>
                    $lastQualityCheck->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s')]) :
                    $this->t('no_quality_check_yet')); ?>

                </p>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->qualityCheckOverdue($detailOrder)): ?>
                <p class="mt-1 text-xs font-bold text-red-700"><?php echo e($this->t('quality_check_overdue')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder->pause_description && $detailOrder->status === 'paused'): ?>
            <p class="mt-3 rounded-md bg-amber-50 p-3 text-sm text-amber-800"><strong><?php echo e($this->t('pause_reason')); ?>:</strong> <?php echo e($detailOrder->pause_description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <div class="mt-4 flex flex-wrap gap-2">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageOrders()): ?>
                <button wire:click="openOrderModal(<?php echo e($detailOrder->id); ?>)"
                    class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('manage_order')); ?></button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canEnterMeasuredData()): ?>
                <button wire:click="openMeasuredModal(<?php echo e($detailOrder->id); ?>)"
                    class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100"><?php echo e($this->t('measured_data')); ?></button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCheckQuality() && ! $detailOrder->hasMeasuredData()): ?>
                <p class="w-full rounded-md bg-amber-50 p-2 text-xs font-semibold text-amber-800"><?php echo e($this->t('measured_data_required')); ?></p>
                <?php elseif($this->canCheckQuality()): ?>
                <button wire:click="openQualityModal(<?php echo e($detailOrder->id); ?>)"
                    class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100"><?php echo e($this->t($this->canDo(\App\Enums\Role::CHECK_COLOR) && !
                    $this->canDo(\App\Enums\Role::CHECK_QUALITY) ? 'color_check' : 'quality_check')); ?></button>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
            <?php else: ?>
            <p class="mt-3 rounded-lg bg-white p-3 text-sm text-slate-500"><?php echo e($this->t('no_running_order')); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailNextOrder): ?>
            <div class="mt-4 rounded-lg bg-white/80 p-3 text-sm">
                <h4 class="font-semibold"><?php echo e($this->t('next_activated_order')); ?></h4>
                <p class="mt-1"><?php echo e($detailNextOrder->order_number); ?> · <?php echo e($detailNextOrder->customer); ?> · <?php echo e($detailNextOrder->product?->code ?? $detailNextOrder->product?->name); ?></p>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMetrics->waitingOrders()->isNotEmpty()): ?>
            <div class="mt-3 rounded-lg bg-white/80 p-3 text-sm">
                <h4 class="font-semibold"><?php echo e($this->t('waiting_orders')); ?></h4>
                <ul class="mt-1 list-inside list-disc">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detailMetrics->waitingOrders(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $waitingOrder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <li><?php echo e($waitingOrder->order_number); ?> · <?php echo e($waitingOrder->customer); ?> · <?php echo e($waitingOrder->product?->code ?? $waitingOrder->product?->name); ?></li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                </ul>
            </div>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </section>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder && ! ($inline ?? false)): ?>
    <div class="flex justify-end">
        <button wire:click="openProductionProtocol(<?php echo e($detailOrder->id); ?>)"
            class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100"><?php echo e($this->t('production_protocol')); ?></button>
    </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/machine-detail-body.blade.php ENDPATH**/ ?>