<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('search_overrides', function (Blueprint $table) {
            $table->id();
            $table->string('query', 500);
            $table->string('title_ms', 500)->nullable();
            $table->string('title_en', 500)->nullable();
            $table->string('url', 2048)->nullable();
            $table->text('description_ms')->nullable();
            $table->text('description_en')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('search_overrides');
    }
};
