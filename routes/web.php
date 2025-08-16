<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\TopicController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/not-found', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('dashboard');
    Route::get('/{username}/thread/{uuid}', [HomeController::class, 'show'])->name('threads.show');
    Route::post('/thread/store', [HomeController::class, 'storeThread'])->name('thread.store');

    Route::get('topics', [TopicController::class, 'index'])->name('get.topics');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
