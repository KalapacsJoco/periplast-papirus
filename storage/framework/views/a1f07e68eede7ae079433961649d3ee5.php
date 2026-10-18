<?php
    $actualPercent = $this->progress($order);
    $expectedPercent = $this->expectedProgress($order);
    $expectedQuantity = $this->expectedQuantity($order);
?>
<div class="mt-2">
    <div class="relative h-3 overflow-hidden rounded-full bg-teal-100">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expectedPercent !== null): ?>
            <div class="absolute inset-y-0 left-0 rounded-full bg-red-400" style="width: <?php echo e($expectedPercent); ?>%" title="<?php echo e($this->t('expected_quantity')); ?>: <?php echo e($expectedQuantity); ?> db"></div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="absolute left-0 top-0 h-2 rounded-full bg-teal-600" style="width: <?php echo e($actualPercent); ?>%" title="<?php echo e($this->t('current_quantity')); ?>: <?php echo e($order->current_quantity); ?> db"></div>
    </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($expectedQuantity !== null): ?>
        <p class="mt-1 flex items-center gap-1 text-xs text-red-700">
            <span class="inline-block h-1.5 w-4 rounded-full bg-red-400"></span>
            <?php echo e($this->t('expected_quantity')); ?>: <?php echo e(number_format($expectedQuantity, 0, ',', ' ')); ?> db
        </p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/production-progress-bar.blade.php ENDPATH**/ ?>