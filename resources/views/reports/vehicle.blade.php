<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 9px; }
        h1 { font-size: 15px; margin-bottom: 0; }
        .muted { color: #666; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #999; padding: 4px; text-align: left; vertical-align: middle; }
        th { background: #f4f4f4; font-size: 8px; text-transform: uppercase; }
        .summary { margin-top: 12px; border: none; }
        .summary td { border: none; padding: 3px 8px 3px 0; font-size: 11px; }
        .thumb { max-width: 55px; max-height: 55px; display: block; }
        .no-photo { color: #aaa; font-size: 8px; }
    </style>
</head>
<body>
    <h1>Vehicle Log — {{ $data['vehicle']['registration_number'] ?? '-' }}</h1>
    <p class="muted">{{ $data['vehicle']['make'] ?? '' }} {{ $data['vehicle']['model'] ?? '' }} &middot; {{ $data['period']['from'] ?? '' }} to {{ $data['period']['to'] ?? '' }}</p>

    <table class="summary">
        <tr><td><strong>Total Journeys</strong></td><td>{{ $data['total_journeys'] ?? 0 }}</td></tr>
        <tr><td><strong>Total Distance</strong></td><td>{{ number_format($data['total_distance'] ?? 0, 2) }} km</td></tr>
        <tr><td><strong>KMPL</strong></td><td>{{ $data['kmpl'] ?? '-' }}</td></tr>
        <tr><td><strong>Total Fuel Cost</strong></td><td>{{ number_format($data['total_fuel_cost'] ?? 0, 2) }}</td></tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th colspan="2">Time</th>
                <th>Driver</th>
                <th>Detail of Journey</th>
                <th>Purpose</th>
                <th>Officer/Official</th>
                <th colspan="2">Meter Reading</th>
                <th>KM Covered</th>
                <th>Signature</th>
                <th>P.O.L. Drawn</th>
                <th>Start / End Photo</th>
            </tr>
            <tr>
                <th></th><th>From</th><th>To</th><th></th><th></th><th></th><th></th>
                <th>From</th><th>To</th><th></th><th></th><th></th><th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data['journeys'] as $journey)
                @php
                    $startImg = \App\Http\Controllers\Api\V1\ReportController::photoDataUri($journey->start_photo_path);
                    $endImg = \App\Http\Controllers\Api\V1\ReportController::photoDataUri($journey->end_photo_path);
                    $invoiceImg = $journey->pol_drawn > 0 ? \App\Http\Controllers\Api\V1\ReportController::photoDataUri($journey->pol_invoice_photo_path) : null;
                @endphp
                <tr>
                    <td>{{ optional($journey->start_time)->format('Y-m-d') ?? '-' }}</td>
                    <td>{{ $journey->start_time ? $journey->start_time->format('h:i A') : '-' }}</td>
                    <td>{{ $journey->end_time_display }}</td>
                    <td>{{ $journey->driver_name }}</td>
                    <td>{{ $journey->detail_display }}</td>
                    <td>{{ $journey->purpose_display }}</td>
                    <td>{{ $journey->officer_display }}</td>
                    <td>{{ $journey->start_km_display }}</td>
                    <td>{{ $journey->end_km_display }}</td>
                    <td>{{ $journey->distance_display }}</td>
                    <td>{{ $journey->signature_display }}</td>
                    {{-- <td>
                        {{ $journey->pol_display }}
                        @if ($invoiceImg)
                            <br><img src="{{ $invoiceImg }}" class="thumb">
                        @elseif ($journey->pol_drawn > 0)
                            <br><span class="no-photo">no invoice</span>
                        @endif
                    </td>
                    <td>
                        @if ($startImg)
                            <img src="{{ $startImg }}" class="thumb">
                        @else
                            <span class="no-photo">no photo</span>
                        @endif
                        @if ($endImg)
                            <img src="{{ $endImg }}" class="thumb">
                        @else
                            <span class="no-photo">no photo</span>
                        @endif
                    </td> --}}

                    <td>
                        {{ $journey->pol_display }}

                        @if ($invoiceImg)
                            <br>
                            <a href="{{ $invoiceImg }}" target="_blank" rel="noopener noreferrer">
                                View Invoice
                            </a>
                        @elseif ($journey->pol_drawn > 0)
                            <br>
                            <span class="no-photo">no invoice</span>
                        @endif
                    </td>

                    <td>
                        @if ($startImg)
                            <a href="{{ $startImg }}" target="_blank" rel="noopener noreferrer">
                                View Start Photo
                            </a>
                        @else
                            <span class="no-photo">no photo</span>
                        @endif

                        <br>

                        @if ($endImg)
                            <a href="{{ $endImg }}" target="_blank" rel="noopener noreferrer">
                                View End Photo
                            </a>
                        @else
                            <span class="no-photo">no photo</span>
                        @endif
                    </td>

                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>