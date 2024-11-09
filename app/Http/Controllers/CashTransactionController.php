<?php

namespace App\Http\Controllers;

use App\Http\Integrations\PackingList\Requests\GetPackingListItem;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Requests\CashTransactionRequest;
use App\Models\CaisseItem;
use App\Models\CashSession;
use App\Models\CashTransaction;
use App\Models\Commercial;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CashTransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(CashTransactionRequest $request)
    {
           $validatorData = $request->validated();
           DB::beginTransaction();
           try {
               $cashSession =  CashSession::where('status', 'pending')->first();
               $cashTransaction = new CashTransaction();
               $cashTransaction->name = $validatorData['name'];
               
            //    $cashTransaction->valeur_reduction = $validatorData['valeur_reduction'];
            //    $cashTransaction->reliquat = $validatorData['reliquat'];
            //    $cashTransaction->comments = $validatorData['comments'];
            //    $cashTransaction->is10Yaars = $validatorData['is10Yaars'];
            //    $cashTransaction->isPayDiff = $validatorData['isPayDiff'];
            //    $cashTransaction->valeur_facture = $validatorData['valeur_facture'];
            //    $cashTransaction->valeur_apres_reduction = $validatorData['valeur_apres_reduction'];
            //    $cashTransaction->somme_verser = $validatorData['somme_verser'];
            //    $cashTransaction->commercial = $validatorData['commercial'];
            //    $cashTransaction->Paniers = $validatorData['Paniers'];
               $cashTransaction->save();
               DB::commit();
               return response()->json(['message' => 'cash transaction created successfully'], 200);
           
           } catch (\Exception $e) {
               DB::rollBack();
               return response()->json(['message' => $e->getMessage()], 400);
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

        $caisse_item_ids =  CaisseItem::all()->pluck('id');

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
