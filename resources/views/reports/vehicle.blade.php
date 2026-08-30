<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h1 { font-size: 18px; margin-bottom: 0; }
        .muted { color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        th { background: #f4f4f4; }
        .summary { margin-top: 16px; }
        .summary td { border: none; padding: 3px 8px 3px 0; }
    </style>
</head>
<body>
    <h1>Vehicle Report — {{ $data['vehicle']['registration_number'] }}</h1>
    <p class="muted">{{ $data['vehicle']['make'] }} {{ $data['vehicle']['model'] }} &middot; {{ $data['period']['from'] }} to {{ $data['period']['to'] }}</p>

    <table class="summary">
        <tr><td><strong>Total Journeys</strong></td><td>{{ $data['total_journeys'] }}</td></tr>
        <tr><td><strong>Total Distance</strong></td><td>{{ number_format($data['total_distance'], 2) }} km</td></tr>
        <tr><td><strong>Total Fuel</strong></td><td>{{ number_format($data['total_fuel_litres'], 2) }} L</td></tr>
        <tr><td><strong>Total Fuel Cost</strong></td><td>{{ number_format($data['total_fuel_cost'], 2) }}</td></tr>
        <tr><td><strong>KMPL</strong></td><td>{{ $data['kmpl'] ?? '-' }}</td></tr>
        <tr><td><strong>Fuel Cost / KM</strong></td><td>{{ $data['fuel_cost_per_km'] ?? '-' }}</td></tr>
        <tr><td><strong>Maintenance Cost</strong></td><td>{{ number_format($data['total_maintenance_cost'], 2) }}</td></tr>
    </table>

    <table>
        <thead>
            <tr><th>Start</th><th>End</th><th>Start KM</th><th>End KM</th><th>Distance</th><th>Photos</th></tr>
        </thead>
        <tbody>
            @foreach ($data['journeys'] as $journey)
                <tr>
                    <td>{{ optional($journey->start_time)->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($journey->end_time)->format('Y-m-d H:i') }}</td>
                    <td>{{ $journey->start_km }}</td>
                    <td>{{ $journey->end_km }}</td>
                    <td>{{ $journey->total_distance }}</td>
                    <td>
                        @if ($journey->start_photo_url)
                            <a href="{{ $journey->start_photo_url }}">Start</a>
                        @endif
                        @if ($journey->end_photo_url)
                            <a href="{{ $journey->end_photo_url }}">End</a>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>