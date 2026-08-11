<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4f4f4; }
        .summary td { border: none; padding: 3px 8px 3px 0; }
    </style>
</head>
<body>
    <h1>Fleet Summary — {{ $data['month'] }}</h1>

    <table class="summary">
        <tr><td><strong>Total Vehicles</strong></td><td>{{ $data['vehicles']['total'] }} (Active: {{ $data['vehicles']['active'] }}, Maintenance: {{ $data['vehicles']['maintenance'] }})</td></tr>
        <tr><td><strong>Total Journeys</strong></td><td>{{ $data['total_journeys'] }}</td></tr>
        <tr><td><strong>Total Distance</strong></td><td>{{ number_format($data['total_distance'], 2) }} km</td></tr>
        <tr><td><strong>Total Fuel</strong></td><td>{{ number_format($data['total_fuel_litres'], 2) }} L</td></tr>
        <tr><td><strong>Total Fuel Cost</strong></td><td>{{ number_format($data['total_fuel_cost'], 2) }}</td></tr>
        <tr><td><strong>Fleet Avg KMPL</strong></td><td>{{ $data['fleet_avg_kmpl'] ?? '-' }}</td></tr>
        <tr><td><strong>Total Maintenance Cost</strong></td><td>{{ number_format($data['total_maintenance_cost'], 2) }}</td></tr>
    </table>

    <table>
        <thead>
            <tr><th>Vehicle</th><th>Distance (km)</th><th>Fuel (L)</th><th>KMPL</th></tr>
        </thead>
        <tbody>
            @foreach ($data['per_vehicle'] as $row)
                <tr>
                    <td>{{ $row['vehicle'] }}</td>
                    <td>{{ $row['distance'] }}</td>
                    <td>{{ $row['fuel_litres'] }}</td>
                    <td>{{ $row['kmpl'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>