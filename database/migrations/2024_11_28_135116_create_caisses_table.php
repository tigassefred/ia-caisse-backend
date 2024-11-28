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
        Schema::create('caisses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dateTime('start_date')->unique();
            $table->dateTime('end_date')->unique();
            $table->string('transaction');
            $table->string('encaissement');
            $table->string('creance');
            $table->string('remboursement');
            $table->string('10yaar');
            $table->string('magazin');
            $table->string('versement_magasin');
            $table->string('versement_10yaar');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('caisses');
    }
};
