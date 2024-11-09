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
        Schema::create('cash_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('cash_in_id')->unique();
            $table->string('name');
            $table->string("build_value")->default("0");
            $table->string("build_value_reduction")->default("0");
            $table->string("reduction_value")->default("0");
            $table->string('reliquat');
            $table->integer('payed_value');
            $table->string('comments')->nullable();
            $table->boolean('is10Yaars');
            $table->uuid('commercial');
            $table->boolean('cash_in')->default(false);
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('cash_session_id')->constrained('cash_sessions')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_transactions');
    }
};
