<section wire:poll.15s class="space-y-5">
    <h1 class="text-2xl font-bold text-slate-900">{{ $this->t('repair_log') }}</h1>
    <p class="text-sm text-slate-600">{{ $this->t('repair_log_intro') }}</p>
    @forelse ($this->repairMoulds as $mould)
        <article wire:key="repair-mould-{{ $mould->id }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h2 class="text-lg font-bold">{{ $mould->name }} ({{ $mould->number }})</h2>
                <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($mould->status)])>{{ $this->statusLabel($mould->status) }}</span>
            </div>
            @if ($mould->problem_description)
                <p class="mt-2 whitespace-pre-wrap text-sm text-red-700">{{ $mould->problem_description }}</p>
            @endif

            @if ($this->canManageMouldRepairs() && in_array($mould->status, ['warning', 'needs_repair', 'under_repair'], true))
                <form wire:submit="createMouldRepair({{ $mould->id }})" class="mt-4 grid gap-3 rounded-lg bg-slate-50 p-4 sm:grid-cols-2">
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
            @endif

            <h3 class="mt-5 font-bold">{{ $this->t('repair_requests') }}</h3>
            <div class="mt-3 space-y-3">
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
                    <p class="text-sm text-slate-500">{{ $this->t('repair_none') }}</p>
                @endforelse
            </div>
        </article>
    @empty
        <p class="rounded-xl bg-white p-6 text-sm text-slate-500">{{ $this->t('repair_none') }}</p>
    @endforelse
</section>
