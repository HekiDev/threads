<?php

use App\Models\ThreadComment;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('thread_comment_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ThreadComment::class)->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('reply_id')->nullable();
            $table->foreign('reply_id')->on('thread_comment_replies')->references('id')->onDelete('cascade');
            $table->longText('comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thread_comment_replies');
    }
};
