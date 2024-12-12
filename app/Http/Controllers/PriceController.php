<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Price;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Price::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "balle" => 'required',
            'colis' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'failled'
            ]);
        }
        Price::query()->create($request->all());
        return response()->json([
            'status' => 'success',
            'message' => ''
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $price)
    {
        Price::query()->update(['is_deleted', true]);
        Price::query()->where('id', $price)
            ->update(['is_deleted', true]);

        return response()->json([
            'status' => 'success'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Price $price)
    {
        $occurence = Payment::query()->where('price_id', $price)->count();
        if ($occurence > 0) {
            return response()->json(['status' => 'failled', 'message' => 'impossible de supprimer cette mention, elle a été déjà utilisée']);
        }
        Price::query()->where('id', $price)->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Suppression de la mention',
        ]);
    }
}
