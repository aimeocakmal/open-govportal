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
        if (DB::getDriverName() === 'pgsql') {
            Schema::ensureVectorExtensionExists();
        }

        Schema::create('content_embeddings', function (Blueprint $table) {
            $table->id();
            $table->string('embeddable_type');
            $table->unsignedBigInteger('embeddable_id');
            $table->smallInteger('chunk_index')->default(0);
            $table->string('locale', 5);
            $table->text('content');

            if (DB::getDriverName() === 'pgsql') {
                $dimension = (int) env('AI_EMBEDDING_DIMENSION', 1536);
                $table->vector('embedding', dimensions: $dimension);
            } else {
                // SQLite fallback for tests — no vector type available
                $table->text('embedding')->nullable();
            }

            $table->jsonb('metadata')->nullable();
            $table->timestampsTz();

            $table->unique(
                ['embeddable_type', 'embeddable_id', 'chunk_index', 'locale'],
                'ce_morph_chunk_locale_unique'
            );
            $table->index(
                ['embeddable_type', 'embeddable_id'],
                'idx_ce_morphic'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_embeddings');
    }
};
