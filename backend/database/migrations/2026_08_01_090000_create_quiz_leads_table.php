<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone');
            $table->string('business_type')->nullable();
            $table->string('cars_per_month')->nullable();
            $table->string('current_control')->nullable();
            $table->string('main_pain')->nullable();
            $table->enum('chosen_path', ['discount', 'trial'])->nullable();
            $table->timestamp('accepted_terms_at')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_leads');
    }
};
