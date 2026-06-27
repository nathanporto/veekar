<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->string('insurer')->nullable()->after('entry_checklist');
            $table->string('claim_number')->nullable()->after('insurer');
            $table->enum('insurance_status', ['aguardando', 'aprovado', 'recusado'])->nullable()->after('claim_number');
        });
    }

    public function down(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->dropColumn(['insurer', 'claim_number', 'insurance_status']);
        });
    }
};
