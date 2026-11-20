<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FleetDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_creates_vehicle_imports_routes_and_schedules_trip(): void
    {
        $this->get('/')->assertOk()->assertSee('Fleet Ops Platform');

        $this->post('/vehicles', [
            'code' => 'BUS-900',
            'plate' => 'DEMO-900',
            'capacity' => 45,
        ])->assertRedirect('/');

        $this->post('/routes/import-csv', [
            'csv_content' => "code,name,origin_name,destination_name,planned_distance_km\nR-900,Demo Route,Origin,Dest,14.2",
        ])->assertRedirect('/');

        $this->post('/trips', [
            'vehicle_id' => 1,
            'route_id' => 1,
            'operator_name' => 'Alex Demo',
        ])->assertRedirect('/');

        $this->get('/')
            ->assertOk()
            ->assertSee('Alex Demo')
            ->assertSee('BUS-900')
            ->assertSee('Demo Route');
    }
}
