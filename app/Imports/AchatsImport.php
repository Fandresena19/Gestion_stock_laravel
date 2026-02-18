<?php

namespace App\Imports;

use App\Models\Achat;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Illuminate\Contracts\Queue\ShouldQueue;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;

class AchatsImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts,
    ShouldQueue
{
    public function model(array $row)
    {
        if (!isset($row['code16']) || (int)$row['code16'] !== 16) {
            return null;
        }

        if (empty($row['code'])) {
            return null;
        }

        $date = null;

        if (!empty($row['date'])) {

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
            } elseif (is_numeric($row['date'])) {

                try {
                    $date = Carbon::instance(
                        ExcelDate::excelToDateTimeObject($row['date'])
                    )->format('Y-m-d');
                } catch (\Exception $e) {
                    $date = null;
                }
            } else {

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

    // 🔥 Lecture par bloc
    public function chunkSize(): int
    {
        return 1000;
    }

    // 🔥 Insert par lot
    public function batchSize(): int
    {
        return 1000;
    }
}
