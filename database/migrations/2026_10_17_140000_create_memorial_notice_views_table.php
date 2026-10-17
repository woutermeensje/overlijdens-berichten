<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Eén rij per bericht per dag, zodat de tabel klein blijft.
        Schema::create('memorial_notice_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memorial_notice_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views')->default(0);

            $table->unique(['memorial_notice_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorial_notice_views');
    }
};
