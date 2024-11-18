<?php

namespace App\Http\Controllers;

use App\Http\Integrations\PackingList\Requests\GetPackingListItem;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Requests\Invoice\StoreInvoiceRequest;
use App\Http\Requests\UpdateInvoiceRequest;
use App\Http\Resources\PayementResource;
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

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now();
        $startDateTime = $date->copy()->setTime(7, 30);
        $endDateTime = $date->copy()->addDay()->setTime(7, 30);
       // $payements = Payment::whereBetween('created_at', [$startDateTime, $endDateTime])->get();
         $payements = Payment::query()->orderBy('created_at' ,'desc')->get();

        return PayementResource::collection($payements);
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
                "customer_id"=> isset($validatorData['client_id']) ? $validatorData['client_id'] : null,
            ];
            $invoice->createInvoice($createInvoiceData);
            $item = $validatorData['Paniers'];

            foreach ($item as $item) {
                $data = [
                    "product_id" => $item["uuid"],
                    "designation" => $item['designation'],
                    "type" => $item['type'],
                    "cbm" => $item['cbm']
                ];
                $invoice->addInvoiceItem($data);
            }
            Log::info(json_encode($invoice));

            $payementData = [
                "user_id" => User::query()->first()->id,
                "amount" => $validatorData['somme_verser'],
                'cash_in' => $validatorData['isPayDiff'],
                'reliquat' => $validatorData['reliquat'],
                'comment' => $validatorData['comments'],

            ];
            $payement = new PaymentService();
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
    public function show(Invoice $invoice)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        //
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

        $caisse_item_ids =  InvoiceItem::all()->pluck('product_id');

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

    public function statistics(Request $request)
    {
        $date = $request->input('date') ? Carbon::parse($request->input('date')) : now();
        $startDateTime = $date->copy()->setTime(7, 30);
        $endDateTime = $date->copy()->addDay()->setTime(7, 30);

        $invoices = Invoice::query()
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->get();
        $payement = Payment::query()->whereIn("invoice_id" , $invoices->pluck('id'))->get();


Log::info($payement);

        return response()->json([
           "data"=>[
               'sommes_previsionelle'=>$invoices->sum("amount"),
               "somme_encaisse"=>$payement->where("cash_in", true)->sum('amount'),
               "somme_en_attente"=>$payement->where("cash_in", false)->sum('amount')
           ]
        ]);
    }
}
