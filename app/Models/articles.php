<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class articles extends Model
{
    public $timestamps = false;

    // Correspond exactement à la colonne en base (majuscule)
    protected $primaryKey = 'Code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['Code', 'Liblong', 'fournisseur'];

    /**
     * Relation vers fournisseur par correspondance de nom (pas de FK).
     */
    public function fournisseurRelation()
    {
        return $this->belongsTo(fournisseur::class, 'fournisseur', 'fournisseur');
    }
}
