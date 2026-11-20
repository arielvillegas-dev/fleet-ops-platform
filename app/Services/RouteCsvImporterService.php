<?php

namespace App\Services;

use App\Models\Route;
use Illuminate\Support\Facades\DB;

class RouteCsvImporterService
{
    /**
     * Imports routes from CSV string content and returns per-line diagnostics.
     *
     * @return array{imported: int, updated: int, errors: array<int, string>}
     */
    public function import(string $csvContent): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent)) ?: [];
        if (count($lines) < 2) {
            return ['imported' => 0, 'updated' => 0, 'errors' => ['CSV must contain a header row and at least one data row.']];
        }

        $header = str_getcsv(array_shift($lines), ',', '"', '');
        $expected = ['code', 'name', 'origin_name', 'destination_name', 'planned_distance_km'];
        if ($header !== $expected) {
            return ['imported' => 0, 'updated' => 0, 'errors' => ['Invalid CSV header. Expected: '.implode(',', $expected)]];
        }

        $imported = 0;
        $updated = 0;
        $errors = [];

        DB::transaction(function () use ($lines, &$imported, &$updated, &$errors) {
            foreach ($lines as $idx => $line) {
                $rowNum = $idx + 2;
                if (trim($line) === '') {
                    continue;
                }

                $cols = str_getcsv($line, ',', '"', '');
                if (count($cols) !== 5) {
                    $errors[] = "Line {$rowNum}: expected 5 columns.";
                    continue;
                }

                [$code, $name, $origin, $dest, $distance] = array_map('trim', $cols);
                if ($code === '' || $name === '' || ! is_numeric($distance) || (float) $distance <= 0) {
                    $errors[] = "Line {$rowNum}: invalid code, name, or positive planned_distance_km.";
                    continue;
                }

                $existing = Route::query()->where('code', $code)->first();
                if ($existing) {
                    $existing->update([
                        'name' => $name,
                        'origin_name' => $origin,
                        'destination_name' => $dest,
                        'planned_distance_km' => (float) $distance,
                    ]);
                    $updated++;
                } else {
                    Route::query()->create([
                        'code' => $code,
                        'name' => $name,
                        'origin_name' => $origin,
                        'destination_name' => $dest,
                        'planned_distance_km' => (float) $distance,
                    ]);
                    $imported++;
                }
            }
        });

        return ['imported' => $imported, 'updated' => $updated, 'errors' => $errors];
    }
}
