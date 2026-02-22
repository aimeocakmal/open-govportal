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
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->string('revisionable_type', 100);
            $table->unsignedBigInteger('revisionable_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('reason', 255)->nullable();
            $table->json('content');
            $table->timestampTz('created_at')->useCurrent();

            $table->index(['revisionable_type', 'revisionable_id']);
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_revisions');
    }
};
