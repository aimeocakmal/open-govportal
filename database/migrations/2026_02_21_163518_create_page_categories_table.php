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
        Schema::create('page_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('page_categories')->nullOnDelete();
            $table->string('name_ms', 255);
            $table->string('name_en', 255)->nullable();
            $table->string('slug', 300)->unique();
            $table->text('description_ms')->nullable();
            $table->text('description_en')->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_page_categories_parent ON page_categories (parent_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_categories');
    }
};
