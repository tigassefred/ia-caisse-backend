<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed $amount
 * @property mixed $discount
 */
class Invoice extends Model
{
    use HasFactory , HasUuids;
    protected $table = 'invoices';
    protected $primaryKey ='id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'id',
        "customer_id",
        'invoice_id',
        'name',
        'amount',
        'discount',
        'commercial_id',
        'is_10Yaar',
        'is_sold'

    ];

    protected $casts =[
       "amount" => "float",
        "discount" => "float",
        "montant_net"=>"float",
    ];

    public function Items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany('App\Models\InvoiceItem','invoice_id','id');
    }

    public function  Commercial(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
       return $this->hasOne('App\Models\Commercial','id','commercial_id');
    }

    protected $appends = ['montant_net'];
    public function getMontantNetAttribute()
    {
      return $this->amount - $this->discount;
    }

        protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->invoice_id = self::generateCashInId();
        });
    }

        private static function generateCashInId()
    {
        $year = date('y');
        $transactionNumber = 1;
        $count = self::whereYear('created_at', date('Y'))->count();
        $_transactionNumber = $count + $transactionNumber;
        $InvoiceID = "FA{$year}-" . str_pad($_transactionNumber, 5, '0', STR_PAD_LEFT);
        while (self::where('invoice_id', $InvoiceID)->exists()) {
            $_transactionNumber++;
            $InvoiceID = "FA{$year}-" . str_pad($_transactionNumber, 5, '0', STR_PAD_LEFT);
        }
        return $InvoiceID;
    }

}

