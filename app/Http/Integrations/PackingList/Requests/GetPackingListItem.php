<?php

namespace App\Http\Integrations\PackingList\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class GetPackingListItem extends Request
{
    /**
     * The HTTP method of the request
     */
    protected Method $method = Method::GET;

    /**
     * The endpoint for the request
     */
    public function resolveEndpoint(): string
    {
        return '/get-packing-list-items';
    }

    public function defaultQuery(): array
    {
        return [
            "type"=>"FOR_PAIEMENT",
        ];
    }
}
