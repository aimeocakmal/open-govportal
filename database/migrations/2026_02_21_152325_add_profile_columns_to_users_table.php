<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('department', 255)->nullable()->after('password');
            $table->string('avatar', 2048)->nullable()->after('department');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->timestampTz('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['department', 'avatar', 'is_active', 'last_login_at']);
        });
    }
};
