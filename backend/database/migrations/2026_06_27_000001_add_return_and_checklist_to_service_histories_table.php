<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->date('return_date')->nullable()->after('notes');
            $table->string('return_reason')->nullable()->after('return_date');
            $table->json('entry_checklist')->nullable()->after('return_reason');
        });
    }

    public function down(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->dropColumn(['return_date', 'return_reason', 'entry_checklist']);
        });
    }
};
