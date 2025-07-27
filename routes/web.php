<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/not-found', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/{username}/thread/{uuid}', [HomeController::class, 'show'])->name('threads.show');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
