<?php

namespace App\Http\Controllers;

use App\Http\Integrations\GetUsersRequest\Requests\GetUserRequest;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Resources\CreanceResource;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;

class CreanceController extends Controller
{
    public function index(){
        $invoice = Invoice::query()->where("is_sold" , false)->get();
       return CreanceResource::collection($invoice);
    }
}
