<?php

namespace App\Http\Controllers;

use App\Http\Integrations\GetUsersRequest\Requests\GetUserRequest;
use App\Http\Integrations\Stock\StockConnector;
use App\Http\Resources\CreanceResource;
use App\Models\Caisse;
use App\Models\Commercial;
use App\Models\Invoice;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class CreanceController extends Controller
{
    public function index()
    {
        $invoice = Invoice::query()->where("is_sold", false)->get();
        return CreanceResource::collection($invoice);
    }

    public function creance_mensuelle_commercial($period)
    {
        $validator = Validator::make(['period' => $period], [
            'period' => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => "La periode est invalide"]);
        }

        $date = Carbon::createFromFormat("m-Y", $period);
        $period_start = $date->copy()->startOfMonth();
        $period_end = $date->copy()->endOfMonth();

        $caisses = Caisse::whereBetween('start_date', [$period_start, $period_end])->get();

        $allInvoices = Invoice::query()->whereIn('caisse_id', $caisses->pluck('id'))
            ->where('is_deleted',0)->get();
        $commerciaux = Commercial::query()->orderBy('name')->get();

        $response = [];
        foreach ($commerciaux  as $com) {
            $CommercialInvoices = $allInvoices->where('commercial_id', $com->id);
            $allPay = Payment::query()->whereIn('invoice_id', $CommercialInvoices->pluck('id'))
                ->where('deleted',0)
                ->get();
            $items = null;

            foreach ($CommercialInvoices as $invoice) {


                $items[] = [
                    'name' => $invoice->name,
                    'amount' => number_format($invoice->amount , 0, ',',' '),
                    'discount' => number_format($invoice->discount, 0, ',',' '),
                    'date' => SupportCarbon::parse($invoice->created_at)->format('d/m/Y'),
                    'reste' => $this->getInvoiceDebit($invoice->id) < 0 ? 0 : number_format($this->getInvoiceDebit($invoice->id),0,',',' ') ,
                    'bl'=>$invoice->invoice_id,
                    'payer'=>number_format(Payment::query()->where('invoice_id', $invoice->id)
                        ->where('deleted',0)->sum('amount'), 0, ',',' '),
                    'comments'=>$allPay->where('invoice_id', $invoice->id),
                ];
            }

            $response['commercials'][] = [
                'name' => $com->name,
                'somme_attendu' => number_format($CommercialInvoices->sum('amount'),0,',',' '),
                'somme_creance' => number_format($this->getInvoicesDebit($CommercialInvoices->pluck('id')),0,',',' '),
                'somme_encaisse' => number_format($allPay->sum('amount'),0,',',' '),
                'reduction' => number_format($CommercialInvoices->sum('discount'),0,',',' '),
                'start_date' => $period_start->format('d/m/Y'),
                'end_date' => $period_end->format('d/m/Y'),
                "items" => $items,

            ];
        }

        return response()->json([
            'data' => $response
        ]);
    }

    public function creance_mensuelle($period)
    {
        $validator = Validator::make(['period' => $period], [
            'period' => "required"
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => "La periode est invalide"]);
        }

        $date = Carbon::createFromFormat("m-Y", $period);
        $period_start = $date->copy()->startOfMonth();
        $period_end = $date->copy()->endOfMonth();

        $caisses = Caisse::whereBetween('start_date', [$period_start, $period_end])->get();

        $allInvoices = Invoice::query()->whereIn('caisse_id', $caisses->pluck('id'))
            ->where('is_deleted',0)->get();


        $allPayment = Payment::query()->whereIn('invoice_id', $allInvoices->pluck('id'))
            ->where('deleted',0)->get();

        $response = [
            "global" => [
                "somme_attendu" => number_format($allInvoices->sum('amount'), 0, '', ' '),
                "somme_encaisse" => number_format($allPayment->where('cash_in', 1)->sum('amount'), 0, '', ' '),
                "somme_creance" =>number_format($this->getInvoicesDebit($allInvoices->pluck('id')), 0, '', ' '),
                "somme_10yaar" => number_format($allPayment->where('cash_in', 1)->whereIn('invoice_id', $allInvoices->where("is_10Yaar",1)->pluck('id'))->sum('amount'), 0, '', ' '),
                "somme_magazin" => number_format($allPayment->where('cash_in', 1)->whereIn('invoice_id', $allInvoices->where("is_10Yaar",0 )->pluck('id'))->sum('amount'), 0, '', ' '),
                "reduction" => number_format($allInvoices->sum('discount'), 0, '', ' '),
                'start_date' => $period_start->format('d/m/Y'),
                'end_date' => $period_end->format('d/m/Y'),
            ],
            "details" => []
        ];

        $commerciaux = Commercial::query()->orderBy('name')->get();

        foreach ($commerciaux  as $com) {
            $CommercialInvoices = $allInvoices->where('commercial_id', $com->id);
            $pay = $allPayment->whereIn('invoice_id', $CommercialInvoices->pluck('id'));

            if (count($pay) > 0) {
                $response['details'][] = [
                    'name' => $com->name,
                    'somme_attendu' => number_format($CommercialInvoices->sum('amount'),0,'.',' '),
                    'somme_encaisse' =>number_format( $pay->where('cash_in', 1)->sum('amount'),0,'.',' '),
                    'somme_creance' => number_format($this->getInvoicesDebit($CommercialInvoices->pluck('id')),0,'.',' '),
                    'reduction' => number_format($CommercialInvoices->sum('discount'),0,'.',' '),
                ];
            }
        }

        return response()->json([
            'data' => $response
        ]);
    }


    private function getInvoiceDebit($id)
    {
        $invoice = Invoice::query()->where('is_deleted',0)->where('id', $id)->first();
        $payment = Payment::query()->where('deleted', 0)->where('invoice_id', $id)->get();
        return $invoice->amount - ($payment->sum('amount') + $invoice->discount);
    }

    private function getInvoicesDebit($invoice_ids)
    {
        $totalDebit = 0;
        foreach ($invoice_ids as $id) {
            $totalDebit += $this->getInvoiceDebit($id);
        }
        return $totalDebit;
    }



}
