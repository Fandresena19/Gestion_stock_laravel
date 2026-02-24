<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportedRow extends Model
{
    protected $table = 'imported_rows';

    protected $fillable = [
        'imported_file_id',
        'row_hash',
    ];

    public function file()
    {
        return $this->belongsTo(ImportedFile::class, 'imported_file_id');
    }
}
