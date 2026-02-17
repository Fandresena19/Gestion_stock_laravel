<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Achat extends Model
{
    protected $table = 'achats';

    protected $fillable = [
        'code16',
        'date',
        'refart',
        'fournisseur',
        'code',
        'liblong',
        'prixu',
        'quantiteachat',
    ];

    public $timestamps = false;

    protected $casts = [
        'date' => 'date',
    ];
}
