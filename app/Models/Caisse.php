<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caisse extends Model
{
    protected $table = 'caisses';
    protected $primaryKey = 'id';
    public $timestamps = true;
    protected $primaryKeyType = 'string';


    protected $fillable = [
        'name',
        'valeur_net',
        'valeur_reel',
        'valeur_reliquat',
        'valeur_encaisse',
        'comment',
        'caisse_id',
    ];

    public function Items(){
        return $this->hasMany(CaisseItem::class);
    }
}
