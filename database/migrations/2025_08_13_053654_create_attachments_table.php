<?php

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
        Schema::create('attachments', function (Blueprint $table) {
            $table->id();
            $table->longText('url')->nullable();
            $table->longText('file_path')->nullable();
            $table->string('file_extension')->nullable();
            $table->longText('file_name')->nullable();
            $table->enum('type', ['media', 'file'])->default('media');
            $table->morphs('attachable');
            $table->nullableMorphs('userable');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
