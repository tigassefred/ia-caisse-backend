<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use \App\Models\Payment;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $cashIn = Payment::query()->where("cash_in", false)->get();
        Payment::query()->update(['cash_in' => false]);
        Payment::query()->whereIn('id', $cashIn->pluck('id'))
            ->update(['cash_in' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $cashIn = Payment::query()->where("cash_in", false)->get();
        Payment::query()->update(['cash_in' => true]);
        Payment::query()->whereIn('id', $cashIn->pluck('id'))
            ->update(['cash_in' => false]);
    }
};
