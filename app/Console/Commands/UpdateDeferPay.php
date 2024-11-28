<?php

namespace App\Console\Commands;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateDeferPay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-defer-pay';

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
            $payment = Payment::query()->where("cash_in", 0)
                ->get();

            foreach ($payment as $p) {
                $p->cash_in = 1;
                $p->reliquat = strval((intval($p->reliquat) + intval($p->amount)));
                $p->amount = 0;
                $p->save();

                $inv = Invoice::query()->find($p->invoice_id);
                if ($inv) {
                    $inv->is_sold = 0;
                    $inv->save();
                } else {
                    $this->error("Invoice not found for payment ID: " . $p->id);
                    throw new Exception("Invoice not found for payment ID: " . $p->id);
                }
            }

           DB::commit();
            $this->info($payment->count() . " deferment payments updated.");
        } catch (Exception $e) {
            DB::rollBack();
            $this->error("An error occurred: " . $e->getMessage());
            // Optionally log the exception
            Log::error('Error updating payments: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }
    }

}
