<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Imports\AchatsImport;
use App\Models\ImportedFile;

class ImportAchatsAuto extends Command
{
    protected $signature = 'import:achats-auto';
    protected $description = 'Import automatique des achats fournisseurs';

    public function handle()
    {
        $mois = [
            1  => 'JANVIER',
            2  => 'FEVRIER',
            3  => 'MARS',
            4  => 'AVRIL',
            5  => 'MAI',
            6  => 'JUIN',
            7  => 'JUILLET',
            8  => 'AOUT',
            9  => 'SEPTEMBRE',
            10 => 'OCTOBRE',
            11 => 'NOVEMBRE',
            12 => 'DECEMBRE',
        ];

        $yesterday = Carbon::yesterday();
        $year        = $yesterday->year;
        $monthNumber = $yesterday->month;
        $monthName   = $mois[$monthNumber];

        $basePath = "D:\\Stage\\Achat\\ACHAT {$year}\\{$monthName} {$year}";
        $this->info("Dossier ciblé : $basePath");

        if (!File::exists($basePath)) {
            $this->error("Dossier introuvable : $basePath");
            Log::error("ImportAchatsAuto - Dossier introuvable : $basePath");
            return 1;
        }

        // Créer le dossier ARCHIVE si nécessaire
        $archivePath = $basePath . '\\ARCHIVE';
        if (!File::exists($archivePath)) {
            File::makeDirectory($archivePath, 0755, true);
            $this->info("Création du dossier ARCHIVE");
        }

        $targetDate = $yesterday->toDateString();
        $files = File::files($basePath);
        $this->info("Fichiers trouvés : " . count($files));

        $importCount = 0;

        foreach ($files as $file) {

            // ✅ Vérifier extension Excel uniquement
            if (!in_array(strtolower($file->getExtension()), ['xlsx', 'xls'])) {
                $this->info("Ignoré (pas Excel) : " . $file->getFilename());
                continue;
            }

            // ✅ Vérifier que le fichier date d'hier
            $fileDate = Carbon::createFromTimestamp($file->getMTime())->toDateString();
            if ($fileDate != $targetDate) {
                $this->info("Ignoré (mauvaise date) : " . $file->getFilename() . " (date fichier : $fileDate)");
                continue;
            }

            // ✅ Vérifier que le fichier n'a pas déjà été importé
            if (ImportedFile::where('filename', $file->getFilename())->exists()) {
                $this->info("Ignoré (déjà importé) : " . $file->getFilename());
                continue;
            }

            try {
                $this->info("Import en cours : " . $file->getFilename());

                // ✅ CORRECTION : import() synchrone au lieu de queueImport()
                // queueImport() nécessite un worker queue actif (php artisan queue:work)
                // import() est immédiat et fiable sans configuration supplémentaire
                Excel::import(new AchatsImport, $file->getRealPath());

                // Enregistrer le fichier comme importé
                ImportedFile::create([
                    'filename'    => $file->getFilename(),
                    'path'        => $file->getRealPath(),
                    'imported_at' => now(),
                ]);

                // Copier dans ARCHIVE
                $destination = $archivePath . '\\' . $file->getFilename();
                File::copy($file->getRealPath(), $destination);
                $this->info("✅ Import + archivage réussis : " . $file->getFilename());

                $importCount++;
            } catch (\Exception $e) {
                $this->error("❌ Erreur import fichier : " . $file->getFilename());
                $this->error($e->getMessage());
                Log::error("ImportAchatsAuto - Erreur : " . $e->getMessage(), [
                    'file' => $file->getFilename(),
                ]);
            }
        }

        $this->info("Import automatique terminé. Fichiers importés : $importCount");
        return 0;
    }
}
