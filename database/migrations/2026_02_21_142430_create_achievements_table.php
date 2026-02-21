<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->string('title_ms', 500);
            $table->string('title_en', 500)->nullable();
            $table->string('slug', 600)->unique();
            $table->text('description_ms')->nullable();
            $table->text('description_en')->nullable();
            $table->date('date');
            $table->string('icon', 2048)->nullable();
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_achievements_date ON achievements(date DESC)');
            DB::statement('CREATE INDEX idx_achievements_status ON achievements(status, is_featured)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('achievements');
    }
};
