<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::prefix('chats')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chat.index');
        Route::get('/{chat}/messages', [ChatController::class, 'getChatMessages'])->name('chat.messages');
        Route::get('/{chat_id}/older-messages', [ChatController::class, 'getOlderMessages'])->name('chat.older-messages');
        Route::get('search/users', [ChatController::class, 'searchChatMembers'])->name('chat.search.users');

        Route::post('store', [ChatController::class, 'storeChat'])->name('chat.store');
        Route::post('/{chat}/store-message', [ChatController::class, 'storeChatMessage'])->name('chat.store-message');
        Route::post('/block-user', [ChatController::class, 'blockUser'])->name('chat.block-user');
    });
});
