<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->unique()->after('phone');
            $table->boolean('is_active')->default(true)->after('admin_permissions');
            $table->boolean('login_blocked')->default(false)->after('is_active');
            $table->timestamp('account_verified_at')->nullable()->after('login_blocked');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'is_active',
                'login_blocked',
                'account_verified_at',
            ]);
        });
    }
};
