<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stocks extends Model
{
    protected $primaryKey = 'code';

    public $incrementing = false;

    protected $keyType = 'char';

    protected $fillable = [
        'code',
        'liblong',
        'quantitestock'
    ];
    public $timestamps = false;
}
