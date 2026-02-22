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
        Schema::create('searchable_content', function (Blueprint $table) {
            $table->id();
            $table->string('searchable_type', 100);
            $table->bigInteger('searchable_id');
            $table->text('title_ms')->nullable();
            $table->text('title_en')->nullable();
            $table->text('content_ms')->nullable();
            $table->text('content_en')->nullable();
            $table->string('url_ms', 2048)->nullable();
            $table->string('url_en', 2048)->nullable();
            $table->integer('priority')->default(0);
            $table->timestampTz('updated_at')->nullable();

            $table->unique(['searchable_type', 'searchable_id']);
        });

        // PostgreSQL-only: add generated tsvector columns and GIN indexes
        if (DB::getDriverName() === 'pgsql') {
            DB::statement("
                ALTER TABLE searchable_content
                ADD COLUMN tsvector_ms TSVECTOR GENERATED ALWAYS AS (
                    to_tsvector('simple', COALESCE(title_ms,'') || ' ' || COALESCE(content_ms,''))
                ) STORED
            ");

            DB::statement("
                ALTER TABLE searchable_content
                ADD COLUMN tsvector_en TSVECTOR GENERATED ALWAYS AS (
                    to_tsvector('english', COALESCE(title_en,'') || ' ' || COALESCE(content_en,''))
                ) STORED
            ");

            DB::statement('CREATE INDEX idx_search_ms ON searchable_content USING GIN(tsvector_ms)');
            DB::statement('CREATE INDEX idx_search_en ON searchable_content USING GIN(tsvector_en)');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('searchable_content');
    }
};
