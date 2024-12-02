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

        $allInvoices = Invoice::query()->whereIn('caisse_id', $caisses->pluck('id'))->get();
        $commerciaux = Commercial::query()->orderBy('name')->get();

        $response = [];
        foreach ($commerciaux  as $com) {
            $CommercialInvoices = $allInvoices->where('commercial_id', $com->id);
            $items = null;
            foreach ($CommercialInvoices as $invoice) {
                $items[] = [
                    'name' => $invoice->name,
                    'amount' => $invoice->amount,
                    'discount' => $invoice->discount,
                    'date' => SupportCarbon::parse($invoice->created_at)->format('d/m/Y'),
                    'reste' => $this->getInvoiceDebit($invoice->id),
                ];
            }

            $response['commercials'][] = [
                'name' => $com->name,
                'somme_attendu' => $CommercialInvoices->sum('amount'),
                'somme_encaisse' => $this->getInvoicesDebit($CommercialInvoices->pluck('id')),
                'reduction' => $CommercialInvoices->sum('discount'),
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

        $allInvoices = Invoice::query()->whereIn('caisse_id', $caisses->pluck('id'))->get();


        $allPayment = Payment::query()->whereIn('invoice_id', $allInvoices->pluck('id'))->get();
        $response = [];

        $response = [
            "global" => [
                "somme_attendu" => $allInvoices->sum('amount'),
                "somme_encaisse" => $allPayment->where('cash_in', 1)->sum('amount'),
                "somme_creance" => $this->getInvoicesDebit($allInvoices->pluck('id')),
                "somme_10yaar" => $allInvoices->where('cash_in', 1)->where('is_10Yaar', 1)->sum('amount'),
                "somme_magazin" => $allInvoices->where('cash_in', 1)->where('is_10Yaar', 0)->sum('amount'),
                "reduction" => $allInvoices->sum('discount'),
                'start_date' => $period_start->format('d/m/Y'),
                'end_date' => $period_end->format('d/m/Y'),
            ],
            "details" => []
        ];

        $commerciaux = Commercial::query()->orderBy('name')->get();
        $items = [];

        foreach ($commerciaux  as $com) {
            $CommercialInvoices = $allInvoices->where('commercial_id', $com->id);
            $pay = $allPayment->whereIn('invoice_id', $CommercialInvoices->pluck('id'));

            if (count($pay) > 0) {
                $response['details'][] = [
                    'name' => $com->name,
                    'somme_attendu' => $CommercialInvoices->sum('amount'),
                    'somme_encaisse' => $pay->where('cash_in', 1)->sum('amount'),
                    'somme_creance' => $this->getInvoicesDebit($CommercialInvoices->pluck('id')),
                    'reduction' => $CommercialInvoices->sum('discount'),
                ];
            }
        }

        return response()->json([
            'data' => $response
        ]);
    }


    private function getInvoiceDebit($id)
    {
        $invoice = Invoice::query()->where('id', $id)->first();
        $payment = Payment::query()->where('invoice_id', $id)->where('deleted', 0)->get();
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
}
