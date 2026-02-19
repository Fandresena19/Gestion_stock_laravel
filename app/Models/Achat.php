<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Achat extends Model
{
    protected $table = 'achats';

    protected $fillable = [
        'Code',
        'Liblong',
        'PrixU',
        'QuantiteAchat',
        'date',
    ];

    public $timestamps = false;

    protected $casts = [
        'date' => 'date',
    ];
}
