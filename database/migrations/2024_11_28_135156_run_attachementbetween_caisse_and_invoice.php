<?php

use App\Models\Caisse;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $date = \Illuminate\Support\Carbon::now()->subDays(20);
        $diff = $date->diffInDays(\Illuminate\Support\Carbon::now()->addDay());
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        for ($i = 0; $i <= intval($diff) + 1; $i++) {
            $startDate = $date->copy()->startOfDay(); // Début de la journée
            $endDate = $date->copy()->endOfDay();     // Fin de la journée
            $id = Str::uuid()->toString();
            Caisse::insert([
                'start_date' => $date->copy()->startOfDay(),
                'end_date' => $date->copy()->endOfDay(),
                'transaction' => "0",
                'encaissement' => "0",
                'creance' => "0",
                'remboursement' => "0",
                '_10yaar' => "0",
                'magazin' => "0",
                'versement_magasin' => "0",
                'versement_10yaar' => "0",
                'id' => $id
            ]);


            $payment = \App\Models\Invoice::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

            foreach ($payment as $pay){
                $pay->caisse_id = $id;
                $pay->save();
            }


            $invCount = \App\Models\Invoice::whereBetween('created_at', [$startDate, $endDate ])->count();

            if ($invCount === 0) {

                Caisse::where('id', $id)->delete();

            }

            $date->addDay();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

         Schema::table('invoices', function (Blueprint $table) {
             $table->uuid('caisse_id')->nullable(false)->change();
         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('invoices', function (Blueprint $table) {
             $table->uuid('caisse_id')->nullable()->change();
         });

         \App\Models\Invoice::query()->update(['caisse_id' => null]);
    }
};
