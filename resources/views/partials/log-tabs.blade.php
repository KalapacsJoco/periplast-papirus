@php($logTabs = ['repairs' => 'log_tab_repairs', 'warnings' => 'log_tab_warnings', 'maintenance' => 'log_tab_maintenance', 'notes' => 'log_tab_notes'])
<div class="flex flex-wrap gap-1 border-b border-slate-200 px-5 pt-3">
    @foreach ($logTabs as $tab => $label)
        <button type="button" wire:click="{{ $selectAction }}('{{ $tab }}')" @class(['rounded-t-md px-4 py-2 text-sm font-semibold', 'bg-teal-50 text-teal-800 border-b-2 border-teal-600' => $currentTab === $tab, 'text-slate-600 hover:text-teal-700' => $currentTab !== $tab])>{{ $this->t($label) }}</button>
    @endforeach
</div>
