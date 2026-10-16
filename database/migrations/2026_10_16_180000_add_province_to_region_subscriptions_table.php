<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('region_subscriptions', function (Blueprint $table) {
            $table->string('province', 60)->nullable()->after('region')->index();
        });
    }

    public function down(): void
    {
        Schema::table('region_subscriptions', function (Blueprint $table) {
            $table->dropColumn('province');
        });
    }
};
