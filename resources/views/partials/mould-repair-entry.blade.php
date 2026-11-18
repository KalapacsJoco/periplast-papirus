<div class="flex flex-wrap justify-between gap-2 text-sm">
    <strong>#{{ $repair->id }} · {{ $this->t('repair_status_'.$repair->status) }}</strong>
    <span>{{ $this->t('repair_priority_short') }}: {{ $repair->priority }} · {{ $this->t('repair_role_'.$repair->assigned_role) }}</span>
</div>
<p class="mt-1 text-xs text-slate-500">{{ $this->t('requested_by') }}: {{ $repair->requester?->name ?? '-' }} · {{ $repair->created_at->timezone(config('app.display_timezone'))->format('Y-m-d H:i') }}</p>
<p class="mt-2 whitespace-pre-wrap text-sm">{{ $repair->description }}</p>

@foreach ($repair->attempts as $attempt)
    <div class="mt-3 rounded-md bg-slate-50 p-3 text-sm">
        <p class="font-semibold">{{ $this->t('repair_work_done') }} · {{ $attempt->repairer?->name ?? '-' }} · {{ $this->t('repair_role_'.$attempt->assigned_role) }}</p>
        <p class="mt-1 whitespace-pre-wrap">{{ $attempt->description }}</p>
        <p class="mt-1 text-slate-600">{{ $this->t('repair_duration') }}: {{ intdiv($attempt->duration_minutes, 60) }} {{ $this->t('repair_hours') }} {{ $attempt->duration_minutes % 60 }} {{ $this->t('repair_minutes') }} · {{ $attempt->repaired_at->timezone(config('app.display_timezone'))->format('Y-m-d H:i') }}</p>
        @if ($attempt->tested_at)
            <p class="mt-2 font-semibold">{{ $this->t($attempt->test_passed ? 'repair_test_passed' : 'repair_test_failed') }} · {{ $attempt->tester?->name ?? '-' }} · {{ $attempt->tested_at->timezone(config('app.display_timezone'))->format('Y-m-d H:i') }}</p>
            @if ($attempt->test_note)<p class="whitespace-pre-wrap">{{ $attempt->test_note }}</p>@endif
        @endif
    </div>
@endforeach
