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
                <div class="mt-4 text-center">
                    <select wire:change="selectLanguage($event.target.value)" class="rounded-md border-slate-300 py-1 text-xs">
                        <option value="hu" <?php if($locale === 'hu'): echo 'selected'; endif; ?>><?php echo e($this->t('hungarian')); ?></option>
                        <option value="de" <?php if($locale === 'de'): echo 'selected'; endif; ?>><?php echo e($this->t('german')); ?></option>
                    </select>
                </div>
            </form>
        </div>
    <?php else: ?>
    <header class="sticky top-0 z-40 border-b-4 border-teal-600 bg-white shadow-sm">
        <div class="mx-auto flex max-w-7xl items-center justify-between gap-3 px-4 py-2 sm:px-6">
            <div class="flex items-center gap-3">
                <img src="<?php echo e(asset('logo.png')); ?>" alt="Periplast" class="h-10 w-auto sm:h-12">
                <span class="hidden text-sm font-semibold text-slate-500 sm:inline"><?php echo e($this->t('live_overview')); ?></span>
            </div>
            <div class="flex items-center gap-2">
                <label class="sr-only" for="locale"><?php echo e($this->t('language')); ?></label>
                <select id="locale" wire:change="selectLanguage($event.target.value)" class="rounded-md border-slate-300 py-1 text-xs font-semibold text-slate-700 focus:border-teal-600 focus:ring-teal-600">
                    <option value="hu" <?php if($locale === 'hu'): echo 'selected'; endif; ?>><?php echo e($this->t('hungarian')); ?></option>
                    <option value="de" <?php if($locale === 'de'): echo 'selected'; endif; ?>><?php echo e($this->t('german')); ?></option>
                </select>
                <span class="hidden text-sm font-medium text-slate-600 md:inline"><?php echo e(auth()->user()->name); ?></span>
                <button wire:click="logout" class="rounded-md bg-slate-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-slate-800"><?php echo e($this->t('logout')); ?></button>
            </div>
        </div>
        <nav class="border-t border-slate-100" aria-label="Main">
            <div class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-2 [scrollbar-width:none] sm:px-4">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__currentLoopData = ['machines' => 'machines', 'moulds' => 'moulds', 'products' => 'products', 'orders' => 'orders']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tab => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                    <button wire:click="selectTab('<?php echo e($tab); ?>')" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['shrink-0 whitespace-nowrap border-b-2 px-4 py-3 text-sm font-semibold transition', 'border-teal-600 text-teal-700' => $activeTab === $tab, 'border-transparent text-slate-600 hover:text-teal-700' => $activeTab !== $tab]); ?>">
                        <?php echo e($this->t($label)); ?>

                    </button>
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="mx-auto max-w-7xl px-4 py-5 sm:px-6 sm:py-6">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($flashMessage): ?>
            <div class="mb-5 flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                <?php echo e($flashMessage); ?>

                <button wire:click="$set('flashMessage', '')" class="font-bold" aria-label="<?php echo e($this->t('cancel')); ?>">&times;</button>
            </div>
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($activeTab === 'machines'): ?>
            <section>
                <div class="mb-5 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-900"><?php echo e($this->t('machines')); ?></h1>
                        <p class="mt-1 text-sm text-slate-600"><?php echo e($this->t('dashboard_subtitle')); ?></p>
                    </div>
                    <span class="rounded-full bg-teal-100 px-3 py-1 text-sm font-semibold text-teal-800"><?php echo e($this->t('machine_count', ['count' => $this->machines->count()])); ?></span>
                </div>

                <div class="grid gap-5 lg:grid-cols-2">
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->machines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $machine): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                        <?php ($order = $machine->orders->first()); ?>
                        <article <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'machine-'.e($machine->id).''; ?>wire:key="machine-<?php echo e($machine->id); ?>" wire:click="openMachineDetail(<?php echo e($machine->id); ?>)" wire:keydown.enter="openMachineDetail(<?php echo e($machine->id); ?>)" role="button" tabindex="0" title="<?php echo e($this->t('open_details')); ?>" class="cursor-pointer overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:border-teal-400 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-teal-600">
                            <div class="flex items-start justify-between gap-3 border-b border-slate-100 p-5">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-900"><?php echo e($machine->name); ?></h2>
                                    <p class="text-sm text-slate-500"><?php echo e($machine->type); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($order?->status ?? 'stopped')]); ?>"><?php echo e($order?->status === 'in_progress' ? $this->t('active') : ($order?->status === 'paused' ? $this->t('stopped') : $this->t('inactive'))); ?></span>
                                    <button wire:click.stop="openMachineModal(<?php echo e($machine->id); ?>)" class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-teal-700" title="<?php echo e($this->t('change_status')); ?>">⚙</button>
                                </div>
                            </div>
                            <div class="p-5">
                                <dl class="grid grid-cols-2 gap-3 text-sm">
                                    <div><dt class="text-slate-500"><?php echo e($this->t('opening')); ?></dt><dd class="font-semibold"><?php echo e($machine->open_mm ?? '-'); ?> mm</dd></div>
                                    <div><dt class="text-slate-500"><?php echo e($this->t('screw_diameter')); ?></dt><dd class="font-semibold"><?php echo e($machine->screw_diameter_cm ?? '-'); ?> cm</dd></div>
                                </dl>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($machine->problem_description): ?>
                                    <p class="mt-4 rounded-md bg-red-50 p-3 text-sm text-red-700"><strong><?php echo e($this->t('problem')); ?>:</strong> <?php echo e($machine->problem_description); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($order): ?>
                                    <div class="mt-5 rounded-lg border border-teal-100 bg-teal-50 p-4">
                                        <div class="flex items-start justify-between gap-3">
                                            <div>
                                                <p class="font-bold text-teal-950"><?php echo e($this->t('current_order')); ?></p>
                                                <p class="text-sm text-teal-800"><?php echo e($order->order_number); ?> · <?php echo e($order->product?->name ?? $this->t('no_products')); ?></p>
                                            </div>
                                            <button wire:click.stop="openOrderModal(<?php echo e($order->id); ?>)" class="rounded-md bg-teal-700 px-3 py-1.5 text-xs font-bold text-white hover:bg-teal-800"><?php echo e($this->t('manage')); ?></button>
                                        </div>
                                        <div class="mt-4 flex justify-between text-sm"><span><?php echo e($this->t('current_quantity')); ?></span><strong><?php echo e($order->current_quantity); ?> / <?php echo e($order->quantity); ?> db</strong></div>
                                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-teal-100"><div class="h-full rounded-full bg-teal-600" style="width: <?php echo e($this->progress($order)); ?>%"></div></div>
                                        <div class="mt-4 flex flex-wrap gap-2">
                                            <span <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'quality-status-'.e($order->id).'-'.e($this->qualityStatus($order)).''; ?>wire:key="quality-status-<?php echo e($order->id); ?>-<?php echo e($this->qualityStatus($order)); ?>" class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($order))]); ?>">
                                                <?php echo e($this->t('quality')); ?>: <?php echo e($this->statusLabel($this->qualityStatus($order))); ?>

                                            </span>
                                            <button wire:click.stop="openQualityModal(<?php echo e($order->id); ?>)" class="rounded-md border border-teal-300 bg-white px-3 py-1 text-xs font-bold text-teal-800 hover:bg-teal-100"><?php echo e($this->t('quality_check')); ?></button>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <p class="mt-5 rounded-lg bg-slate-50 p-4 text-sm text-slate-500"><?php echo e($this->t('no_running_order')); ?></p>
                                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            </div>
                        </article>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::endLoop(); ?><?php endif; ?><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::closeLoop(); ?><?php endif; ?>
                        <p class="rounded-xl bg-white p-8 text-center text-slate-500 lg:col-span-2"><?php echo e($this->t('no_machines')); ?></p>
                    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                </div>

            </section>
        <?php elseif($activeTab === 'orders'): ?>
            <section>
                <h1 class="mb-5 text-2xl font-bold text-slate-900"><?php echo e($this->t('orders')); ?></h1>
                <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <h2 class="text-lg font-bold"><?php echo e($this->t('waiting_orders')); ?></h2>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::openLoop(); ?><?php endif; ?><?php $__empty_1 = true; $__currentLoopData = $this->waitingOrders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::startLoopIteration(); ?><?php endif; ?>
                            <div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'waiting-order-'.e($order->id).''; ?>wire:key="waiting-order-<?php echo e($order->id); ?>" class="flex items-center justify-between gap-3 rounded-lg bg-slate-50 p-4">
                                <div><p class="font-semibold"><?php echo e($order->order_number); ?></p><p class="text-sm text-slate-500"><?php echo e($order->machine?->name); ?> · <?php echo e($order->product?->name); ?></p></div>
                                <button wire:click="activateOrder(<?php echo e($order->id); ?>)" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('activate')); ?></button>
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
                            <button wire:click="openMouldModal(<?php echo e($mould->id); ?>)" class="mt-5 w-full rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('change_status')); ?></button>
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailMachineId && ($detail = $this->detailMachine)): ?>
        <?php ($detailOrder = $detail->orders->first()); ?>
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
                        <button wire:click="openMachineModal(<?php echo e($detail->id); ?>)" class="mt-4 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('change_machine_status')); ?></button>
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
                            <button wire:click="openMouldModal(<?php echo e($detailMould->id); ?>)" class="mt-4 rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('change_mould_status')); ?></button>
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
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm sm:grid-cols-3">
                                <div><dt class="text-slate-500"><?php echo e($this->t('order_number')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->order_number); ?></dd></div>
                                <div class="col-span-2"><dt class="text-slate-500"><?php echo e($this->t('product')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->product?->name ?? '-'); ?> <span class="font-normal text-slate-500"><?php echo e($detailOrder->product?->code); ?></span></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('required_quantity')); ?></dt><dd class="font-semibold"><?php echo e(number_format($detailOrder->quantity, 0, ',', ' ')); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('planned_cycle')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->product?->planned_cycle_time ? number_format($detailOrder->product->planned_cycle_time / 1000, 1, ',', ' ').' s' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('actual_cycle')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->cycle_time ? number_format($detailOrder->cycle_time / 1000, 1, ',', ' ').' s' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('net_weight')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->net_weight !== null ? $detailOrder->net_weight.' g' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('gross_weight')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->gross_weight !== null ? $detailOrder->gross_weight.' g' : '-'); ?></dd></div>
                                <div><dt class="text-slate-500"><?php echo e($this->t('started_at')); ?></dt><dd class="font-semibold"><?php echo e($detailOrder->started_at?->format('Y-m-d H:i') ?? '-'); ?></dd></div>
                            </dl>
                            <div class="mt-4 flex justify-between text-sm"><span><?php echo e($this->t('current_quantity')); ?></span><strong><?php echo e($detailOrder->current_quantity); ?> / <?php echo e($detailOrder->quantity); ?> db</strong></div>
                            <div class="mt-2 h-2 overflow-hidden rounded-full bg-teal-100"><div class="h-full rounded-full bg-teal-600" style="width: <?php echo e($this->progress($detailOrder)); ?>%"></div></div>
                            <div class="mt-3">
                                <span class="<?php echo \Illuminate\Support\Arr::toCssClasses(['rounded-full px-2.5 py-1 text-xs font-bold', $this->statusClass($this->qualityStatus($detailOrder))]); ?>"><?php echo e($this->t('quality')); ?>: <?php echo e($this->statusLabel($this->qualityStatus($detailOrder))); ?></span>
                            </div>
                            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($detailOrder->pause_description && $detailOrder->status === 'paused'): ?>
                                <p class="mt-3 rounded-md bg-amber-50 p-3 text-sm text-amber-800"><strong><?php echo e($this->t('pause_reason')); ?>:</strong> <?php echo e($detailOrder->pause_description); ?></p>
                            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button wire:click="openOrderModal(<?php echo e($detailOrder->id); ?>)" class="rounded-md bg-teal-700 px-3 py-2 text-sm font-bold text-white hover:bg-teal-800"><?php echo e($this->t('manage_order')); ?></button>
                                <button wire:click="openQualityModal(<?php echo e($detailOrder->id); ?>)" class="rounded-md border border-teal-300 bg-white px-3 py-2 text-sm font-bold text-teal-800 hover:bg-teal-100"><?php echo e($this->t('quality_check')); ?></button>
                            </div>
                        <?php else: ?>
                            <p class="mt-3 rounded-lg bg-white p-3 text-sm text-slate-500"><?php echo e($this->t('no_running_order')); ?></p>
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
                    <label class="block text-sm font-semibold"><?php echo e($this->t('new_status')); ?><select wire:model.live="status" class="mt-1 w-full rounded-md border-slate-300"><option value="active"><?php echo e($this->t('active')); ?></option><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showMachineModal): ?><option value="stopped"><?php echo e($this->t('stopped')); ?></option><option value="warning"><?php echo e($this->t('warning')); ?></option><option value="broken"><?php echo e($this->t('broken')); ?></option><?php else: ?><option value="standby"><?php echo e($this->t('standby')); ?></option><option value="warning"><?php echo e($this->t('warning')); ?></option><option value="needs_repair"><?php echo e($this->t('needs_repair')); ?></option><option value="under_repair"><?php echo e($this->t('under_repair')); ?></option><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></select></label>
                    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($status, ['warning', 'broken', 'needs_repair'], true)): ?><label class="block text-sm font-semibold"><?php echo e($this->t('problem_description')); ?><textarea wire:model="problemDescription" rows="3" class="mt-1 w-full rounded-md border-slate-300"></textarea><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['problemDescription'];
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

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showOrderModal): ?>
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold"><?php echo e($this->t('manage_order')); ?></h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold"><?php echo e($this->t('status')); ?><select wire:model.live="orderStatus" class="mt-1 w-full rounded-md border-slate-300"><option value="in_progress"><?php echo e($this->t('in_progress')); ?></option><option value="paused"><?php echo e($this->t('paused')); ?></option><option value="completed"><?php echo e($this->t('completed')); ?></option><option value="interrupted"><?php echo e($this->t('interrupted')); ?></option></select></label><label class="text-sm font-semibold"><?php echo e($this->t('current_quantity')); ?><input wire:model="currentQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(in_array($orderStatus, ['completed', 'interrupted'], true)): ?><label class="text-sm font-semibold"><?php echo e($this->t('good_quantity')); ?><input wire:model="goodQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><label class="text-sm font-semibold"><?php echo e($this->t('bad_quantity')); ?><input wire:model="badQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?></div><div class="mt-6 flex justify-end gap-3"><button wire:click="$set('showOrderModal', false)" class="rounded-md px-4 py-2 text-sm font-bold text-slate-600"><?php echo e($this->t('cancel')); ?></button><button wire:click="saveOrder" class="rounded-md bg-teal-700 px-4 py-2 text-sm font-bold text-white"><?php echo e($this->t('save')); ?></button></div></div></div>
    <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>

    <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($showQualityModal): ?>
        <div class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-950/50 p-4"><div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl bg-white p-5 shadow-xl sm:p-6"><h2 class="text-xl font-bold"><?php echo e($this->t('record_quality_check')); ?></h2><div class="mt-5 grid gap-4"><label class="text-sm font-semibold"><?php echo e($this->t('result')); ?><select wire:model.live="qualityStatus" class="mt-1 w-full rounded-md border-slate-300"><option value="good"><?php echo e($this->t('good')); ?></option><option value="warning"><?php echo e($this->t('warning')); ?></option><option value="quality_issue"><?php echo e($this->t('quality_issue')); ?></option></select></label><label class="text-sm font-semibold"><?php echo e($this->t('current_quantity')); ?><input wire:model="currentQuantity" type="number" min="0" class="mt-1 w-full rounded-md border-slate-300"></label><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($qualityStatus !== 'good'): ?><div <?php \Livewire\Features\SupportCompiledWireKeys\SupportCompiledWireKeys::$currentLoop['key'] = 'quality-problem-fields-'.e($qualityStatus).''; ?>wire:key="quality-problem-fields-<?php echo e($qualityStatus); ?>" class="grid gap-4"><label class="text-sm font-semibold"><?php echo e($this->t('problem_type')); ?><select wire:model="qualityProblemType" class="mt-1 w-full rounded-md border-slate-300"><option value="surface_defect"><?php echo e($this->t('surface_defect')); ?></option><option value="dimension_issue"><?php echo e($this->t('dimension_issue')); ?></option><option value="color_issue"><?php echo e($this->t('color_issue')); ?></option><option value="material_issue"><?php echo e($this->t('material_issue')); ?></option><option value="burn_mark"><?php echo e($this->t('burn_mark')); ?></option><option value="flash"><?php echo e($this->t('flash')); ?></option><option value="short_shot"><?php echo e($this->t('short_shot')); ?></option><option value="contamination"><?php echo e($this->t('contamination')); ?></option><option value="other"><?php echo e($this->t('other')); ?></option></select></label><label class="text-sm font-semibold"><?php echo e($this->t('problem_reason')); ?><textarea wire:model="qualityProblemNote" rows="3" placeholder="<?php echo e($this->t('problem_reason_placeholder')); ?>" class="mt-1 w-full rounded-md border-slate-300"></textarea><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php $__errorArgs = ['qualityProblemNote'];
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