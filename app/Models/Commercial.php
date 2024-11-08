<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commercial extends Model
{


    protected $primaryKey = 'id';
    protected $primaryKeyType = 'string';
    public $incrementing = false;
    protected $table = 'commercials';
    protected $fillable = ['name'];
}
