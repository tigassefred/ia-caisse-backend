<?php

namespace App\Http\Resources;

use App\Models\Price;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentReceiptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $item = [];
        $price = Price::query()->where("is_deleted", 0)->first();
        foreach ($this->Invoice->Items as $id) {
            $price_unit = $id->type === 'balle' ? $price->balle : $price->colis;
            array_push($item, [
                "designation" => $id->designation,
                "type" => $id->type,
                "cbm" => $id->cbm,
                "prix_unitaire" => $price_unit,
                "prx_total" => $price_unit * floatval($id->cbm),
                "groupage" => $id->groupage,
            ]);
        }
        $accompt = $this->invoice->Payments->where('cash_in', true)->sum('amount') - $this->amount;
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'reliquat' => $this->reliquat,
            'items' => $item,
            "invoice" => $this->invoice,
            "accompte" => $accompt < 0 ? 0 : $accompt,
            'caissier' => User::query()->where('id', $this->user_id)->first()->short ?? "",

            //'items'=>$this->Invoice->Items,
        ];
    }
}
