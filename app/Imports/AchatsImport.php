<?php

namespace App\Imports;

use App\Models\ImportedFile;
use App\Models\ImportedRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * AchatsImport — Import intelligent avec déduplication par hash de ligne.
 *
 * Comportement :
 *  - Le fichier est identifié par son NOM (filename), pas sa date.
 *  - À chaque import (même fichier, même date ou date différente) :
 *      → Toutes les lignes du fichier sont scannées.
 *      → Seules les lignes NOUVELLES (hash inconnu pour ce fichier) sont insérées.
 *      → Les lignes déjà présentes sont ignorées silencieusement.
 *
 * Hash = SHA-256(Code|Liblong|PrixU|Qté|occurrence)
 *  - L'occurrence permet de distinguer deux lignes IDENTIQUES légitimes dans un même fichier.
 *
 * ⚠️  La table `achats` ne reçoit jamais de doublons grâce à cette logique.
 */
class AchatsImport implements ToCollection, WithChunkReading
{
    protected string $filename;
    protected string $path;
    protected string $importDate;

    protected int $newRows     = 0;
    protected int $skippedRows = 0;

    /**
     * @param string      $filename    Nom original du fichier (clé de déduplication)
     * @param string      $path        Chemin temporaire ou réel du fichier
     * @param string|null $importDate  Date à affecter aux achats (Y-m-d). Défaut : aujourd'hui.
     */
    public function __construct(string $filename, string $path = '', ?string $importDate = null)
    {
        $this->filename   = $filename;
        $this->path       = $path;
        $this->importDate = $importDate ?? now()->format('Y-m-d');
    }

    // =========================================================================
    // Point d'entrée principal (appelé par chunk)
    // =========================================================================

    public function collection(Collection $rows)
    {
        // ------------------------------------------------------------------
        // 1. Récupérer ou créer l'entrée du fichier dans imported_files.
        //    • firstOrCreate : si nouveau fichier → créé avec total_rows = 0
        //    • Si fichier connu (même nom, nouvelle date) → récupéré tel quel
        // ------------------------------------------------------------------
        $importedFile = ImportedFile::firstOrCreate(
            ['filename' => $this->filename],
            [
                'path'        => $this->path,
                'imported_at' => now(),
                'total_rows'  => 0,
            ]
        );

        // Mettre à jour le chemin et la date du dernier import à chaque passage
        $importedFile->update([
            'path'        => $this->path,
            'imported_at' => now(),
        ]);

        // ------------------------------------------------------------------
        // 2. Charger tous les hashes déjà connus pour CE fichier.
        //    Utilisé pour un lookup O(1) lors du traitement des lignes.
        // ------------------------------------------------------------------
        $knownHashes = ImportedRow::where('imported_file_id', $importedFile->id)
            ->pluck('row_hash')
            ->flip()          // tableau [hash => index] pour isset() rapide
            ->all();

        $newAchats   = [];
        $newHashRows = [];

        // Compteur d'occurrences par contenu brut.
        // Permet de générer des hashes distincts pour deux lignes parfaitement
        // identiques (ex : même article commandé deux fois dans le même fichier).
        $contentCount = [];

        // ------------------------------------------------------------------
        // 3. Parcourir chaque ligne du chunk
        // ------------------------------------------------------------------
        foreach ($rows as $row) {

            // Ignorer la ligne d'en-tête (insensible à la casse)
            if (isset($row[0]) && strtolower(trim((string) $row[0])) === 'référence') {
                continue;
            }

            // Ignorer les lignes sans code article
            $code = trim((string) ($row[0] ?? ''));
            if ($code === '') {
                continue;
            }

            // --- Construire la clé de contenu ---
            $liblong = trim((string) ($row[1] ?? ''));
            $prixU   = trim((string) ($row[3] ?? ''));
            $qte     = trim((string) ($row[5] ?? ''));

            $contentKey = implode('|', [$code, $liblong, $prixU, $qte]);

            // Incrémenter l'occurrence pour ce contenu dans ce chunk
            $contentCount[$contentKey] = ($contentCount[$contentKey] ?? 0) + 1;

            // --- Hash unique = contenu + occurrence ---
            $hash = hash('sha256', $contentKey . '|occ=' . $contentCount[$contentKey]);

            // --- Ligne déjà importée → ignorer ---
            if (isset($knownHashes[$hash])) {
                $this->skippedRows++;
                continue;
            }

            // --- Préparer l'insertion de l'achat ---
            $newAchats[] = [
                'Code'          => $code,
                'Liblong'       => $liblong,
                'PrixU'         => $this->parseNumber($prixU),
                'QuantiteAchat' => $this->parseNumber($qte),
                'date'          => $this->importDate,
            ];

            // --- Préparer l'insertion du hash ---
            $newHashRows[] = [
                'imported_file_id' => $importedFile->id,
                'row_hash'         => $hash,
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            // Marquer localement pour éviter les doublons DANS ce chunk
            // (important si le même fichier est importé en plusieurs chunks)
            $knownHashes[$hash] = true;
            $this->newRows++;
        }

        // ------------------------------------------------------------------
        // 4. Insertion en batch (performances) + mise à jour du compteur
        // ------------------------------------------------------------------
        if (!empty($newAchats)) {
            DB::table('achats')->insert($newAchats);

            // insertOrIgnore au cas où deux chunks simultanés produiraient le même hash
            DB::table('imported_rows')->insertOrIgnore($newHashRows);

            $importedFile->increment('total_rows', $this->newRows);
        }
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Parse un nombre au format européen "1 234,56" ou anglais "1234.56".
     */
    protected function parseNumber($value): float
    {
        if ($value === null || $value === '') {
            return 0.0;
        }
        // Supprimer espaces normaux et insécables (NBSP)
        $cleaned = str_replace([' ', "\u{00A0}"], '', (string) $value);
        // Remplacer virgule décimale par point
        $cleaned = str_replace(',', '.', $cleaned);
        return (float) $cleaned;
    }

    // =========================================================================
    // Accesseurs
    // =========================================================================

    public function getNewRowsCount(): int
    {
        return $this->newRows;
    }

    public function getSkippedRowsCount(): int
    {
        return $this->skippedRows;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
