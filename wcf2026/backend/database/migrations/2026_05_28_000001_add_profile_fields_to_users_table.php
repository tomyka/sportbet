<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->string('time_zone', 64)->default('UTC')->after('display_name');
            $table->string('locale', 8)->default('en')->after('time_zone');
            $table->boolean('is_global_admin')->default(false)->after('locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'time_zone', 'locale', 'is_global_admin']);
        });
    }
};
