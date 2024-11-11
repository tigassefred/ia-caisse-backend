<?php

namespace App\Http\Resources;

use App\Models\Commercial;
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

        return [
            'id'=>$this->id,
            'amount'=>$this->amunt,
            'reliquat'=>$this->reliquat,
            'invoice'=>$this->invoice,
            'commercial'=>Commercial::query()->find($this->invoixe->commercial_id),
        ];
    }
}
