<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('operation', 50);
            $table->string('locale', 5)->nullable();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedInteger('prompt_tokens')->nullable();
            $table->unsignedInteger('completion_tokens')->nullable();
            $table->string('provider', 50)->nullable();
            $table->string('model', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
    }
};
