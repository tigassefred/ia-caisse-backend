<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CashSession extends Model
{
    use HasUuids;
    protected $table = 'cash_sessions';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    protected $incrementing = false;
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'balance',
        'deficit',
        'status',
        'id'
    ];


}
