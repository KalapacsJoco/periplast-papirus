<?php

use App\Models\Error;
use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/machines', function () {
    return Machine::query()
        ->select(['id', 'name', 'type', 'open_mm', 'screw_diameter_cm', 'status', 'problem_description'])
        ->orderBy('name')
        ->get();
});

// Update machine status
Route::post('/machines/{id}/status', function (Request $request, $id) {
    $validated = $request->validate([
        'status' => 'required|in:active,stopped,warning,broken,setup',
        'problem_description' => 'nullable|string',
    ]);

    $machine = Machine::findOrFail($id);
    $status = $validated['status'];
    $problem_description = $validated['problem_description'] ?? null;

    // If changing to 'broken', create an error entry
    if ($status === 'broken' && $machine->status !== 'broken') {
        Error::create([
            'machine_id' => $machine->id,
            'date' => now()->toDateString(),
            'type' => 'breakdown',
            'severity' => 'high',
            'description' => $problem_description ?? 'Machine breakdown',
            'status' => 'open',
        ]);
    }

    $machine->update(['status' => $status, 'problem_description' => $problem_description]);

    Order::query()
        ->where('machine_id', $machine->id)
        ->whereIn('status', ['in_progress', 'paused'])
        ->get()
        ->each(fn (Order $order) => $order->appendProtocolEntry((int) $order->current_quantity,
            'machine_status',
            'Machine status changed to '.$status.($problem_description ? ': '.$problem_description : '')
        ));

    if ($status === 'broken') {
        Order::pauseRunning(['machine_id' => $machine->id], $problem_description ?: 'Machine broken - order paused automatically');
    }

    return $machine;
});

// Restore a broken machine
Route::post('/machines/{id}/restore', function ($id) {
    $machine = Machine::findOrFail($id);

    if ($machine->status === 'broken') {
        $error = $machine->errors()
            ->where('status', 'open')
            ->latest('date')
            ->first();

        if ($error) {
            $error->update(['status' => 'resolved', 'restored_at' => now()]);
        }

        $machine->update(['status' => 'active', 'problem_description' => null]);
        Order::query()
            ->where('machine_id', $machine->id)
            ->whereIn('status', ['in_progress', 'paused'])
            ->get()
            ->each(fn (Order $order) => $order->appendProtocolEntry((int) $order->current_quantity,
                'machine_status',
                'Machine restored to active.'
            ));
    }

    return $machine;
});

// ── Moulds ───────────────────────────────────────────────────────────────────

Route::get('/moulds', function () {
    return Mould::query()
        ->select(['id', 'name', 'type', 'number', 'cavity_count', 'open_mm', 'status', 'problem_description'])
        ->with(['products:id,name,code'])
        ->orderBy('name')
        ->get();
});

Route::post('/moulds/{id}/status', function (Request $request, $id) {
    $validated = $request->validate([
        'status' => 'required|in:active,standby,warning,needs_repair,under_repair',
        'problem_description' => 'nullable|string',
    ]);

    $mould = Mould::findOrFail($id);
    $status = $validated['status'];
    $problemDescription = in_array($status, ['warning', 'needs_repair'], true)
        ? ($validated['problem_description'] ?? null)
        : null;

    $mould->update([
        'status' => $status,
        'problem_description' => $problemDescription,
    ]);

    Order::query()
        ->where('mould_id', $mould->id)
        ->whereIn('status', ['in_progress', 'paused'])
        ->get()
        ->each(fn (Order $order) => $order->appendProtocolEntry((int) $order->current_quantity,
            'mould_status',
            'Mould status changed to '.$status.($problemDescription ? ': '.$problemDescription : '')
        ));

    if ($status === 'needs_repair') {
        Order::pauseRunning(['mould_id' => $mould->id], $problemDescription ?: 'Mould needs repair - order paused automatically');
    }

    return $mould->load('products:id,name,code');
});

// ── Products ─────────────────────────────────────────────────────────────────

Route::get('/products', function () {
    return Product::query()
        ->select(['id', 'name', 'code', 'material_id', 'color_id'])
        ->with([
            'material:id,name,code',
            'color:id,name,code',
            'additives:id,name,code',
            'moulds:id,name,number',
        ])
        ->orderBy('name')
        ->get();
});

// ── Orders ───────────────────────────────────────────────────────────────────

Route::get('/orders', function () {
    return Order::query()
        ->select(['id', 'order_number', 'machine_id', 'mould_id', 'product_id', 'date', 'planned_end_date', 'customer', 'quantity', 'current_quantity', 'good_quantity', 'bad_quantity', 'started_at', 'ended_at', 'last_paused_at', 'cycle_time', 'net_weight', 'gross_weight', 'color_percentage', 'measured_cycle_time', 'measured_net_weight', 'measured_gross_weight', 'measured_color_percentage', 'measured_at', 'additives_percentages', 'production_protocol', 'status', 'created_at'])
        ->with([
            'machine:id,name,status,problem_description',
            'mould:id,name,number,cavity_count,status,problem_description',
            'product:id,name,code,is_active',
        ])
        ->orderBy('order_number')
        ->get();
});

Route::post('/orders/{id}/status', function (Request $request, $id) {
    $validated = $request->validate([
        'status' => 'required|in:waiting,in_progress,paused,completed,interrupted',
        'pause_description' => 'nullable|string',
        'current_quantity' => 'required|integer|min:0',
        'good_quantity' => 'required_if:status,completed,interrupted|integer|min:0',
        'bad_quantity' => 'required_if:status,completed,interrupted|integer|min:0',
    ]);

    $order = Order::findOrFail($id);
    $previousStatus = $order->status;
    $status = $validated['status'];
    $pauseDescription = in_array($status, ['paused', 'interrupted'], true)
        ? ($validated['pause_description'] ?? null)
        : null;
    $currentQuantity = max(0, min((int) $validated['current_quantity'], (int) $order->quantity));
    $goodQuantity = $validated['good_quantity'] ?? $currentQuantity ?? $order->good_quantity ?? 0;
    $badQuantity = $validated['bad_quantity'] ?? $order->bad_quantity ?? 0;

    $startedAt = $order->started_at;
    $endedAt = $order->ended_at;
    $lastPausedAt = $order->last_paused_at;

    if ($status === 'in_progress' && ! $startedAt) {
        $startedAt = now();
    }

    if (
        $status === 'in_progress'
        && in_array($previousStatus, ['paused', 'interrupted'], true)
        && $lastPausedAt
        && $startedAt
    ) {
        $pausedSeconds = $lastPausedAt->diffInSeconds(now());
        $startedAt = $startedAt->copy()->addSeconds($pausedSeconds);
        $lastPausedAt = null;
    }

    if (in_array($status, ['paused', 'interrupted'], true) && $previousStatus === 'in_progress') {
        $lastPausedAt = now();
    }

    if ($status === 'completed') {
        $endedAt = now();
        $lastPausedAt = null;
    } elseif ($status !== 'completed') {
        $endedAt = null;
    }

    if ($status === 'waiting') {
        $lastPausedAt = null;
    }

    $order->update([
        'status' => $status,
        'pause_description' => $pauseDescription,
        'started_at' => $startedAt,
        'ended_at' => $endedAt,
        'last_paused_at' => $lastPausedAt,
        'current_quantity' => $currentQuantity,
        'good_quantity' => $goodQuantity,
        'bad_quantity' => $badQuantity,
    ]);
    if ($previousStatus !== $status) {
        $order->appendProtocolEntry($currentQuantity,
            'order_status',
            'Order status changed from '.$previousStatus.' to '.$status.($pauseDescription ? ': '.$pauseDescription : '')
        );
    }

    return $order->load(['machine:id,name,status,problem_description', 'mould:id,name,number,cavity_count,status,problem_description', 'product:id,name,code,is_active']);
});

Route::post('/orders/{id}/production-data', function (Request $request, $id) {
    $validated = $request->validate([
        'current_quantity' => 'required|integer|min:0',
    ]);

    $order = Order::findOrFail($id);
    $currentQuantity = min($validated['current_quantity'], $order->quantity);
    $willBeCompleted = $currentQuantity >= $order->quantity;

    $order->update([
        'current_quantity' => $currentQuantity,
        'good_quantity' => $currentQuantity,
        'started_at' => $order->started_at ?? now(),
        'status' => $willBeCompleted ? 'completed' : ($order->status === 'waiting' ? 'in_progress' : $order->status),
        'ended_at' => $willBeCompleted ? ($order->ended_at ?? now()) : null,
        'last_paused_at' => $willBeCompleted ? null : $order->last_paused_at,
    ]);
    if ($order->wasChanged('current_quantity')) {
        $order->appendProtocolEntry($currentQuantity,
            'production_update',
            'Production quantity updated.'
        );
    }

    return $order->load([
        'machine:id,name,status,problem_description',
        'mould:id,name,number,cavity_count,status,problem_description',
        'product:id,name,code,is_active',
    ]);
});

Route::post('/orders/{id}/protocol', function (Request $request, $id) {
    $validated = $request->validate([
        'machine_name' => 'nullable|string|max:255',
        'part_code' => 'nullable|string|max:255',
        'material_code' => 'nullable|string|max:255',
        'color_code' => 'nullable|string|max:255',
        'color_percentage' => 'nullable|numeric|min:0',
        'additives' => 'nullable|string|max:2000',
        'cycle_time' => 'nullable|integer|min:1',
        'quantity' => 'nullable|integer|min:1',
        'gross_weight' => 'nullable|numeric|min:0',
        'net_weight' => 'nullable|numeric|min:0',
    ]);

    $order = Order::findOrFail($id);
    $existingProtocol = is_array($order->production_protocol) ? $order->production_protocol : [];
    $previousParameters = [
        'cycle_time' => $order->cycle_time,
        'quantity' => $order->quantity,
        'gross_weight' => $order->gross_weight,
        'net_weight' => $order->net_weight,
        'color_percentage' => $order->color_percentage,
    ];

    $order->update([
        'cycle_time' => $validated['cycle_time'] ?? $order->cycle_time,
        'quantity' => $validated['quantity'] ?? $order->quantity,
        'gross_weight' => isset($validated['gross_weight']) ? (float) $validated['gross_weight'] : $order->gross_weight,
        'net_weight' => isset($validated['net_weight']) ? (float) $validated['net_weight'] : $order->net_weight,
        'color_percentage' => isset($validated['color_percentage']) ? (float) $validated['color_percentage'] : $order->color_percentage,
        'production_protocol' => array_merge($existingProtocol, [
            'machine_name' => $validated['machine_name'] ?? $order->machine?->name,
            'part_code' => $validated['part_code'] ?? $order->product?->code,
            'material_code' => $validated['material_code'] ?? null,
            'color_code' => $validated['color_code'] ?? null,
            'color_percentage' => isset($validated['color_percentage']) ? (float) $validated['color_percentage'] : null,
            'additives' => $validated['additives'] ?? null,
            'cycle_time' => $validated['cycle_time'] ?? $order->cycle_time,
            'quantity' => $validated['quantity'] ?? $order->quantity,
            'gross_weight' => isset($validated['gross_weight']) ? (float) $validated['gross_weight'] : null,
            'net_weight' => isset($validated['net_weight']) ? (float) $validated['net_weight'] : null,
            'quality_status' => $existingProtocol['quality_status'] ?? 'good',
            'last_quality_check_at' => $existingProtocol['last_quality_check_at'] ?? null,
            'quality_checks' => is_array($existingProtocol['quality_checks'] ?? null) ? $existingProtocol['quality_checks'] : [],
        ]),
    ]);

    $order->refresh();
    $newParameters = [
        'cycle_time' => $order->cycle_time,
        'quantity' => $order->quantity,
        'gross_weight' => $order->gross_weight,
        'net_weight' => $order->net_weight,
        'color_percentage' => $order->color_percentage,
    ];
    if ($previousParameters !== $newParameters) {
        $order->appendProtocolEntry((int) $order->current_quantity,
            'parameter_change',
            'Production parameters updated.',
            ['before' => $previousParameters, 'after' => $newParameters]
        );
    }

    return $order->load([
        'machine:id,name,status,problem_description',
        'mould:id,name,number,cavity_count,status,problem_description',
        'product:id,name,code,is_active',
    ]);
});

Route::post('/orders/{id}/measured-data', function (Request $request, $id) {
    $validated = $request->validate([
        'measured_cycle_time' => 'required|integer|min:1',
        'measured_net_weight' => 'required|numeric|min:0',
        'measured_gross_weight' => 'required|numeric|min:0',
        'measured_color_percentage' => 'required|numeric|min:0',
    ]);

    $order = Order::findOrFail($id);
    $changes = $order->updateMeasured([
        'cycle_time' => (int) $validated['measured_cycle_time'],
        'net_weight' => (float) $validated['measured_net_weight'],
        'gross_weight' => (float) $validated['measured_gross_weight'],
        'color_percentage' => (float) $validated['measured_color_percentage'],
    ]);
    $order->appendProtocolEntry((int) $order->current_quantity, 'parameter_change', 'Measured data updated.', ['changes' => $changes]);

    return $order->load([
        'machine:id,name,status,problem_description',
        'mould:id,name,number,cavity_count,status,problem_description',
        'product:id,name,code,is_active',
    ]);
});

Route::post('/orders/{id}/quality-check', function (Request $request, $id) {
    $validated = $request->validate([
        'quality_status' => 'required|in:good,warning,quality_issue',
        'problem_type' => 'nullable|required_unless:quality_status,good|in:surface_defect,dimension_issue,color_issue,material_issue,burn_mark,flash,short_shot,contamination,other',
        'problem_note' => 'nullable|required_if:problem_type,other|string|max:2000',
        'follow_up_status' => 'nullable|required_unless:quality_status,good|in:warning,stopped',
        'resolution_note' => 'nullable|string|max:2000',
        'current_quantity' => 'required|integer|min:0',
    ]);

    $order = Order::findOrFail($id);
    if (! $order->hasMeasuredData()) {
        throw ValidationException::withMessages([
            'measured_data' => ['Measured data must be filled in before a quality check.'],
        ]);
    }
    $existingProtocol = is_array($order->production_protocol) ? $order->production_protocol : [];
    $qualityChecks = is_array($existingProtocol['quality_checks'] ?? null) ? $existingProtocol['quality_checks'] : [];

    $qualityStatus = $validated['quality_status'];
    $previousQualityStatus = ($existingProtocol['quality_status'] ?? 'good');
    $problemType = $validated['problem_type'] ?? null;
    $problemNote = $validated['problem_note'] ?? null;
    $resolutionNote = isset($validated['resolution_note']) ? trim((string) $validated['resolution_note']) : null;
    $currentQuantity = max(0, min((int) $validated['current_quantity'], (int) $order->quantity));
    $followUpStatus = $qualityStatus === 'good' ? null : ($validated['follow_up_status'] ?? 'warning');
    $checkedAt = now();

    if (
        $qualityStatus === 'good'
        && in_array($previousQualityStatus, ['warning', 'quality_issue'], true)
        && ! $resolutionNote
    ) {
        throw ValidationException::withMessages([
            'resolution_note' => ['Resolution note is required when quality status is restored to good.'],
        ]);
    }

    $qualityChecks[] = [
        'checked_at' => $checkedAt->toIso8601String(),
        'signed_by' => $request->user()?->name ?? 'API',
        'signed_by_user_id' => $request->user()?->getAuthIdentifier(),
        'quality_status' => $qualityStatus,
        'protocol_entry' => $qualityStatus === 'good' ? 'IO' : ($problemType === 'other' ? (string) $problemNote : (string) $problemType),
        'current_quantity' => $currentQuantity,
        'problem_type' => $problemType,
        'problem_note' => $problemType === 'other' ? $problemNote : null,
        'follow_up_status' => $followUpStatus,
        'resolved_from_status' => $qualityStatus === 'good' ? $previousQualityStatus : null,
        'resolution_note' => $qualityStatus === 'good' ? $resolutionNote : null,
    ];

    $newOrderStatus = $order->status;
    $pauseDescription = $order->pause_description;
    $lastPausedAt = $order->last_paused_at;

    if ($followUpStatus === 'stopped' && $order->status === 'in_progress') {
        $newOrderStatus = 'paused';
        $pauseDescription = 'Paused by quality check';
        $lastPausedAt = now();
    }

    $order->update([
        'status' => $newOrderStatus,
        'pause_description' => $pauseDescription,
        'last_paused_at' => $lastPausedAt,
        'current_quantity' => $currentQuantity,
        'production_protocol' => array_merge($existingProtocol, [
            'quality_status' => $qualityStatus,
            'last_quality_check_at' => $checkedAt->toIso8601String(),
            'quality_checks' => $qualityChecks,
        ]),
    ]);

    $protocolEntryNote = $qualityStatus === 'good'
        ? ($resolutionNote ?: 'Quality restored to IO')
        : ($problemType === 'other' ? (string) $problemNote : (string) $problemType);

    $entryMeta = [
        'quality_status' => $qualityStatus,
        'follow_up_status' => $followUpStatus,
        'problem_type' => $problemType,
        'resolution_note' => $qualityStatus === 'good' ? $resolutionNote : null,
    ];

    $order = $order->appendProtocolEntry($currentQuantity,
        'quality_check',
        $protocolEntryNote,
        $entryMeta,
    );

    return $order->load([
        'machine:id,name,status,problem_description',
        'mould:id,name,number,cavity_count,status,problem_description',
        'product:id,name,code,is_active',
    ]);
});

Route::post('/orders/{id}/protocol-entry', function (Request $request, $id) {
    $validated = $request->validate([
        'current_quantity' => 'required|integer|min:0',
        'entry_type' => 'required|in:quality_check,order_status,machine_issue,mould_issue,product_status,protocol_update,note,parameter_change,machine_status,mould_status,production_update,assignment,task_started,issue',
        'note' => 'nullable|string|max:2000',
        'meta' => 'nullable|array',
    ]);

    $order = Order::findOrFail($id);
    $order = $order->appendProtocolEntry((int) $validated['current_quantity'],
        $validated['entry_type'],
        $validated['note'] ?? null,
        $validated['meta'] ?? [],
    );

    return $order->load([
        'machine:id,name,status,problem_description',
        'mould:id,name,number,cavity_count,status,problem_description',
        'product:id,name,code,is_active',
    ]);
});

Route::post('/products/{id}/status', function (Request $request, $id) {
    $validated = $request->validate([
        'status' => 'required|in:active,inactive',
    ]);

    $product = Product::findOrFail($id);

    $product->update([
        'is_active' => $validated['status'] === 'active',
    ]);

    Order::query()
        ->where('product_id', $product->id)
        ->whereIn('status', ['in_progress', 'paused'])
        ->get()
        ->each(fn (Order $order) => $order->appendProtocolEntry((int) $order->current_quantity,
            'product_status',
            'Product status changed to '.$validated['status'].'.'
        ));

    return $product->load([
        'material:id,name,code',
        'color:id,name,code',
        'additives:id,name,code',
        'moulds:id,name,number',
    ]);
});
