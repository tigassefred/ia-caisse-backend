<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
   use HasFactory , HasUuids;
    protected $table = 'invoice_items';
    protected $fillable = [
        'id',
        'invoice_id',
        'product_id',
        'designation',
        'type',
        'cbm'
    ];

    public function invoice():belongsTo
    {
        return $this->belongsTo(Invoice::class);
    }
}

