<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransaction extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'cash_transactions';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cash_in_id',
        'name',
        'build_value',
        'build_value_reduction',
        'reduction_value',
        'reliquat',
        'payed_value',
        'comments',
        'is10Yaars',
        'commercial',
        'cash_in',
        'user_id',
        'cash_session_id',
    ];

    // Attributes that should be cast to native types
    protected $casts = [
        'is10Yaars' => 'boolean',
        'cash_in' => 'boolean',
        'payed_value' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cashSession(): BelongsTo
    {
        return $this->belongsTo(CashSession::class);
    }

    public function commercial(): BelongsTo
    {
        return $this->belongsTo(Commercial::class);
    }


    protected static function booted()
    {
        static::creating(function ($transaction) {
            $transaction->cash_in_id = self::generateCashInId();
        });
    }

    /**
     * Generate a new `cash_in_id` based on the current year and the number of records.
     * @return string
     */
    private static function generateCashInId()
    {
        $year = date('y');
        $transactionNumber = 1;
        $count = self::whereYear('created_at', date('Y'))->count();
        $_transactionNumber = $count + $transactionNumber;
        $cashInId = "BS{$year}-" . str_pad($_transactionNumber, 5, '0', STR_PAD_LEFT);
        while (self::where('cash_in_id', $cashInId)->exists()) {
            $_transactionNumber++;
            $cashInId = "BS{$year}-" . str_pad($_transactionNumber, 5, '0', STR_PAD_LEFT);
        }
        return $cashInId;
    }
}
