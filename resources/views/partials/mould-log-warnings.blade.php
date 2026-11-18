<div class="space-y-4">
    @if ($mould->status === 'warning')
        <div class="rounded-lg border border-amber-300 bg-amber-50 p-4">
            <p class="text-sm font-bold text-amber-900">{{ $this->t('warning_active') }}</p>
            @if ($mould->problem_description)
                <p class="mt-1 whitespace-pre-wrap text-sm text-amber-900">{{ $mould->problem_description }}</p>
            @endif
            @if ($this->canManageMouldRepairs())
                <button type="button" wire:click="dismissMouldWarning({{ $mould->id }})" wire:confirm="{{ $this->t('warning_dismiss_confirm') }}" wire:loading.attr="disabled" class="mt-3 rounded-md bg-slate-600 px-3 py-2 text-sm font-bold text-white">{{ $this->t('warning_dismiss') }}</button>
            @endif
        </div>
        @if ($this->canManageMouldRepairs())
            @include('partials.mould-repair-form')
        @endif
    @else
        <p class="text-sm text-slate-500">{{ $this->t('warning_none') }}</p>
    @endif
</div>
