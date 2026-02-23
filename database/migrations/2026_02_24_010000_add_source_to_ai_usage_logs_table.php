<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->string('source', 30)->default('admin_editor')->after('operation');
            $table->index('source');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('ai_usage_logs', function (Blueprint $table) {
            $table->dropIndex(['source']);
            $table->dropIndex(['created_at']);
            $table->dropColumn('source');
        });
    }
};
