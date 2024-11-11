<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashTransactionItem extends Model
{
    use HasFactory, HasUuids;
    protected $table = 'cash_transaction_items';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'cash_transaction_id',
        'groupage',
        'designation',
        'type',
        'cbm',
        'qte',
        'id',
        'item_id'
    ];

    public function cashTransaction(): BelongsTo
    {
        return $this->belongsTo(CashTransaction::class, 'cash_transaction_id');
    }
}
