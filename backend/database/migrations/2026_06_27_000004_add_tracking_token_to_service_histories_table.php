<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->string('tracking_token', 32)->nullable()->unique()->after('estimated_delivery');
        });

        // Gera token para registros existentes
        DB::table('service_histories')->whereNull('tracking_token')->orderBy('id')->each(function ($row) {
            DB::table('service_histories')
                ->where('id', $row->id)
                ->update(['tracking_token' => Str::random(32)]);
        });
    }

    public function down(): void
    {
        Schema::table('service_histories', function (Blueprint $table) {
            $table->dropColumn('tracking_token');
        });
    }
};
