<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'invoice_id' => $this->invoice_id,
            'name' => $this->name,
            'amount' => $this->amount,
            'discount' => $this->discount,
            'commercial' => $this->Commercial,
            'is_10Yaar' => $this->is_10Yaar,
            'is_sold' => $this->is_sold,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'montant_net' => $this->montant_net,
            'payments'=>$this->Payments

        ];
    }
}
