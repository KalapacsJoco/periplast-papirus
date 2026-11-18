<form wire:submit="createMouldRepair(<?php echo e($mould->id); ?>)" class="grid gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-2">
    <h3 class="font-bold sm:col-span-2"><?php echo e($this->t('repair_new')); ?></h3>
    <label class="text-sm font-semibold sm:col-span-2"><?php echo e($this->t('repair_problem')); ?>

        <textarea wire:model="repairDescriptions.<?php echo e($mould->id); ?>" maxlength="2000" required class="mt-1 w-full rounded-md border-slate-300"></textarea>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairDescriptions.'.$mould->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </label>
    <label class="text-sm font-semibold"><?php echo e($this->t('repair_priority')); ?>

        <select wire:model="repairPriorities.<?php echo e($mould->id); ?>" required class="mt-1 w-full rounded-md border-slate-300">
            <option value=""><?php echo e($this->t('repair_select')); ?></option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php for($priority = 1; $priority <= 5; $priority++): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?><option value="<?php echo e($priority); ?>"><?php echo e($priority); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endfor; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        </select>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairPriorities.'.$mould->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </label>
    <label class="text-sm font-semibold"><?php echo e($this->t('repair_assigned_role')); ?>

        <select wire:model="repairRoles.<?php echo e($mould->id); ?>" required class="mt-1 w-full rounded-md border-slate-300">
            <option value=""><?php echo e($this->t('repair_select')); ?></option>
            <option value="reparation"><?php echo e($this->t('repair_role_reparation')); ?></option>
            <option value="maintenance"><?php echo e($this->t('repair_role_maintenance')); ?></option>
        </select>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairRoles.'.$mould->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </label>
    <button type="submit" wire:loading.attr="disabled" class="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white"><?php echo e($this->t('repair_submit')); ?></button>
</form>
<?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/mould-repair-form.blade.php ENDPATH**/ ?>