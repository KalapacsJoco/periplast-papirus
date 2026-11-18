<div class="space-y-4">
    <form wire:submit="addMouldNote(<?php echo e($mould->id); ?>)" class="space-y-2">
        <label class="block text-sm font-semibold"><?php echo e($this->t('note_new')); ?>

            <textarea wire:model="mouldNotes.<?php echo e($mould->id); ?>" maxlength="2000" required class="mt-1 w-full rounded-md border-slate-300"></textarea>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['mouldNotes.'.$mould->id];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </label>
        <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white"><?php echo e($this->t('note_save')); ?></button>
    </form>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $mould->notes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $note): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
        <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'mould-note-'.e($note->id).''; ?>wire:key="mould-note-<?php echo e($note->id); ?>" class="rounded-lg border border-slate-200 p-3">
            <p class="text-xs text-slate-500"><?php echo e($note->author?->name ?? '-'); ?> · <?php echo e($note->created_at->timezone(config('app.display_timezone'))->format('Y-m-d H:i')); ?></p>
            <p class="mt-1 whitespace-pre-wrap text-sm"><?php echo e($note->body); ?></p>
        </div>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
        <p class="text-sm text-slate-500"><?php echo e($this->t('note_none')); ?></p>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div>
<?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/mould-log-notes.blade.php ENDPATH**/ ?>