<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minister_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255);
            $table->string('title_ms', 500)->nullable();
            $table->string('title_en', 500)->nullable();
            $table->text('bio_ms')->nullable();
            $table->text('bio_en')->nullable();
            $table->string('photo', 2048)->nullable();
            $table->boolean('is_current')->default(true);
            $table->date('appointed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minister_profiles');
    }
};
