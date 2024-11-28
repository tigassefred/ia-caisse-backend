<?php

namespace App\Console\Commands;

use App\Models\Caisse;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Testter extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:testter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date = \Illuminate\Support\Carbon::now()->subDays(10);
        $diff = $date->diffInDays(\Illuminate\Support\Carbon::now());
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        for ($i = 0; $i <= intval($diff) + 1; $i++) {
            $id = Str::uuid()->toString();
            Caisse::insert([
                'start_date' => $date->copy()->startOfDay(),
                'end_date' => $date->copy()->endOfDay(),
                'transaction' => "0",
                'encaissement' => "0",
                'creance' => "0",
                'remboursement' => "0",
                '10yaar' => "0",
                'magazin' => "0",
                'versement_magasin' => "0",
                'versement_10yaar' => "0",
                'id' => $id
            ]);

            $startDate = $date->copy()->startOfDay(); // Début de la journée
            $endDate = $date->copy()->endOfDay();     // Fin de la journée

            $payment = \App\Models\Invoice::query()
               ->whereBetween('created_at', [$startDate, $endDate])
                ->get();

           $this->error($payment->count());

           foreach ($payment as $pay){
               $pay->caisse_id = $id;
               $pay->save();
           }


            $invCount = \App\Models\Invoice::whereBetween('created_at', [$date->startOfDay(), $date->endOfDay()])->count();

            if ($invCount === 0) {

                Caisse::where('id', $id)->delete();

            }

            $date->addDay();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
