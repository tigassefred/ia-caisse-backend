<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class createCreanceForNotCashIn implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
       // Lo
    //        $payment = Payment::query()->where("cash_in", 0)->get();
    //        foreach ($payment as $p) {
    //            $p->cash_in = 1;
    //            $p->reliquat = intval($p->reliquat) + intval($p->amount);
    //            $p->amount = 0;
    //            $p->save();
    //            $inv = Invoice::query()->where("id", $p->invoice_id)->first();
    //            $inv->is_sold = 0;
    //            $inv->save();
    //        }
    }
}
