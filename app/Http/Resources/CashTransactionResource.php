<?php

namespace App\Http\Resources;

use App\Models\CashSession;
use App\Models\CashTransactionItem;
use App\Models\Commercial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $items  =CashTransactionItem::where('cash_transaction_id' , $this->id)->get();
        return [
            'id'=>$this->id,
            'cash_in_id'=>$this->cash_in_id,
            'name'=>$this->name,
            'build_value'=>$this->build_value,
            'build_value_reduction'=>$this->build_value_reduction,
            'reduction_value'=>$this->reduction_value,
            'reliquat'=>$this->reliquat,
            'payed_value'=>$this->payed_value,
            'comments'=>$this->comments,
            'is10Yaars'=>$this->is10Yaars,
            'commercial'=>Commercial::find($this->commercial),
            'cash_in'=>$this->cash_in,
            'user'=>User::find($this->user_id),
            'cash_session'=>CashSession::find($this->cash_session_id),
            'created_at'=>$this->created_at,
            'updated_at'=>$this->updated_at,
            'Items'=>$items,
            'cbm'=>$items->sum('cbm') ?? 0,
            'type'=> "Rembourssement"
        ];
    }
}
