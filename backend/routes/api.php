<?php

use App\Models\Machine;
use Illuminate\Support\Facades\Route;

Route::get('/machines', function () {
    return Machine::query()
        ->select(['id', 'name', 'type', 'open_mm', 'screw_diameter_cm', 'status'])
        ->orderBy('name')
        ->get();
});
