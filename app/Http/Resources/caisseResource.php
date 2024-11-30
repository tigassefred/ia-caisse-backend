<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class caisseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return mixed[]|string
     */
    private function displayPrice(String $amount){
        return  number_format($amount, 0, ',', ' ') . "CFA";
    }
    public function toArray(Request $request): array
    {
        return array(
            'id' => $this->id,
            'enabled'=> !($this->status === 0),
            'transaction' => $this->displayPrice($this->transaction),
            'periode' => Carbon::parse($this->start_date)->format('d/m/Y H:i') .
                ' au ' . Carbon::parse($this->end_date)->format('d/m/Y H:i'),
            'encaissement' => $this->displayPrice($this->encaissement),
            'creance' => $this->displayPrice($this->creance),
            '10yaar' => $this->displayPrice($this->_10yaar),
            'magasin' => $this->displayPrice($this->magazin),
            'rembourssement' => $this->displayPrice($this->remboursement),
            'versement' => $this->displayPrice(
                floatval($this->versement_10yaar) + floatval($this->versement_magasin)
            ),
            'situation' => "0k",

        );
    }
}
