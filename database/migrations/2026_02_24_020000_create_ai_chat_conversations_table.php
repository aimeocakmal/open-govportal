<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_id', 100)->index();
            $table->string('ip_address', 45)->nullable();
            $table->string('title', 255)->nullable();
            $table->text('summary')->nullable();
            $table->json('tags')->nullable();
            $table->string('locale', 5)->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->unsignedInteger('total_prompt_tokens')->default(0);
            $table->unsignedInteger('total_completion_tokens')->default(0);
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('ended_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_chat_conversations');
    }
};
