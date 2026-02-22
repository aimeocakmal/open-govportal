<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->string('type', 50)->default('link')->after('section');
            $table->string('icon', 100)->nullable()->after('url');
        });
    }

    public function down(): void
    {
        Schema::table('footer_settings', function (Blueprint $table) {
            $table->dropColumn(['type', 'icon']);
        });
    }
};
