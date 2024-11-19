<?php

namespace App\Http\Controllers;

use App\Http\Integrations\GetUsersRequest\Requests\GetUserRequest;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {


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
    public function store(StorePaymentRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        //
    }

    public function versement($id): \Illuminate\Http\JsonResponse
    {
        DB::beginTransaction();
        try {
            $pay = new PaymentService($id);
            $pay->makeVersement();
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => "failed",
                "message" => "L'encaissement a échoué, veuillez réessayer"
            ],501);
        }
        return response()->json([
            "status" => 'success',
            'message' => "La somme a été générée avec succès"
        ]);
    }

}
