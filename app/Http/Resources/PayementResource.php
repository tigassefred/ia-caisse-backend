<?php

namespace App\Http\Resources;

use App\Models\Commercial;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
       $Invoice = Invoice::find($this->invoice_id);
        return array(
            'id'=>$this->id,
            'amount'=>$this->amount,
            'reliquat'=>$this->reliquat,
            "user"=>$this->user,
            'invoice'=>$Invoice,
            'items'=>$Invoice->Items,
            'cbm'=>$Invoice->Items->sum('cbm'),
            "commercial"=> $Invoice->Commercial,
            'cash_in'=>$this->cash_in,
//            'commercial'=>Commercial::query()->find($this->invoixe->commercial_id),
        );
    }
}
