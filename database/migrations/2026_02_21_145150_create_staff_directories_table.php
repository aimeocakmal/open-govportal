<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_directories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('position_ms', 500)->nullable();
            $table->string('position_en', 500)->nullable();
            $table->string('department_ms', 255)->nullable();
            $table->string('department_en', 255)->nullable();
            $table->string('division_ms', 255)->nullable();
            $table->string('division_en', 255)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('fax', 50)->nullable();
            $table->string('photo', 2048)->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_staff_name ON staff_directories(name)');
            DB::statement('CREATE INDEX idx_staff_department ON staff_directories(department_ms, department_en)');
            DB::statement("CREATE INDEX idx_staff_search ON staff_directories USING GIN(
                to_tsvector('simple', COALESCE(name,'') || ' ' || COALESCE(position_ms,'') || ' ' || COALESCE(department_ms,''))
            )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_directories');
    }
};
