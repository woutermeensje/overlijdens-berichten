<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memorial_notices', function (Blueprint $table) {
            // Contactgegevens van degene die het bericht plaatst; niet publiek zichtbaar.
            $table->string('placer_name', 120)->nullable()->after('condolence_url');
            $table->string('placer_email', 190)->nullable()->after('placer_name');
            $table->string('placer_phone', 60)->nullable()->after('placer_email');
        });
    }

    public function down(): void
    {
        Schema::table('memorial_notices', function (Blueprint $table) {
            $table->dropColumn(['placer_name', 'placer_email', 'placer_phone']);
        });
    }
};
