<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hero_banners', function (Blueprint $table) {
            $table->id();
            $table->string('title_ms', 500)->nullable();
            $table->string('title_en', 500)->nullable();
            $table->text('subtitle_ms')->nullable();
            $table->text('subtitle_en')->nullable();
            $table->string('image', 2048);
            $table->string('image_alt_ms', 500)->nullable();
            $table->string('image_alt_en', 500)->nullable();
            $table->string('cta_label_ms', 200)->nullable();
            $table->string('cta_label_en', 200)->nullable();
            $table->string('cta_url', 2048)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_hero_active_order ON hero_banners(is_active, sort_order)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('hero_banners');
    }
};
