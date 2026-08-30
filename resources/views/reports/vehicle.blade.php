<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 10px; }
        h1 { font-size: 16px; margin-bottom: 0; }
        .muted { color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 14px; }
        th, td { border: 1px solid #999; padding: 4px 5px; text-align: left; vertical-align: top; }
        th { background: #f4f4f4; font-size: 9px; text-transform: uppercase; }
        .summary { margin-top: 14px; border: none; }
        .summary td { border: none; padding: 3px 8px 3px 0; font-size: 11px; }
    </style>
</head>
<body>
    <h1>Vehicle Log — {{ $data['vehicle']['registration_number'] }}</h1>
    <p class="muted">{{ $data['vehicle']['make'] }} {{ $data['vehicle']['model'] }} &middot; {{ $data['period']['from'] }} to {{ $data['period']['to'] }}</p>

    <table class="summary">
        <tr><td><strong>Total Journeys</strong></td><td>{{ $data['total_journeys'] }}</td></tr>
        <tr><td><strong>Total Distance</strong></td><td>{{ number_format($data['total_distance'], 2) }} km</td></tr>
        <tr><td><strong>KMPL</strong></td><td>{{ $data['kmpl'] ?? '-' }}</td></tr>
        <tr><td><strong>Total Fuel Cost</strong></td><td>{{ number_format($data['total_fuel_cost'], 2) }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th colspan="2">Time</th>
                <th>Detail of Journey</th>
                <th>Purpose of Journey</th>
                <th>Name of Officer/Official</th>
                <th colspan="2">Meter Reading</th>
                <th>KM Covered</th>
                <th>Signature</th>
                <th>P.O.L. Drawn</th>
                <th>Remarks</th>
            </tr>
            <tr>
                <th></th>
                <th>From</th>
                <th>To</th>
                <th></th>
                <th></th>
                <th></th>
                <th>From</th>
                <th>To</th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['journeys'] as $journey)
                <tr>
                    <td>{{ optional($journey->start_time)->format('Y-m-d') }}</td>
                    <td>{{ optional($journey->start_time)->format('H:i') }}</td>
                    <td>{{ optional($journey->end_time)->format('H:i') }}</td>
                    <td>{{ $journey->detail_of_journey ?? '-' }}</td>
                    <td>{{ $journey->purpose ?? '-' }}</td>
                    <td>{{ $journey->officer_name ?? $journey->driver_name ?? '-' }}</td>
                    <td>{{ $journey->start_km }}</td>
                    <td>{{ $journey->end_km ?? '-' }}</td>
                    <td>{{ $journey->total_distance ?? '-' }}</td>
                    <td>{{ $journey->signature ?? '-' }}</td>
                    <td>{{ $journey->pol_drawn ?? '-' }}</td>
                    <td>{{ $journey->remarks ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>