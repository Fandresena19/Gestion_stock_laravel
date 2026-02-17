<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class articles extends Model
{
    //Dire de ne pas chercher created_at,et update_at
    public $timestamps = false;

    //Indiquer que Code est le primarykey
    protected $primaryKey = 'Code';
    public $incrementing = false; //pas d'incrementation
    protected $keyType = 'Char'; //Type de charactère est char 
}
