<form wire:submit="createMouldRepair({{ $mould->id }})" class="grid gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-2">
    <h3 class="font-bold sm:col-span-2">{{ $this->t('repair_new') }}</h3>
    <label class="text-sm font-semibold sm:col-span-2">{{ $this->t('repair_problem') }}
        <textarea wire:model="repairDescriptions.{{ $mould->id }}" maxlength="2000" required class="mt-1 w-full rounded-md border-slate-300"></textarea>
        @error('repairDescriptions.'.$mould->id)<span class="text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-semibold">{{ $this->t('repair_priority') }}
        <select wire:model="repairPriorities.{{ $mould->id }}" required class="mt-1 w-full rounded-md border-slate-300">
            <option value="">{{ $this->t('repair_select') }}</option>
            @for ($priority = 1; $priority <= 5; $priority++)<option value="{{ $priority }}">{{ $priority }}</option>@endfor
        </select>
        @error('repairPriorities.'.$mould->id)<span class="text-red-600">{{ $message }}</span>@enderror
    </label>
    <label class="text-sm font-semibold">{{ $this->t('repair_assigned_role') }}
        <select wire:model="repairRoles.{{ $mould->id }}" required class="mt-1 w-full rounded-md border-slate-300">
            <option value="">{{ $this->t('repair_select') }}</option>
            <option value="reparation">{{ $this->t('repair_role_reparation') }}</option>
            <option value="maintenance">{{ $this->t('repair_role_maintenance') }}</option>
        </select>
        @error('repairRoles.'.$mould->id)<span class="text-red-600">{{ $message }}</span>@enderror
    </label>
    <button type="submit" wire:loading.attr="disabled" class="w-fit rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('repair_submit') }}</button>
</form>
