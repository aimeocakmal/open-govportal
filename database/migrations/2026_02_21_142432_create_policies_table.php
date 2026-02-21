<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('policies', function (Blueprint $table) {
            $table->id();
            $table->string('title_ms', 500);
            $table->string('title_en', 500)->nullable();
            $table->string('slug', 600)->unique();
            $table->text('description_ms')->nullable();
            $table->text('description_en')->nullable();
            $table->string('category', 100)->nullable();
            $table->string('file_url', 2048)->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->integer('download_count')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestampTz('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestampsTz();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_policies_category ON policies(category)');
            DB::statement('CREATE INDEX idx_policies_status ON policies(status, published_at DESC)');
            DB::statement("CREATE INDEX idx_policies_search ON policies USING GIN(
                to_tsvector('simple', COALESCE(title_ms,'') || ' ' || COALESCE(title_en,''))
            )");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('policies');
    }
};
