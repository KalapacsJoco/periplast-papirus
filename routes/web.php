<?php

use App\Models\Photo;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/photos/{photo}', function (Photo $photo) {
    return Storage::disk($photo->disk)->response($photo->path, $photo->original_name);
})->middleware('signed')->name('photos.show');
