<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memorial_notice_condolences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memorial_notice_id')->constrained()->cascadeOnDelete();
            $table->string('first_name', 120);
            $table->string('last_name', 120);
            $table->string('email');
            $table->text('message');
            $table->timestamps();

            $table->index(['memorial_notice_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorial_notice_condolences');
    }
};
