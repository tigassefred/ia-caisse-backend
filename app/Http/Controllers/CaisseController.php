<?php

namespace App\Http\Controllers;

use App\Http\Integrations\PackingList\Requests\GetPackingListItem;
use App\Http\Integrations\Stock\StockConnector;
use App\Models\Caisse;
use App\Models\CaisseItem;
use App\Models\Commercial;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CaisseController extends Controller
{



    public function dashboard()
    {
        $connector = new StockConnector();
        $request = new GetPackingListItem();
        $response = $connector->send($request);
        $packingList = $response->array();

        $caisse_item_ids =  CaisseItem::all()->pluck('id');

        if($caisse_item_ids->isEmpty()){
            $missingItems =  $packingList['data'];
        }else{
            $missingItems = array_filter($packingList['data'], function ($item) use ($caisse_item_ids) {
                return isset($item['uuid']) && !$caisse_item_ids->contains($item['uuid']);
            });
        }
        $commercial=  Commercial::where('is_deleted', 0)->get();
        $Price =  Price::where('is_deleted', 0)->select('balle','colis')->get();
       
        return response()->json(['commercials' => $commercial , 'packingList' => $missingItems,
        'price' => $Price
    ]);
    }


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['message' => 'Hello World']);
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Caisse $caisse)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Caisse $caisse)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Caisse $caisse)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Caisse $caisse)
    {
        //
    }
}
