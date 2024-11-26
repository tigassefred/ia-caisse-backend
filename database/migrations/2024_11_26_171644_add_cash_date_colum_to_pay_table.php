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
        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('cash_date')->nullable()->after('cash_in');
        });

        $payments = \App\Models\Payment::query()->get();
        foreach ($payments as $pay){
            $pay->cash_date = $pay->created_at;
            $pay->save();
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->timestamp('cash_date')->default(now())->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('cash_date');
        });
    }
};
