<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar')->nullable()->after('email');
            $table->string('role')->default('editor')->after('avatar');
            $table->string('phone')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('phone');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['avatar', 'role', 'phone', 'is_active']);
            $table->dropSoftDeletes();
        });
    }
};
