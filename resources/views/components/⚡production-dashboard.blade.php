<?php

use App\Auth\TabAuthentication;
use App\Enums\Role;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Notifications\OrderWorkflowNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

new class extends Component
{
    public string $activeTab = 'machines';

    public ?int $detailMachineId = null;

    public ?int $expandedMachineId = null;

    public ?int $orderId = null;

    public ?int $qualityOrderId = null;

    public ?int $protocolOrderId = null;

    public bool $showOrderModal = false;

    public bool $showQualityModal = false;

    public bool $showProtocolModal = false;

    public bool $showCalculatorModal = false;

    public string $machineFilter = 'all';

    public ?int $calcProductId = null;

    public ?int $calcMouldId = null;

    public $calcCavities = null;

    public $calcCycleSeconds = null;

    public $calcQuantity = null;
    public string $status = '';

    public string $problemDescription = '';

    public string $orderStatus = 'in_progress';

    public int $currentQuantity = 0;

    public int $goodQuantity = 0;

    public int $badQuantity = 0;

    public ?int $measuredOrderId = null;

    public bool $showMeasuredModal = false;

    public $measuredCycleSeconds = null;

    public $measuredNetWeight = null;

    public $measuredGrossWeight = null;

    public $measuredColorPercentage = null;

    public string $qualityStatus = 'good';

    public string $quantityWarning = '';

    public string $qualityProblemType = 'surface_defect';

    public string $qualityProblemNote = '';

    public string $resolutionDescription = '';

    public string $protocolEntryType = 'note';

    public string $protocolEntryNote = '';

    public bool $protocolStopsProduction = false;

    public string $protocolResolveTarget = '';

    public string $flashMessage = '';

    public string $locale = 'hu';

    #[Locked]
    public string $tabId = '';

    public string $email = '';

    public string $password = '';

    public bool $remember = false;

    public function mount(): void
    {
        $this->locale = session('papirus.locale', config('app.locale'));
        $this->tabId = TabAuthentication::tabIdFromRequest(request()) ?? '';
    }

    public function login(): void
    {
        $credentials = $this->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => $this->t('email_required'),
            'email.email' => $this->t('email_invalid'),
            'password.required' => $this->t('password_required'),
        ]);

        $throttleKey = Str::lower($credentials['email']).'|'.request()->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $this->addError('email', $this->t('login_throttled', ['seconds' => RateLimiter::availableIn($throttleKey)]));

            return;
        }

        $guard = Auth::guard('web');

        if (! $guard->validate($credentials)) {
            RateLimiter::hit($throttleKey);
            $this->addError('email', $this->t('login_failed'));

            return;
        }

        $user = $guard->getLastAttempted();
        abort_unless($user instanceof User, 500);

        abort_unless(Str::isUuid($this->tabId), 400);

        app(TabAuthentication::class)->login($this->tabId, $user, $this->remember);
        Auth::guard('papirus-tab')->setUser($user);
        RateLimiter::clear($throttleKey);
        $this->reset('email', 'password', 'remember');
    }

    public function logout(): void
    {
        abort_unless(Str::isUuid($this->tabId), 400);

        app(TabAuthentication::class)->logout($this->tabId);
        Auth::guard('papirus-tab')->forgetUser();
        $this->redirect('/', navigate: false);
    }

    private function ensureAuthenticated(): void
    {
        abort_unless(Auth::check(), 403);
    }

    private function ensurePermission(string ...$permissions): void
    {
        $this->ensureAuthenticated();
        abort_unless(collect($permissions)->contains(fn (string $permission): bool => Auth::user()->hasPermission($permission)), 403);
    }

    public function canDo(string $permission): bool
    {
        return Auth::user()?->hasPermission($permission) ?? false;
    }

    public function isAdmin(): bool
    {
        return Auth::user()?->role === Role::Admin;
    }

    public function canManageOrders(): bool
    {
        return $this->isAdmin() || $this->canDo(Role::ACTIVATE_ORDER) || $this->canDo(Role::PAUSE_ORDER) || $this->canDo(Role::INTERRUPT_ORDER);
    }

    public function canCheckQuality(): bool
    {
        return $this->isAdmin() || $this->canDo(Role::CHECK_QUALITY) || $this->canDo(Role::CHECK_COLOR);
    }

    public function canViewTab(string $tab): bool
    {
        return match ($tab) {
            'machines' => true,
            'orders', 'notifications', 'maintenance', 'calculators' => true,
            'tasks' => $this->isAdmin() || $this->canDo(Role::ASSIGN_ORDER) || $this->canDo(Role::CHECK_COLOR),
            'moulds', 'products' => $this->isAdmin(),
            default => false,
        };
    }

    public function selectLanguage(string $locale): void
    {
        if (! in_array($locale, ['hu', 'de'], true)) {
            return;
        }

        $this->locale = $locale;
        session(['papirus.locale' => $locale]);
    }

    public function t(string $key, array $replace = []): string
    {
        return trans('dashboard.'.$key, $replace, $this->locale);
    }

    #[Computed]
    public function machines()
    {
        return Machine::query()
            ->with(['orders' => fn ($query) => $query
                ->where(function ($query): void {
                    $query
                        ->whereIn('status', ['waiting', 'in_progress', 'paused', 'interrupted'])
                        ->orWhere('ended_at', '>=', now()->subDay());
                })
                ->with(['mould:id,name,number,cavity_count,status,problem_description', 'product:id,name,code,planned_cycle_time'])
                ->orderBy('date')
                ->orderBy('id')])
            ->orderBy('name')
            ->get();
    }

    public function machineHasWarning(Machine $machine): bool
    {
        if ($machine->status === 'warning') {
            return true;
        }

        $order = $machine->productionMetrics()->currentOrder();

        return $order !== null
            && ($order->mould?->status === 'warning' || $this->qualityStatus($order) === 'warning');
    }

    /** Returns stopped, setup, idle, warning or running. */
    public function machineState(Machine $machine): string
    {
        $order = $machine->productionMetrics()->currentOrder();

        if (
            in_array($machine->status, ['stopped', 'broken'], true)
            || ($order !== null && (
                in_array($order->mould?->status, ['needs_repair', 'under_repair'], true)
                || $this->qualityStatus($order) === 'quality_issue'
            ))
        ) {
            return 'stopped';
        }

        if ($machine->status === 'setup') {
            return 'setup';
        }

        if ($order === null) {
            return 'idle';
        }

        return $this->machineHasWarning($machine) ? 'warning' : 'running';
    }

    public function machineMatchesFilter(Machine $machine, string $filter): bool
    {
        return match ($filter) {
            'active' => $machine->status === 'active' && in_array($this->machineState($machine), ['running', 'warning'], true),
            'stopped', 'setup', 'idle' => $this->machineState($machine) === $filter,
            'warning' => $this->machineHasWarning($machine),
            default => true,
        };
    }

    public function filterMachines(string $filter): void
    {
        $this->machineFilter = in_array($filter, ['active', 'stopped', 'warning', 'setup', 'idle'], true) && $this->machineFilter !== $filter ? $filter : 'all';
    }

    #[Computed]
    public function moulds()
    {
        return Mould::query()->with('products:id,name,code')->orderBy('name')->get();
    }

    #[Computed]
    public function products()
    {
        return Product::query()
            ->with(['material:id,name,code', 'color:id,name,code', 'additives:id,name,code', 'moulds:id,name,number'])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function waitingOrders()
    {
        return Order::query()
            ->where('status', 'waiting')
            ->with(['machine:id,name', 'mould:id,name,number', 'product:id,name,code'])
            ->orderBy('order_number')
            ->get();
    }

    #[Computed]
    public function ordersForViewing()
    {
        return Order::query()
            ->with(['machine:id,name', 'product:id,name,code', 'assignedOperator:id,name'])
            ->orderByDesc('date')
            ->orderBy('order_number')
            ->get();
    }

    #[Computed]
    public function pausedOrders()
    {
        return Order::query()
            ->where('status', 'paused')
            ->with(['machine:id,name', 'product:id,name,code'])
            ->orderBy('order_number')
            ->get();
    }

    #[Computed]
    public function ordersForAssignment()
    {
        return Order::query()
            ->whereIn('status', ['in_progress', 'paused'])
            ->whereNotNull('activated_at')
            ->whereNull('task_started_at')
            ->with(['machine:id,name', 'product:id,name,code', 'assignedOperator:id,name'])
            ->orderBy('order_number')
            ->get();
    }

    #[Computed]
    public function ordersForMyTasks()
    {
        return Order::query()
            ->where('assigned_operator_id', Auth::id())
            ->whereIn('status', ['in_progress', 'paused'])
            ->whereNull('task_started_at')
            ->with(['machine:id,name,status', 'product:id,name,code'])
            ->orderBy('order_number')
            ->get();
    }

    #[Computed]
    public function operators()
    {
        return User::query()
            ->whereIn('role', [Role::Operator->value, Role::Coordinator->value])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    #[Computed]
    public function notifications()
    {
        return Auth::user()->notifications()->latest()->limit(50)->get();
    }

    #[Computed]
    public function unreadNotificationCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function openCalculator(): void
    {
        $this->reset(['calcProductId', 'calcMouldId', 'calcCavities', 'calcCycleSeconds', 'calcQuantity']);
        $this->showCalculatorModal = true;
    }

    public function updatedCalcProductId(): void
    {
        $product = $this->calcProductId ? Product::query()->with('moulds:id,name,number,cavity_count')->find($this->calcProductId) : null;
        $mould = $product?->moulds->first();

        $this->calcMouldId = $mould?->id;
        $this->calcCavities = $mould?->cavity_count;
        $this->calcCycleSeconds = $product?->planned_cycle_time ? round($product->planned_cycle_time / 1000, 2) : null;
    }

    public function updatedCalcMouldId(): void
    {
        $this->calcCavities = $this->calcMouldId ? Mould::query()->find($this->calcMouldId)?->cavity_count : null;
    }

    public function calculatedProductionMinutes(): ?int
    {
        $cavities = (int) $this->calcCavities;
        $cycle = (float) $this->calcCycleSeconds;
        $quantity = (int) $this->calcQuantity;

        if ($cavities <= 0 || $cycle <= 0 || $quantity <= 0) {
            return null;
        }

        return (int) ceil(ceil($quantity / $cavities) * $cycle / 60);
    }

    public function selectTab(string $tab): void
    {
        $this->activeTab = $this->canViewTab($tab) ? $tab : 'machines';
    }

    #[Computed]
    public function detailMachine()
    {
        return $this->machineWithDetails($this->detailMachineId);
    }

    #[Computed]
    public function expandedMachine()
    {
        return $this->machineWithDetails($this->expandedMachineId);
    }

    private function machineWithDetails(?int $machineId): ?Machine
    {
        if (! $machineId) {
            return null;
        }

        return Machine::query()
            ->with([
                'moulds:id,name,number',
                'orders' => fn ($query) => $query
                    ->where(function ($query): void {
                        $query
                            ->whereIn('status', ['waiting', 'in_progress', 'paused', 'interrupted'])
                            ->orWhere('ended_at', '>=', now()->subDay());
                    })
                    ->with(['mould', 'product.material', 'product.color', 'product.additives'])
                    ->orderBy('date')
                    ->orderBy('id'),
            ])
            ->find($machineId);
    }

    public function toggleMachineRow(int $machineId): void
    {
        $this->ensureAuthenticated();
        $this->expandedMachineId = $this->expandedMachineId === $machineId ? null : Machine::findOrFail($machineId)->id;
        unset($this->expandedMachine);
    }

    public function openMachineDetail(int $machineId): void
    {
        $this->ensureAuthenticated();
        $this->detailMachineId = Machine::findOrFail($machineId)->id;
    }

    public function closeMachineDetail(): void
    {
        $this->detailMachineId = null;
    }

    /** True when the open modal would restart equipment or production that was stopped by a fault. */
    public function requiresResolution(): bool
    {
        if ($this->showQualityModal && $this->qualityOrderId) {
            $order = Order::find($this->qualityOrderId);

            return $order !== null && $this->qualityStatus($order) === 'quality_issue' && $this->qualityStatus !== 'quality_issue';
        }

        return false;
    }

    private function resolutionNote(): string
    {
        return $this->t('resolution_note', ['text' => $this->resolutionDescription]);
    }

    public function openOrderModal(int $orderId): void
    {
        $this->ensurePermission(Role::PAUSE_ORDER, Role::INTERRUPT_ORDER);
        $order = Order::findOrFail($orderId);
        $this->orderId = $order->id;
        $this->orderStatus = $this->isAdmin() ? $order->status : '';
        $this->currentQuantity = $order->current_quantity;
        $this->goodQuantity = $order->good_quantity;
        $this->badQuantity = $order->bad_quantity;
        $this->resetValidation();
        $this->showOrderModal = true;
    }

    public function saveOrder(): void
    {
        $this->ensurePermission(Role::PAUSE_ORDER, Role::INTERRUPT_ORDER);
        $allowedStatuses = $this->isAdmin()
            ? ['in_progress', 'paused', 'completed', 'interrupted']
            : ['paused', 'interrupted'];
        $validated = $this->validate([
            'orderStatus' => ['required', Rule::in($allowedStatuses)],
            'currentQuantity' => ['required', 'integer', 'min:0'],
            'goodQuantity' => ['required_if:orderStatus,completed,interrupted', 'integer', 'min:0'],
            'badQuantity' => ['required_if:orderStatus,completed,interrupted', 'integer', 'min:0'],
        ]);

        $order = Order::findOrFail($this->orderId);
        if (! $this->isAdmin()) {
            abort_unless(
                $validated['orderStatus'] === 'paused' && $order->status === 'in_progress'
                || $validated['orderStatus'] === 'interrupted' && in_array($order->status, ['in_progress', 'paused'], true),
                422
            );
        }

        $quantity = min($validated['currentQuantity'], $order->quantity);
        $updates = [
            'status' => $validated['orderStatus'],
            'current_quantity' => $quantity,
        ];

        if ($validated['orderStatus'] === 'in_progress' && ! $order->started_at) {
            $updates['started_at'] = now();
        }
        if (in_array($validated['orderStatus'], ['paused', 'interrupted'], true)) {
            $updates['last_paused_at'] = now();
        }
        if (in_array($validated['orderStatus'], ['completed', 'interrupted'], true)) {
            $updates['good_quantity'] = $validated['goodQuantity'];
            $updates['bad_quantity'] = $validated['badQuantity'];
        }
        if ($validated['orderStatus'] === 'completed') {
            $updates['ended_at'] = now();
        }

        $previousStatus = $order->status;
        $order->update($updates);
        $this->appendProtocolEntry($order->refresh(), $quantity, 'order_status', $this->t('order_status_note', ['status' => $this->statusLabel($validated['orderStatus'])]));
        if ($previousStatus !== $validated['orderStatus'] && Auth::user()->role === Role::Programmer) {
            $this->notifyWorkflowRecipients($order->refresh(), $validated['orderStatus']);
        }

        $this->showOrderModal = false;
        $this->flashMessage = $this->t('order_status_updated');
        unset($this->machines, $this->waitingOrders);
    }

    public function activateOrder(int $orderId): void
    {
        $this->ensurePermission(Role::ACTIVATE_ORDER);
        $order = Order::findOrFail($orderId);
        abort_unless(in_array($order->status, ['waiting', 'paused'], true), 422);
        $order->update([
            'status' => 'in_progress',
            'started_at' => $order->started_at ?? now(),
            'activated_at' => $order->status === 'waiting' ? now() : $order->activated_at,
            'last_paused_at' => null,
        ]);
        $this->appendProtocolEntry($order->refresh(), $order->current_quantity, 'order_status', $this->t('order_activated_note'));
        if (Auth::user()->role === Role::Programmer) {
            $this->notifyWorkflowRecipients($order->refresh(), 'activated');
        }
        $this->flashMessage = $this->t('order_activated');
        if (! $order->hasMeasuredData() && $this->canEnterMeasuredData()) {
            $this->openMeasuredModal($order->id);
        }
        unset($this->machines, $this->waitingOrders);
    }

    public function canEnterMeasuredData(): bool
    {
        return $this->isAdmin() || $this->canDo(Role::ENTER_MEASURED_DATA);
    }

    public function openMeasuredModal(int $orderId): void
    {
        $this->ensurePermission(Role::ENTER_MEASURED_DATA);
        $order = Order::findOrFail($orderId);
        $this->measuredOrderId = $order->id;
        $this->measuredCycleSeconds = $order->measured_cycle_time === null ? null : $order->measured_cycle_time / 1000;
        $this->measuredNetWeight = $order->measured_net_weight;
        $this->measuredGrossWeight = $order->measured_gross_weight;
        $this->measuredColorPercentage = $order->measured_color_percentage;
        $this->resetValidation();
        $this->showMeasuredModal = true;
    }

    public function saveMeasuredData(): void
    {
        $this->ensurePermission(Role::ENTER_MEASURED_DATA);
        $validated = $this->validate([
            'measuredCycleSeconds' => ['required', 'numeric', 'gt:0'],
            'measuredNetWeight' => ['required', 'numeric', 'min:0'],
            'measuredGrossWeight' => ['required', 'numeric', 'min:0'],
            'measuredColorPercentage' => ['required', 'numeric', 'min:0'],
        ]);

        $order = Order::findOrFail($this->measuredOrderId);
        $order->update([
            'measured_cycle_time' => (int) round((float) $validated['measuredCycleSeconds'] * 1000),
            'measured_net_weight' => (float) $validated['measuredNetWeight'],
            'measured_gross_weight' => (float) $validated['measuredGrossWeight'],
            'measured_color_percentage' => (float) $validated['measuredColorPercentage'],
            'measured_at' => now(),
        ]);
        $this->appendProtocolEntry($order->refresh(), $order->current_quantity, 'parameter_change', $this->t('measured_data_saved_note'));

        $this->showMeasuredModal = false;
        $this->flashMessage = $this->t('measured_data_saved');
        unset($this->machines, $this->waitingOrders);
    }

    public function openQualityModal(int $orderId): void
    {
        $this->ensurePermission(Role::CHECK_QUALITY, Role::CHECK_COLOR);
        $order = Order::findOrFail($orderId);
        if (! $order->hasMeasuredData()) {
            $this->flashMessage = $this->t('measured_data_required');

            return;
        }
        $this->qualityOrderId = $order->id;
        $this->currentQuantity = $order->current_quantity;
        $this->qualityStatus = 'good';
        $this->qualityProblemType = $this->canDo(Role::CHECK_COLOR) && ! $this->canDo(Role::CHECK_QUALITY)
            ? 'color_issue'
            : 'surface_defect';
        $this->qualityProblemNote = '';
        $this->resolutionDescription = '';
        $this->quantityWarning = '';
        $this->resetValidation();
        $this->showQualityModal = true;
    }

    public function saveQualityCheck(bool $confirmed = false): void
    {
        $this->ensurePermission(Role::CHECK_QUALITY, Role::CHECK_COLOR);
        $isColorCheck = ! $this->canDo(Role::CHECK_QUALITY);
        $validated = $this->validate([
            'qualityStatus' => ['required', Rule::in(['good', 'warning', 'quality_issue'])],
            'currentQuantity' => ['required', 'integer', 'min:0'],
            'qualityProblemType' => ['required_unless:qualityStatus,good', 'nullable', Rule::in($isColorCheck ? ['color_issue'] : ['surface_defect', 'dimension_issue', 'color_issue', 'material_issue', 'burn_mark', 'flash', 'short_shot', 'contamination', 'other'])],
            'qualityProblemNote' => ['nullable', 'string', 'max:1000', Rule::requiredIf($this->qualityStatus !== 'good')],
            'resolutionDescription' => ['nullable', 'string', 'max:1000', Rule::requiredIf($this->requiresResolution())],
        ]);
        $resolved = $this->requiresResolution();

        $order = Order::findOrFail($this->qualityOrderId);
        abort_unless($order->hasMeasuredData(), 422, $this->t('measured_data_required'));
        $quantity = min($validated['currentQuantity'], $order->quantity);

        if (! $confirmed) {
            $expectedQuantity = $this->expectedQuantity($order);

            if ($quantity < $order->current_quantity) {
                $this->quantityWarning = $this->t('quantity_below_last_warning', ['last' => $order->current_quantity]);

                return;
            }

            if ($expectedQuantity !== null && $quantity > $expectedQuantity) {
                $this->quantityWarning = $this->t('quantity_above_expected_warning', ['expected' => $expectedQuantity]);

                return;
            }
        }

        $this->quantityWarning = '';

        $protocol = $order->production_protocol ?? [];
        $checks = $protocol['quality_checks'] ?? [];
        $checks[] = [
            'checked_at' => now()->toIso8601String(),
            'quality_status' => $validated['qualityStatus'],
            'current_quantity' => $quantity,
            'check_type' => $isColorCheck ? 'color' : 'quality',
            'signed_by' => Auth::user()->name,
            'signed_by_user_id' => Auth::id(),
            'problem_type' => $validated['qualityStatus'] === 'good' ? null : $validated['qualityProblemType'],
            'problem_note' => $validated['qualityStatus'] === 'good' ? null : $validated['qualityProblemNote'],
        ];
        $followUpStatus = $validated['qualityStatus'] === 'quality_issue' ? 'stopped' : ($validated['qualityStatus'] === 'warning' ? 'warning' : null);
        $orderUpdates = [
            'production_protocol' => array_merge($protocol, [
                'quality_status' => $validated['qualityStatus'],
                'last_quality_check_at' => now()->toIso8601String(),
                'quality_checks' => $checks,
            ]),
        ];

        if ($followUpStatus === 'stopped' && $order->status === 'in_progress') {
            $orderUpdates['status'] = 'paused';
            $orderUpdates['pause_description'] = $this->t('quality_paused_reason');
            $orderUpdates['last_paused_at'] = now();
        }

        $order->update($orderUpdates);
        if ($order->machine?->status === 'setup' && $order->hasMeasuredData()) {
            $order->machine->update(['status' => 'active', 'problem_description' => null]);
            unset($this->machines, $this->detailMachine, $this->expandedMachine);
        }
        $this->appendProtocolEntry(
            $order->refresh(),
            $quantity,
            'quality_check',
            $this->t('quality_check_note', ['status' => $this->statusLabel($validated['qualityStatus'])])
                .($validated['qualityStatus'] !== 'good' && filled($validated['qualityProblemNote'] ?? null) ? ': '.$validated['qualityProblemNote'] : '')
                .($resolved ? ' — '.$this->resolutionNote() : ''),
            ['quality_status' => $validated['qualityStatus']]
        );

        $this->showQualityModal = false;
        $this->flashMessage = $followUpStatus === 'stopped'
            ? $this->t('quality_issue_stopped')
            : $this->t('quality_check_saved');
        unset($this->machines);
    }

    public function assignOperator(int $orderId, string $operatorId): void
    {
        $this->ensurePermission(Role::ASSIGN_ORDER);
        $order = Order::query()
            ->whereIn('status', ['in_progress', 'paused'])
            ->findOrFail($orderId);
        $previousOperatorId = $order->assigned_operator_id;

        if ($operatorId === '') {
            $order->update(['assigned_operator_id' => null]);
            if ($previousOperatorId !== null) {
                $this->appendProtocolEntry(
                    $order->refresh(),
                    $order->current_quantity,
                    'assignment',
                    $this->t('operator_unassigned_note')
                );
            }
        } else {
            $operator = User::query()
                ->whereKey($operatorId)
                ->whereIn('role', [Role::Operator->value, Role::Coordinator->value])
                ->firstOrFail();
            $assignmentChanged = (int) $order->assigned_operator_id !== $operator->id;

            $order->update(['assigned_operator_id' => $operator->id]);
            if ($assignmentChanged) {
                $operator->notify(new OrderWorkflowNotification($order, 'assigned', Auth::user()->name));
                $this->appendProtocolEntry(
                    $order->refresh(),
                    $order->current_quantity,
                    'assignment',
                    $this->t('operator_assignment_note', ['operator' => $operator->name])
                );
            }
        }

        $this->flashMessage = $this->t('operator_assigned');
        unset($this->ordersForAssignment, $this->ordersForMyTasks);
    }

    public function startAssignedTask(int $orderId): void
    {
        abort_unless(in_array(Auth::user()?->role, [Role::Operator, Role::Coordinator], true), 403);

        $order = Order::query()
            ->where('assigned_operator_id', Auth::id())
            ->where('status', 'in_progress')
            ->whereNull('task_started_at')
            ->findOrFail($orderId);

        $order->update(['task_started_at' => now()]);
        $order->machine()->update([
            'status' => 'setup',
            'problem_description' => null,
        ]);
        $this->appendProtocolEntry($order->refresh(), $order->current_quantity, 'task_started', $this->t('task_started_note'));

        $this->flashMessage = $this->t('task_started');
        unset($this->machines, $this->ordersForMyTasks);
    }

    public function openProductionProtocol(int $orderId): void
    {
        $this->ensureAuthenticated();
        $this->protocolOrderId = Order::query()->findOrFail($orderId)->id;
        $this->protocolEntryType = 'note';
        $this->protocolEntryNote = '';
        $this->protocolStopsProduction = false;
        $this->protocolResolveTarget = '';
        $this->resetValidation();
        $this->showProtocolModal = true;
    }

    public function addProductionProtocolEntry(): void
    {
        $this->ensureAuthenticated();
        $validated = $this->validate([
            'protocolEntryType' => ['required', Rule::in(['note', 'parameter_change', 'machine_issue', 'mould_issue', 'other', 'resolved'])],
            'protocolEntryNote' => ['required', 'string', 'max:2000'],
        ]);

        $order = Order::query()->findOrFail($this->protocolOrderId);
        $type = $validated['protocolEntryType'];
        $note = $validated['protocolEntryNote'];
        $stop = $this->protocolStopsProduction;

        if ($type === 'resolved') {
            $this->ensurePermission(Role::START_MACHINE);
            $problems = $this->openProblems($order);
            $this->validate(['protocolResolveTarget' => ['required', Rule::in(array_keys($problems))]]);
            $target = $this->protocolResolveTarget;
            $problem = $problems[$target];
            $note = $this->t('protocol_resolved_note', [
                'target' => $problem['label'],
                'problem' => $problem['description'],
                'solution' => $note,
            ]);
        } elseif (in_array($type, ['machine_issue', 'mould_issue', 'other'], true)) {
            $this->ensurePermission($stop ? Role::STOP_MACHINE : Role::REPORT_EQUIPMENT_WARNING);
        }

        $this->appendProtocolEntry($order, $order->current_quantity, $type, $note);

        match ($type) {
            'machine_issue', 'other' => $order->machine?->update([
                'status' => $stop ? 'stopped' : 'warning',
                'problem_description' => $note,
            ]),
            'mould_issue' => $order->mould?->update([
                'status' => $stop ? 'needs_repair' : 'warning',
                'problem_description' => $note,
            ]),
            'resolved' => $this->resolveProblem($problem, $target),
            default => null,
        };

        if ($type === 'mould_issue' && $stop && $order->mould_id) {
            $this->pauseOrdersFor('mould_id', $order->mould_id, $note);
        }

        unset($this->machines);
        $this->protocolEntryNote = '';
        $this->protocolResolveTarget = '';
        $this->protocolStopsProduction = false;
        $this->flashMessage = $this->t('protocol_entry_saved');
    }

    public function markNotificationsRead(): void
    {
        $this->ensureAuthenticated();
        Auth::user()->unreadNotifications()->update(['read_at' => now()]);
        unset($this->notifications, $this->unreadNotificationCount);
    }

    public function notificationText(array $data): string
    {
        $replace = [
            'order' => $data['order_number'] ?? '',
            'actor' => $data['actor'] ?? '',
        ];

        return match ($data['event'] ?? '') {
            'activated' => $this->t('notification_activated', $replace),
            'paused' => $this->t('notification_paused', $replace),
            'interrupted' => $this->t('notification_interrupted', $replace),
            'assigned' => $this->t('notification_assigned', $replace),
            default => $this->t('notification_unknown'),
        };
    }

    private function notifyWorkflowRecipients(Order $order, string $event): void
    {
        User::query()
            ->whereIn('role', [Role::Coordinator->value, Role::Operator->value])
            ->each(fn (User $recipient) => $recipient->notify(
                new OrderWorkflowNotification($order, $event, Auth::user()->name)
            ));
    }

    public function statusClass(string $status): string
    {
        return match ($status) {
            'active', 'in_progress', 'good' => 'bg-emerald-100 text-emerald-800',
            'warning' => 'bg-amber-100 text-amber-800',
            'broken', 'needs_repair', 'quality_issue', 'interrupted' => 'bg-red-100 text-red-800',
            'under_repair', 'setup' => 'bg-blue-100 text-blue-800',
            default => 'bg-slate-200 text-slate-700',
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => $this->t('active'),
            'inactive' => $this->t('inactive'),
            'stopped' => $this->t('stopped'),
            'standby' => $this->t('standby'),
            'warning' => $this->t('warning'),
            'broken' => $this->t('broken'),
            'needs_repair' => $this->t('needs_repair'),
            'under_repair' => $this->t('under_repair'),
            'setup' => $this->t('setup'),
            'in_progress' => $this->t('in_progress'),
            'paused' => $this->t('paused'),
            'completed' => $this->t('completed'),
            'interrupted' => $this->t('interrupted'),
            'good' => $this->t('good'),
            'quality_issue' => $this->t('quality_issue'),
            default => $status,
        };
    }

    public function progress(Order $order): int
    {
        return $order->quantity > 0 ? min(100, (int) round(($order->current_quantity / $order->quantity) * 100)) : 0;
    }

    public function productionStartedAt(Order $order): ?Carbon
    {
        $checks = collect(data_get($order->production_protocol, 'quality_checks', []))
            ->filter(fn ($check) => is_array($check) && ($check['quality_status'] ?? null) === 'good' && ! empty($check['checked_at']))
            ->map(fn (array $check) => Carbon::parse($check['checked_at']));

        return $checks->isEmpty() ? null : $checks->min();
    }

    public function expectedQuantity(Order $order): ?int
    {
        $cycleMs = $order->product?->planned_cycle_time ?: $order->cycle_time;
        $cavities = $order->mould?->cavity_count;
        $startedAt = $this->productionStartedAt($order);

        if (! $startedAt || ! $cycleMs || ! $cavities || $order->quantity <= 0) {
            return null;
        }

        $end = $order->ended_at ?? ($order->status === 'paused' ? $order->last_paused_at : null) ?? now();
        $elapsedMs = max(0, $startedAt->diffInMilliseconds($end, false));

        return (int) min($order->quantity, floor($elapsedMs / $cycleMs) * $cavities);
    }

    public function expectedProgress(Order $order): ?int
    {
        $expected = $this->expectedQuantity($order);

        return $expected === null ? null : (int) round($expected / $order->quantity * 100);
    }

    public function qualityStatus(Order $order): string
    {
        $protocol = $order->production_protocol ?? [];
        $qualityStatus = $protocol['quality_status'] ?? null;

        if (in_array($qualityStatus, ['good', 'warning', 'quality_issue'], true)) {
            return $qualityStatus;
        }

        $qualityChecks = $protocol['quality_checks'] ?? [];
        $lastQualityCheck = is_array($qualityChecks) ? end($qualityChecks) : null;
        $lastStatus = is_array($lastQualityCheck) ? ($lastQualityCheck['quality_status'] ?? null) : null;

        return in_array($lastStatus, ['good', 'warning', 'quality_issue'], true) ? $lastStatus : 'good';
    }

    public function lastQualityCheckAt(Order $order): ?Carbon
    {
        $timestamp = data_get($order->production_protocol, 'last_quality_check_at');

        return is_string($timestamp) && $timestamp !== '' ? Carbon::parse($timestamp) : null;
    }

    public function qualityCheckOverdue(Order $order): bool
    {
        $lastCheck = $this->lastQualityCheckAt($order);

        return $lastCheck === null || $lastCheck->lte(now()->subHours(3));
    }

    public function protocolEntryAppearance(string $type, ?string $qualityStatus = null): array
    {
        if ($type === 'quality_check') {
            return match ($qualityStatus) {
                'good' => ['container' => 'border-l-4 border-emerald-500 bg-emerald-50', 'badge' => 'bg-emerald-100 text-emerald-800', 'icon' => '✓'],
                'warning' => ['container' => 'border-l-4 border-amber-500 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'icon' => '!'],
                'quality_issue' => ['container' => 'border-l-4 border-red-500 bg-red-50', 'badge' => 'bg-red-100 text-red-800', 'icon' => '!'],
                default => ['container' => 'border-l-4 border-teal-500 bg-teal-50', 'badge' => 'bg-teal-100 text-teal-800', 'icon' => '✓'],
            };
        }

        return match ($type) {
            'machine_issue', 'mould_issue', 'issue' => ['container' => 'border-l-4 border-red-500 bg-red-50', 'badge' => 'bg-red-100 text-red-800', 'icon' => '!'],
            'parameter_change' => ['container' => 'border-l-4 border-amber-500 bg-amber-50', 'badge' => 'bg-amber-100 text-amber-800', 'icon' => '⚙'],
            'machine_status', 'mould_status' => ['container' => 'border-l-4 border-blue-500 bg-blue-50', 'badge' => 'bg-blue-100 text-blue-800', 'icon' => '↻'],
            'assignment', 'task_started' => ['container' => 'border-l-4 border-violet-500 bg-violet-50', 'badge' => 'bg-violet-100 text-violet-800', 'icon' => '▶'],
            'order_status', 'production_update' => ['container' => 'border-l-4 border-cyan-500 bg-cyan-50', 'badge' => 'bg-cyan-100 text-cyan-800', 'icon' => '↔'],
            default => ['container' => 'border-l-4 border-slate-400 bg-slate-50', 'badge' => 'bg-slate-200 text-slate-700', 'icon' => '•'],
        };
    }

    /** Restores the equipment and resumes the orders that were paused because of this exact problem. */
    private function resolveProblem(array $problem, string $target): void
    {
        $equipment = $problem['equipment'];
        $reason = $equipment->problem_description;

        $equipment->update(['status' => 'active', 'problem_description' => null]);

        if ($reason) {
            Order::query()
                ->where($target.'_id', $equipment->id)
                ->where('status', 'paused')
                ->where('pause_description', $reason)
                ->update(['status' => 'in_progress', 'pause_description' => null, 'last_paused_at' => null]);
        }
    }

    /** Machine/mould of the order that currently carry a warning or fault, keyed by target. */
    public function openProblems(Order $order): array
    {
        $problems = [];
        $faulty = ['warning', 'stopped', 'broken', 'needs_repair', 'under_repair'];

        foreach (['machine' => $order->machine, 'mould' => $order->mould] as $target => $equipment) {
            if ($equipment && in_array($equipment->status, $faulty, true)) {
                $problems[$target] = [
                    'label' => $this->t('protocol_target_'.$target),
                    'description' => $equipment->problem_description ?: $this->statusLabel($equipment->status),
                    'status' => $equipment->status,
                    'equipment' => $equipment,
                ];
            }
        }

        return $problems;
    }

    private function pauseOrdersFor(string $column, int $id, string $reason): void
    {
        Order::query()->where($column, $id)->where('status', 'in_progress')->each(function (Order $order) use ($reason): void {
            $order->update([
                'status' => 'paused',
                'pause_description' => $reason,
                'last_paused_at' => now(),
                'good_quantity' => $order->good_quantity ?: $order->current_quantity,
            ]);
            $this->appendProtocolEntry($order->refresh(), $order->current_quantity, 'issue', $reason);
        });
    }

    private function appendProtocolEntry(Order $order, int $quantity, string $type, string $note, array $meta = []): void
    {
        $protocol = $order->production_protocol ?? [];
        $entries = $protocol['entries'] ?? [];
        $entries[] = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'entry_type' => $type,
            'current_quantity' => max(0, min($quantity, $order->quantity)),
            'note' => $note,
            'meta' => $meta,
            'signed_by' => Auth::user()?->name ?? 'API',
            'signed_by_user_id' => Auth::id(),
        ];
        $order->update([
            'current_quantity' => max(0, min($quantity, $order->quantity)),
            'production_protocol' => array_merge($protocol, ['entries' => $entries]),
        ]);
    }
};
?>

<div class="min-h-screen bg-slate-100 text-slate-700">
    @guest
        <div class="flex min-h-screen items-center justify-center p-4">
            <form wire:submit="login" class="w-full max-w-sm rounded-xl border-t-4 border-teal-600 bg-white p-6 shadow-lg sm:p-8">
                <img src="{{ asset('logo.png') }}" alt="Periplast" class="mx-auto h-24 w-auto">
                <h1 class="mt-3 text-center text-sm text-slate-500">{{ $this->t('login_title') }}</h1>
                <div class="mt-6 space-y-4">
                    <label class="block text-sm font-semibold">{{ $this->t('email') }}
                        <input wire:model="email" type="email" autocomplete="username" autofocus class="mt-1 w-full rounded-md border-slate-300">
                    </label>
                    <label class="block text-sm font-semibold">{{ $this->t('password') }}
                        <input wire:model="password" type="password" autocomplete="current-password" class="mt-1 w-full rounded-md border-slate-300">
                    </label>
                    <label class="flex items-center gap-2 text-sm"><input wire:model="remember" type="checkbox" class="rounded border-slate-300"> {{ $this->t('remember_me') }}</label>
                    @error('email')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                    @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button type="submit" wire:loading.attr="disabled" class="mt-6 w-full rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('login') }}</button>
                <div class="mt-4 flex justify-center">
                    @include('partials.language-switcher')
                </div>
            </form>
        </div>
    @else
    <header x-data="{ menuOpen: false }" x-init="new ResizeObserver(() => document.documentElement.style.setProperty('--header-h', $el.offsetHeight + 'px')).observe($el)" class="sticky top-0 z-40 border-b-4 border-teal-600 bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-2 sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button" x-on:click="menuOpen = !menuOpen" x-bind:aria-expanded="menuOpen" aria-label="Menu" class="relative rounded-md border border-slate-300 p-2 text-slate-700 hover:bg-slate-100 md:hidden">
                    <svg x-show="! menuOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="menuOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    @if ($this->unreadNotificationCount > 0)
                        <span class="absolute -right-1.5 -top-1.5 h-3 w-3 rounded-full bg-red-600 ring-2 ring-white"></span>
                    @endif
                </button>
                <img src="{{ asset('logo.png') }}" alt="Periplast" class="h-10 w-auto sm:h-12">
                <span class="hidden text-sm font-semibold text-slate-500 lg:inline">{{ $this->t('live_overview') }}</span>
            </div>
            <div class="flex items-center gap-3">
                @include('partials.language-switcher')
                <span class="hidden text-sm font-medium text-slate-600 md:inline">{{ auth()->user()->name }}</span>
                <button wire:click="logout" class="rounded-md bg-slate-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800">{{ $this->t('logout') }}</button>
            </div>
        </div>
        <nav wire:poll.15s class="border-t border-slate-100" aria-label="Main">
            <div x-bind:class="menuOpen ? 'flex' : 'hidden md:flex'" class="mx-auto max-w-7xl flex-col gap-1 px-2 py-2 md:flex-row md:overflow-x-auto md:py-0 sm:px-4">
                @foreach (['machines' => 'machines', 'tasks' => 'tasks', 'orders' => 'orders', 'notifications' => 'notifications', 'maintenance' => 'maintenance', 'calculators' => 'calculators', 'moulds' => 'moulds', 'products' => 'products'] as $tab => $label)
                    @if ($this->canViewTab($tab))
                    <button wire:click="selectTab('{{ $tab }}')" x-on:click="menuOpen = false" @class(['shrink-0 whitespace-nowrap rounded-md px-4 py-3 text-left text-sm font-semibold transition md:rounded-none md:border-b-2', 'bg-teal-50 text-teal-700 md:border-teal-600 md:bg-transparent' => $activeTab === $tab, 'text-slate-600 hover:text-teal-700 md:border-transparent' => $activeTab !== $tab])>
                        {{ $this->t($label) }}
                        @if ($tab === 'notifications' && $this->unreadNotificationCount > 0)
                            <span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-bold text-white">{{ $this->unreadNotificationCount }}</span>
                        @endif
                    </button>
                    @endif
                @endforeach
            </div>
        </nav>
    </header>

    <main @class([
        'mx-auto w-full max-w-none px-3 py-5 sm:px-5 sm:py-6' => $activeTab === 'machines',
        'mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6' => $activeTab !== 'machines',
    ])>
        @if ($flashMessage)
            <div class="mb-5 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ $flashMessage }}
                <button wire:click="$set('flashMessage', '')" class="font-bold" aria-label="{{ $this->t('cancel') }}">&times;</button>
            </div>
        @endif

        @if ($activeTab === 'machines')
            <section wire:poll.60s>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $this->t('machines') }}</h1>
                    <select wire:model.live="machineFilter" aria-label="{{ $this->t('machine_status_label') }}" class="rounded-md border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="all">{{ $this->t('all_machines') }}</option>
                        <option value="active">{{ $this->t('active_machines') }}</option>
                        <option value="warning">{{ $this->t('warning_machines') }}</option>
                        <option value="stopped">{{ $this->t('stopped_machines') }}</option>
                        <option value="setup">{{ $this->t('setup_machines') }}</option>
                        <option value="idle">{{ $this->t('idle_machines') }}</option>
                    </select>
                </div>

                <div class="hidden xl:block">
                    <table class="w-full table-auto border-separate border-spacing-y-1.5 text-left text-[11px] leading-tight [&_td]:break-words [&_td]:px-1.5 [&_th]:px-1.5 [&_th]:align-bottom [&_th]:text-[10px]">
                        <thead class="text-slate-700">
                            <tr>
                                @foreach (['machine_number', 'type', 'running_product', 'gross_weight', 'net_weight', 'order_number', 'product_code', 'required_quantity', 'current_quantity', 'yesterday_quantity_same_time', 'cavity_count', 'actual_cycle_time', 'utilization_last_24_hours', 'theoretical_daily_production', 'actual_production_last_24_hours', 'color_percentage', 'daily_color_consumption', 'next_activated_order', 'production_protocol'] as $column)
                                    <th @class(['sticky top-[var(--header-h,7rem)] z-30 bg-slate-200 py-2', 'rounded-l-md' => $loop->first, 'rounded-r-md' => $loop->last]) title="{{ $this->t($column) }}">{{ $this->t(in_array($column, ['type', 'gross_weight', 'net_weight', 'production_protocol'], true) ? $column : 'col_'.$column) }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        @forelse ($this->machines->filter(fn ($machine) => $this->machineMatchesFilter($machine, $machineFilter)) as $machine)
                            @php($metrics = $machine->productionMetrics())
                            @php($order = $metrics->currentOrder())
                            @php($nextOrder = $metrics->nextActivatedOrder())
                            @php($state = $this->machineState($machine))
                            <tbody wire:key="machine-{{ $machine->id }}">
                                <tr @class([
                                    'group cursor-pointer align-middle text-slate-800 transition-colors focus:outline-none [&>td:first-child]:border-l-4 [&>td]:transition-colors [&:hover>td]:border-teal-500 [&:hover>td]:bg-teal-100 [&:hover>td:first-child]:border-l-teal-600 [&:focus-visible>td]:border-teal-500 [&:focus-visible>td]:bg-teal-100',
                                    '[&>td]:bg-emerald-50 [&>td]:border-emerald-200 [&>td:first-child]:border-l-emerald-500' => $state === 'running',
                                    '[&>td]:bg-amber-50 [&>td]:border-amber-200 [&>td:first-child]:border-l-amber-500' => $state === 'warning',
                                    '[&>td]:bg-red-50 [&>td]:border-red-200 [&>td:first-child]:border-l-red-500' => $state === 'stopped',
                                    '[&>td]:bg-blue-50 [&>td]:border-blue-200 [&>td:first-child]:border-l-blue-500' => $state === 'setup',
                                    '[&>td:first-child]:border-l-slate-300' => $state === 'idle',
                                ]) wire:click="toggleMachineRow({{ $machine->id }})" wire:keydown.enter="toggleMachineRow({{ $machine->id }})" role="button" tabindex="0" aria-expanded="{{ $expandedMachineId === $machine->id ? 'true' : 'false' }}" title="{{ $this->t('open_details') }}">
                                    <td class="rounded-l-md border-y border-l border-slate-200 bg-white px-2 py-2 font-bold text-slate-900 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $machine->name }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $machine->type }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">@if ($order && $this->qualityCheckOverdue($order))<span class="mr-1 inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-400 text-[10px] font-black text-white" title="{{ $this->t('quality_check_overdue') }}" aria-label="{{ $this->t('quality_check_overdue') }}">!</span>@endif{{ $order?->product?->name ?? '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">@if ($order?->measured_gross_weight !== null)<span @class(['font-bold text-red-600' => $order->measuredDeviates('gross_weight')]) title="{{ $this->t('theoretical') }}: {{ $order?->gross_weight ?? '-' }}">{{ $order->measured_gross_weight }}</span>@else<span class="text-slate-400" title="{{ $this->t('theoretical') }}">{{ $order?->gross_weight ?? '-' }}</span>@endif</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">@if ($order?->measured_net_weight !== null)<span @class(['font-bold text-red-600' => $order->measuredDeviates('net_weight')]) title="{{ $this->t('theoretical') }}: {{ $order?->net_weight ?? '-' }}">{{ $order->measured_net_weight }}</span>@else<span class="text-slate-400" title="{{ $this->t('theoretical') }}">{{ $order?->net_weight ?? '-' }}</span>@endif</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $order?->order_number ?? '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $order?->product?->code ?? '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $order ? number_format($order->quantity, 0, ',', ' ') : '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $order ? number_format($order->current_quantity, 0, ',', ' ') : '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $metrics->quantityAtYesterdaySameTime($order) ?? '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $order?->mould?->cavity_count ?? '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">@if ($order?->measured_cycle_time !== null)<span @class(['font-bold text-red-600' => $order->measuredDeviates('cycle_time')]) title="{{ $this->t('theoretical') }}: {{ $order?->cycle_time === null ? '-' : number_format($order->cycle_time / 1000, 2, ',', ' ') }}">{{ number_format($order->measured_cycle_time / 1000, 2, ',', ' ') }}</span>@else<span class="text-slate-400" title="{{ $this->t('theoretical') }}">{{ $order?->cycle_time === null ? '-' : number_format($order->cycle_time / 1000, 2, ',', ' ') }}</span>@endif</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ ($utilization = $metrics->utilizationLast24Hours($order)) === null ? '-' : number_format($utilization, 1, ',', ' ').'%' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $metrics->theoreticalDailyProduction($order) ?? '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ $metrics->producedLast24Hours() ?? '-' }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">@if ($order?->measured_color_percentage !== null)<span @class(['font-bold text-red-600' => $order->measuredDeviates('color_percentage')]) title="{{ $this->t('theoretical') }}: {{ $order?->color_percentage === null ? '-' : number_format((float) $order->color_percentage, 2, ',', ' ').'%' }}">{{ number_format((float) $order->measured_color_percentage, 2, ',', ' ') }}%</span>@else<span class="text-slate-400" title="{{ $this->t('theoretical') }}">{{ $order?->color_percentage === null ? '-' : number_format((float) $order->color_percentage, 2, ',', ' ').'%' }}</span>@endif</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">{{ ($colorConsumption = $metrics->dailyColorConsumptionKg($order)) === null ? '-' : number_format($colorConsumption, 3, ',', ' ') }}</td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">
                                        @if ($nextOrder)
                                            <span class="block font-semibold">{{ $nextOrder->order_number }}</span>
                                            <span class="block">{{ $nextOrder->product?->code ?? $nextOrder->product?->name }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td class="rounded-r-md border-y border-r border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">
                                        @if ($order)
                                            <button wire:click.stop="openProductionProtocol({{ $order->id }})" class="whitespace-nowrap rounded-md border border-slate-300 bg-white px-2 py-1 text-[11px] font-bold text-slate-700 hover:bg-slate-100">{{ $this->t('production_protocol') }}</button>
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>
                                @if ($expandedMachineId === $machine->id && ($expanded = $this->expandedMachine))
                                    <tr wire:key="machine-expanded-{{ $machine->id }}">
                                        <td colspan="19" class="p-0">
                                            <div x-data="{ show: false }" x-init="$nextTick(() => show = true)" x-show="show" x-cloak
                                                x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-y-2 opacity-0" x-transition:enter-end="translate-y-0 opacity-100"
                                                class="rounded-md border border-teal-200 border-l-4 border-l-teal-600 bg-slate-50 p-4 shadow-inner">
                                                <div class="mb-3 flex items-center justify-between gap-3">
                                                    <h3 class="text-base font-bold text-slate-900">{{ $expanded->name }} <span class="font-normal text-slate-500">· {{ $expanded->type }}</span></h3>
                                                    <button type="button" wire:click="toggleMachineRow({{ $machine->id }})" class="rounded-md px-2.5 py-1 text-xl leading-none text-slate-500 hover:bg-slate-200 hover:text-slate-800" aria-label="{{ $this->t('close') }}">&times;</button>
                                                </div>
                                                @include('partials.machine-detail-body', ['detail' => $expanded, 'inline' => true])
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        @empty
                            <tbody>
                                <tr>
                                    <td colspan="19" class="rounded-md bg-white p-8 text-center text-slate-500">{{ $this->t('no_machines') }}</td>
                                </tr>
                            </tbody>
                        @endforelse
                    </table>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:hidden">
                    @forelse ($this->machines->filter(fn ($machine) => $this->machineMatchesFilter($machine, $machineFilter)) as $machine)
                        @php($order = $machine->productionMetrics()->currentOrder())
                        @php($state = $this->machineState($machine))
                        <article wire:key="machine-card-{{ $machine->id }}" wire:click="openMachineDetail({{ $machine->id }})" wire:keydown.enter="openMachineDetail({{ $machine->id }})" role="button" tabindex="0" title="{{ $this->t('open_details') }}" @class([
                            'cursor-pointer overflow-hidden rounded-xl border shadow-sm transition hover:border-teal-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-600',
                            'border-emerald-300 bg-emerald-50' => $state === 'running',
                            'border-amber-300 bg-amber-50' => $state === 'warning',
                            'border-red-300 bg-red-50' => $state === 'stopped',
                            'border-blue-300 bg-blue-50' => $state === 'setup',
                            'border-slate-200 bg-white' => $state === 'idle',
                        ])>
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900">{{ $machine->name }}</h2>
                                    <p class="text-sm text-slate-500">{{ $machine->type }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($machine->status)])>{{ $this->statusLabel($machine->status) }}</span>
                                    @if ($this->canDo(\App\Enums\Role::START_MACHINE) || $this->canDo(\App\Enums\Role::STOP_MACHINE) || $this->canDo(\App\Enums\Role::REPORT_EQUIPMENT_WARNING))
                                        <button wire:click.stop="openMachineModal({{ $machine->id }})" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-teal-700" title="{{ $this->t('change_status') }}">⚙</button>
                                    @endif
                                </div>
                            </div>
                            <div class="p-5">
                                <dl class="grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-slate-500">{{ $this->t('machine_status_label') }}</dt><dd class="mt-0.5"><span @class(['inline-block rounded-full px-2.5 py-0.5 text-xs font-bold', $this->statusClass($machine->status)])>{{ $machine->status === 'active' ? $this->t('good') : $this->statusLabel($machine->status) }}</span>@if ($machine->problem_description && $machine->status !== 'active')<p class="mt-1 break-words text-xs text-slate-700">{{ $machine->problem_description }}</p>@endif</dd></div>
                                    <div><dt class="text-slate-500">{{ $this->t('mould_status_label') }}</dt><dd class="mt-0.5">@if ($order?->mould)<span @class(['inline-block rounded-full px-2.5 py-0.5 text-xs font-bold', $this->statusClass($order->mould->status)])>{{ $order->mould->status === 'active' ? $this->t('good') : $this->statusLabel($order->mould->status) }}</span>@if ($order->mould->problem_description && $order->mould->status !== 'active')<p class="mt-1 break-words text-xs text-slate-700">{{ $order->mould->problem_description }}</p>@endif @else<span class="font-semibold">-</span>@endif</dd></div>
                                </dl>
                                @if ($order)
                                    <div class="mt-5 rounded-lg border border-teal-100 bg-teal-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-teal-950">{{ $this->t('current_order') }}</p>
                                                <p class="text-sm text-teal-800">{{ $order->order_number }} · {{ $order->product?->name ?? $this->t('no_products') }}</p>
                                            </div>
                                            @if ($this->canManageOrders())
                                                <button wire:click.stop="openOrderModal({{ $order->id }})" class="rounded-md bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800">{{ $this->t('manage') }}</button>
                                            @endif
                                        </div>
                                        <div class="mt-4 flex justify-between text-sm"><span>{{ $this->t('current_quantity') }}</span><strong>{{ $order->current_quantity }} / {{ $order->quantity }} db</strong></div>
                                        @include('partials.production-progress-bar', ['order' => $order])
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($order))])>
                                                {{ $this->t('quality') }}: {{ $this->statusLabel($this->qualityStatus($order)) }}
                                            </span>
                                            @php($lastQualityCheck = $this->lastQualityCheckAt($order))
                                            <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-red-100 text-red-800' => $this->qualityCheckOverdue($order), 'bg-slate-100 text-slate-700' => ! $this->qualityCheckOverdue($order)])>
                                                {{ $lastQualityCheck ? $this->t('last_quality_check_at', ['time' => $lastQualityCheck->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s')]) : $this->t('no_quality_check_yet') }}
                                            </span>
                                            @if ($this->qualityCheckOverdue($order))
                                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-800">{{ $this->t('quality_check_overdue') }}</span>
                                            @endif
                                            @if ($this->canCheckQuality())
                                                <button wire:click.stop="openQualityModal({{ $order->id }})" class="rounded-md border border-teal-300 bg-white px-3 py-1 text-xs font-bold text-teal-800 hover:bg-teal-100">{{ $this->t($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY) ? 'color_check' : 'quality_check') }}</button>
                                            @endif
                                            <button wire:click.stop="openProductionProtocol({{ $order->id }})" class="rounded-md border border-slate-300 bg-white px-3 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100">{{ $this->t('production_protocol') }}</button>
                                        </div>
                                    </div>
                                @else
                                    <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-500">{{ $this->t('no_running_order') }}</p>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 sm:col-span-2">{{ $this->t('no_machines') }}</p>
                    @endforelse
                </div>

            </section>
        @elseif ($activeTab === 'notifications')
            <section wire:poll.15s class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-bold text-slate-900">{{ $this->t('notifications') }}</h1>
                    @if ($this->unreadNotificationCount > 0)
                        <button wire:click="markNotificationsRead" class="text-sm font-semibold text-teal-700 hover:text-teal-900">{{ $this->t('mark_notifications_read') }}</button>
                    @endif
                </div>
                <ul class="mt-4 space-y-2">
                    @forelse ($this->notifications as $notification)
                        <li @class(['rounded-md px-3 py-2 text-sm', 'bg-teal-50 text-teal-900' => ! $notification->read_at, 'bg-slate-50 text-slate-600' => $notification->read_at])>
                            {{ $this->notificationText($notification->data) }}
                            <span class="ml-2 text-xs text-slate-400">{{ $notification->created_at->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="text-sm text-slate-500">{{ $this->t('no_notifications') }}</li>
                    @endforelse
                </ul>
            </section>
        @elseif ($activeTab === 'maintenance')
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h1 class="text-2xl font-bold text-slate-900">{{ $this->t('maintenance') }}</h1>
                <p class="mt-4 text-sm text-slate-500">{{ $this->t('no_maintenance_yet') }}</p>
            </section>
        @elseif ($activeTab === 'calculators')
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h1 class="mb-5 text-2xl font-bold text-slate-900">{{ $this->t('calculators') }}</h1>
                <button wire:click="openCalculator" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-bold text-slate-800 hover:border-teal-400 hover:bg-teal-50">{{ $this->t('production_time_calculator') }}</button>
            </section>
        @elseif ($activeTab === 'tasks')
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900">{{ $this->t('tasks') }}</h1>
                @if ($this->canDo(\App\Enums\Role::ASSIGN_ORDER))
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-bold">{{ $this->t('assignments') }}</h2>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @forelse ($this->ordersForAssignment as $order)
                                <div wire:key="assignment-{{ $order->id }}" class="rounded-lg bg-slate-50 p-4">
                                    <p class="font-semibold">{{ $order->order_number }}</p>
                                    <p class="text-sm text-slate-500">{{ $order->machine?->name }} · {{ $order->product?->name }}</p>
                                    <p class="mt-1 text-sm">{{ $this->t('status') }}: {{ $this->statusLabel($order->status) }}</p>
                                    <label class="mt-3 block text-sm font-semibold">{{ $this->t('operator') }}
                                        <select wire:change="assignOperator({{ $order->id }}, $event.target.value)" class="mt-1 w-full rounded-md border-slate-300">
                                            <option value="">{{ $this->t('select_operator') }}</option>
                                            @foreach ($this->operators as $operator)
                                                <option value="{{ $operator->id }}" @selected($order->assigned_operator_id === $operator->id)>{{ $operator->name }}</option>
                                            @endforeach
                                        </select>
                                    </label>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 md:col-span-2">{{ $this->t('no_orders_to_assign') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif

                @if ($this->canDo(\App\Enums\Role::CHECK_COLOR))
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-bold">{{ $this->t('my_assignments') }}</h2>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            @forelse ($this->ordersForMyTasks as $order)
                                <div wire:key="my-assignment-{{ $order->id }}" class="rounded-lg bg-slate-50 p-4">
                                    <p class="font-semibold">{{ $order->order_number }}</p>
                                    <p class="text-sm text-slate-500">{{ $order->machine?->name }} · {{ $order->product?->name }}</p>
                                    <p class="mt-2 text-sm">{{ $this->t('status') }}: {{ $this->statusLabel($order->status) }}</p>
                                    <p class="mt-1 text-sm">{{ $this->t('current_quantity') }}: {{ $order->current_quantity }} / {{ $order->quantity }}</p>
                                    <button wire:click="startAssignedTask({{ $order->id }})" class="mt-3 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('start_task') }}</button>
                                </div>
                            @empty
                                <p class="text-sm text-slate-500 md:col-span-2">{{ $this->t('no_assignments') }}</p>
                            @endforelse
                        </div>
                    </div>
                @endif
            </section>
        @elseif ($activeTab === 'orders')
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900">{{ $this->t('orders') }}</h1>
                <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold">{{ $this->t('all_orders') }}</h2>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @forelse ($this->ordersForViewing as $order)
                            <div wire:key="order-{{ $order->id }}" class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-4">
                                <div>
                                    <p class="font-semibold">{{ $order->order_number }}</p>
                                    <p class="text-sm text-slate-500">{{ $order->machine?->name }} · {{ $order->product?->name }}</p>
                                    <p class="text-sm text-slate-600">{{ $this->t('status') }}: {{ $this->statusLabel($order->status) }}</p>
                                    @if ($order->assignedOperator)
                                        <p class="text-sm text-slate-600">{{ $this->t('operator') }}: {{ $order->assignedOperator->name }}</p>
                                    @endif
                                </div>
                                @if ($this->canDo(\App\Enums\Role::ACTIVATE_ORDER) && in_array($order->status, ['waiting', 'paused'], true))
                                    <button wire:click="activateOrder({{ $order->id }})" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t($order->status === 'paused' ? 'resume' : 'activate') }}</button>
                                @endif
                                <button wire:click="openProductionProtocol({{ $order->id }})" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100">{{ $this->t('production_protocol') }}</button>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500 md:col-span-2">{{ $this->t('no_waiting_orders') }}</p>
                        @endforelse
                    </div>
                </div>

            </section>
        @elseif ($activeTab === 'moulds')
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900">{{ $this->t('moulds') }}</h1>
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($this->moulds as $mould)
                        <article wire:key="mould-{{ $mould->id }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex justify-between gap-3"><h2 class="font-bold text-slate-900">{{ $mould->name }}</h2><span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($mould->status)])>{{ $this->statusLabel($mould->status) }}</span></div>
                            <dl class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><dt class="text-slate-500">{{ $this->t('number') }}</dt><dd>{{ $mould->number }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">{{ $this->t('cavity_count') }}</dt><dd>{{ $mould->cavity_count ?? '-' }}</dd></div><div class="flex justify-between"><dt class="text-slate-500">{{ $this->t('opening') }}</dt><dd>{{ $mould->open_mm ?? '-' }} mm</dd></div></dl>
                            @if ($mould->products->isNotEmpty())<p class="mt-4 text-sm text-slate-600"><strong>{{ $this->t('product') }}:</strong> {{ $mould->products->pluck('name')->join(', ') }}</p>@endif
                            @if ($mould->problem_description)<p class="mt-3 text-sm text-red-700">{{ $mould->problem_description }}</p>@endif
                        </article>
                    @empty
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">{{ $this->t('no_moulds') }}</p>
                    @endforelse
                </div>
            </section>
        @else
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900">{{ $this->t('products') }}</h1>
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($this->products as $product)
                        <article wire:key="product-{{ $product->id }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h2 class="font-bold text-slate-900">{{ $product->name }}</h2><p class="mt-1 text-sm text-slate-500">{{ $product->code }}</p>
                            <dl class="mt-4 space-y-2 text-sm"><div><dt class="text-slate-500">{{ $this->t('material') }}</dt><dd class="font-medium">{{ $product->material?->name ?? '-' }} {{ $product->material?->code ? '('.$product->material->code.')' : '' }}</dd></div><div><dt class="text-slate-500">{{ $this->t('color') }}</dt><dd class="font-medium">{{ $product->color?->name ?? '-' }} {{ $product->color?->code ? '('.$product->color->code.')' : '' }}</dd></div><div><dt class="text-slate-500">{{ $this->t('additives') }}</dt><dd class="font-medium">{{ $product->additives->pluck('name')->join(', ') ?: '-' }}</dd></div><div><dt class="text-slate-500">{{ $this->t('compatible_moulds') }}</dt><dd class="font-medium">{{ $product->moulds->pluck('name')->join(', ') ?: '-' }}</dd></div></dl>
                        </article>
                    @empty
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3">{{ $this->t('no_products') }}</p>
                    @endforelse
                </div>
            </section>
        @endif
    </main>

    @include('partials.footer')
    @if ($detailMachineId && ($detail = $this->detailMachine))
        <div wire:key="machine-detail-{{ $detail->id }}" wire:click.self="closeMachineDetail" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 sm:items-center sm:p-4">
            <div class="flex max-h-[95vh] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl bg-white shadow-xl sm:rounded-xl">
                <div class="flex items-start justify-between gap-3 border-b-4 border-teal-600 px-5 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900">{{ $detail->name }}</h2>
                        <p class="text-sm text-slate-500">{{ $detail->type }}</p>
                    </div>
                    <button wire:click="closeMachineDetail" class="rounded-md px-3 py-1 text-2xl leading-none text-slate-500 hover:bg-slate-100 hover:text-slate-800" aria-label="{{ $this->t('close') }}">&times;</button>
                </div>

                <div class="space-y-4 overflow-y-auto p-4 sm:p-5">
                    @include('partials.machine-detail-body', ['detail' => $detail])
                </div>
            </div>
        </div>
    @endif

    @if ($showCalculatorModal)
        @php($calcProduct = $calcProductId ? $this->products->firstWhere('id', $calcProductId) : null)
        @php($calcMinutes = $this->calculatedProductionMinutes())
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6">
                <h2 class="text-xl font-bold">{{ $this->t('production_time_calculator') }}</h2>
                <div class="mt-5 grid gap-4">
                    <label class="text-sm font-semibold">{{ $this->t('product') }}
                        <select wire:model.live="calcProductId" class="mt-1 w-full rounded-md border-slate-300">
                            <option value="">{{ $this->t('select_product') }}</option>
                            @foreach ($this->products as $product)
                                <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->code }})</option>
                            @endforeach
                        </select>
                    </label>
                    @if ($calcProduct)
                        @if ($calcProduct->moulds->count() > 1)
                            <label class="text-sm font-semibold">{{ $this->t('select_mould') }}
                                <select wire:model.live="calcMouldId" class="mt-1 w-full rounded-md border-slate-300">
                                    @foreach ($calcProduct->moulds as $mould)
                                        <option value="{{ $mould->id }}">{{ $mould->name }} ({{ $mould->number }})</option>
                                    @endforeach
                                </select>
                            </label>
                        @endif
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-semibold">{{ $this->t('cavity_count') }}<input wire:model.live.debounce.400ms="calcCavities" type="number" min="1" class="mt-1 w-full rounded-md border-slate-300"></label>
                            <label class="text-sm font-semibold">{{ $this->t('cycle_time_seconds') }}<input wire:model.live.debounce.400ms="calcCycleSeconds" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-slate-300"></label>
                        </div>
                        <p class="-mt-2 text-xs text-slate-500">{{ $this->t('calc_not_saved_hint') }}</p>
                        <label class="text-sm font-semibold">{{ $this->t('production_quantity') }}<input wire:model.live.debounce.400ms="calcQuantity" type="number" min="1" class="mt-1 w-full rounded-md border-slate-300"></label>
                        <div class="rounded-lg bg-teal-50 p-4 text-sm text-teal-900">
                            @if ($calcMinutes !== null)
                                <div class="font-semibold">{{ $this->t('calc_result') }}</div>
                                <div class="mt-1 text-xl font-bold">{{ intdiv($calcMinutes, 1440) }} {{ $this->t('calc_days') }}, {{ intdiv($calcMinutes % 1440, 60) }} {{ $this->t('calc_hours') }}, {{ $calcMinutes % 60 }} {{ $this->t('calc_minutes') }}</div>
                            @else
                                {{ $this->t('calc_missing_data') }}
                            @endif
                        </div>
                    @endif
                </div>
                <div class="mt-6 flex justify-end"><button wire:click="$set('showCalculatorModal', false)" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('close') }}</button></div>
            </div>
        </div>
    @endif

    @if ($showOrderModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold">{{ $this->t('manage_order') }}</h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold">{{ $this->t('status') }}<select wire:model.live="orderStatus" class="mt-1 w-full rounded-md border-slate-300">@if (! $this->isAdmin())<option value="">{{ $this->t('select_status') }}</option>@endif @if ($this->isAdmin())<option value="in_progress">{{ $this->t('in_progress') }}</option><option value="paused">{{ $this->t('paused') }}</option><option value="completed">{{ $this->t('completed') }}</option><option value="interrupted">{{ $this->t('interrupted') }}</option>@else<option value="paused">{{ $this->t('paused') }}</option><option value="interrupted">{{ $this->t('interrupted') }}</option>@endif</select></label><label class="text-sm font-semibold">{{ $this->t('current_quantity') }}<input wire:model="currentQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label>@if(in_array($orderStatus, ['completed', 'interrupted'], true))<label class="text-sm font-semibold">{{ $this->t('good_quantity') }}<input wire:model="goodQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><label class="text-sm font-semibold">{{ $this->t('bad_quantity') }}<input wire:model="badQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label>@endif</div><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('showOrderModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600">{{ $this->t('cancel') }}</button><button wire:click="saveOrder" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('save') }}</button></div></div></div>
    @endif

    @if ($showProtocolModal && ($protocolOrder = \App\Models\Order::query()->find($protocolOrderId)))
        @php($protocol = $protocolOrder->production_protocol ?? [])
        @php($entries = collect($protocol['entries'] ?? [])->sortByDesc(fn (array $entry) => $entry['timestamp'] ?? ''))
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4">
            <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
                <div class="flex items-start justify-between gap-3 border-b-4 border-teal-600 px-5 py-4">
                    <div>
                        <h2 class="text-xl font-bold">{{ $this->t('production_protocol') }} · {{ $protocolOrder->order_number }}</h2>
                        <p class="text-sm text-slate-500">{{ $protocolOrder->machine?->name }} · {{ $protocolOrder->product?->name }}</p>
                    </div>
                    <button wire:click="$set('showProtocolModal', false)" class="rounded-md px-3 py-1 text-2xl leading-none text-slate-500 hover:bg-slate-100" aria-label="{{ $this->t('close') }}">&times;</button>
                </div>

                <div class="space-y-5 overflow-y-auto p-5">
                    <section>
                        <h3 class="font-bold">{{ $this->t('protocol_entries') }}</h3>
                        <ol class="mt-3 space-y-3">
                            @forelse ($entries as $entry)
                                @php($appearance = $this->protocolEntryAppearance(data_get($entry, 'entry_type', 'note'), data_get($entry, 'meta.quality_status')))
                                <li wire:key="protocol-entry-{{ $protocolOrder->id }}-{{ $loop->index }}" class="rounded-r-lg {{ $appearance['container'] }} p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold {{ $appearance['badge'] }}" aria-hidden="true">{{ $appearance['icon'] }}</span>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $appearance['badge'] }}">{{ $this->t('protocol_type_'.data_get($entry, 'entry_type', 'note')) }}</span>
                                        </span>
                                        <time class="text-sm text-slate-600">{{ \Illuminate\Support\Carbon::parse(data_get($entry, 'timestamp'))->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s') }}</time>
                                    </div>
                                    <p class="mt-2 whitespace-pre-wrap text-sm">{{ data_get($entry, 'note', '') }}</p>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                        <span>{{ $this->t('signed_by') }}: {{ data_get($entry, 'signed_by', $this->t('unknown_signer')) }}</span>
                                        <span>{{ $this->t('current_quantity') }}: {{ data_get($entry, 'current_quantity', $protocolOrder->current_quantity) }}</span>
                                    </div>
                                </li>
                            @empty
                                <li class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500">{{ $this->t('no_protocol_entries') }}</li>
                            @endforelse
                        </ol>
                    </section>

                    <section class="rounded-lg border border-slate-200 p-4">
                        <h3 class="font-bold">{{ $this->t('add_protocol_entry') }}</h3>
                        <div class="mt-3 grid gap-3">
                            <label class="text-sm font-semibold">{{ $this->t('protocol_entry_type') }}
                                <select wire:model.live="protocolEntryType" class="mt-1 w-full rounded-md border-slate-300">
                                    @php($openProblems = $this->openProblems($protocolOrder))
                                    @foreach (['note', 'parameter_change', 'machine_issue', 'mould_issue', 'other', 'resolved'] as $type)
                                        @continue($type === 'resolved' && (empty($openProblems) || ! $this->canDo(\App\Enums\Role::START_MACHINE)))
                                        <option value="{{ $type }}">{{ $this->t('protocol_type_'.$type) }}</option>
                                    @endforeach
                                </select>
                            </label>
                            @if ($protocolEntryType === 'resolved' && ! empty($openProblems))
                                <fieldset class="grid gap-2">
                                    <legend class="text-sm font-semibold">{{ $this->t('protocol_resolve_which') }}</legend>
                                    @foreach ($openProblems as $target => $problem)
                                        <label class="flex items-start gap-2 rounded-md border border-slate-200 p-2 text-sm">
                                            <input type="radio" wire:model="protocolResolveTarget" value="{{ $target }}" class="mt-1">
                                            <span>
                                                <span @class(['rounded-full px-2 py-0.5 text-xs font-bold', $this->statusClass($problem['status'])])>{{ $problem['label'] }} · {{ $this->statusLabel($problem['status']) }}</span>
                                                <span class="mt-1 block text-slate-700">{{ $problem['description'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                    @error('protocolResolveTarget')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                                </fieldset>
                            @endif
                            @if (in_array($protocolEntryType, ['machine_issue', 'mould_issue', 'other'], true))
                                <label class="flex items-center gap-2 text-sm font-semibold">
                                    <input type="checkbox" wire:model="protocolStopsProduction" class="rounded border-slate-300">
                                    {{ $this->t('protocol_stops_production') }}
                                </label>
                                <p class="-mt-2 text-xs text-slate-500">{{ $this->t('protocol_stops_production_hint') }}</p>
                            @endif
                            <label class="text-sm font-semibold">{{ $this->t('protocol_entry_note') }}
                                <textarea wire:model="protocolEntryNote" rows="3" maxlength="2000" class="mt-1 w-full rounded-md border-slate-300"></textarea>
                                @error('protocolEntryNote')<span class="text-xs text-red-600">{{ $message }}</span>@enderror
                            </label>
                        </div>
                        <div class="mt-4 flex justify-end gap-3">
                            <button wire:click="$set('showProtocolModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600">{{ $this->t('cancel') }}</button>
                            <button wire:click="addProductionProtocolEntry" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('save') }}</button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    @endif

    @if ($showMeasuredModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4">
            <form wire:submit="saveMeasuredData" class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6">
                <h2 class="text-xl font-bold">{{ $this->t('measured_data') }}</h2>
                <div class="mt-5 grid gap-4">
                    <label class="text-sm font-semibold">{{ $this->t('actual_cycle') }} (s)<input wire:model="measuredCycleSeconds" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-slate-300">@error('measuredCycleSeconds')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="text-sm font-semibold">{{ $this->t('net_weight') }} (g)<input wire:model="measuredNetWeight" type="number" step="0.001" min="0" class="mt-1 w-full rounded-md border-slate-300">@error('measuredNetWeight')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="text-sm font-semibold">{{ $this->t('gross_weight') }} (g)<input wire:model="measuredGrossWeight" type="number" step="0.001" min="0" class="mt-1 w-full rounded-md border-slate-300">@error('measuredGrossWeight')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                    <label class="text-sm font-semibold">{{ $this->t('color_percentage') }} (%)<input wire:model="measuredColorPercentage" type="number" step="0.01" min="0" class="mt-1 w-full rounded-md border-slate-300">@error('measuredColorPercentage')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>
                </div>
                <div class="mt-5 flex justify-end gap-3">
                    <button type="button" wire:click="$set('showMeasuredModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600">{{ $this->t('cancel') }}</button>
                    <button type="submit" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('save') }}</button>
                </div>
            </form>
        </div>
    @endif

    @if ($showQualityModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold">{{ $this->t($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY) ? 'record_color_check' : 'record_quality_check') }}</h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold">{{ $this->t('result') }}<select wire:model.live="qualityStatus" class="mt-1 w-full rounded-md border-slate-300"><option value="good">{{ $this->t('good') }}</option><option value="warning">{{ $this->t('warning') }}</option><option value="quality_issue">{{ $this->t('quality_issue') }}</option></select></label><label class="text-sm font-semibold">{{ $this->t('current_quantity') }}<input wire:model="currentQuantity" wire:input="$set('quantityWarning', '')" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label>@if($quantityWarning !== '')<div class="rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900" role="alert"><p class="font-semibold">{{ $quantityWarning }}</p><p class="mt-1">{{ $this->t('quantity_confirm_question') }}</p><div class="mt-3 flex gap-2"><button wire:click="saveQualityCheck(true)" class="rounded-md bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700">{{ $this->t('quantity_confirm_yes') }}</button><button wire:click="$set('quantityWarning', '')" class="rounded-md border border-amber-400 px-3 py-1.5 text-xs font-bold text-amber-900">{{ $this->t('quantity_confirm_edit') }}</button></div></div>@endif@if($qualityStatus !== 'good')<div wire:key="quality-problem-fields-{{ $qualityStatus }}" class="grid gap-4"><label class="text-sm font-semibold">{{ $this->t('problem_type') }}<select wire:model="qualityProblemType" class="mt-1 w-full rounded-md border-slate-300">@if ($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY))<option value="color_issue">{{ $this->t('color_issue') }}</option>@else<option value="surface_defect">{{ $this->t('surface_defect') }}</option><option value="dimension_issue">{{ $this->t('dimension_issue') }}</option><option value="color_issue">{{ $this->t('color_issue') }}</option><option value="material_issue">{{ $this->t('material_issue') }}</option><option value="burn_mark">{{ $this->t('burn_mark') }}</option><option value="flash">{{ $this->t('flash') }}</option><option value="short_shot">{{ $this->t('short_shot') }}</option><option value="contamination">{{ $this->t('contamination') }}</option><option value="other">{{ $this->t('other') }}</option>@endif</select></label><label class="text-sm font-semibold">{{ $this->t('problem_reason') }}<textarea wire:model="qualityProblemNote" rows="3" placeholder="{{ $this->t('problem_reason_placeholder') }}" class="mt-1 w-full rounded-md border-slate-300"></textarea>@error('qualityProblemNote')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label></div>@endif @if ($this->requiresResolution())<label class="text-sm font-semibold">{{ $this->t('resolution_description') }}<textarea wire:model="resolutionDescription" rows="3" class="mt-1 w-full rounded-md border-slate-300"></textarea>@error('resolutionDescription')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>@endif</div><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('showQualityModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600">{{ $this->t('cancel') }}</button><button wire:click="saveQualityCheck" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('record') }}</button></div></div></div>
    @endif
    @endguest
</div>