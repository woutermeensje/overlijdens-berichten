<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('category', 40)->index();
            $table->string('email', 190);
            $table->string('phone', 40)->nullable();
            $table->string('website', 255)->nullable();
            $table->string('address', 190)->nullable();
            $table->string('city', 120);
            $table->string('province', 60)->nullable()->index();
            $table->string('place_id', 64)->nullable();
            $table->text('about');
            // published = zichtbaar op /organisaties; een beheerder kan hem offline halen.
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
