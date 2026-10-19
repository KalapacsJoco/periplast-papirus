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
?>

<div class="min-h-screen bg-slate-100 text-slate-700">
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->guard()->guest()): ?>
        <div class="flex min-h-screen items-center justify-center p-4">
            <form wire:submit="login" class="w-full max-w-sm rounded-xl border-t-4 border-teal-600 bg-white p-6 shadow-lg sm:p-8">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Periplast" class="mx-auto h-24 w-auto">
                <h1 class="mt-3 text-center text-sm text-slate-500"><?php echo e($this->t('login_title')); ?></h1>
                <div class="mt-6 space-y-4">
                    <label class="block text-sm font-semibold"><?php echo e($this->t('email')); ?>

                        <input wire:model="email" type="email" autocomplete="username" autofocus class="mt-1 w-full rounded-md border-slate-300">
                    </label>
                    <label class="block text-sm font-semibold"><?php echo e($this->t('password')); ?>

                        <input wire:model="password" type="password" autocomplete="current-password" class="mt-1 w-full rounded-md border-slate-300">
                    </label>
                    <label class="flex items-center gap-2 text-sm"><input wire:model="remember" type="checkbox" class="rounded border-slate-300"> <?php echo e($this->t('remember_me')); ?></label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-sm text-red-600"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <button type="submit" wire:loading.attr="disabled" class="mt-6 w-full rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('login')); ?></button>
                <div class="mt-4 flex justify-center">
                    <?php echo $__env->make('partials.language-switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </form>
        </div>
    <?php else: ?>
    <header x-data="{ menuOpen: false }" class="sticky top-0 z-40 border-b-4 border-teal-600 bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-2 sm:px-6">
            <div class="flex items-center gap-3">
                <button type="button" x-on:click="menuOpen = !menuOpen" x-bind:aria-expanded="menuOpen" aria-label="Menu" class="relative rounded-md border border-slate-300 p-2 text-slate-700 hover:bg-slate-100 md:hidden">
                    <svg x-show="! menuOpen" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="menuOpen" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unreadNotificationCount > 0): ?>
                        <span class="absolute -right-1.5 -top-1.5 h-3 w-3 rounded-full bg-red-600 ring-2 ring-white"></span>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </button>
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Periplast" class="h-10 w-auto sm:h-12">
                <span class="hidden text-sm font-semibold text-slate-500 lg:inline"><?php echo e($this->t('live_overview')); ?></span>
            </div>
            <div class="flex items-center gap-3">
                <?php echo $__env->make('partials.language-switcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                <span class="hidden text-sm font-medium text-slate-600 md:inline"><?php echo e(auth()->user()->name); ?></span>
                <button wire:click="logout" class="rounded-md bg-slate-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800"><?php echo e($this->t('logout')); ?></button>
            </div>
        </div>
        <nav wire:poll.15s class="border-t border-slate-100" aria-label="Main">
            <div x-bind:class="menuOpen ? 'flex' : 'hidden md:flex'" class="mx-auto max-w-7xl flex-col gap-1 px-2 py-2 md:flex-row md:overflow-x-auto md:py-0 sm:px-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['machines' => 'machines', 'tasks' => 'tasks', 'orders' => 'orders', 'notifications' => 'notifications', 'maintenance' => 'maintenance', 'calculators' => 'calculators', 'moulds' => 'moulds', 'products' => 'products']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canViewTab($tab)): ?>
                    <button wire:click="selectTab('<?php echo e($tab); ?>')" x-on:click="menuOpen = false" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['shrink-0 whitespace-nowrap rounded-md px-4 py-3 text-left text-sm font-semibold transition md:rounded-none md:border-b-2', 'bg-teal-50 text-teal-700 md:border-teal-600 md:bg-transparent' => $activeTab === $tab, 'text-slate-600 hover:text-teal-700 md:border-transparent' => $activeTab !== $tab]); ?>">
                        <?php echo e($this->t($label)); ?>

                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($tab === 'notifications' && $this->unreadNotificationCount > 0): ?>
                            <span class="ml-1 inline-flex min-w-5 items-center justify-center rounded-full bg-red-600 px-1.5 py-0.5 text-xs font-bold text-white"><?php echo e($this->unreadNotificationCount); ?></span>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="<?php echo \Illuminate\Support\Arr::toCssClasses([
        'mx-auto w-full max-w-none px-3 py-5 sm:px-5 sm:py-6' => $activeTab === 'machines',
        'mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6' => $activeTab !== 'machines',
    ]); ?>">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($flashMessage): ?>
            <div class="mb-5 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <?php echo e($flashMessage); ?>

                <button wire:click="$set('flashMessage', '')" class="font-bold" aria-label="<?php echo e($this->t('cancel')); ?>">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'machines'): ?>
            <section wire:poll.60s>
                <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-bold text-slate-900"><?php echo e($this->t('machines')); ?></h1>
                    <select wire:model.live="machineFilter" aria-label="<?php echo e($this->t('machine_status_label')); ?>" class="rounded-md border-slate-300 bg-white px-3 py-2 text-sm">
                        <option value="all"><?php echo e($this->t('all_machines')); ?></option>
                        <option value="active"><?php echo e($this->t('active_machines')); ?></option>
                        <option value="warning"><?php echo e($this->t('warning_machines')); ?></option>
                        <option value="stopped"><?php echo e($this->t('stopped_machines')); ?></option>
                    </select>
                </div>

                <div class="hidden xl:block">
                    <table class="w-full table-auto border-separate border-spacing-y-1.5 text-left text-[11px] leading-tight [&_td]:break-words [&_td]:px-1.5 [&_th]:px-1.5 [&_th]:align-bottom [&_th]:text-[10px]">
                        <thead class="text-slate-700">
                            <tr>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['machine_number', 'type', 'running_product', 'gross_weight', 'net_weight', 'order_number', 'product_code', 'required_quantity', 'current_quantity', 'yesterday_quantity_same_time', 'cavity_count', 'actual_cycle_time', 'database_cycle_time', 'utilization_last_24_hours', 'theoretical_daily_production', 'actual_production_last_24_hours', 'color_percentage', 'daily_color_consumption', 'next_activated_order', 'waiting_orders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $column): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <th class="<?php echo \Illuminate\Support\Arr::toCssClasses(['bg-slate-200 py-2', 'rounded-l-md' => $loop->first, 'rounded-r-md' => $loop->last]); ?>" title="<?php echo e($this->t($column)); ?>"><?php echo e($this->t(in_array($column, ['type', 'gross_weight', 'net_weight'], true) ? $column : 'col_'.$column)); ?></th>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </tr>
                        </thead>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->machines->filter(fn ($machine) => $this->machineMatchesFilter($machine, $machineFilter)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <?php ($metrics = $machine->productionMetrics()); ?>
                            <?php ($order = $metrics->currentOrder()); ?>
                            <?php ($nextOrder = $metrics->nextActivatedOrder()); ?>
                            <?php ($tone = $machine->status === 'stopped' ? 'red' : ($this->machineHasWarning($machine) ? 'amber' : ($machine->status === 'active' ? 'emerald' : 'slate'))); ?>
                            <tbody <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'machine-'.e($machine->id).''; ?>wire:key="machine-<?php echo e($machine->id); ?>">
                                <tr class="<?php echo \Illuminate\Support\Arr::toCssClasses([
                                    'group cursor-pointer align-middle text-slate-800 transition-colors focus:outline-none [&>td:first-child]:border-l-4 [&>td]:transition-colors [&:hover>td]:border-teal-500 [&:hover>td]:bg-teal-100 [&:hover>td:first-child]:border-l-teal-600 [&:focus-visible>td]:border-teal-500 [&:focus-visible>td]:bg-teal-100',
                                    '[&>td]:bg-emerald-50 [&>td]:border-emerald-200 [&>td:first-child]:border-l-emerald-500' => $tone === 'emerald',
                                    '[&>td]:bg-amber-50 [&>td]:border-amber-200 [&>td:first-child]:border-l-amber-500' => $tone === 'amber',
                                    '[&>td]:bg-red-50 [&>td]:border-red-200 [&>td:first-child]:border-l-red-500' => $tone === 'red',
                                    '[&>td:first-child]:border-l-slate-400' => $tone === 'slate',
                                ]); ?>" wire:click="openMachineDetail(<?php echo e($machine->id); ?>)" wire:keydown.enter="openMachineDetail(<?php echo e($machine->id); ?>)" role="button" tabindex="0" title="<?php echo e($this->t('open_details')); ?>">
                                    <td class="rounded-l-md border-y border-l border-slate-200 bg-white px-2 py-2 font-bold text-slate-900 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($machine->name); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($machine->type); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order && $this->qualityCheckOverdue($order)): ?><span class="mr-1 inline-flex h-4 w-4 items-center justify-center rounded-full bg-amber-400 text-[10px] font-black text-white" title="<?php echo e($this->t('quality_check_overdue')); ?>" aria-label="<?php echo e($this->t('quality_check_overdue')); ?>">!</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php echo e($order?->product?->name ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->gross_weight ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->net_weight ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->order_number ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->product?->code ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order ? number_format($order->quantity, 0, ',', ' ') : '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order ? number_format($order->current_quantity, 0, ',', ' ') : '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($metrics->quantityAtYesterdaySameTime($order) ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->mould?->cavity_count ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->cycle_time === null ? '-' : number_format($order->cycle_time / 1000, 2, ',', ' ')); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->product?->planned_cycle_time === null ? '-' : number_format($order->product->planned_cycle_time / 1000, 2, ',', ' ')); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e(($utilization = $metrics->utilizationLast24Hours($order)) === null ? '-' : number_format($utilization, 1, ',', ' ').'%'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($metrics->theoreticalDailyProduction($order) ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($metrics->producedLast24Hours() ?? '-'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e($order?->color_percentage === null ? '-' : number_format((float) $order->color_percentage, 2, ',', ' ').'%'); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50"><?php echo e(($colorConsumption = $metrics->dailyColorConsumptionKg($order)) === null ? '-' : number_format($colorConsumption, 3, ',', ' ')); ?></td>
                                    <td class="border-y border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($nextOrder): ?>
                                            <span class="block font-semibold"><?php echo e($nextOrder->order_number); ?></span>
                                            <span class="block"><?php echo e($nextOrder->product?->code ?? $nextOrder->product?->name); ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                    <td class="rounded-r-md border-y border-r border-slate-200 bg-white px-2 py-2 group-hover:border-teal-500 group-hover:bg-teal-50">
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_2 = true; $__currentLoopData = $metrics->waitingOrders(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $waitingOrder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                            <span class="block"><?php echo e($waitingOrder->order_number); ?> · <?php echo e($waitingOrder->product?->code ?? $waitingOrder->product?->name); ?></span>
                                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                            -
                                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                    </td>
                                </tr>
                            </tbody>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <tbody>
                                <tr>
                                    <td colspan="20" class="rounded-md bg-white p-8 text-center text-slate-500"><?php echo e($this->t('no_machines')); ?></td>
                                </tr>
                            </tbody>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </table>
                </div>

                <div class="grid gap-4 sm:grid-cols-2 xl:hidden">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->machines->filter(fn ($machine) => $this->machineMatchesFilter($machine, $machineFilter)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php ($order = $machine->orders->first()); ?>
                        <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'machine-card-'.e($machine->id).''; ?>wire:key="machine-card-<?php echo e($machine->id); ?>" wire:click="openMachineDetail(<?php echo e($machine->id); ?>)" wire:keydown.enter="openMachineDetail(<?php echo e($machine->id); ?>)" role="button" tabindex="0" title="<?php echo e($this->t('open_details')); ?>" class="cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-teal-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-600">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900"><?php echo e($machine->name); ?></h2>
                                    <p class="text-sm text-slate-500"><?php echo e($machine->type); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($machine->status)]); ?>"><?php echo e($this->statusLabel($machine->status)); ?></span>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::START_MACHINE) || $this->canDo(\App\Enums\Role::STOP_MACHINE) || $this->canDo(\App\Enums\Role::REPORT_EQUIPMENT_WARNING)): ?>
                                        <button wire:click.stop="openMachineModal(<?php echo e($machine->id); ?>)" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-teal-700" title="<?php echo e($this->t('change_status')); ?>">⚙</button>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                            </div>
                            <div class="p-5">
                                <dl class="grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-slate-500"><?php echo e($this->t('machine_status_label')); ?></dt><dd class="mt-0.5"><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-block rounded-full px-2.5 py-0.5 text-xs font-bold', $this->statusClass($machine->status)]); ?>"><?php echo e($machine->status === 'active' ? $this->t('good') : $this->statusLabel($machine->status)); ?></span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($machine->problem_description && $machine->status !== 'active'): ?><p class="mt-1 break-words text-xs text-slate-700"><?php echo e($machine->problem_description); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></dd></div>
                                    <div><dt class="text-slate-500"><?php echo e($this->t('mould_status_label')); ?></dt><dd class="mt-0.5"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order?->mould): ?><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['inline-block rounded-full px-2.5 py-0.5 text-xs font-bold', $this->statusClass($order->mould->status)]); ?>"><?php echo e($order->mould->status === 'active' ? $this->t('good') : $this->statusLabel($order->mould->status)); ?></span><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->mould->problem_description && $order->mould->status !== 'active'): ?><p class="mt-1 break-words text-xs text-slate-700"><?php echo e($order->mould->problem_description); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php else: ?><span class="font-semibold">-</span><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></dd></div>
                                </dl>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order): ?>
                                    <div class="mt-5 rounded-lg border border-teal-100 bg-teal-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-teal-950"><?php echo e($this->t('current_order')); ?></p>
                                                <p class="text-sm text-teal-800"><?php echo e($order->order_number); ?> · <?php echo e($order->product?->name ?? $this->t('no_products')); ?></p>
                                            </div>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageOrders()): ?>
                                                <button wire:click.stop="openOrderModal(<?php echo e($order->id); ?>)" class="rounded-md bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800"><?php echo e($this->t('manage')); ?></button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                        </div>
                                        <div class="mt-4 flex justify-between text-sm"><span><?php echo e($this->t('current_quantity')); ?></span><strong><?php echo e($order->current_quantity); ?> / <?php echo e($order->quantity); ?> db</strong></div>
                                        <?php echo $__env->make('partials.production-progress-bar', ['order' => $order], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($order))]); ?>">
                                                <?php echo e($this->t('quality')); ?>: <?php echo e($this->statusLabel($this->qualityStatus($order))); ?>

                                            </span>
                                            <?php ($lastQualityCheck = $this->lastQualityCheckAt($order)); ?>
                                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', 'bg-red-100 text-red-800' => $this->qualityCheckOverdue($order), 'bg-slate-100 text-slate-700' => ! $this->qualityCheckOverdue($order)]); ?>">
                                                <?php echo e($lastQualityCheck ? $this->t('last_quality_check_at', ['time' => $lastQualityCheck->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s')]) : $this->t('no_quality_check_yet')); ?>

                                            </span>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->qualityCheckOverdue($order)): ?>
                                                <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-bold text-red-800"><?php echo e($this->t('quality_check_overdue')); ?></span>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCheckQuality()): ?>
                                                <button wire:click.stop="openQualityModal(<?php echo e($order->id); ?>)" class="rounded-md border border-teal-300 bg-white px-3 py-1 text-xs font-bold text-teal-800 hover:bg-teal-100"><?php echo e($this->t($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY) ? 'color_check' : 'quality_check')); ?></button>
                                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                            <button wire:click.stop="openProductionProtocol(<?php echo e($order->id); ?>)" class="rounded-md border border-slate-300 bg-white px-3 py-1 text-xs font-bold text-slate-700 hover:bg-slate-100"><?php echo e($this->t('production_protocol')); ?></button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-500"><?php echo e($this->t('no_running_order')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 sm:col-span-2"><?php echo e($this->t('no_machines')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

            </section>
        <?php elseif($activeTab === 'notifications'): ?>
            <section wire:poll.15s class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h1 class="text-2xl font-bold text-slate-900"><?php echo e($this->t('notifications')); ?></h1>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->unreadNotificationCount > 0): ?>
                        <button wire:click="markNotificationsRead" class="text-sm font-semibold text-teal-700 hover:text-teal-900"><?php echo e($this->t('mark_notifications_read')); ?></button>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <ul class="mt-4 space-y-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->notifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notification): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <li class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-md px-3 py-2 text-sm', 'bg-teal-50 text-teal-900' => ! $notification->read_at, 'bg-slate-50 text-slate-600' => $notification->read_at]); ?>">
                            <?php echo e($this->notificationText($notification->data)); ?>

                            <span class="ml-2 text-xs text-slate-400"><?php echo e($notification->created_at->diffForHumans()); ?></span>
                        </li>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <li class="text-sm text-slate-500"><?php echo e($this->t('no_notifications')); ?></li>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </ul>
            </section>
        <?php elseif($activeTab === 'maintenance'): ?>
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h1 class="text-2xl font-bold text-slate-900"><?php echo e($this->t('maintenance')); ?></h1>
                <p class="mt-4 text-sm text-slate-500"><?php echo e($this->t('no_maintenance_yet')); ?></p>
            </section>
        <?php elseif($activeTab === 'calculators'): ?>
            <section class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <h1 class="mb-5 text-2xl font-bold text-slate-900"><?php echo e($this->t('calculators')); ?></h1>
                <button wire:click="openCalculator" class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-left text-sm font-bold text-slate-800 hover:border-teal-400 hover:bg-teal-50"><?php echo e($this->t('production_time_calculator')); ?></button>
            </section>
        <?php elseif($activeTab === 'tasks'): ?>
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900"><?php echo e($this->t('tasks')); ?></h1>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::ASSIGN_ORDER)): ?>
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-bold"><?php echo e($this->t('assignments')); ?></h2>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->ordersForAssignment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'assignment-'.e($order->id).''; ?>wire:key="assignment-<?php echo e($order->id); ?>" class="rounded-lg bg-slate-50 p-4">
                                    <p class="font-semibold"><?php echo e($order->order_number); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo e($order->machine?->name); ?> · <?php echo e($order->product?->name); ?></p>
                                    <p class="mt-1 text-sm"><?php echo e($this->t('status')); ?>: <?php echo e($this->statusLabel($order->status)); ?></p>
                                    <label class="mt-3 block text-sm font-semibold"><?php echo e($this->t('operator')); ?>

                                        <select wire:change="assignOperator(<?php echo e($order->id); ?>, $event.target.value)" class="mt-1 w-full rounded-md border-slate-300">
                                            <option value=""><?php echo e($this->t('select_operator')); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->operators; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operator): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                                <option value="<?php echo e($operator->id); ?>" <?php if($order->assigned_operator_id === $operator->id): echo 'selected'; endif; ?>><?php echo e($operator->name); ?></option>
                                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                        </select>
                                    </label>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <p class="text-sm text-slate-500 md:col-span-2"><?php echo e($this->t('no_orders_to_assign')); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::CHECK_COLOR)): ?>
                    <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 class="text-lg font-bold"><?php echo e($this->t('my_assignments')); ?></h2>
                        <div class="mt-4 grid gap-3 md:grid-cols-2">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->ordersForMyTasks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'my-assignment-'.e($order->id).''; ?>wire:key="my-assignment-<?php echo e($order->id); ?>" class="rounded-lg bg-slate-50 p-4">
                                    <p class="font-semibold"><?php echo e($order->order_number); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo e($order->machine?->name); ?> · <?php echo e($order->product?->name); ?></p>
                                    <p class="mt-2 text-sm"><?php echo e($this->t('status')); ?>: <?php echo e($this->statusLabel($order->status)); ?></p>
                                    <p class="mt-1 text-sm"><?php echo e($this->t('current_quantity')); ?>: <?php echo e($order->current_quantity); ?> / <?php echo e($order->quantity); ?></p>
                                    <button wire:click="startAssignedTask(<?php echo e($order->id); ?>)" class="mt-3 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('start_task')); ?></button>
                                </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <p class="text-sm text-slate-500 md:col-span-2"><?php echo e($this->t('no_assignments')); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
            </section>
        <?php elseif($activeTab === 'orders'): ?>
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900"><?php echo e($this->t('orders')); ?></h1>
                <div class="mb-5 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold"><?php echo e($this->t('all_orders')); ?></h2>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->ordersForViewing; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'order-'.e($order->id).''; ?>wire:key="order-<?php echo e($order->id); ?>" class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-4">
                                <div>
                                    <p class="font-semibold"><?php echo e($order->order_number); ?></p>
                                    <p class="text-sm text-slate-500"><?php echo e($order->machine?->name); ?> · <?php echo e($order->product?->name); ?></p>
                                    <p class="text-sm text-slate-600"><?php echo e($this->t('status')); ?>: <?php echo e($this->statusLabel($order->status)); ?></p>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order->assignedOperator): ?>
                                        <p class="text-sm text-slate-600"><?php echo e($this->t('operator')); ?>: <?php echo e($order->assignedOperator->name); ?></p>
                                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                </div>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::ACTIVATE_ORDER) && in_array($order->status, ['waiting', 'paused'], true)): ?>
                                    <button wire:click="activateOrder(<?php echo e($order->id); ?>)" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t($order->status === 'paused' ? 'resume' : 'activate')); ?></button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button wire:click="openProductionProtocol(<?php echo e($order->id); ?>)" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100"><?php echo e($this->t('production_protocol')); ?></button>
                            </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            <p class="text-sm text-slate-500 md:col-span-2"><?php echo e($this->t('no_waiting_orders')); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </div>
                </div>

            </section>
        <?php elseif($activeTab === 'moulds'): ?>
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900"><?php echo e($this->t('moulds')); ?></h1>
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->moulds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mould): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'mould-'.e($mould->id).''; ?>wire:key="mould-<?php echo e($mould->id); ?>" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <div class="flex justify-between gap-3"><h2 class="font-bold text-slate-900"><?php echo e($mould->name); ?></h2><span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($mould->status)]); ?>"><?php echo e($this->statusLabel($mould->status)); ?></span></div>
                            <dl class="mt-4 space-y-2 text-sm"><div class="flex justify-between"><dt class="text-slate-500"><?php echo e($this->t('number')); ?></dt><dd><?php echo e($mould->number); ?></dd></div><div class="flex justify-between"><dt class="text-slate-500"><?php echo e($this->t('cavity_count')); ?></dt><dd><?php echo e($mould->cavity_count ?? '-'); ?></dd></div><div class="flex justify-between"><dt class="text-slate-500"><?php echo e($this->t('opening')); ?></dt><dd><?php echo e($mould->open_mm ?? '-'); ?> mm</dd></div></dl>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mould->products->isNotEmpty()): ?><p class="mt-4 text-sm text-slate-600"><strong><?php echo e($this->t('product')); ?>:</strong> <?php echo e($mould->products->pluck('name')->join(', ')); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($mould->problem_description): ?><p class="mt-3 text-sm text-red-700"><?php echo e($mould->problem_description); ?></p><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isAdmin() || $this->canDo(\App\Enums\Role::REPORT_EQUIPMENT_WARNING)): ?>
                                <button wire:click="openMouldModal(<?php echo e($mould->id); ?>)" class="mt-5 w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('change_status')); ?></button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3"><?php echo e($this->t('no_moulds')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php else: ?>
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900"><?php echo e($this->t('products')); ?></h1>
                <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'product-'.e($product->id).''; ?>wire:key="product-<?php echo e($product->id); ?>" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h2 class="font-bold text-slate-900"><?php echo e($product->name); ?></h2><p class="mt-1 text-sm text-slate-500"><?php echo e($product->code); ?></p>
                            <dl class="mt-4 space-y-2 text-sm"><div><dt class="text-slate-500"><?php echo e($this->t('material')); ?></dt><dd class="font-medium"><?php echo e($product->material?->name ?? '-'); ?> <?php echo e($product->material?->code ? '('.$product->material->code.')' : ''); ?></dd></div><div><dt class="text-slate-500"><?php echo e($this->t('color')); ?></dt><dd class="font-medium"><?php echo e($product->color?->name ?? '-'); ?> <?php echo e($product->color?->code ? '('.$product->color->code.')' : ''); ?></dd></div><div><dt class="text-slate-500"><?php echo e($this->t('additives')); ?></dt><dd class="font-medium"><?php echo e($product->additives->pluck('name')->join(', ') ?: '-'); ?></dd></div><div><dt class="text-slate-500"><?php echo e($this->t('compatible_moulds')); ?></dt><dd class="font-medium"><?php echo e($product->moulds->pluck('name')->join(', ') ?: '-'); ?></dd></div></dl>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 md:col-span-2 xl:col-span-3"><?php echo e($this->t('no_products')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
            </section>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    </main>

    <?php echo $__env->make('partials.footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMachineId && ($detail = $this->detailMachine)): ?>
        <?php ($detailMetrics = $detail->productionMetrics()); ?>
        <?php ($detailOrder = $detailMetrics->currentOrder() ?? $detailMetrics->nextActivatedOrder()); ?>
        <?php ($detailNextOrder = $detailMetrics->nextActivatedOrder()); ?>
        <?php ($detailNextOrder = $detailNextOrder?->is($detailOrder) ? null : $detailNextOrder); ?>
        <?php ($detailMould = $detailOrder?->mould); ?>
        <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'machine-detail-'.e($detail->id).''; ?>wire:key="machine-detail-<?php echo e($detail->id); ?>" wire:click.self="closeMachineDetail" class="fixed inset-0 z-50 flex items-end justify-center bg-slate-950/50 sm:items-center sm:p-4">
            <div class="flex max-h-[95vh] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl bg-white shadow-xl sm:rounded-xl">
                <div class="flex items-start justify-between gap-3 border-b-4 border-teal-600 px-5 py-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-900"><?php echo e($detail->name); ?></h2>
                        <p class="text-sm text-slate-500"><?php echo e($detail->type); ?></p>
                    </div>
                    <button wire:click="closeMachineDetail" class="rounded-md px-3 py-1 text-2xl leading-none text-slate-500 hover:bg-slate-100 hover:text-slate-800" aria-label="<?php echo e($this->t('close')); ?>">&times;</button>
                </div>

                <div class="space-y-4 overflow-y-auto p-4 sm:p-5">
                    <section class="rounded-lg border border-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-slate-900"><?php echo e($this->t('machine_details')); ?></h3>
                            <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($detail->status)]); ?>"><?php echo e($this->statusLabel($detail->status)); ?></span>
                        </div>
                        <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                            <div><dt class="text-slate-500"><?php echo e($this->t('type')); ?></dt><dd class="font-semibold"><?php echo e($detail->type); ?></dd></div>
                            <div><dt class="text-slate-500"><?php echo e($this->t('opening')); ?></dt><dd class="font-semibold"><?php echo e($detail->open_mm ?? '-'); ?> mm</dd></div>
                            <div><dt class="text-slate-500"><?php echo e($this->t('screw_diameter')); ?></dt><dd class="font-semibold"><?php echo e($detail->screw_diameter_cm ?? '-'); ?> cm</dd></div>
                        </dl>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->problem_description): ?>
                            <p class="mt-3 rounded-md bg-red-50 p-3 text-sm text-red-700"><strong><?php echo e($this->t('problem')); ?>:</strong> <?php echo e($detail->problem_description); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::START_MACHINE) || $this->canDo(\App\Enums\Role::STOP_MACHINE) || $this->canDo(\App\Enums\Role::REPORT_EQUIPMENT_WARNING)): ?>
                            <button wire:click="openMachineModal(<?php echo e($detail->id); ?>)" class="mt-4 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('change_machine_status')); ?></button>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>

                    <section class="rounded-lg border border-slate-200 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-slate-900"><?php echo e($this->t('mounted_mould')); ?></h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMould): ?>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($detailMould->status)]); ?>"><?php echo e($this->statusLabel($detailMould->status)); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMould): ?>
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-4">
                                <div class="col-span-2"><dt class="text-slate-500"><?php echo e($this->t('moulds')); ?></dt><dd class="font-semibold"><?php echo e($detailMould->name); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('number')); ?></dt><dd class="font-semibold"><?php echo e($detailMould->number ?? '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('cavity_count')); ?></dt><dd class="font-semibold"><?php echo e($detailMould->cavity_count ?? '-'); ?></dd></div>
                            </dl>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMould->problem_description): ?>
                                <p class="mt-3 rounded-md bg-red-50 p-3 text-sm text-red-700"><strong><?php echo e($this->t('problem')); ?>:</strong> <?php echo e($detailMould->problem_description); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isAdmin() || $this->canDo(\App\Enums\Role::REPORT_EQUIPMENT_WARNING)): ?>
                                <button wire:click="openMouldModal(<?php echo e($detailMould->id); ?>)" class="mt-4 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('change_mould_status')); ?></button>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php else: ?>
                            <p class="mt-3 rounded-lg bg-slate-50 p-3 text-sm text-slate-500"><?php echo e($this->t('no_mounted_mould')); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detail->moulds->isNotEmpty()): ?>
                            <p class="mt-4 text-xs font-semibold uppercase tracking-wide text-slate-500"><?php echo e($this->t('compatible_moulds')); ?></p>
                            <div class="mt-2 flex flex-wrap gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detail->moulds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $compatible): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                    <span class="rounded-full bg-slate-100 px-2.5 py-1 text-xs text-slate-700"><?php echo e($compatible->name); ?></span>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>

                    <section class="rounded-lg border border-teal-100 bg-teal-50 p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="font-bold text-teal-950"><?php echo e($this->t('order_details')); ?></h3>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder): ?>
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($detailOrder->status)]); ?>"><?php echo e($this->statusLabel($detailOrder->status)); ?></span>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder): ?>
                            <?php ($lastQualityCheck = $this->lastQualityCheckAt($detailOrder)); ?>
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                                <div><dt class="text-slate-500"><?php echo e($this->t('order_number')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->order_number); ?></dd></div>
                                <div class="col-span-2"><dt class="text-slate-500"><?php echo e($this->t('product')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->product?->name ?? '-'); ?> <span class="font-normal text-slate-500"><?php echo e($detailOrder->product?->code); ?></span></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('customer')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->customer); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('required_quantity')); ?></dt><dd class="font-semibold"><?php echo e(number_format($detailOrder->quantity, 0, ',', ' ')); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('planned_cycle')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->product?->planned_cycle_time ? number_format($detailOrder->product->planned_cycle_time / 1000, 1, ',', ' ').' s' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('actual_cycle')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->cycle_time ? number_format($detailOrder->cycle_time / 1000, 1, ',', ' ').' s' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('net_weight')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->net_weight !== null ? $detailOrder->net_weight.' g' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('gross_weight')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->gross_weight !== null ? $detailOrder->gross_weight.' g' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('color_percentage')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->color_percentage !== null ? number_format((float) $detailOrder->color_percentage, 2, ',', ' ').'%' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('yesterday_quantity_same_time')); ?></dt><dd class="font-semibold"><?php echo e($detailMetrics->quantityAtYesterdaySameTime($detailOrder) ?? '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('started_at')); ?></dt><dd class="font-semibold"><?php echo e($this->productionStartedAt($detailOrder)?->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i') ?? '-'); ?></dd></div>
                            </dl>
                            <dl class="mt-4 grid grid-cols-2 gap-3 rounded-lg bg-white/80 p-3 text-sm sm:grid-cols-4">
                                <div><dt class="text-slate-500"><?php echo e($this->t('utilization_last_24_hours')); ?></dt><dd class="font-semibold"><?php echo e(($utilization = $detailMetrics->utilizationLast24Hours($detailOrder)) === null ? '-' : number_format($utilization, 1, ',', ' ').'%'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('theoretical_daily_production')); ?></dt><dd class="font-semibold"><?php echo e($detailMetrics->theoreticalDailyProduction($detailOrder) ?? '-'); ?> db</dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('actual_production_last_24_hours')); ?></dt><dd class="font-semibold"><?php echo e($detailMetrics->producedLast24Hours() ?? '-'); ?> db</dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('daily_color_consumption')); ?></dt><dd class="font-semibold"><?php echo e(($consumption = $detailMetrics->dailyColorConsumptionKg($detailOrder)) === null ? '-' : number_format($consumption, 3, ',', ' ').' kg'); ?></dd></div>
                            </dl>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder->product?->material || $detailOrder->product?->color || $detailOrder->product?->additives?->isNotEmpty()): ?>
                                <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-slate-500"><?php echo e($this->t('material')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->product?->material?->name ?? '-'); ?></dd></div>
                                    <div><dt class="text-slate-500"><?php echo e($this->t('color')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->product?->color?->name ?? '-'); ?></dd></div>
                                    <div class="col-span-2"><dt class="text-slate-500"><?php echo e($this->t('additives')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->product?->additives?->pluck('name')->join(', ') ?: '-'); ?></dd></div>
                                </dl>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="mt-4 flex justify-between text-sm"><span><?php echo e($this->t('current_quantity')); ?></span><strong><?php echo e($detailOrder->current_quantity); ?> / <?php echo e($detailOrder->quantity); ?> db</strong></div>
                            <?php echo $__env->make('partials.production-progress-bar', ['order' => $detailOrder], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                            <div class="mt-3">
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($detailOrder))]); ?>"><?php echo e($this->t('quality')); ?>: <?php echo e($this->statusLabel($this->qualityStatus($detailOrder))); ?></span>
                                <p class="<?php echo \Illuminate\Support\Arr::toCssClasses(['mt-2 text-xs font-medium', 'text-red-700' => $this->qualityCheckOverdue($detailOrder), 'text-slate-600' => ! $this->qualityCheckOverdue($detailOrder)]); ?>">
                                    <?php echo e($lastQualityCheck ? $this->t('last_quality_check_at', ['time' => $lastQualityCheck->copy()->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s')]) : $this->t('no_quality_check_yet')); ?>

                                </p>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->qualityCheckOverdue($detailOrder)): ?>
                                    <p class="mt-1 text-xs font-bold text-red-700"><?php echo e($this->t('quality_check_overdue')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder->pause_description && $detailOrder->status === 'paused'): ?>
                                <p class="mt-3 rounded-md bg-amber-50 p-3 text-sm text-amber-800"><strong><?php echo e($this->t('pause_reason')); ?>:</strong> <?php echo e($detailOrder->pause_description); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canManageOrders()): ?>
                                    <button wire:click="openOrderModal(<?php echo e($detailOrder->id); ?>)" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('manage_order')); ?></button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canCheckQuality()): ?>
                                    <button wire:click="openQualityModal(<?php echo e($detailOrder->id); ?>)" class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100"><?php echo e($this->t($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY) ? 'color_check' : 'quality_check')); ?></button>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <button wire:click="openProductionProtocol(<?php echo e($detailOrder->id); ?>)" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-bold text-slate-700 hover:bg-slate-100"><?php echo e($this->t('production_protocol')); ?></button>
                            </div>
                        <?php else: ?>
                            <p class="mt-3 rounded-lg bg-white p-3 text-sm text-slate-500"><?php echo e($this->t('no_running_order')); ?></p>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailNextOrder): ?>
                            <div class="mt-4 rounded-lg bg-white/80 p-3 text-sm">
                                <h4 class="font-semibold"><?php echo e($this->t('next_activated_order')); ?></h4>
                                <p class="mt-1"><?php echo e($detailNextOrder->order_number); ?> · <?php echo e($detailNextOrder->customer); ?> · <?php echo e($detailNextOrder->product?->code ?? $detailNextOrder->product?->name); ?></p>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMetrics->waitingOrders()->isNotEmpty()): ?>
                            <div class="mt-3 rounded-lg bg-white/80 p-3 text-sm">
                                <h4 class="font-semibold"><?php echo e($this->t('waiting_orders')); ?></h4>
                                <ul class="mt-1 list-inside list-disc">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $detailMetrics->waitingOrders(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $waitingOrder): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <li><?php echo e($waitingOrder->order_number); ?> · <?php echo e($waitingOrder->customer); ?> · <?php echo e($waitingOrder->product?->code ?? $waitingOrder->product?->name); ?></li>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                    </section>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showMachineModal || $showMouldModal): ?>
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6">
                <h2 class="text-xl font-bold"><?php echo e($showMachineModal ? $this->t('machines').' '.$this->t('change_status') : $this->t('moulds').' '.$this->t('change_status')); ?></h2>
                <div class="mt-5 space-y-4">
                    <label class="block text-sm font-semibold"><?php echo e($this->t('new_status')); ?><select wire:model.live="status" class="mt-1 w-full rounded-md border-slate-300"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showMachineModal): ?><option value="" disabled><?php echo e($this->t('select_status')); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::START_MACHINE)): ?><option value="active"><?php echo e($this->t('active')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::STOP_MACHINE)): ?><option value="stopped"><?php echo e($this->t('stopped')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::REPORT_EQUIPMENT_WARNING)): ?><option value="warning"><?php echo e($this->t('warning')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isAdmin()): ?><option value="broken"><?php echo e($this->t('broken')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php else: ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isAdmin()): ?><option value="active"><?php echo e($this->t('active')); ?></option><option value="standby"><?php echo e($this->t('standby')); ?></option><option value="warning"><?php echo e($this->t('warning')); ?></option><option value="needs_repair"><?php echo e($this->t('needs_repair')); ?></option><option value="under_repair"><?php echo e($this->t('under_repair')); ?></option><?php else: ?><option value="" disabled><?php echo e($this->t('select_status')); ?></option><option value="active"><?php echo e($this->t('active')); ?></option><option value="warning"><?php echo e($this->t('warning')); ?></option><option value="needs_repair"><?php echo e($this->t('needs_repair')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['warning', 'broken', 'needs_repair', 'stopped'], true)): ?><label class="block text-sm font-semibold"><?php echo e($this->t('problem_description')); ?><textarea wire:model="problemDescription" rows="3" class="mt-1 w-full rounded-md border-slate-300"></textarea><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['problemDescription'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="mt-6 flex justify-end gap-3"><button wire:click="$set('<?php echo e($showMachineModal ? 'showMachineModal' : 'showMouldModal'); ?>', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600"><?php echo e($this->t('cancel')); ?></button><button wire:click="<?php echo e($showMachineModal ? 'saveMachineStatus' : 'saveMouldStatus'); ?>" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white"><?php echo e($this->t('save')); ?></button></div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showCalculatorModal): ?>
        <?php ($calcProduct = $calcProductId ? $this->products->firstWhere('id', $calcProductId) : null); ?>
        <?php ($calcMinutes = $this->calculatedProductionMinutes()); ?>
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6">
                <h2 class="text-xl font-bold"><?php echo e($this->t('production_time_calculator')); ?></h2>
                <div class="mt-5 grid gap-4">
                    <label class="text-sm font-semibold"><?php echo e($this->t('product')); ?>

                        <select wire:model.live="calcProductId" class="mt-1 w-full rounded-md border-slate-300">
                            <option value=""><?php echo e($this->t('select_product')); ?></option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $this->products; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $product): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <option value="<?php echo e($product->id); ?>"><?php echo e($product->name); ?> (<?php echo e($product->code); ?>)</option>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        </select>
                    </label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($calcProduct): ?>
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($calcProduct->moulds->count() > 1): ?>
                            <label class="text-sm font-semibold"><?php echo e($this->t('select_mould')); ?>

                                <select wire:model.live="calcMouldId" class="mt-1 w-full rounded-md border-slate-300">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = $calcProduct->moulds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mould): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($mould->id); ?>"><?php echo e($mould->name); ?> (<?php echo e($mould->number); ?>)</option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </label>
                        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="text-sm font-semibold"><?php echo e($this->t('cavity_count')); ?><input wire:model.live.debounce.400ms="calcCavities" type="number" min="1" class="mt-1 w-full rounded-md border-slate-300"></label>
                            <label class="text-sm font-semibold"><?php echo e($this->t('cycle_time_seconds')); ?><input wire:model.live.debounce.400ms="calcCycleSeconds" type="number" min="0" step="0.01" class="mt-1 w-full rounded-md border-slate-300"></label>
                        </div>
                        <p class="-mt-2 text-xs text-slate-500"><?php echo e($this->t('calc_not_saved_hint')); ?></p>
                        <label class="text-sm font-semibold"><?php echo e($this->t('production_quantity')); ?><input wire:model.live.debounce.400ms="calcQuantity" type="number" min="1" class="mt-1 w-full rounded-md border-slate-300"></label>
                        <div class="rounded-lg bg-teal-50 p-4 text-sm text-teal-900">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($calcMinutes !== null): ?>
                                <div class="font-semibold"><?php echo e($this->t('calc_result')); ?></div>
                                <div class="mt-1 text-xl font-bold"><?php echo e(intdiv($calcMinutes, 1440)); ?> <?php echo e($this->t('calc_days')); ?>, <?php echo e(intdiv($calcMinutes % 1440, 60)); ?> <?php echo e($this->t('calc_hours')); ?>, <?php echo e($calcMinutes % 60); ?> <?php echo e($this->t('calc_minutes')); ?></div>
                            <?php else: ?>
                                <?php echo e($this->t('calc_missing_data')); ?>

                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </div>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>
                <div class="mt-6 flex justify-end"><button wire:click="$set('showCalculatorModal', false)" class="rounded-md bg-slate-700 px-4 py-2 text-sm font-bold text-white"><?php echo e($this->t('close')); ?></button></div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showOrderModal): ?>
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold"><?php echo e($this->t('manage_order')); ?></h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold"><?php echo e($this->t('status')); ?><select wire:model.live="orderStatus" class="mt-1 w-full rounded-md border-slate-300"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(! $this->isAdmin()): ?><option value=""><?php echo e($this->t('select_status')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?> <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->isAdmin()): ?><option value="in_progress"><?php echo e($this->t('in_progress')); ?></option><option value="paused"><?php echo e($this->t('paused')); ?></option><option value="completed"><?php echo e($this->t('completed')); ?></option><option value="interrupted"><?php echo e($this->t('interrupted')); ?></option><?php else: ?><option value="paused"><?php echo e($this->t('paused')); ?></option><option value="interrupted"><?php echo e($this->t('interrupted')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></label><label class="text-sm font-semibold"><?php echo e($this->t('current_quantity')); ?><input wire:model="currentQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($orderStatus, ['completed', 'interrupted'], true)): ?><label class="text-sm font-semibold"><?php echo e($this->t('good_quantity')); ?><input wire:model="goodQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><label class="text-sm font-semibold"><?php echo e($this->t('bad_quantity')); ?><input wire:model="badQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('showOrderModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600"><?php echo e($this->t('cancel')); ?></button><button wire:click="saveOrder" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white"><?php echo e($this->t('save')); ?></button></div></div></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showProtocolModal && ($protocolOrder = \App\Models\Order::query()->find($protocolOrderId))): ?>
        <?php ($protocol = $protocolOrder->production_protocol ?? []); ?>
        <?php ($entries = collect($protocol['entries'] ?? [])->sortByDesc(fn (array $entry) => $entry['timestamp'] ?? '')); ?>
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4">
            <div class="flex max-h-[90vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
                <div class="flex items-start justify-between gap-3 border-b-4 border-teal-600 px-5 py-4">
                    <div>
                        <h2 class="text-xl font-bold"><?php echo e($this->t('production_protocol')); ?> · <?php echo e($protocolOrder->order_number); ?></h2>
                        <p class="text-sm text-slate-500"><?php echo e($protocolOrder->machine?->name); ?> · <?php echo e($protocolOrder->product?->name); ?></p>
                    </div>
                    <button wire:click="$set('showProtocolModal', false)" class="rounded-md px-3 py-1 text-2xl leading-none text-slate-500 hover:bg-slate-100" aria-label="<?php echo e($this->t('close')); ?>">&times;</button>
                </div>

                <div class="space-y-5 overflow-y-auto p-5">
                    <section>
                        <h3 class="font-bold"><?php echo e($this->t('protocol_entries')); ?></h3>
                        <ol class="mt-3 space-y-3">
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                <?php ($appearance = $this->protocolEntryAppearance(data_get($entry, 'entry_type', 'note'), data_get($entry, 'meta.quality_status'))); ?>
                                <li <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'protocol-entry-'.e($protocolOrder->id).'-'.e($loop->index).''; ?>wire:key="protocol-entry-<?php echo e($protocolOrder->id); ?>-<?php echo e($loop->index); ?>" class="rounded-r-lg <?php echo e($appearance['container']); ?> p-4">
                                    <div class="flex flex-wrap items-center justify-between gap-2">
                                        <span class="inline-flex items-center gap-2">
                                            <span class="flex h-7 w-7 items-center justify-center rounded-full text-sm font-bold <?php echo e($appearance['badge']); ?>" aria-hidden="true"><?php echo e($appearance['icon']); ?></span>
                                            <span class="rounded-full px-2.5 py-1 text-xs font-bold <?php echo e($appearance['badge']); ?>"><?php echo e($this->t('protocol_type_'.data_get($entry, 'entry_type', 'note'))); ?></span>
                                        </span>
                                        <time class="text-sm text-slate-600"><?php echo e(\Illuminate\Support\Carbon::parse(data_get($entry, 'timestamp'))->timezone(config('app.display_timezone'))->format('Y-m-d H:i:s')); ?></time>
                                    </div>
                                    <p class="mt-2 whitespace-pre-wrap text-sm"><?php echo e(data_get($entry, 'note', '')); ?></p>
                                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-slate-500">
                                        <span><?php echo e($this->t('signed_by')); ?>: <?php echo e(data_get($entry, 'signed_by', $this->t('unknown_signer'))); ?></span>
                                        <span><?php echo e($this->t('current_quantity')); ?>: <?php echo e(data_get($entry, 'current_quantity', $protocolOrder->current_quantity)); ?></span>
                                    </div>
                                </li>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                <li class="rounded-lg bg-slate-50 p-4 text-sm text-slate-500"><?php echo e($this->t('no_protocol_entries')); ?></li>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                        </ol>
                    </section>

                    <section class="rounded-lg border border-slate-200 p-4">
                        <h3 class="font-bold"><?php echo e($this->t('add_protocol_entry')); ?></h3>
                        <div class="mt-3 grid gap-3">
                            <label class="text-sm font-semibold"><?php echo e($this->t('protocol_entry_type')); ?>

                                <select wire:model="protocolEntryType" class="mt-1 w-full rounded-md border-slate-300">
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['note', 'parameter_change', 'machine_issue', 'mould_issue', 'other']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                                        <option value="<?php echo e($type); ?>"><?php echo e($this->t('protocol_type_'.$type)); ?></option>
                                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                                </select>
                            </label>
                            <label class="text-sm font-semibold"><?php echo e($this->t('protocol_entry_note')); ?>

                                <textarea wire:model="protocolEntryNote" rows="3" maxlength="2000" class="mt-1 w-full rounded-md border-slate-300"></textarea>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['protocolEntryNote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </label>
                        </div>
                        <div class="mt-4 flex justify-end gap-3">
                            <button wire:click="$set('showProtocolModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600"><?php echo e($this->t('cancel')); ?></button>
                            <button wire:click="addProductionProtocolEntry" wire:loading.attr="disabled" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white"><?php echo e($this->t('save')); ?></button>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showQualityModal): ?>
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold"><?php echo e($this->t($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY) ? 'record_color_check' : 'record_quality_check')); ?></h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold"><?php echo e($this->t('result')); ?><select wire:model.live="qualityStatus" class="mt-1 w-full rounded-md border-slate-300"><option value="good"><?php echo e($this->t('good')); ?></option><option value="warning"><?php echo e($this->t('warning')); ?></option><option value="quality_issue"><?php echo e($this->t('quality_issue')); ?></option></select></label><label class="text-sm font-semibold"><?php echo e($this->t('current_quantity')); ?><input wire:model="currentQuantity" wire:input="$set('quantityWarning', '')" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($quantityWarning !== ''): ?><div class="rounded-md border border-amber-300 bg-amber-50 p-3 text-sm text-amber-900" role="alert"><p class="font-semibold"><?php echo e($quantityWarning); ?></p><p class="mt-1"><?php echo e($this->t('quantity_confirm_question')); ?></p><div class="mt-3 flex gap-2"><button wire:click="saveQualityCheck(true)" class="rounded-md bg-amber-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-amber-700"><?php echo e($this->t('quantity_confirm_yes')); ?></button><button wire:click="$set('quantityWarning', '')" class="rounded-md border border-amber-400 px-3 py-1.5 text-xs font-bold text-amber-900"><?php echo e($this->t('quantity_confirm_edit')); ?></button></div></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?><?php if($qualityStatus !== 'good'): ?><div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'quality-problem-fields-'.e($qualityStatus).''; ?>wire:key="quality-problem-fields-<?php echo e($qualityStatus); ?>" class="grid gap-4"><label class="text-sm font-semibold"><?php echo e($this->t('problem_type')); ?><select wire:model="qualityProblemType" class="mt-1 w-full rounded-md border-slate-300"><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($this->canDo(\App\Enums\Role::CHECK_COLOR) && ! $this->canDo(\App\Enums\Role::CHECK_QUALITY)): ?><option value="color_issue"><?php echo e($this->t('color_issue')); ?></option><?php else: ?><option value="surface_defect"><?php echo e($this->t('surface_defect')); ?></option><option value="dimension_issue"><?php echo e($this->t('dimension_issue')); ?></option><option value="color_issue"><?php echo e($this->t('color_issue')); ?></option><option value="material_issue"><?php echo e($this->t('material_issue')); ?></option><option value="burn_mark"><?php echo e($this->t('burn_mark')); ?></option><option value="flash"><?php echo e($this->t('flash')); ?></option><option value="short_shot"><?php echo e($this->t('short_shot')); ?></option><option value="contamination"><?php echo e($this->t('contamination')); ?></option><option value="other"><?php echo e($this->t('other')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></label><label class="text-sm font-semibold"><?php echo e($this->t('problem_reason')); ?><textarea wire:model="qualityProblemNote" rows="3" placeholder="<?php echo e($this->t('problem_reason_placeholder')); ?>" class="mt-1 w-full rounded-md border-slate-300"></textarea><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['qualityProblemNote'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><span class="text-xs text-red-600"><?php echo e($message); ?></span><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></label></div><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('showQualityModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600"><?php echo e($this->t('cancel')); ?></button><button wire:click="saveQualityCheck" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white"><?php echo e($this->t('record')); ?></button></div></div></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
</div><?php /**PATH C:\Users\kalap\Herd\Papirus\storage\framework\views/livewire/views/1a614361.blade.php ENDPATH**/ ?>