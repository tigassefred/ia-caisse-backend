<?php

namespace App\Http\Controllers;

use App\Http\Integrations\PackingList\Requests\GetPackingListItem;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\InvoiceUnpaidResource;
use App\Http\Resources\PayementResource;
use App\Models\Caisse;
use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use App\Services\refacto\InvoiceServices;
use App\Services\refacto\PaymentService as RefactoPaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $date = Carbon::parse($request->input('date', now()))
            ->setTime(now()->hour, now()->minute, now()->second);


        $plage = $this->getPlage($date);
        $start_date = $plage[0];
        $end_date = $plage[1];

        $response = Caisse::query();
        $response = $response->where('start_date', '>=', $start_date)
            ->where('end_date', '<=', $end_date);

        $caisse = $response->first();

        if (!$caisse) {
            return response()->json([]);
        }

        $startDateTime = $caisse->start_date;
        $endDateTime = $caisse->end_date;

        $invoices = Invoice::query()->where('caisse_id', $caisse->id)->get();

        $query = Payment::query();

        $datePaymnent = Payment::query()
            ->where('deleted', 0)
            ->where('type', '2')
            ->whereBetween('cash_in_date', [$startDateTime, $endDateTime])
            ->get();

        $payements = $query
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->where('deleted', 0)
            ->where('type', 1)
            ->get();

        $allPay = $payements->merge($datePaymnent);
        $allPay = $allPay->sortByDesc('created_at');

        return PayementResource::collection($allPay);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreInvoiceRequest $request
     * @return JsonResponse
     */
    public function store(StoreInvoiceRequest $request)
    {
        $validatorData = $request->validated();
        DB::beginTransaction();
        try {
            $invoice = new InvoiceServices(null);
            $name = $validatorData['name'];
            $amount = $validatorData['valeur_facture'];
            $dicount = $validatorData['valeur_reduction'];
            $caisse = Caisse::query()->where('status', 1)->first()->id;
            $zone = $validatorData['is10Yaars'];

            $invoice->setNewInvoice($name, $amount, $dicount, $caisse, $zone);
            $invoice->setCommercial($validatorData['commercial']);
            $invoice->setPrice(Price::query()->where('is_deleted', false)->first()->id);
            $invoice->createInvoice();

            foreach ($validatorData['Paniers'] as $item) {
                $invoice->addInvoiceItem([
                    "product_id" => $item["uuid"],
                    "designation" => $item['designation'],
                    "type" => $item['type'],
                    "cbm" => $item['cbm'],
                    'groupage' => $item['name'],
                ]);
            }

            $payment = new RefactoPaymentService(null);
            $payment->setAmount(floatval($validatorData['somme_verser']));
            $payment->settype(1);
            $payment->setUser(User::first()->id);
            $payment->setReliquat(InvoiceServices::GET_RELIQUAT($invoice->getInvoice()->id,  $validatorData['somme_verser']));
            $payment->setComment($validatorData['comments']);

            InvoiceServices::ATTACHE_PAIEMENT($invoice->getInvoice()->id, $payment->getNewPay());
            $invoice->activeInvoice();

            if (InvoiceServices::GET_RELIQUAT($invoice->getInvoice()->id, 0) == 0) {
                InvoiceServices::SOLDED($invoice->getInvoice()->id);
            }
            DB::commit();
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
                    'status' => 'failed'
                ],
                status: 400
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($invoice)
    {
        $inv = Invoice::query()->where('id', $invoice)->first();
        return new InvoiceResource($inv);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateInvoiceRequest $request, Invoice $invoice)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($invoice)
    {
        $invoice = Invoice::query()->where('id', $invoice)->first();
        $invoice->is_deleted = true;
        $invoice->save();
        $pay = Payment::query()->where('invoice_id', $invoice->id)->get();
        foreach ($pay as $p) {
            $p->deleted = true;
            $p->save();
        }
        return response()->json([
            "message" => "L'invoice a été supprimée avec succès"
        ]);
    }

    public function dashboard()
    {
        $connector = new StockConnector();
        $request = new GetPackingListItem();
        $response = $connector->send($request);
        $packingList = $response->array();
        $caisse_item_ids = InvoiceItem::all()->pluck('product_id');
        if ($caisse_item_ids->isEmpty()) {
            $missingItems = $packingList['data'];
        } else {
            $missingItems = array_filter($packingList['data'], function ($item) use ($caisse_item_ids) {
                return isset($item['uuid']) && !$caisse_item_ids->contains($item['uuid']);
            });
        }
        $commercial = Commercial::where('is_deleted', 0)->get();
        $Price = Price::where('is_deleted', 0)->select('balle', 'colis')->get();

        return response()->json([
            'commercials' => $commercial,
            'packingList' => $missingItems,
            'price' => $Price
        ]);
    }

    public function statistics(Request $request)
    {
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : now();
        $date = Carbon::parse($request->input('date', now()))
            ->setTime(now()->hour, now()->minute, now()->second);


        $plage = $this->getPlage($date);
        $start_date = $plage[0];
        $end_date = $plage[1];

        $response = Caisse::query();
        $response = $response->where('start_date', '>=', $start_date)
            ->where('end_date', '<=', $end_date);

        $caisse = $response->first();
        $startDateTime = $caisse->start_date;
        $endDateTime = $caisse->end_date;

        if (!$caisse) {
            return response()->json([
                "data" => [
                    'sommes_previsionelle' => 0,
                    "somme_encaisse" => 0,
                    "rembourssement" => 0,
                    "somme_en_attente" => 0,
                    "reliquat" => 0,
                    "sommes_10yaar" => 0,
                    'magasin' => 0,
                    "dette_cumulle" => 0,
                ]
            ]);
        }


        $invoices = Invoice::query()->where('caisse_id', $caisse->id)
            ->where('is_deleted', false)
            ->get();
        $payement = Payment::query()->whereIn("invoice_id", $invoices->pluck('id'))
            ->where('deleted', 0)
            ->where('type', 1)->get();

        $rembourssement = Payment::query()->whereBetween('cash_in_date', [$startDateTime, $endDateTime])
            ->where('deleted', 0)
            ->where('type', 2)->get();


        $magasin = $payement->whereIn("invoice_id", $invoices->where("is_10Yaar", 0)->pluck('id'));
        $payement_10 = $payement->whereIn("invoice_id", $invoices->where("is_10Yaar", 1)->pluck('id'));


        $total_invoice_debit = Invoice::query()->where('is_sold', 0)
            ->where('is_deleted', false)->get();

        $total_payment_debit = Payment::query()->where('deleted', false)
            ->whereIn("invoice_id", $total_invoice_debit->pluck('id'))->get();

        return response()->json([
            "data" => [
                'sommes_previsionelle' => $invoices->sum("amount") + $rembourssement->sum("amount"),
                "somme_encaisse" => $payement->where('cash_in', 1)->sum('amount') + $rembourssement->sum('amount'),
                "rembourssement" => $rembourssement->sum('amount'),
                "somme_en_attente" => $payement->where("cash_in", 0)->sum('amount'),
                "reliquat" => $this->getInvoicesDebit($invoices->pluck('id')),

                "sommes_10yaar" => $payement_10->where('cash_in', true)->sum('amount'),
                'magasin' => $magasin->where('cash_in', true)->sum('amount'),

                "dette_cumulle" => $total_invoice_debit->sum("amount") - $total_payment_debit->sum("amount"),
            ]
        ]);
    }

    public function unpaid_list()
    {
        $unpaidInvoice = Invoice::query()
            ->where('is_sold', false)
            ->where('is_deleted', 0)
            ->orderBy('created_at', 'desc')
            ->get();

        return InvoiceUnpaidResource::collection($unpaidInvoice);
    }

    public function rembourssement($id, Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required',
            'amount' => 'required',
            'date' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first(),
                'status' => "failled"
            ]);
        }

        try {
            $amount = $request->amount;
            $invoiceService = new InvoiceService($id);
            $invoiceService->payDebit($amount, $request->date);
            return response()->json([
                "status" => "success"
            ]);
        } catch (\Exception $th) {
            return response()->json([
                "message" => "Echecs du rembourssement, veuillez recommencer",
                'status' => "failled"
            ], 400);
        }
    }

    private function getInvoiceDebit($id)
    {
        $invoice = Invoice::query()->where('id', $id)->first();
        $payment = Payment::query()->where('invoice_id', $id)
            ->where('cash_in', 1)
            ->where('deleted', 0)->get();
        $totalInvoiceDebit = $invoice->amount - ($payment->sum('amount') + $invoice->discount);
        return $totalInvoiceDebit;
    }

    private function getInvoicesDebit($invoice_ids)
    {
        $totalDebit = 0;
        foreach ($invoice_ids as $id) {
            $totalDebit += $this->getInvoiceDebit($id);
        }
        return $totalDebit;
    }

    private function getPlage($date)
    {

        $compare_time = Carbon::parse(now())->setTime(7, 30, 0);
        $start_time = Carbon::parse($date)->setTime(7, 30, 0);

        if (now()->isBefore($compare_time)) {
            $start_date = $start_time->copy();
            $start_date->subDay();

            $end_date = $start_time->copy();
            $end_date->setMinute(35);
        } else {
            $start_date = $start_time->copy();
            $end_date = $start_time->setMinute(35)->copy()->addDay();
        }
        return [$start_date, $end_date];
    }
}
