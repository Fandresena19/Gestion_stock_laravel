<?php

namespace App\Imports;

use App\Models\Achat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

// ✅ CORRECTION : ShouldQueue retiré → import synchrone garanti
// Si vous voulez réactiver la queue plus tard, décommentez ShouldQueue
// ET lancez : php artisan queue:work
class AchatsImport implements
    ToModel,
    WithChunkReading,
    WithBatchInserts
{
    public function model(array $row)
    {
        // Ignorer la ligne des titres (sécurité supplémentaire)
        if (isset($row[0]) && $row[0] == "Référence") {
            return null;
        }

        // Vérifier que la référence existe
        if (empty($row[0])) {
            return null;
        }

        // ✅ CORRECTION PRINCIPALE :
        // Les clés doivent correspondre EXACTEMENT aux valeurs dans $fillable du modèle Achat
        // $fillable = ['Code', 'Liblong', 'PrixU', 'QuantiteAchat', 'date']
        return new Achat([
            'Code'          => $row[0], // Référence
            'Liblong'       => $row[1], // Désignation
            'PrixU'         => $row[3], // PuA TTC
            'QuantiteAchat' => $row[5], // Qté
            'date'          => now()->subDay()->format('Y-m-d'),
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function batchSize(): int
    {
        return 500;
    }
}
