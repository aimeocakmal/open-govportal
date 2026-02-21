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
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->cascadeOnDelete();
            $table->string('label_ms', 255);
            $table->string('label_en', 255)->nullable();
            $table->string('url', 2048)->nullable();
            $table->string('route_name', 255)->nullable();
            $table->jsonb('route_params')->nullable();
            $table->string('icon', 100)->nullable();
            $table->smallInteger('sort_order')->default(0);
            $table->string('target', 10)->default('_self');
            $table->boolean('is_active')->default(true);
            $table->jsonb('required_roles')->nullable();
            $table->smallInteger('mega_columns')->default(1);
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_menu_items_menu ON menu_items (menu_id, sort_order)');
            DB::statement('CREATE INDEX idx_menu_items_parent ON menu_items (parent_id)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};
