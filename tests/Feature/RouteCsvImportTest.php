<?php

namespace Tests\Feature;

use App\Services\RouteCsvImporterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RouteCsvImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_imports_valid_rows_and_reports_invalid_lines(): void
    {
        $csv = implode("\n", [
            'code,name,origin_name,destination_name,planned_distance_km',
            'R-100,Airport Express,Downtown,Airport,35.5',
            'R-BAD,Missing Distance,A,B,-4',
            'R-100,Airport Express Updated,Downtown,Airport,36.0',
        ]);

        $result = app(RouteCsvImporterService::class)->import($csv);

        $this->assertSame(1, $result['imported']);
        $this->assertSame(1, $result['updated']);
        $this->assertCount(1, $result['errors']);
        $this->assertDatabaseHas('routes', [
            'code' => 'R-100',
            'name' => 'Airport Express Updated',
        ]);
    }
}
