<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('region')->nullable(); // null = heel Nederland
            $table->string('token')->unique(); // voor uitschrijven
            $table->timestamps();

            $table->unique(['email', 'region']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_subscriptions');
    }
};
