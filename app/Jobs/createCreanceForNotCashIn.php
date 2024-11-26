<?php

namespace App\Jobs;

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
        $payment = Payment::query()->where("cash_in" , 0)->get();
        foreach ($payment as $p) {
            $p->cash_in = 1;
            $p->save();
        }
    }
}
