<section wire:poll.15s class="space-y-5">
    <h1 class="text-2xl font-bold text-slate-900"><?php echo e($this->t('repair_log')); ?></h1>
    <p class="text-sm text-slate-600"><?php echo e($this->t('repair_log_intro')); ?></p>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->repairMoulds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mould): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'repair-mould-'.e($mould->id).''; ?>wire:key="repair-mould-<?php echo e($mould->id); ?>" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-bold"><?php echo e($mould->name); ?> (<?php echo e($mould->number); ?>)</h2>
                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($mould->status)]); ?>"><?php echo e($this->statusLabel($mould->status)); ?></span>
            </div>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mould->problem_description): ?>
                <p class="mt-2 whitespace-pre-wrap text-sm text-red-700"><?php echo e($mould->problem_description); ?></p>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageMouldRepairs() && in_array($mould->status, ['warning', 'needs_repair', 'under_repair'], true)): ?>
                <form wire:submit="createMouldRepair(<?php echo e($mould->id); ?>)" class="mt-4 grid gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-2">
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
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

            <h3 class="mt-5 font-bold"><?php echo e($this->t('repair_requests')); ?></h3>
            <div class="mt-3 space-y-3">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $mould->repairRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $repair): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
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
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                    <p class="text-sm text-slate-500"><?php echo e($this->t('repair_none')); ?></p>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </div>
        </article>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <p class="rounded-xl bg-white p-6 text-sm text-slate-500"><?php echo e($this->t('repair_none')); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</section>
<?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/mould-repairs.blade.php ENDPATH**/ ?>