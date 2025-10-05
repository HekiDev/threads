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
    Route::post('/thread/comment/{uuid}', [HomeController::class, 'storeComment'])->name('thread.store.comment');
    Route::post('/thread/comment/{comment}/reply', [HomeController::class, 'storeCommentReply'])->name('thread.store.comment-reply');
    Route::post('/thread/comment/{reply}/sub-reply', [HomeController::class, 'storeCommentSubReply'])->name('thread.store.comment-sub-reply');

    Route::post('/thread/react/{uuid}', [HomeController::class, 'storeReaction'])->name('thread.store.reaction');
    Route::post('/thread/comment/{comment}/react', [HomeController::class, 'storeCommentReaction'])->name('thread.store.comment-reaction');
    Route::post('/thread/comment/{reply}/sub-reply/react', [HomeController::class, 'storeCommentSubReplyReaction'])->name('thread.store.sub-comment-reaction');

    Route::get('topics', [TopicController::class, 'index'])->name('get.topics');

    Route::get('/thread/comment/{comment_id}/replies', [HomeController::class, 'getMoreReplies'])->name('thread.more-replies');

    Route::post('/user/{user}/follow', [HomeController::class, 'followUser'])->name('user.follow');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/chat.php';
