<div class="flex items-center gap-1.5" role="group" aria-label="<?php echo e($this->t('language')); ?>">
    <button type="button" wire:click="selectLanguage('hu')" title="<?php echo e($this->t('hungarian')); ?>" aria-label="<?php echo e($this->t('hungarian')); ?>" aria-pressed="<?php echo e($locale === 'hu' ? 'true' : 'false'); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['overflow-hidden rounded-sm border transition', 'border-teal-600 ring-2 ring-teal-500' => $locale === 'hu', 'border-slate-300 opacity-60 hover:opacity-100' => $locale !== 'hu']); ?>">
        <svg viewBox="0 0 3 2" class="block h-5 w-7" aria-hidden="true"><rect width="3" height="2" fill="#477050"/><rect width="3" height="1.3334" fill="#fff"/><rect width="3" height=".6667" fill="#CE2939"/></svg>
    </button>
    <button type="button" wire:click="selectLanguage('de')" title="<?php echo e($this->t('german')); ?>" aria-label="<?php echo e($this->t('german')); ?>" aria-pressed="<?php echo e($locale === 'de' ? 'true' : 'false'); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['overflow-hidden rounded-sm border transition', 'border-teal-600 ring-2 ring-teal-500' => $locale === 'de', 'border-slate-300 opacity-60 hover:opacity-100' => $locale !== 'de']); ?>">
        <svg viewBox="0 0 5 3" class="block h-5 w-7" aria-hidden="true"><rect width="5" height="3" fill="#FFCE00"/><rect width="5" height="2" fill="#D00"/><rect width="5" height="1" fill="#000"/></svg>
    </button>
</div>
<?php /**PATH C:\Users\kalap\Herd\Papirus\resources\views/partials/language-switcher.blade.php ENDPATH**/ ?>