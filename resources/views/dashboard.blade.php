<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Ops Platform Demo</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #0f172a; color: #f8fafc; margin: 0; padding: 24px 16px; }
        .container { max-width: 1100px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin-bottom: 16px; }
        .card { background: #1e293b; border: 1px solid #334155; border-radius: 10px; padding: 18px; margin-bottom: 16px; }
        label { display: grid; gap: 6px; margin-bottom: 10px; font-size: 0.9rem; color: #94a3b8; }
        input, select, textarea { padding: 8px 10px; border-radius: 6px; border: 1px solid #334155; background: #0f172a; color: #f8fafc; }
        button { padding: 9px 14px; border: none; border-radius: 6px; background: #38bdf8; color: #0f172a; font-weight: 600; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 8px; border-bottom: 1px solid #334155; font-size: 0.9rem; }
        .alert { padding: 10px 12px; border-radius: 6px; background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; margin-bottom: 16px; }
    </style>
</head>
<body>
<div class="container">
    <h1>Fleet Ops Platform (Full Stack + Telemetry Demo)</h1>
    @if (session('status'))
        <div id="flash-status" class="alert">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert" style="background: rgba(239,68,68,0.2); border-color: #ef4444;">
            {{ implode(' ', $errors->all()) }}
        </div>
    @endif

    <div class="grid">
        <section class="card">
            <h2>Register Vehicle</h2>
            <form id="vehicle-form" method="POST" action="{{ route('vehicles.store') }}">
                @csrf
                <label>Code <input name="code" required placeholder="BUS-101"></label>
                <label>Plate <input name="plate" required placeholder="DEMO-101"></label>
                <label>Capacity <input name="capacity" type="number" value="40" required></label>
                <button type="submit">Save Vehicle</button>
            </form>
        </section>

        <section class="card">
            <h2>Import Routes (CSV)</h2>
            <form id="csv-form" method="POST" action="{{ route('routes.import') }}">
                @csrf
                <label>CSV Content
                    <textarea name="csv_content" rows="5" required>code,name,origin_name,destination_name,planned_distance_km
R-DEMO-1,Central Loop,Terminal North,Downtown Hub,12.5</textarea>
                </label>
                <button type="submit">Import Routes</button>
            </form>
        </section>

        <section class="card">
            <h2>Schedule Trip</h2>
            <form id="trip-form" method="POST" action="{{ route('trips.store') }}">
                @csrf
                <label>Vehicle
                    <select name="vehicle_id" required>
                        @foreach ($vehicles as $v)
                            <option value="{{ $v->id }}">{{ $v->code }} ({{ $v->plate }})</option>
                        @endforeach
                    </select>
                </label>
                <label>Route
                    <select name="route_id" required>
                        @foreach ($routes as $r)
                            <option value="{{ $r->id }}">{{ $r->code }} - {{ $r->name }}</option>
                        @endforeach
                    </select>
                </label>
                <label>Operator <input name="operator_name" required placeholder="Demo Operator"></label>
                <button type="submit">Create Trip</button>
            </form>
        </section>
    </div>

    <section class="card">
        <h2>Trips & Telemetry Distance</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Operator</th>
                    <th>Vehicle</th>
                    <th>Route</th>
                    <th>Status</th>
                    <th>Points</th>
                    <th>Actual km</th>
                </tr>
            </thead>
            <tbody id="trips-tbody">
                @forelse ($trips as $t)
                    <tr>
                        <td>{{ $t->id }}</td>
                        <td>{{ $t->operator_name }}</td>
                        <td>{{ $t->vehicle?->code }}</td>
                        <td>{{ $t->route?->name }}</td>
                        <td>{{ $t->status }}</td>
                        <td>{{ $t->telemetry_points_count }}</td>
                        <td>{{ number_format($t->actual_distance_km, 3) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7">No trips scheduled yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</div>
</body>
</html>
