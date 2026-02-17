<?php

namespace App\Imports;

use App\Models\Achat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class AchatsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // ❌ Ne rien insérer si code16 différent de 16
        if (!isset($row['code16']) || (int)$row['code16'] !== 16) {
            return null;
        }

        // ❌ Ne rien insérer si code est null ou vide
        if (empty($row['code'])) {
            return null;
        }

        $date = null;

        // 🔥 Vérification si date existe
        if (!empty($row['date'])) {

            // 🔹 Cas 1 : format 160126 (ddmmyy)
            if (is_numeric($row['date']) && strlen($row['date']) == 6) {

                $day   = substr($row['date'], 0, 2);
                $month = substr($row['date'], 2, 2);
                $year  = '20' . substr($row['date'], 4, 2);

                try {
                    $date = Carbon::createFromFormat('d/m/Y', "$day/$month/$year")
                        ->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            }

            // 🔹 Cas 2 : vraie date Excel (nombre long genre 45231)
            elseif (is_numeric($row['date'])) {

                try {
                    $date = Carbon::instance(
                        ExcelDate::excelToDateTimeObject($row['date'])
                    )->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            }

            // 🔹 Cas 3 : date texte normale
            else {
                try {
                    $date = Carbon::parse($row['date'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            }
        }

        return new Achat([
            'code16'        => 16,
            'date'          => $date,
            'refart'        => $row['refart'] ?? null,
            'fournisseur'   => $row['fournisseur'] ?? null,
            'code'          => $row['code'],
            'liblong'       => $row['liblong'] ?? null,
            'prixu'         => $row['prixu'] ?? 0,
            'quantiteachat' => $row['quantiteachat'] ?? 0,
        ]);
    }
}
