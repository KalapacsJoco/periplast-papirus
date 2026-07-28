<?php

use App\Models\Machine;
use App\Models\Mould;
use App\Models\Order;
use App\Models\Product;
use App\Models\Error;
use Illuminate\Support\Facades\Route;

Route::get('/machines', function () {
    return Machine::query()
        ->select(['id', 'name', 'type', 'open_mm', 'screw_diameter_cm', 'status', 'problem_description'])
        ->orderBy('name')
        ->get();
});

// Update machine status
Route::post('/machines/{id}/status', function (Illuminate\Http\Request $request, $id) {
    $validated = $request->validate([
        'status' => 'required|in:active,stopped,warning,broken',
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

Route::post('/moulds/{id}/status', function (Illuminate\Http\Request $request, $id) {
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
        ->select(['id', 'order_number', 'machine_id', 'mould_id', 'product_id', 'cycle_time', 'net_weight', 'gross_weight', 'color_percentage', 'status'])
        ->with([
            'machine:id,name,status,problem_description',
            'mould:id,name,number,status,problem_description',
            'product:id,name,code,is_active',
        ])
        ->orderBy('order_number')
        ->get();
});

Route::post('/orders/{id}/status', function (Illuminate\Http\Request $request, $id) {
    $validated = $request->validate([
        'status' => 'required|in:waiting,in_progress,paused,completed',
        'pause_description' => 'nullable|string',
    ]);

    $order = Order::findOrFail($id);
    $status = $validated['status'];
    $pauseDescription = $status === 'paused'
        ? ($validated['pause_description'] ?? null)
        : null;

    $order->update([
        'status' => $status,
        'pause_description' => $pauseDescription,
    ]);

    return $order->load(['machine:id,name', 'mould:id,name,number', 'product:id,name,code']);
});

Route::post('/products/{id}/status', function (Illuminate\Http\Request $request, $id) {
    $validated = $request->validate([
        'status' => 'required|in:active,inactive',
    ]);

    $product = Product::findOrFail($id);

    $product->update([
        'is_active' => $validated['status'] === 'active',
    ]);

    return $product->load([
        'material:id,name,code',
        'color:id,name,code',
        'additives:id,name,code',
        'moulds:id,name,number',
    ]);
});
