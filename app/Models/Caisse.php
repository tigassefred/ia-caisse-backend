<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    use HasUuids;

    protected $table = 'caisses';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $primaryKeyType = 'string';


    protected $fillable = [
        'id',
        'start_date',
        'end_date',
        'transaction',
        'encaissement',
        'creance',
        'remboursement',
        '_10yaar',
        'magazin',
        'versement_magasin',
        'versement_10yaar',
        'status'
    ];

    public function Invoices(){
        return $this->hasMany(Invoice::class);
    }
}
