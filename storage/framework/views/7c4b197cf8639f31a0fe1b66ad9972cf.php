<div class="space-y-4">
    <p class="text-sm text-slate-600"><?php echo e($this->t('repair_log_intro')); ?></p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageMouldRepairs() && in_array($mould->status, ['needs_repair', 'under_repair'], true)): ?>
        <?php echo $__env->make('partials.mould-repair-form', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mould->problem_description && in_array($mould->status, ['needs_repair', 'under_repair'], true)): ?>
        <p class="whitespace-pre-wrap text-sm text-red-700"><?php echo e($mould->problem_description); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <h3 class="font-bold"><?php echo e($this->t('repair_requests')); ?></h3>
    <div class="space-y-3">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $mould->repairRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $repair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
            <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'repair-request-'.e($repair->id).''; ?>wire:key="repair-request-<?php echo e($repair->id); ?>" class="rounded-lg border border-slate-200 p-4">
                <?php echo $__env->make('partials.mould-repair-entry', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canWorkOnMouldRepair($repair->assigned_role) && in_array($repair->status, ['waiting', 'in_progress'], true)): ?>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($repair->status === 'waiting'): ?>
                            <button wire:click="setMouldRepairWorkStatus(<?php echo e($repair->id); ?>, 'in_progress')" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('repair_start')); ?></button>
                        <?php else: ?>
                            <button wire:click="setMouldRepairWorkStatus(<?php echo e($repair->id); ?>, 'waiting')" wire:loading.attr="disabled" class="rounded-md bg-slate-600 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('repair_wait')); ?></button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($repair->status === 'in_progress'): ?>
                        <form wire:submit="finishMouldRepair(<?php echo e($repair->id); ?>)" class="mt-3 grid gap-3 sm:grid-cols-2">
                            <label class="text-sm font-semibold sm:col-span-2"><?php echo e($this->t('repair_work_done')); ?>

                                <textarea wire:model="repairWorkDescriptions.<?php echo e($repair->id); ?>" maxlength="2000" required class="mt-1 w-full rounded-md border-slate-300"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairWorkDescriptions.'.$repair->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                            <label class="text-sm font-semibold"><?php echo e($this->t('repair_hours')); ?>

                                <input wire:model="repairHours.<?php echo e($repair->id); ?>" type="number" min="0" max="10000" required class="mt-1 w-full rounded-md border-slate-300">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairHours.'.$repair->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                            <label class="text-sm font-semibold"><?php echo e($this->t('repair_minutes')); ?>

                                <input wire:model="repairMinutes.<?php echo e($repair->id); ?>" type="number" min="0" max="59" required class="mt-1 w-full rounded-md border-slate-300">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairMinutes.'.$repair->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                            <button type="submit" wire:loading.attr="disabled" class="w-fit rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('repair_finish')); ?></button>
                        </form>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($repair->status === 'awaiting_test' && $this->canTestMouldRepair()): ?>
                    <div class="mt-4 space-y-3 rounded-md border border-amber-300 bg-amber-50 p-3">
                        <p class="text-sm font-semibold"><?php echo e($this->t('repair_test_instruction')); ?></p>
                        <label class="block text-sm"><?php echo e($this->t('repair_test_note')); ?>

                            <textarea wire:model="repairTestNotes.<?php echo e($repair->id); ?>" maxlength="2000" class="mt-1 w-full rounded-md border-slate-300"></textarea>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairTestNotes.'.$repair->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($repair->cavities_reduced && $mould->hasClosedCavities()): ?>
                            <label class="flex items-center gap-2 text-sm font-semibold">
                                <input type="checkbox" wire:model="repairAllCavitiesWork.<?php echo e($repair->id); ?>" class="rounded border-slate-300">
                                <?php echo e($this->t('repair_all_cavities_work', ['active' => $mould->active_cavityes, 'total' => $mould->cavity_count])); ?>

                            </label>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="testMouldRepair(<?php echo e($repair->id); ?>, true)" wire:loading.attr="disabled" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('repair_test_passed')); ?></button>
                            <button wire:click="testMouldRepair(<?php echo e($repair->id); ?>, false)" wire:loading.attr="disabled" class="rounded-md bg-red-700 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('repair_test_failed')); ?></button>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($repair->status === 'failed' && $this->canManageMouldRepairs()): ?>
                    <form wire:submit="reopenMouldRepair(<?php echo e($repair->id); ?>)" class="mt-4 flex flex-wrap items-end gap-3">
                        <label class="text-sm font-semibold"><?php echo e($this->t('repair_assigned_role')); ?>

                            <select wire:model="repairReopenRoles.<?php echo e($repair->id); ?>" required class="mt-1 block rounded-md border-slate-300">
                                <option value=""><?php echo e($this->t('repair_select')); ?></option>
                                <option value="reparation"><?php echo e($this->t('repair_role_reparation')); ?></option>
                                <option value="maintenance"><?php echo e($this->t('repair_role_maintenance')); ?></option>
                            </select>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['repairReopenRoles.'.$repair->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </label>
                        <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('repair_reopen')); ?></button>
                    </form>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            <p class="text-sm text-slate-500"><?php echo e($this->t('repair_history_empty')); ?></p>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </div>
</div>
<?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/mould-log-repairs.blade.php ENDPATH**/ ?>