<?php

use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component
{
    public string $activeTab = 'machines';
    public ?int $machineId = null;
    public ?int $detailMachineId = null;
    public ?int $mouldId = null;
    public ?int $orderId = null;
    public ?int $qualityOrderId = null;
    public bool $showMachineModal = false;
    public bool $showMouldModal = false;
    public bool $showOrderModal = false;
    public bool $showQualityModal = false;
    public string $status = '';
    public string $problemDescription = '';
    public string $orderStatus = 'in_progress';
    public int $currentQuantity = 0;
    public int $goodQuantity = 0;
    public int $badQuantity = 0;
    public string $qualityStatus = 'good';
    public string $qualityProblemType = 'surface_defect';
    public string $qualityProblemNote = '';
    public string $flashMessage = '';
    public string $locale = 'hu';
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    public function mount(): void
    {
        $this->locale = session('papirus.locale', config('app.locale'));
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

        if (! Auth::attempt($credentials, $this->remember)) {
            RateLimiter::hit($throttleKey);
            $this->addError('email', $this->t('login_failed'));

            return;
        }

        RateLimiter::clear($throttleKey);
        session()->regenerate();
        $this->reset('email', 'password', 'remember');
    }

    public function logout(): void
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();
        $this->redirect('/', navigate: false);
    }

    private function ensureAuthenticated(): void
    {
        abort_unless(Auth::check(), 403);
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
                ->whereIn('status', ['in_progress', 'paused'])
                ->with(['mould:id,name,number,cavity_count', 'product:id,name,code'])
                ->orderByDesc('started_at')])
            ->orderBy('name')
            ->get();
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

    public function selectTab(string $tab): void
    {
        $this->activeTab = in_array($tab, ['machines', 'moulds', 'products', 'orders'], true) ? $tab : 'machines';
    }

    #[Computed]
    public function detailMachine()
    {
        if (! $this->detailMachineId) {
            return null;
        }

        return Machine::query()
            ->with([
                'moulds:id,name,number',
                'orders' => fn ($query) => $query
                    ->whereIn('status', ['in_progress', 'paused'])
                    ->with(['mould', 'product'])
                    ->orderByDesc('started_at'),
            ])
            ->find($this->detailMachineId);
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

    public function openMachineModal(int $machineId): void
    {
        $this->ensureAuthenticated();
        $machine = Machine::findOrFail($machineId);
        $this->machineId = $machine->id;
        $this->status = $machine->status;
        $this->problemDescription = $machine->problem_description ?? '';
        $this->resetValidation();
        $this->showMachineModal = true;
    }

    public function saveMachineStatus(): void
    {
        $this->ensureAuthenticated();
        $validated = $this->validate([
            'status' => ['required', Rule::in(['active', 'stopped', 'warning', 'broken'])],
            'problemDescription' => ['nullable', 'string', 'max:1000', Rule::requiredIf(in_array($this->status, ['warning', 'broken'], true))],
        ]);

        $machine = Machine::findOrFail($this->machineId);
        $machine->update([
            'status' => $validated['status'],
            'problem_description' => in_array($validated['status'], ['warning', 'broken'], true)
                ? $validated['problemDescription']
                : null,
        ]);

        if ($validated['status'] === 'broken') {
            $this->pauseOrdersFor('machine_id', $machine->id, $validated['problemDescription'] ?: $this->t('machine_paused_reason'));
        }

        $this->showMachineModal = false;
        $this->flashMessage = $this->t('machine_status_updated');
        unset($this->machines);
    }

    public function openMouldModal(int $mouldId): void
    {
        $this->ensureAuthenticated();
        $mould = Mould::findOrFail($mouldId);
        $this->mouldId = $mould->id;
        $this->status = $mould->status;
        $this->problemDescription = $mould->problem_description ?? '';
        $this->resetValidation();
        $this->showMouldModal = true;
    }

    public function saveMouldStatus(): void
    {
        $this->ensureAuthenticated();
        $validated = $this->validate([
            'status' => ['required', Rule::in(['active', 'standby', 'warning', 'needs_repair', 'under_repair'])],
            'problemDescription' => ['nullable', 'string', 'max:1000', Rule::requiredIf(in_array($this->status, ['warning', 'needs_repair'], true))],
        ]);

        $mould = Mould::findOrFail($this->mouldId);
        $mould->update([
            'status' => $validated['status'],
            'problem_description' => in_array($validated['status'], ['warning', 'needs_repair'], true)
                ? $validated['problemDescription']
                : null,
        ]);

        if ($validated['status'] === 'needs_repair') {
            $this->pauseOrdersFor('mould_id', $mould->id, $validated['problemDescription'] ?: $this->t('mould_paused_reason'));
        }

        $this->showMouldModal = false;
        $this->flashMessage = $this->t('mould_status_updated');
        unset($this->moulds, $this->machines);
    }

    public function openOrderModal(int $orderId): void
    {
        $this->ensureAuthenticated();
        $order = Order::findOrFail($orderId);
        $this->orderId = $order->id;
        $this->orderStatus = $order->status;
        $this->currentQuantity = $order->current_quantity;
        $this->goodQuantity = $order->good_quantity;
        $this->badQuantity = $order->bad_quantity;
        $this->resetValidation();
        $this->showOrderModal = true;
    }

    public function saveOrder(): void
    {
        $this->ensureAuthenticated();
        $validated = $this->validate([
            'orderStatus' => ['required', Rule::in(['in_progress', 'paused', 'completed', 'interrupted'])],
            'currentQuantity' => ['required', 'integer', 'min:0'],
            'goodQuantity' => ['required_if:orderStatus,completed,interrupted', 'integer', 'min:0'],
            'badQuantity' => ['required_if:orderStatus,completed,interrupted', 'integer', 'min:0'],
        ]);

        $order = Order::findOrFail($this->orderId);
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

        $order->update($updates);
        $this->appendProtocolEntry($order->refresh(), $quantity, 'order_status', $this->t('order_status_note', ['status' => $this->statusLabel($validated['orderStatus'])]));

        $this->showOrderModal = false;
        $this->flashMessage = $this->t('order_status_updated');
        unset($this->machines, $this->waitingOrders);
    }

    public function activateOrder(int $orderId): void
    {
        $this->ensureAuthenticated();
        $order = Order::findOrFail($orderId);
        $order->update(['status' => 'in_progress', 'started_at' => $order->started_at ?? now(), 'last_paused_at' => null]);
        $this->appendProtocolEntry($order->refresh(), $order->current_quantity, 'order_status', $this->t('order_activated_note'));
        $this->flashMessage = $this->t('order_activated');
        unset($this->machines, $this->waitingOrders);
    }

    public function openQualityModal(int $orderId): void
    {
        $this->ensureAuthenticated();
        $order = Order::findOrFail($orderId);
        $this->qualityOrderId = $order->id;
        $this->currentQuantity = $order->current_quantity;
        $this->qualityStatus = 'good';
        $this->qualityProblemType = 'surface_defect';
        $this->qualityProblemNote = '';
        $this->resetValidation();
        $this->showQualityModal = true;
    }

    public function saveQualityCheck(): void
    {
        $this->ensureAuthenticated();
        $validated = $this->validate([
            'qualityStatus' => ['required', Rule::in(['good', 'warning', 'quality_issue'])],
            'currentQuantity' => ['required', 'integer', 'min:0'],
            'qualityProblemType' => ['required_unless:qualityStatus,good', 'nullable', 'string', 'max:100'],
            'qualityProblemNote' => ['nullable', 'string', 'max:1000', Rule::requiredIf($this->qualityStatus !== 'good')],
        ]);

        $order = Order::findOrFail($this->qualityOrderId);
        $quantity = min($validated['currentQuantity'], $order->quantity);
        $protocol = $order->production_protocol ?? [];
        $checks = $protocol['quality_checks'] ?? [];
        $checks[] = [
            'checked_at' => now()->toIso8601String(),
            'quality_status' => $validated['qualityStatus'],
            'current_quantity' => $quantity,
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
        $this->appendProtocolEntry($order->refresh(), $quantity, 'quality_check', $this->t('quality_check_note', ['status' => $this->statusLabel($validated['qualityStatus'])]));

        $this->showQualityModal = false;
        $this->flashMessage = $followUpStatus === 'stopped'
            ? $this->t('quality_issue_stopped')
            : $this->t('quality_check_saved');
        unset($this->machines);
    }

    public function statusClass(string $status): string
    {
        return match ($status) {
            'active', 'in_progress', 'good' => 'bg-emerald-100 text-emerald-800',
            'warning' => 'bg-amber-100 text-amber-800',
            'broken', 'needs_repair', 'quality_issue', 'interrupted' => 'bg-red-100 text-red-800',
            'under_repair' => 'bg-blue-100 text-blue-800',
            default => 'bg-slate-200 text-slate-700',
        };
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'active' => $this->t('active'),
            'stopped' => $this->t('stopped'),
            'standby' => $this->t('standby'),
            'warning' => $this->t('warning'),
            'broken' => $this->t('broken'),
            'needs_repair' => $this->t('needs_repair'),
            'under_repair' => $this->t('under_repair'),
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

    private function appendProtocolEntry(Order $order, int $quantity, string $type, string $note): void
    {
        $protocol = $order->production_protocol ?? [];
        $entries = $protocol['entries'] ?? [];
        $entries[] = [
            'timestamp' => Carbon::now()->toIso8601String(),
            'entry_type' => $type,
            'current_quantity' => max(0, min($quantity, $order->quantity)),
            'note' => $note,
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
                <div class="mt-4 text-center">
                    <select wire:change="selectLanguage($event.target.value)" class="rounded-md border-slate-300 py-1 text-xs">
                        <option value="hu" @selected($locale === 'hu')>{{ $this->t('hungarian') }}</option>
                        <option value="de" @selected($locale === 'de')>{{ $this->t('german') }}</option>
                    </select>
                </div>
            </form>
        </div>
    @else
    <header class="sticky top-0 z-40 border-b-4 border-teal-600 bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-2 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="{{ asset('logo.png') }}" alt="Periplast" class="h-10 w-auto sm:h-12">
                <span class="hidden text-sm font-semibold text-slate-500 sm:inline">{{ $this->t('live_overview') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <label class="sr-only" for="locale">{{ $this->t('language') }}</label>
                <select id="locale" wire:change="selectLanguage($event.target.value)" class="rounded-md border-slate-300 py-1 text-xs font-semibold text-slate-700 focus:border-teal-600 focus:ring-teal-600">
                    <option value="hu" @selected($locale === 'hu')>{{ $this->t('hungarian') }}</option>
                    <option value="de" @selected($locale === 'de')>{{ $this->t('german') }}</option>
                </select>
                <span class="hidden text-sm font-medium text-slate-600 md:inline">{{ auth()->user()->name }}</span>
                <button wire:click="logout" class="rounded-md bg-slate-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800">{{ $this->t('logout') }}</button>
            </div>
        </div>
        <nav class="border-t border-slate-100" aria-label="Main">
            <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-2 [scrollbar-width:none] sm:px-4">
                @foreach (['machines' => 'machines', 'moulds' => 'moulds', 'products' => 'products', 'orders' => 'orders'] as $tab => $label)
                    <button wire:click="selectTab('{{ $tab }}')" @class(['shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition', 'border-teal-600 text-teal-700' => $activeTab === $tab, 'border-transparent text-slate-600 hover:text-teal-700' => $activeTab !== $tab])>
                        {{ $this->t($label) }}
                    </button>
                @endforeach
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6">
        @if ($flashMessage)
            <div class="mb-5 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                {{ $flashMessage }}
                <button wire:click="$set('flashMessage', '')" class="font-bold" aria-label="{{ $this->t('cancel') }}">&times;</button>
            </div>
        @endif

        @if ($activeTab === 'machines')
            <section>
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900">{{ $this->t('machines') }}</h1>
                        <p class="mt-1 text-sm text-slate-600">{{ $this->t('dashboard_subtitle') }}</p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-sm font-semibold text-teal-800">{{ $this->t('machine_count', ['count' => $this->machines->count()]) }}</span>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    @forelse ($this->machines as $machine)
                        @php($order = $machine->orders->first())
                        <article wire:key="machine-{{ $machine->id }}" wire:click="openMachineDetail({{ $machine->id }})" wire:keydown.enter="openMachineDetail({{ $machine->id }})" role="button" tabindex="0" title="{{ $this->t('open_details') }}" class="cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-teal-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-600">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900">{{ $machine->name }}</h2>
                                    <p class="text-sm text-slate-500">{{ $machine->type }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($order?->status ?? 'stopped')])>{{ $order?->status === 'in_progress' ? $this->t('active') : ($order?->status === 'paused' ? $this->t('stopped') : $this->t('inactive')) }}</span>
                                    <button wire:click.stop="openMachineModal({{ $machine->id }})" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-teal-700" title="{{ $this->t('change_status') }}">⚙</button>
                                </div>
                            </div>
                            <div class="p-5">
                                <dl class="grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-slate-500">{{ $this->t('opening') }}</dt><dd class="font-semibold">{{ $machine->open_mm ?? '-' }} mm</dd></div>
                                    <div><dt class="text-slate-500">{{ $this->t('screw_diameter') }}</dt><dd class="font-semibold">{{ $machine->screw_diameter_cm ?? '-' }} cm</dd></div>
                                </dl>
                                @if ($machine->problem_description)
                                    <p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700"><strong>{{ $this->t('problem') }}:</strong> {{ $machine->problem_description }}</p>
                                @endif
                                @if ($order)
                                    <div class="mt-5 rounded-lg border border-teal-100 bg-teal-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-teal-950">{{ $this->t('current_order') }}</p>
                                                <p class="text-sm text-teal-800">{{ $order->order_number }} · {{ $order->product?->name ?? $this->t('no_products') }}</p>
                                            </div>
                                            <button wire:click.stop="openOrderModal({{ $order->id }})" class="rounded-md bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800">{{ $this->t('manage') }}</button>
                                        </div>
                                        <div class="mt-4 flex justify-between text-sm"><span>{{ $this->t('current_quantity') }}</span><strong>{{ $order->current_quantity }} / {{ $order->quantity }} db</strong></div>
                                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-teal-100"><div class="h-full rounded-full bg-teal-600" style="width: {{ $this->progress($order) }}%"></div></div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span wire:key="quality-status-{{ $order->id }}-{{ $this->qualityStatus($order) }}" @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($order))])>
                                                {{ $this->t('quality') }}: {{ $this->statusLabel($this->qualityStatus($order)) }}
                                            </span>
                                            <button wire:click.stop="openQualityModal({{ $order->id }})" class="rounded-md border border-teal-300 bg-white px-3 py-1 text-xs font-bold text-teal-800 hover:bg-teal-100">{{ $this->t('quality_check') }}</button>
                                        </div>
                                    </div>
                                @else
                                    <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-500">{{ $this->t('no_running_order') }}</p>
                                @endif
                            </div>
                        </article>
                    @empty
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 lg:col-span-2">{{ $this->t('no_machines') }}</p>
                    @endforelse
                </div>

            </section>
        @elseif ($activeTab === 'orders')
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900">{{ $this->t('orders') }}</h1>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold">{{ $this->t('waiting_orders') }}</h2>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        @forelse ($this->waitingOrders as $order)
                            <div wire:key="waiting-order-{{ $order->id }}" class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-4">
                                <div><p class="font-semibold">{{ $order->order_number }}</p><p class="text-sm text-slate-500">{{ $order->machine?->name }} · {{ $order->product?->name }}</p></div>
                                <button wire:click="activateOrder({{ $order->id }})" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('activate') }}</button>
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
                            <button wire:click="openMouldModal({{ $mould->id }})" class="mt-5 w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('change_status') }}</button>
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

    @if ($detailMachineId && ($detail = $this->detailMachine))
        @php($detailOrder = $detail->orders->first())
        @php($detailMould = $detailOrder?->mould)
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
                    <section class="rounded-lg border border-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-slate-900">{{ $this->t('machine_details') }}</h3>
                            <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($detail->status)])>{{ $this->statusLabel($detail->status) }}</span>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                            <div><dt class="text-slate-500">{{ $this->t('type') }}</dt><dd class="font-semibold">{{ $detail->type }}</dd></div>
                            <div><dt class="text-slate-500">{{ $this->t('opening') }}</dt><dd class="font-semibold">{{ $detail->open_mm ?? '-' }} mm</dd></div>
                            <div><dt class="text-slate-500">{{ $this->t('screw_diameter') }}</dt><dd class="font-semibold">{{ $detail->screw_diameter_cm ?? '-' }} cm</dd></div>
                        </dl>
                        @if ($detail->problem_description)
                            <p class="mt-3 rounded-md bg-red-50 p-3 text-sm text-red-700"><strong>{{ $this->t('problem') }}:</strong> {{ $detail->problem_description }}</p>
                        @endif
                        <button wire:click="openMachineModal({{ $detail->id }})" class="mt-4 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('change_machine_status') }}</button>
                    </section>

                    <section class="rounded-lg border border-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-slate-900">{{ $this->t('mounted_mould') }}</h3>
                            @if ($detailMould)
                                <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($detailMould->status)])>{{ $this->statusLabel($detailMould->status) }}</span>
                            @endif
                        </div>
                        @if ($detailMould)
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                                <div class="col-span-2"><dt class="text-slate-500">{{ $this->t('moulds') }}</dt><dd class="font-semibold">{{ $detailMould->name }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('number') }}</dt><dd class="font-semibold">{{ $detailMould->number ?? '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('cavity_count') }}</dt><dd class="font-semibold">{{ $detailMould->cavity_count ?? '-' }}</dd></div>
                            </dl>
                            @if ($detailMould->problem_description)
                                <p class="mt-3 rounded-md bg-red-50 p-3 text-sm text-red-700"><strong>{{ $this->t('problem') }}:</strong> {{ $detailMould->problem_description }}</p>
                            @endif
                            <button wire:click="openMouldModal({{ $detailMould->id }})" class="mt-4 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('change_mould_status') }}</button>
                        @else
                            <p class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-500">{{ $this->t('no_mounted_mould') }}</p>
                        @endif
                        @if ($detail->moulds->isNotEmpty())
                            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $this->t('compatible_moulds') }}</p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($detail->moulds as $compatible)
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700">{{ $compatible->name }}</span>
                                @endforeach
                            </div>
                        @endif
                    </section>

                    <section class="rounded-lg border border-teal-100 bg-teal-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-teal-950">{{ $this->t('order_details') }}</h3>
                            @if ($detailOrder)
                                <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($detailOrder->status)])>{{ $this->statusLabel($detailOrder->status) }}</span>
                            @endif
                        </div>
                        @if ($detailOrder)
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                                <div><dt class="text-slate-500">{{ $this->t('order_number') }}</dt><dd class="font-semibold">{{ $detailOrder->order_number }}</dd></div>
                                <div class="col-span-2"><dt class="text-slate-500">{{ $this->t('product') }}</dt><dd class="font-semibold">{{ $detailOrder->product?->name ?? '-' }} <span class="font-normal text-slate-500">{{ $detailOrder->product?->code }}</span></dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('required_quantity') }}</dt><dd class="font-semibold">{{ number_format($detailOrder->quantity, 0, ',', ' ') }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('planned_cycle') }}</dt><dd class="font-semibold">{{ $detailOrder->product?->planned_cycle_time ? number_format($detailOrder->product->planned_cycle_time / 1000, 1, ',', ' ').' s' : '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('actual_cycle') }}</dt><dd class="font-semibold">{{ $detailOrder->cycle_time ? number_format($detailOrder->cycle_time / 1000, 1, ',', ' ').' s' : '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('net_weight') }}</dt><dd class="font-semibold">{{ $detailOrder->net_weight !== null ? $detailOrder->net_weight.' g' : '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('gross_weight') }}</dt><dd class="font-semibold">{{ $detailOrder->gross_weight !== null ? $detailOrder->gross_weight.' g' : '-' }}</dd></div>
                                <div><dt class="text-slate-500">{{ $this->t('started_at') }}</dt><dd class="font-semibold">{{ $detailOrder->started_at?->format('Y-m-d H:i') ?? '-' }}</dd></div>
                            </dl>
                            <div class="mt-4 flex justify-between text-sm"><span>{{ $this->t('current_quantity') }}</span><strong>{{ $detailOrder->current_quantity }} / {{ $detailOrder->quantity }} db</strong></div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-teal-100"><div class="h-full rounded-full bg-teal-600" style="width: {{ $this->progress($detailOrder) }}%"></div></div>
                            <div class="mt-3">
                                <span @class(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($detailOrder))])>{{ $this->t('quality') }}: {{ $this->statusLabel($this->qualityStatus($detailOrder)) }}</span>
                            </div>
                            @if ($detailOrder->pause_description && $detailOrder->status === 'paused')
                                <p class="mt-3 rounded-md bg-amber-50 p-3 text-sm text-amber-800"><strong>{{ $this->t('pause_reason') }}:</strong> {{ $detailOrder->pause_description }}</p>
                            @endif
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button wire:click="openOrderModal({{ $detailOrder->id }})" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800">{{ $this->t('manage_order') }}</button>
                                <button wire:click="openQualityModal({{ $detailOrder->id }})" class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100">{{ $this->t('quality_check') }}</button>
                            </div>
                        @else
                            <p class="mt-3 rounded-lg bg-white p-3 text-sm text-slate-500">{{ $this->t('no_running_order') }}</p>
                        @endif
                    </section>
                </div>
            </div>
        </div>
    @endif

    @if ($showMachineModal || $showMouldModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6">
                <h2 class="text-xl font-bold">{{ $showMachineModal ? $this->t('machines').' '.$this->t('change_status') : $this->t('moulds').' '.$this->t('change_status') }}</h2>
                <div class="mt-5 space-y-4">
                    <label class="block text-sm font-semibold">{{ $this->t('new_status') }}<select wire:model.live="status" class="mt-1 w-full rounded-md border-slate-300"><option value="active">{{ $this->t('active') }}</option>@if ($showMachineModal)<option value="stopped">{{ $this->t('stopped') }}</option><option value="warning">{{ $this->t('warning') }}</option><option value="broken">{{ $this->t('broken') }}</option>@else<option value="standby">{{ $this->t('standby') }}</option><option value="warning">{{ $this->t('warning') }}</option><option value="needs_repair">{{ $this->t('needs_repair') }}</option><option value="under_repair">{{ $this->t('under_repair') }}</option>@endif</select></label>
                    @if (in_array($status, ['warning', 'broken', 'needs_repair'], true))<label class="block text-sm font-semibold">{{ $this->t('problem_description') }}<textarea wire:model="problemDescription" rows="3" class="mt-1 w-full rounded-md border-slate-300"></textarea>@error('problemDescription')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label>@endif
                </div>
                <div class="mt-6 flex justify-end gap-3"><button wire:click="$set('{{ $showMachineModal ? 'showMachineModal' : 'showMouldModal' }}', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600">{{ $this->t('cancel') }}</button><button wire:click="{{ $showMachineModal ? 'saveMachineStatus' : 'saveMouldStatus' }}" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('save') }}</button></div>
            </div>
        </div>
    @endif

    @if ($showOrderModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold">{{ $this->t('manage_order') }}</h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold">{{ $this->t('status') }}<select wire:model.live="orderStatus" class="mt-1 w-full rounded-md border-slate-300"><option value="in_progress">{{ $this->t('in_progress') }}</option><option value="paused">{{ $this->t('paused') }}</option><option value="completed">{{ $this->t('completed') }}</option><option value="interrupted">{{ $this->t('interrupted') }}</option></select></label><label class="text-sm font-semibold">{{ $this->t('current_quantity') }}<input wire:model="currentQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label>@if(in_array($orderStatus, ['completed', 'interrupted'], true))<label class="text-sm font-semibold">{{ $this->t('good_quantity') }}<input wire:model="goodQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><label class="text-sm font-semibold">{{ $this->t('bad_quantity') }}<input wire:model="badQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label>@endif</div><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('showOrderModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600">{{ $this->t('cancel') }}</button><button wire:click="saveOrder" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('save') }}</button></div></div></div>
    @endif

    @if ($showQualityModal)
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold">{{ $this->t('record_quality_check') }}</h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold">{{ $this->t('result') }}<select wire:model.live="qualityStatus" class="mt-1 w-full rounded-md border-slate-300"><option value="good">{{ $this->t('good') }}</option><option value="warning">{{ $this->t('warning') }}</option><option value="quality_issue">{{ $this->t('quality_issue') }}</option></select></label><label class="text-sm font-semibold">{{ $this->t('current_quantity') }}<input wire:model="currentQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label>@if($qualityStatus !== 'good')<div wire:key="quality-problem-fields-{{ $qualityStatus }}" class="grid gap-4"><label class="text-sm font-semibold">{{ $this->t('problem_type') }}<select wire:model="qualityProblemType" class="mt-1 w-full rounded-md border-slate-300"><option value="surface_defect">{{ $this->t('surface_defect') }}</option><option value="dimension_issue">{{ $this->t('dimension_issue') }}</option><option value="color_issue">{{ $this->t('color_issue') }}</option><option value="material_issue">{{ $this->t('material_issue') }}</option><option value="burn_mark">{{ $this->t('burn_mark') }}</option><option value="flash">{{ $this->t('flash') }}</option><option value="short_shot">{{ $this->t('short_shot') }}</option><option value="contamination">{{ $this->t('contamination') }}</option><option value="other">{{ $this->t('other') }}</option></select></label><label class="text-sm font-semibold">{{ $this->t('problem_reason') }}<textarea wire:model="qualityProblemNote" rows="3" placeholder="{{ $this->t('problem_reason_placeholder') }}" class="mt-1 w-full rounded-md border-slate-300"></textarea>@error('qualityProblemNote')<span class="text-xs text-red-600">{{ $message }}</span>@enderror</label></div>@endif</div><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('showQualityModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600">{{ $this->t('cancel') }}</button><button wire:click="saveQualityCheck" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white">{{ $this->t('record') }}</button></div></div></div>
    @endif
    @endguest
</div>