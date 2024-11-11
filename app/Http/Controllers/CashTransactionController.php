<?php

namespace App\Http\Controllers;

use App\Http\Integrations\PackingList\Requests\GetPackingListItem;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Requests\CashTransactionRequest;
use App\Http\Resources\CashTransactionResource;
use App\Models\CashTransaction;
use App\Models\CashTransactionItem;
use App\Models\Commercial;
use App\Models\Price;
use App\Services\CashTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CashSession;

class CashTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $cashSession_id = CashSession::query()->where('status','waiting')->first();
        $cashTransactions = CashTransaction::query()->where('cash_session_id',$cashSession_id->id)
            ->OrderBy('created_at','desc')->get();

        return CashTransactionResource::collection($cashTransactions);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CashTransactionRequest $request): \Illuminate\Http\JsonResponse
    {
           $validatorData = $request->validated();
           DB::beginTransaction();
           try {
                 $transaction = new CashTransactionService();
                 $transaction->createCashTransaction($validatorData);
                 $item = $validatorData['Paniers'];
                 $transaction->setTransactionItems($item);
                 DB::commit();
                 return response()->json([
                     "status"=>'success',
                     'message' => 'Le bon de caisse a ete genere avec success'
                     ], 200);
           } catch (\Exception $e) {
               DB::rollBack();
               Log::info("***************************************************");
               Log::error($e->getCode());
               Log::error($e->getLine());
               Log::error($e->getMessage());
               Log::info("***************************************************");
               return response()->json(
                   [
                       'message' => "L'enregistrement a été interrompu. Veuillez vérifier vos informations s'il vous plaît.!",
                       'status'=>'failed'
                   ], status: 400);
           }
     }

    /**
     * Display the specified resource.
     */
    public function show(CashTransaction $cashTransaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CashTransaction $cashTransaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, CashTransaction $cashTransaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CashTransaction $cashTransaction)
    {
        //
    }

    public function dashboard()
    {
        $connector = new StockConnector();
        $request = new GetPackingListItem();
        $response = $connector->send($request);
        $packingList = $response->array();

        $caisse_item_ids =  CashTransactionItem::all()->pluck('id');

        if ($caisse_item_ids->isEmpty()) {
            $missingItems =  $packingList['data'];
        } else {
            $missingItems = array_filter($packingList['data'], function ($item) use ($caisse_item_ids) {
                return isset($item['uuid']) && !$caisse_item_ids->contains($item['uuid']);
            });
        }
        $commercial =  Commercial::where('is_deleted', 0)->get();
        $Price =  Price::where('is_deleted', 0)->select('balle', 'colis')->get();

        return response()->json([
            'commercials' => $commercial,
            'packingList' => $missingItems,
            'price' => $Price
        ]);
    }

}
