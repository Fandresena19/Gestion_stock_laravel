<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportedFile extends Model
{
    protected $fillable = [
        'filename',
        'path',
        'imported_at'
    ];
}
