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
        Schema::create('cash_transaction_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cash_transaction_id')->constrained('cash_transactions')->cascadeOnDelete();
            $table->uuid('id')->unique();
            $table->string('groupage');
            $table->string('designation');
            $table->string('type');
            $table->string('cbm');
            $table->integer('qte');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transaction_items');
    }
};
