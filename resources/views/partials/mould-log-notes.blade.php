<div class="space-y-4">
    <form wire:submit="addMouldNote({{ $mould->id }})" class="space-y-2">
        <label class="block text-sm font-semibold">{{ $this->t('note_new') }}
            <textarea wire:model="mouldNotes.{{ $mould->id }}" maxlength="2000" required class="mt-1 w-full rounded-md border-slate-300"></textarea>
            @error('mouldNotes.'.$mould->id)<span class="text-red-600">{{ $message }}</span>@enderror
        </label>
        <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white">{{ $this->t('note_save') }}</button>
    </form>
    @forelse ($mould->notes as $note)
        <div wire:key="mould-note-{{ $note->id }}" class="rounded-lg border border-slate-200 p-3">
            <p class="text-xs text-slate-500">{{ $note->author?->name ?? '-' }} · {{ $note->created_at->timezone(config('app.display_timezone'))->format('Y-m-d H:i') }}</p>
            <p class="mt-1 whitespace-pre-wrap text-sm">{{ $note->body }}</p>
        </div>
    @empty
        <p class="text-sm text-slate-500">{{ $this->t('note_none') }}</p>
    @endforelse
</div>
