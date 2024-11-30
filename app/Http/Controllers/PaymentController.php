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
use App\Services\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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

            // Compter le nombre de paiements associés à la facture
            $paymentsCount = $inv->Payments()->count();

            if ($paymentsCount > 1) {
                // Si plusieurs paiements, marquer seulement le paiement comme supprimé
                $pay->deleted = 1;
                $inv->is_sold = 0;
                $pay->save();
            } else {
                // Si un seul paiement, supprimer les items et marquer la facture et le paiement comme supprimés
                $inv->items()->delete();
                $inv->is_deleted = 1;
                $inv->save();

                $pay->deleted = 1;
                $pay->save();
            }
            DB::commit();
            return response()->json(['messaage' => 'Payment deleted successfully', 'status' => 'success'], 200);
        } catch (Exception $th) {
            DB::rollBack();
            return response()->json(['message' => $th->getMessage()], 503);
        }
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
            ], 501);
        }
        return response()->json([
            "status" => 'success',
            'message' => "La somme a été générée avec succès"
        ]);
    }

    public function sendPaymentInvoice($id)
    {
        $payment = Payment::query()->where('id', $id)->first();
        return new PaymentReceiptResource($payment);
    }


}
