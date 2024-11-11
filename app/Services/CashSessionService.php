<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\CashTransaction;

class CashSessionService
{

    /**
     * @return CashSession
     */
    public function getCurrentCashSession() : CashSession
    {
        return CashSession::where('status' , 'waiting')->first();
    }

       public static function ASSOCIATE_TRANSACTION(CashTransaction $transaction): void
       {
           $cashSession = CashSession::where('status' , 'waiting')->first();
           $transaction->cashSession()->associate($cashSession);
           $transaction->save();
       }


}
