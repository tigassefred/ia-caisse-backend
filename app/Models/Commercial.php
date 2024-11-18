<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @method static find($commercial_id)
 */
class Commercial extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    protected $table = 'commercials';
    protected $fillable = ['name'];
}
