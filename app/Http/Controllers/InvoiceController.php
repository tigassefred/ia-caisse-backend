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
use App\Models\CashTransactionItem;
use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\Price;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentService;
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
        $date = $request->input('date')
            ? Carbon::parse($request->input('date'))
            : now();

        $endDateTime = $date->copy()->endOfDay();
        $startDateTime = $date->copy()->startOfDay();

        $caisse = Caisse::whereBetween('start_date', [$startDateTime, $endDateTime])->first();
        if (!$caisse) {
            return response()->json([]);
        }

        $invoices = Invoice::query()->where('caisse_id', $caisse->id)->get();

        $query = Payment::query();

        $datePaymnent = Payment::query()
            ->where('deleted', 0)
            ->orderBy('created_at', 'desc')
            ->whereBetween('cash_in_date', [$startDateTime, $endDateTime])->get();

        $payements = $query
            ->whereIn('invoice_id', $invoices->pluck('id'))
            ->where('deleted', 0)
            ->whereNotIn('id', $datePaymnent->pluck('id'))
            ->orderBy('created_at', 'desc')
            ->get();

        $allPay = $payements->merge($datePaymnent);

        return PayementResource::collection($allPay);
    }

    /**
     * Store a newly created resource in storage.
     * @param StoreInvoiceRequest $request
     * @return JsonResponse
     */
    public function store(StoreInvoiceRequest $request): \Illuminate\Http\JsonResponse
    {

        $validatorData = $request->validated();
        DB::beginTransaction();
        try {
            $invoice = new InvoiceService();
            $createInvoiceData = [
                'amount' => $validatorData['valeur_facture'],
                'discount' => $validatorData['valeur_reduction'],
                'commercial_id' => $validatorData['commercial'],
                'is_10Yaar' => $validatorData['is10Yaars'],
                'is_sold' => !(intval($validatorData['reliquat']) > 0),
                'name' => $validatorData['name'],
                "customer_id" => isset($validatorData['client_id']) ? $validatorData['client_id'] : null,
                'price_id' => Price::query()->where('is_deleted', false)->first()->id,
                'caisse_id' => Caisse::query()->where('status', 1)->first()->id

            ];
            $invoice->createInvoice($createInvoiceData);
            $item = $validatorData['Paniers'];

            foreach ($item as $item) {
                $data = [
                    "product_id" => $item["uuid"],
                    "designation" => $item['designation'],
                    "type" => $item['type'],
                    "cbm" => $item['cbm'],
                    'groupage' => $item['name'],

                ];
                $invoice->addInvoiceItem($data);
            }
            $payementData = [
                "user_id" => User::query()->first()->id,
                "amount" => $validatorData['somme_verser'],
                'cash_in' => floatval($validatorData['somme_verser']) > 0 ? false : true,
                'reliquat' => $validatorData['reliquat'],
                'comment' => $validatorData['comments']

            ];
            $payement = new PaymentService(null);
            $payement->makePayment($payementData, $invoice->getInvoice()->id);
            DB::commit();
            return response()->json([
                "status" => 'success',
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
                    'status' => 'failed'
                ], status: 400);
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
    public function destroy(Invoice $invoice)
    {
        //
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

        $endDateTime = $date->copy()->endOfDay();
        $startDateTime = $date->copy()->startOfDay();

        $caisse = Caisse::whereBetween('start_date', [$startDateTime, $endDateTime])->first();
        if (!$caisse) {
            return response()->json([
                "data" => [
                    'sommes_previsionelle' => 0,
                    "somme_encaisse" => 0,
                    "rembourssement"=>0,
                    "somme_en_attente" => 0,
                    "reliquat" => 0,
                    "sommes_10yaar" => 0,
                    'magasin'=>0,
                    "dette_cumulle" => 0,
                ]
            ]);
        }


        $invoices = Invoice::query()->where('caisse_id', $caisse->id)
            ->where('is_deleted', false)
            ->get();
        $payement = Payment::query()->whereIn("invoice_id", $invoices->pluck('id'))
            ->where('deleted', 0)
            ->where('type',1)->get();

        $rembourssement = Payment::query()->whereBetween('cash_in_date',[$startDateTime, $endDateTime])
            ->where('deleted', 0)
            ->where('type',2)->get();


        $magasin = $payement->whereIn("invoice_id", $invoices->where("is_10Yaar", 0)->pluck('id'));
        $payement_10 = $payement->whereIn("invoice_id", $invoices->where("is_10Yaar", 1)->pluck('id'));


        $total_invoice_debit = Invoice::query()->where('is_sold', 0)
            ->where('is_deleted', false)->get();

        $total_payment_debit = Payment::query()->
        where('deleted', false)
            ->whereIn("invoice_id", $total_invoice_debit->pluck('id'))->get();

        return response()->json([
            "data" => [
                'sommes_previsionelle' => $invoices->sum("amount") + $rembourssement->sum("amount"),
                "somme_encaisse" => $payement->where('cash_in', 1)->sum('amount')+$rembourssement->sum('amount'),
                "rembourssement"=>$rembourssement->sum('amount'),
                "somme_en_attente" => $payement->where("cash_in", 0)->sum('amount'),
                "reliquat" => $payement->where('cash_in', 1)->sum('reliquat'),

                "sommes_10yaar" => $payement_10->where('cash_in', true)->sum('amount'),
                'magasin'=>$magasin->where('cash_in', true)->sum('amount'),

                "dette_cumulle" => $total_invoice_debit->sum("amount") - $total_payment_debit->sum("amount"),
            ]
        ]);
    }

    public function unpaid_list()
    {
        $unpaidInvoice = Invoice::query()->where('is_sold', false)->
        where('is_deleted', false)
            ->orderBy('created_at', 'desc')->get();
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
        } catch (Exception $th) {
            return response()->json([
                "message" => "Echecs du rembourssement, veuillez recommencer",
                'status' => "failled"
            ], 400);
        }
    }


}
