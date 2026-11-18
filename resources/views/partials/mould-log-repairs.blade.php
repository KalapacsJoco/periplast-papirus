<div class="space-y-4">
    <p class="text-sm text-slate-600">{{ $this->t('repair_log_intro') }}</p>
    @if ($this->canManageMouldRepairs() && in_array($mould->status, ['needs_repair', 'under_repair'], true))
        @include('partials.mould-repair-form')
    @endif
    @if ($mould->problem_description && in_array($mould->status, ['needs_repair', 'under_repair'], true))
        <p class="whitespace-pre-wrap text-sm text-red-700">{{ $mould->problem_description }}</p>
    @endif
    <h3 class="font-bold">{{ $this->t('repair_requests') }}</h3>
    <div class="space-y-3">
        @forelse ($mould->repairRequests as $repair)
            <div wire:key="repair-request-{{ $repair->id }}" class="rounded-lg border border-slate-200 p-4">
                @include('partials.mould-repair-entry')

                @if ($this->canWorkOnMouldRepair($repair->assigned_role) && in_array($repair->status, ['waiting', 'in_progress'], true))
                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($repair->status === 'waiting')
                            <button wire:click="setMouldRepairWorkStatus({{ $repair->id }}, 'in_progress')" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white">{{ $this->t('repair_start') }}</button>
                        @else
                            <button wire:click="setMouldRepairWorkStatus({{ $repair->id }}, 'waiting')" wire:loading.attr="disabled" class="rounded-md bg-slate-600 px-3 py-2 text-sm font-bold text-white">{{ $this->t('repair_wait') }}</button>
                        @endif
                    </div>
                    @if ($repair->status === 'in_progress')
                        <form wire:submit="finishMouldRepair({{ $repair->id }})" class="mt-3 grid gap-3 sm:grid-cols-2">
                            <label class="text-sm font-semibold sm:col-span-2">{{ $this->t('repair_work_done') }}
                                <textarea wire:model="repairWorkDescriptions.{{ $repair->id }}" maxlength="2000" required class="mt-1 w-full rounded-md border-slate-300"></textarea>
                                @error('repairWorkDescriptions.'.$repair->id)<span class="text-red-600">{{ $message }}</span>@enderror
                            </label>
                            <label class="text-sm font-semibold">{{ $this->t('repair_hours') }}
                                <input wire:model="repairHours.{{ $repair->id }}" type="number" min="0" max="10000" required class="mt-1 w-full rounded-md border-slate-300">
                                @error('repairHours.'.$repair->id)<span class="text-red-600">{{ $message }}</span>@enderror
                            </label>
                            <label class="text-sm font-semibold">{{ $this->t('repair_minutes') }}
                                <input wire:model="repairMinutes.{{ $repair->id }}" type="number" min="0" max="59" required class="mt-1 w-full rounded-md border-slate-300">
                                @error('repairMinutes.'.$repair->id)<span class="text-red-600">{{ $message }}</span>@enderror
                            </label>
                            <button type="submit" wire:loading.attr="disabled" class="w-fit rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white">{{ $this->t('repair_finish') }}</button>
                        </form>
                    @endif
                @endif

                @if ($repair->status === 'awaiting_test' && $this->canTestMouldRepair())
                    <div class="mt-4 space-y-3 rounded-md border border-amber-300 bg-amber-50 p-3">
                        <p class="text-sm font-semibold">{{ $this->t('repair_test_instruction') }}</p>
                        <label class="block text-sm">{{ $this->t('repair_test_note') }}
                            <textarea wire:model="repairTestNotes.{{ $repair->id }}" maxlength="2000" class="mt-1 w-full rounded-md border-slate-300"></textarea>
                            @error('repairTestNotes.'.$repair->id)<span class="text-red-600">{{ $message }}</span>@enderror
                        </label>
                        @if ($repair->cavities_reduced && $mould->hasClosedCavities())
                            <label class="flex items-center gap-2 text-sm font-semibold">
                                <input type="checkbox" wire:model="repairAllCavitiesWork.{{ $repair->id }}" class="rounded border-slate-300">
                                {{ $this->t('repair_all_cavities_work', ['active' => $mould->active_cavityes, 'total' => $mould->cavity_count]) }}
                            </label>
                        @endif
                        <div class="flex flex-wrap gap-2">
                            <button wire:click="testMouldRepair({{ $repair->id }}, true)" wire:loading.attr="disabled" class="rounded-md bg-emerald-700 px-3 py-2 text-sm font-bold text-white">{{ $this->t('repair_test_passed') }}</button>
                            <button wire:click="testMouldRepair({{ $repair->id }}, false)" wire:loading.attr="disabled" class="rounded-md bg-red-700 px-3 py-2 text-sm font-bold text-white">{{ $this->t('repair_test_failed') }}</button>
                        </div>
                    </div>
                @endif
                @if ($repair->status === 'failed' && $this->canManageMouldRepairs())
                    <form wire:submit="reopenMouldRepair({{ $repair->id }})" class="mt-4 flex flex-wrap items-end gap-3">
                        <label class="text-sm font-semibold">{{ $this->t('repair_assigned_role') }}
                            <select wire:model="repairReopenRoles.{{ $repair->id }}" required class="mt-1 block rounded-md border-slate-300">
                                <option value="">{{ $this->t('repair_select') }}</option>
                                <option value="reparation">{{ $this->t('repair_role_reparation') }}</option>
                                <option value="maintenance">{{ $this->t('repair_role_maintenance') }}</option>
                            </select>
                            @error('repairReopenRoles.'.$repair->id)<span class="text-red-600">{{ $message }}</span>@enderror
                        </label>
                        <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white">{{ $this->t('repair_reopen') }}</button>
                    </form>
                @endif
            </div>
        @empty
            <p class="text-sm text-slate-500">{{ $this->t('repair_history_empty') }}</p>
        @endforelse
    </div>
</div>
