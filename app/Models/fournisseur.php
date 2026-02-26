<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class fournisseur extends Model
{
    protected $primaryKey = 'id_fournisseur';
    protected $keyType = 'int';
    public $incrementing = true;
    protected $table = 'fournisseurs';

    protected $fillable = ['fournisseur'];

    public $timestamps = false;
    /**
     * Les articles liés à ce fournisseur.
     */
    public function articles()
    {
        return $this->hasMany(articles::class, 'id_fournisseur');
    }
}
