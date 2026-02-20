<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memorial_notices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('type', 40);
            $table->text('excerpt')->nullable();
            $table->longText('content');

            $table->string('province', 120)->nullable();
            $table->string('city', 120)->nullable();
            $table->date('born_date')->nullable();
            $table->date('died_date')->nullable();

            $table->string('funeral_company_name')->nullable();
            $table->string('funeral_company_contact')->nullable();
            $table->string('funeral_company_phone', 60)->nullable();
            $table->string('funeral_company_email')->nullable();
            $table->string('funeral_company_url')->nullable();

            $table->string('next_of_kin_first_name', 120)->nullable();
            $table->string('next_of_kin_last_name', 120)->nullable();
            $table->string('next_of_kin_email')->nullable();
            $table->string('condolence_url')->nullable();

            $table->string('status', 30)->default('published');
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memorial_notices');
    }
};
