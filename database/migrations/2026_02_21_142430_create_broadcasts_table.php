<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('broadcasts', function (Blueprint $table) {
            $table->id();
            $table->string('title_ms', 500);
            $table->string('title_en', 500)->nullable();
            $table->string('slug', 600)->unique();
            $table->text('content_ms')->nullable();
            $table->text('content_en')->nullable();
            $table->string('excerpt_ms', 1000)->nullable();
            $table->string('excerpt_en', 1000)->nullable();
            $table->string('featured_image', 2048)->nullable();
            $table->string('type', 50)->default('announcement');
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_broadcasts_status_published ON broadcasts(status, published_at DESC)');
            DB::statement("CREATE INDEX idx_broadcasts_search ON broadcasts USING GIN(
                to_tsvector('simple', COALESCE(title_ms,'') || ' ' || COALESCE(title_en,''))
            )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('broadcasts');
    }
};
