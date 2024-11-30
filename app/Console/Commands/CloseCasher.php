<?php

namespace App\Console\Commands;

use App\Models\Caisse;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CloseCasher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:close-casher';

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
        DB::beginTransaction();
        try {
            $caisse_en_cours = Caisse::query()->where('status', 1)->first();
            $caisse_en_cours->status = 0;
            $caisse_en_cours->end_date = Carbon::now();
            $caisse_en_cours->save();
            Caisse::create([
                'start_date' => Carbon::now(),
                'end_date' => Carbon::now()->addDays(),
                'transaction' => "0",
                'encaissement' => "0",
                'creance' => "0",
                'remboursement' => "0",
                '_10yaar' => "0",
                'magazin' => "0",
                'versement_magasin' => "0",
                'versement_10yaar' => "0",
                'id' => Str::uuid()->toString(),
                "status"=>1
            ]);
            DB::commit();
        }catch (\Exception $exception){
            DB::rollBack();
        }
    }
}
