<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static whereBetween(string $string, array $array)
 */
class Payment extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'payments';
    protected $fillable = [
        'user_id',
        'invoice_id',
        'id',
        'amount',
        'reliquat',
        'cash_in',
        'deleted',
        'comment',
        'type',
        'cash_in_date',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\User');
    }

    public function invoice(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo('App\Models\Invoice');
    }


    protected  $casts = [
       'amount' => "float",
        'reliquat' => "float",
        "cash_in"=>'boolean'
    ];
}
