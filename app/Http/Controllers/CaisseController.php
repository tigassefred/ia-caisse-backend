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
use Illuminate\Support\Facades\Validator;

class CaisseController extends Controller
{



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


    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return response()->json(['message' => 'Hello World']);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'valeur_reduction' => 'required|integer|min:0',
            'reliquat' => 'required|integer|min:0',
            'comments' => 'nullable|string',
            'is10Yaars' => 'required|boolean',
            'isPayDiff' => 'required|boolean',
            'valeur_facture' => 'required|integer|min:0',
            'valeur_apres_reduction' => 'required|integer|min:0',
            'somme_verser' => 'required|integer|min:0',
            'commercial' => 'required|uuid',
            'Paniers' => 'required|array|min:1',
            'Paniers.*.uuid' => 'required|integer',
            'Paniers.*.qte' => 'required|integer|min:0',
            'Paniers.*.name' => 'required|string|max:255',
            'Paniers.*.designation' => 'required|string|max:255',
            'Paniers.*.type' => 'required|string|max:50',
            'Paniers.*.cbm' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()
            ], 400);
        }


        $caisse = new Caisse();



        return response()->json(['message' => 'Hello World']);
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
