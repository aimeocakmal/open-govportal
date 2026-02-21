<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('subject', 500)->nullable();
            $table->text('message');
            $table->string('page_url', 2048)->nullable();
            $table->smallInteger('rating')->nullable();
            $table->string('status', 20)->default('new');
            $table->text('reply')->nullable();
            $table->timestampTz('replied_at')->nullable();
            $table->foreignId('replied_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_feedback_status ON feedbacks(status, created_at DESC)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('feedbacks');
    }
};
