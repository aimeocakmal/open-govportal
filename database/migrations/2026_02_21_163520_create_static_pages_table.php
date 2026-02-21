<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('page_categories')->nullOnDelete();
            $table->string('title_ms', 500);
            $table->string('title_en', 500)->nullable();
            $table->string('slug', 600)->unique();
            $table->text('content_ms')->nullable();
            $table->text('content_en')->nullable();
            $table->string('excerpt_ms', 1000)->nullable();
            $table->string('excerpt_en', 1000)->nullable();
            $table->string('status', 20)->default('draft');
            $table->boolean('is_in_sitemap')->default(true);
            $table->string('meta_title_ms', 255)->nullable();
            $table->string('meta_title_en', 255)->nullable();
            $table->string('meta_desc_ms', 500)->nullable();
            $table->string('meta_desc_en', 500)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_static_pages_slug ON static_pages (slug)');
            DB::statement('CREATE INDEX idx_static_pages_category ON static_pages (category_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('static_pages');
    }
};
