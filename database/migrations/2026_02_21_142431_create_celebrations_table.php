<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('celebrations', function (Blueprint $table) {
            $table->id();
            $table->string('title_ms', 500);
            $table->string('title_en', 500)->nullable();
            $table->string('slug', 600)->unique()->nullable();
            $table->text('description_ms')->nullable();
            $table->text('description_en')->nullable();
            $table->date('event_date')->nullable();
            $table->string('image', 2048)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('celebrations');
    }
};
