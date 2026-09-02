<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 16px 0 6px; padding-bottom: 3px; border-bottom: 2px solid #333; }
        .muted { color: #666; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; vertical-align: middle; }
        th { background: #333; color: #fff; font-size: 9px; text-transform: uppercase; }
        .summary-grid { width: 100%; border: none; margin-bottom: 4px; }
        .summary-grid td { border: 1px solid #ddd; padding: 8px 10px; }
        .summary-label { color: #666; font-size: 8px; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .summary-value { font-size: 14px; font-weight: bold; }
        .alert-value { color: #b91c1c; }
        .no-data { color: #aaa; font-style: italic; }
    </style>
</head>
<body>
    <h1>Fleet Summary Report</h1>
    <p class="muted">{{ $data['period_label'] ?? $data['month'] }}</p>

    <h2>Fleet Overview</h2>
    <table class="summary-grid">
        <tr>
            <td><span class="summary-label">Total Vehicles</span><span class="summary-value">{{ $data['vehicles']['total'] }}</span></td>
            <td><span class="summary-label">Active</span><span class="summary-value">{{ $data['vehicles']['active'] }}</span></td>
            <td><span class="summary-label">In Maintenance</span><span class="summary-value">{{ $data['vehicles']['maintenance'] }}</span></td>
            <td><span class="summary-label">Inactive</span><span class="summary-value">{{ $data['vehicles']['inactive'] }}</span></td>
        </tr>
    </table>

    <h2>Activity This Month</h2>
    <table class="summary-grid">
        <tr>
            <td><span class="summary-label">Total Journeys</span><span class="summary-value">{{ $data['total_journeys'] }}</span></td>
            <td><span class="summary-label">Total Distance</span><span class="summary-value">{{ number_format($data['total_distance'], 2) }} km</span></td>
            <td><span class="summary-label">Fleet Avg Efficiency</span><span class="summary-value">{{ $data['fleet_avg_kmpl'] ?? '-' }} km/L</span></td>
        </tr>
        <tr>
            <td><span class="summary-label">Total Fuel Purchased</span><span class="summary-value">{{ number_format($data['total_fuel_litres'], 2) }} L</span></td>
            <td><span class="summary-label">Total Fuel Cost</span><span class="summary-value">{{ number_format($data['total_fuel_cost'], 2) }}</span></td>
            <td><span class="summary-label">Total Maintenance Cost</span><span class="summary-value">{{ number_format($data['total_maintenance_cost'], 2) }}</span></td>
        </tr>
    </table>

    <h2>Maintenance Status (Fleet-Wide, As Of Today)</h2>
    <table class="summary-grid">
        <tr>
            <td><span class="summary-label">Currently Overdue</span><span class="summary-value alert-value">{{ $data['vehicles_overdue_maintenance'] }}</span></td>
            <td><span class="summary-label">Due Within 7 Days / 500 km</span><span class="summary-value">{{ $data['vehicles_due_soon_maintenance'] }}</span></td>
        </tr>
    </table>

    <h2>Per-Vehicle Breakdown ({{ $data['period_label'] ?? $data['month'] }})</h2>
    <table>
        <thead>
            <tr>
                <th>Vehicle</th>
                <th>Trips</th>
                <th>Distance</th>
                <th>Fuel Purchased</th>
                <th>Fuel Cost</th>
                <th>Efficiency</th>
                <th>Maintenance Cost</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['per_vehicle'] as $v)
                <tr>
                    <td>{{ $v['vehicle'] }}</td>
                    <td>{{ $v['trips'] }}</td>
                    <td>{{ number_format($v['distance'], 2) }} km</td>
                    <td>{{ number_format($v['fuel_litres'], 2) }} L</td>
                    <td>{{ number_format($v['fuel_cost'], 2) }}</td>
                    <td>{{ $v['kmpl'] ?? '-' }} km/L</td>
                    <td>{{ number_format($v['maintenance_cost'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="no-data">No vehicles found.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>