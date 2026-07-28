<?php

use App\Models\Machine;
use App\Models\Mould;
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
        ->with(['product:id,name'])
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

    return $mould->load('product:id,name');
});
