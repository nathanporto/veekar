<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('product_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_history_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['entrada', 'saida']);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 10, 2)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->date('movement_date');
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_movements');
    }
};
