<?php

namespace App\Http\Controllers;

use App\Http\Integrations\PackingList\Requests\GetPackingListItem;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Resources\caisseResource;
use App\Models\Caisse;
use App\Models\CaisseItem;
use App\Models\Commercial;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CaisseController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $caisse = Caisse::query()->orderBy('start_date', 'desc')->get();
        return CaisseResource::collection($caisse);
    }

    public function latest()
    {
        $caisse = Caisse::query()->orderBy('start_date', 'desc')->limit(3)->get();
        $casher = [];
        foreach ($caisse as $caisse) {
            array_push($casher, [
                'id' => $caisse->id,
                'start_date' => "Caisse : ".Carbon::parse($caisse->start_date)->format('M d'),
                'status' => $caisse->status,
            ]);
        }
        return response()->json($casher);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

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
