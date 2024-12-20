<?php

namespace App\Http\Controllers;

use App\Http\Integrations\GetUsersRequest\Requests\GetUserRequest;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentReceiptResource;
use App\Models\Caisse;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\User;
use App\Services\refacto\InvoiceServices;
use App\Services\refacto\PaymentService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index() {}

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
    public function store($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|exists:invoices,id',
            'amount' => 'required|integer|min:1',
            'date' => 'required',
            'discount' => 'nullable|integer|min:0',
        ]);

        if (floatval(InvoiceServices::GET_RELIQUAT($id, 0)) < floatval($request->amount)) {
            return response()->json([
                'message' => 'Impossible d\'effectuer cet paiement',
                'status' => "failled"
            ], 400);
        }


        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => "failled"
            ], 400);
        }

        DB::beginTransaction();
        try {

            if ($request->discount >  0) {
                InvoiceServices::PLUS_DISCOUNT($id, $request->discount);
            }

            $paymentService = new PaymentService(null);
            $paymentService->setAmount($request->amount);
            $paymentService->setType(2);
            $paymentService->setReliquat(InvoiceServices::GET_RELIQUAT($id, $request->amount));
            $paymentService->setUser(User::query()->first()->id);

            $firstPayment = Payment::query()->where('invoice_id', $id)->where('type', 1)->first();
            $cashInDate = Carbon::parse($firstPayment->cash_in_date);
            $PAY_ID = null;

            if ($cashInDate->isSameDay(Carbon::parse($request->date))) {
                $firstPayment->cash_in = true;
                $firstPayment->cash_in_date = Carbon::now();
                $firstPayment->amount = $firstPayment->amount + $request->amount;
                $firstPayment->reliquat = $paymentService->getNewPay()['reliquat'];
                $firstPayment->save();
            } else {
                $PAY_ID = InvoiceServices::ATTACHE_PAIEMENT($id, $paymentService->getNewPay());
                PaymentService::CASH_IN($PAY_ID, Carbon::parse($request->date)->format('Y-m-d'));
            }

            if (InvoiceServices::GET_RELIQUAT($id, 0) <= 0) {
                InvoiceServices::SOLDED($id);
            }
            DB::commit();
            return response()->json([
                "message" => "Remboursement effectué avec succès",
                'status' => "success"
            ], 200);
        } catch (\Exception $th) {
            dd($th->getMessage());
            DB::rollBack();
            return response()->json([
                "message" => $th->getMessage(),
                'status' => "failled"
            ], 400);
        }
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
    public function update(Request $request, $payment)
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'caisse' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()->first()], 400);
            }

            $pay = Payment::query()->where('id', $payment)->first();
            $inv = Invoice::query()->where('id', $pay->invoice_id)->first();
            $inv->name = $request->name;
            $inv->caisse_id = $request->caisse;
            $pay->cash_in_date = Caisse::query()->where('id', $request->caisse)->first()->start_date;
            $pay->comment = $request->description;
            $pay->save();

            $inv->is_10Yaar = $request->is10Yaars;
            $inv->save();
            $pay->save();
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 400);
        }

        return response()->json([
            'message' => 'Payment updated'
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($paymentId)
    {
        try {

            DB::beginTransaction();
            // Rechercher le paiement
            $pay = Payment::find($paymentId);

            if (!$pay) {
                return response()->json(['error' => 'Payment not found'], 404);
            }

            // Rechercher la facture associée
            $inv = Invoice::find($pay->invoice_id);

            if (!$inv) {
                return response()->json(['error' => 'Invoice not found'], 404);
            }

            if ($pay->type == 2) {
                $pay->deleted = 1;
                $pay->save();
                InvoiceServices::UNSOLDED($pay->invoice_id);
            }
            if ($pay->type == 1) {
                $inv->is_deleted = 1;
                $inv->save();
                Payment::where('invoice_id', $pay->invoice_id)->update(['deleted' => 1]);
            }

            DB::commit();
            return response()->json(['messaage' => 'Payment deleted successfully', 'status' => 'success'], 200);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 503);
        }
    }


    public function versement($id)
    {
        DB::beginTransaction();
        try {
            PaymentService::CASH_IN($id, Carbon::now()->format('Y-m-d'));
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => "failed",
                "message" => "L'encaissement a échoué, veuillez réessayer"
            ], 501);
        }
        return response()->json([
            "status" => 'success',
            'message' => "La somme a été versée avec succès"
        ]);
    }

    public function debite($id)
    {
        DB::beginTransaction();
        try {
            $pay = Payment::query()->where('id', $id)->first();
            $pay->reliquat = InvoiceServices::GET_RELIQUAT($pay->invoice_id, 0) - floatval($pay->amount);
            $pay->amount = 0;
            $pay->cash_in = false;
            $pay->save();
            Invoice::query()->where('id', $pay->invoice_id)->update(['is_sold' => 0]);
            DB::commit();
        } catch (\Exception $th) {
            DB::rollBack();
            return response()->json([
                'status' => "failed",
                "message" => "L'encaissement a échoué, veuillez réessayer"
            ], 501);
        }
        return response()->json([
            "status" => 'success',
            'message' => "La somme a été versée avec succès"
        ]);
    }

    public function sendPaymentInvoice($id)
    {
        $payment = Payment::query()->where('id', $id)->first();
        return new PaymentReceiptResource($payment);
    }
}
