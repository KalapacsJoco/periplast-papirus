<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\Mould;
use App\Models\MouldRepairRequest;
use App\Models\User;
use App\Notifications\MouldRepairNotification;
use Illuminate\Support\Facades\DB;

class MouldRepairWorkflow
{
    public function create(int $mouldId, User $actor, string $description, int $priority, Role $role): void
    {
        $this->requireRole($actor, [Role::Coordinator]);
        abort_unless(in_array($role, [Role::Reparation, Role::Maintenance], true), 422);

        DB::transaction(function () use ($mouldId, $actor, $description, $priority, $role): void {
            $mould = Mould::query()->lockForUpdate()->findOrFail($mouldId);
            abort_unless(in_array($mould->status, ['warning', 'needs_repair', 'under_repair'], true), 422);
            $mould->repairRequests()->create([
                'requested_by' => $actor->id,
                'description' => $description,
                'priority' => $priority,
                'assigned_role' => $role->value,
                'status' => 'waiting',
                'issue_version' => $mould->repair_issue_version,
            ]);
            $mould->update(['status' => 'under_repair']);
            $this->notify($mould, 'repair_requested', $actor, [Role::Programmer, $role]);
        });
    }

    public function setWorkStatus(int $requestId, User $actor, string $status): void
    {
        abort_unless(in_array($status, ['waiting', 'in_progress'], true), 422);
        $this->withLockedRequest($requestId, function (MouldRepairRequest $request) use ($actor, $status): void {
            $this->requireAssignedRole($actor, $request);
            abort_unless(in_array($request->status, ['waiting', 'in_progress'], true), 422);
            $request->update(['status' => $status]);
        });
    }

    public function finish(int $requestId, User $actor, string $description, int $minutes): void
    {
        $this->withLockedRequest($requestId, function (MouldRepairRequest $request, Mould $mould) use ($actor, $description, $minutes): void {
            $this->requireAssignedRole($actor, $request);
            abort_unless($request->status === 'in_progress', 422);
            $request->attempts()->create([
                'repaired_by' => $actor->id,
                'assigned_role' => $request->assigned_role,
                'description' => $description,
                'duration_minutes' => $minutes,
                'repaired_at' => now(),
            ]);
            $request->update(['status' => 'awaiting_test']);
            $this->notify($mould, 'repair_awaiting_test', $actor, [Role::Coordinator, Role::Operator, Role::Maintenance, Role::QualityManager]);
        });
    }

    public function test(int $requestId, User $actor, bool $passed, ?string $note): void
    {
        $this->requireRole($actor, [Role::Coordinator, Role::Operator, Role::Maintenance, Role::QualityManager]);
        $this->withLockedRequest($requestId, function (MouldRepairRequest $request, Mould $mould) use ($actor, $passed, $note): void {
            abort_unless($request->status === 'awaiting_test', 422);
            $attempt = $request->attempts()->latest('id')->firstOrFail();
            $attempt->update(['test_passed' => $passed, 'test_note' => $note, 'tested_by' => $actor->id, 'tested_at' => now()]);
            $request->update(['status' => $passed ? 'completed' : 'failed']);

            if (! $passed) {
                $this->notify($mould, 'repair_test_failed', $actor, [Role::Coordinator]);

                return;
            }

            $requests = $mould->repairRequests();
            if (! (clone $requests)->where('status', '!=', 'completed')->exists()) {
                $latestIssueVersion = (clone $requests)->max('issue_version');
                $mould->update($latestIssueVersion >= $mould->repair_issue_version
                    ? ['status' => 'active', 'problem_description' => null]
                    : ['status' => 'needs_repair']);
            }
        });
    }

    public function reopen(int $requestId, User $actor, Role $role): void
    {
        $this->requireRole($actor, [Role::Coordinator]);
        abort_unless(in_array($role, [Role::Reparation, Role::Maintenance], true), 422);
        $this->withLockedRequest($requestId, function (MouldRepairRequest $request, Mould $mould) use ($actor, $role): void {
            abort_unless($request->status === 'failed', 422);
            $request->update(['status' => 'waiting', 'assigned_role' => $role->value, 'issue_version' => $mould->repair_issue_version]);
            $this->notify($mould, 'repair_requested', $actor, [Role::Programmer, $role]);
        });
    }

    private function withLockedRequest(int $requestId, callable $callback): void
    {
        DB::transaction(function () use ($requestId, $callback): void {
            $mouldId = MouldRepairRequest::query()->findOrFail($requestId)->mould_id;
            $mould = Mould::query()->lockForUpdate()->findOrFail($mouldId);
            $request = MouldRepairRequest::query()->where('mould_id', $mould->id)->lockForUpdate()->findOrFail($requestId);
            $callback($request, $mould);
        });
    }

    private function requireAssignedRole(User $actor, MouldRepairRequest $request): void
    {
        abort_unless($actor->role === Role::Admin || $actor->role?->value === $request->assigned_role, 403);
    }

    private function requireRole(User $actor, array $roles): void
    {
        abort_unless($actor->role === Role::Admin || in_array($actor->role, $roles, true), 403);
    }

    private function notify(Mould $mould, string $event, User $actor, array $roles): void
    {
        User::query()->whereIn('role', array_map(fn (Role $role): string => $role->value, $roles))
            ->each(fn (User $recipient) => $recipient->notify(new MouldRepairNotification($mould, $event, $actor->name)));
    }
}
