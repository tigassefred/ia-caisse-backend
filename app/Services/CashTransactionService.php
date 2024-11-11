<?php

namespace App\Services;

use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\CashTransactionItem;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CashTransactionService
{

    protected ?CashTransaction $cashTransaction = null;
     public function __construct(?String $id = null){
         if($id){
              $this->cashTransaction = CashTransaction::find($id);
         }
     }

     public function createCashTransaction(Array $cashTransaction): void
     {
         $newCashTransaction = new CashTransaction();
         $newCashTransaction->name = $cashTransaction['name'];
         $newCashTransaction->build_value  = $cashTransaction['valeur_facture'];
         $newCashTransaction ->build_value_reduction =  $cashTransaction['valeur_apres_reduction'];
         $newCashTransaction ->reduction_value =  $cashTransaction['valeur_reduction'];
         $newCashTransaction ->payed_value =  $cashTransaction['somme_verser'];
         $newCashTransaction ->is10Yaars = $cashTransaction['is10Yaars'];
         $newCashTransaction ->cash_in = $cashTransaction['isPayDiff'];
         $newCashTransaction ->user_id  = User::query()->first()->id;   //Auth::user()->id;
         $newCashTransaction ->comments = $cashTransaction['comments'];
         $newCashTransaction ->reliquat = $cashTransaction['reliquat'];
         $newCashTransaction->commercial = $cashTransaction['commercial'];
          $this->saveTransaction($newCashTransaction);
          $this->cashTransaction = $newCashTransaction;
     }

     private function saveTransaction(CashTransaction $cashTransaction ): void
     {
          CashSessionService::ASSOCIATE_TRANSACTION($cashTransaction);
     }

     public function setTransactionItems(Array $items): void
     {
         foreach($items as $item){
              $trans_items= new CashTransactionItem();
              $trans_items->item_id = $item['uuid'];
              $trans_items->groupage = $item['name'];
              $trans_items->designation = $item['designation'];
              $trans_items->type = $item['type'];
             $trans_items->cbm = $item['cbm'];
             $trans_items->cashTransaction()->associate($this->cashTransaction);
             $trans_items->save();
         }
     }

    /**
     * @return mixed
     */
    public function getCashTransaction(): mixed
    {
        return $this->cashTransaction;
    }
}
