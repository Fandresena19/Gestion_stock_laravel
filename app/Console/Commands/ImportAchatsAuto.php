<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Imports\AchatsImport;
use App\Models\ImportedFile;
use App\Models\ImportedRow;

/**
 * ImportAchatsAuto — Commande planifiée d'import automatique des achats.
 *
 * Logique de déduplication :
 *  1. Le fichier est identifié par son NOM (filename).
 *  2. Si le nom est déjà enregistré → on scanne quand même TOUTES les lignes.
 *  3. Seules les lignes dont le hash est NOUVEAU pour ce fichier sont insérées.
 *  4. Les lignes déjà présentes (même contenu, même occurrence) sont ignorées.
 *
 * Cela garantit :
 *  - Aucun doublon dans la table `achats`.
 *  - Si un fichier revient avec de nouvelles lignes (mise à jour), elles sont bien insérées.
 *  - Si un fichier est ré-importé sans changement, 0 ligne est insérée.
 */
class ImportAchatsAuto extends Command
{
    protected $signature   = 'import:achats-auto';
    protected $description = 'Import automatique des achats fournisseurs (déduplication par hash de ligne)';

    /** Correspondance numéro de mois → nom en français */
    private const MOIS = [
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

    public function handle(): int
    {
        $yesterday   = Carbon::yesterday();
        $year        = $yesterday->year;
        $monthNumber = $yesterday->month;
        $monthName   = self::MOIS[$monthNumber];
        $importDate  = $yesterday->format('Y-m-d');

        $basePath = "D:\\Stage\\Achat\\ACHAT {$year}\\{$monthName} {$year}";

        $this->info("📂 Dossier ciblé : $basePath");

        // ------------------------------------------------------------------
        // Vérification du dossier source
        // ------------------------------------------------------------------
        if (!File::exists($basePath)) {
            $this->error("❌ Dossier introuvable : $basePath");
            Log::error("ImportAchatsAuto — Dossier introuvable : $basePath");
            return 1;
        }

        // Créer le sous-dossier ARCHIVE si nécessaire
        $archivePath = $basePath . '\\ARCHIVE';
        if (!File::exists($archivePath)) {
            File::makeDirectory($archivePath, 0755, true);
            $this->info("📁 Dossier ARCHIVE créé.");
        }

        // ------------------------------------------------------------------
        // Récupération des fichiers Excel dans le dossier
        // ------------------------------------------------------------------
        $files = File::files($basePath);
        $this->info("📋 Fichiers trouvés : " . count($files));

        $totalImported  = 0;
        $totalSkipped   = 0;
        $filesProcessed = 0;

        foreach ($files as $file) {

            // ── Filtrer : Excel uniquement ──────────────────────────────────
            if (!in_array(strtolower($file->getExtension()), ['xlsx', 'xls'])) {
                $this->line("  ⏭  Ignoré (non Excel) : " . $file->getFilename());
                continue;
            }

            $filename = $file->getFilename();
            $realPath = $file->getRealPath();

            // ── Vérification de la date du fichier ─────────────────────────
            // On accepte uniquement les fichiers modifiés hier (cohérence avec l'import du jour).
            $fileDate   = Carbon::createFromTimestamp($file->getMTime())->toDateString();
            $targetDate = $yesterday->toDateString();

            if ($fileDate !== $targetDate) {
                $this->line("  ⏭  Ignoré (date fichier : $fileDate ≠ $targetDate) : $filename");
                continue;
            }

            // ── Décision d'import ───────────────────────────────────────────
            // Qu'il soit nouveau ou déjà connu, on le scanne toujours :
            // AchatsImport se charge de n'insérer que les lignes nouvelles.
            $existingRecord = ImportedFile::where('filename', $filename)->first();

            if ($existingRecord) {
                $knownCount = ImportedRow::where('imported_file_id', $existingRecord->id)->count();
                $this->info("  🔄 Fichier connu ($knownCount ligne(s) déjà importée(s)), scan des nouvelles lignes : $filename");
            } else {
                $this->info("  🆕 Nouveau fichier, import complet : $filename");
            }

            // ── Import ──────────────────────────────────────────────────────
            try {
                $importer = new AchatsImport($filename, $realPath, $importDate);
                \Maatwebsite\Excel\Facades\Excel::import($importer, $realPath);

                $newRows     = $importer->getNewRowsCount();
                $skippedRows = $importer->getSkippedRowsCount();

                $totalImported += $newRows;
                $totalSkipped  += $skippedRows;

                $this->info("  ✅ $filename → $newRows insérée(s), $skippedRows ignorée(s).");

                // ── Archivage ───────────────────────────────────────────────
                $destination = $archivePath . '\\' . $filename;
                File::copy($realPath, $destination);
                $this->info("  📁 Archivé : $destination");

                $filesProcessed++;
            } catch (\Throwable $e) {
                $this->error("  ❌ Erreur lors de l'import de : $filename");
                $this->error("     " . $e->getMessage());
                Log::error("ImportAchatsAuto — Erreur import", [
                    'file'    => $filename,
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                // NB : on NE met PAS à jour imported_at en cas d'échec
                // → le fichier sera re-tenté au prochain passage.
            }
        }

        // ------------------------------------------------------------------
        // Résumé
        // ------------------------------------------------------------------
        $this->info("──────────────────────────────────────────────────");
        $this->info("✔  Import terminé.");
        $this->info("   Fichiers traités  : $filesProcessed");
        $this->info("   Lignes insérées   : $totalImported");
        $this->info("   Lignes ignorées   : $totalSkipped");

        return 0;
    }
}
