<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorial_notices', function (Blueprint $table) {
            $table->string('deceased_first_name', 120)->nullable()->after('type');
            $table->string('deceased_last_name', 120)->nullable()->after('deceased_first_name');
            $table->string('photo_url')->nullable()->after('excerpt');
        });
    }

    public function down(): void
    {
        Schema::table('memorial_notices', function (Blueprint $table) {
            $table->dropColumn(['deceased_first_name', 'deceased_last_name', 'photo_url']);
        });
    }
};
