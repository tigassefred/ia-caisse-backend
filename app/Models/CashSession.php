<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static where(string $string, string $string1)
 */
class CashSession extends Model
{
    use HasUuids, HasFactory;
    protected $table = 'cash_sessions';
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'balance',
        'deficit',
        'status',
        'id'
    ];

    public function transactions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CashTransaction::class, 'cash_session_id', 'id');
    }


}
