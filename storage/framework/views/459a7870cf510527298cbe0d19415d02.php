<div class="space-y-4">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mould->status === 'warning'): ?>
        <div class="rounded-lg border border-amber-300 bg-amber-50 p-4">
            <p class="text-sm font-bold text-amber-900"><?php echo e($this->t('warning_active')); ?></p>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mould->problem_description): ?>
                <p class="mt-1 whitespace-pre-wrap text-sm text-amber-900"><?php echo e($mould->problem_description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageMouldRepairs()): ?>
                <button type="button" wire:click="dismissMouldWarning(<?php echo e($mould->id); ?>)" wire:confirm="<?php echo e($this->t('warning_dismiss_confirm')); ?>" wire:loading.attr="disabled" class="mt-3 rounded-md bg-slate-600 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('warning_dismiss')); ?></button>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageMouldRepairs()): ?>
            <?php echo $__env->make('partials.mould-repair-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php else: ?>
        <p class="text-sm text-slate-500"><?php echo e($this->t('warning_none')); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/mould-log-warnings.blade.php ENDPATH**/ ?>