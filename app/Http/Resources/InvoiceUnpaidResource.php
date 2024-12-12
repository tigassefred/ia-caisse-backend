<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class InvoiceUnpaidResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $payment = $this->Payments->where('deleted', 0);
        Log::info($this->invoice_id);
        return [
            'id' => $this->id,
            'invoice_id' => $this->invoice_id,
            'name' => $this->name,
            'commercial' => $this->Commercial,
            'discount' => $this->discount,
            'versement' => $payment->sum("amount"),
            'reste_payer' => $this->montant_net - $payment->sum("amount"),
            'created_at' => $this->created_at,
            'date' => Carbon::parse($this->created_at)->format("d/m/Y"),
            'caisse_id'=>$this->caisse_id,

        ];
    }
}
