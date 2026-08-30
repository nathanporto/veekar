<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quiz_leads', function (Blueprint $table) {
            $table->unsignedTinyInteger('recovery_step')->default(0)->after('user_id');
            $table->timestamp('recovery_last_sent_at')->nullable()->after('recovery_step');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_leads', function (Blueprint $table) {
            $table->dropColumn(['recovery_step', 'recovery_last_sent_at']);
        });
    }
};
