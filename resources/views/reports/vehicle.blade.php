<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 9.5px; color: #1f2937; }
        h1 { font-size: 16px; margin: 0 0 2px; }
        h2 { font-size: 12px; margin: 16px 0 6px; padding-bottom: 3px; border-bottom: 2px solid #333; }
        .muted { color: #666; margin: 0 0 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        th, td { border: 1px solid #999; padding: 5px; text-align: left; vertical-align: middle; }
        th { background: #333; color: #fff; font-size: 8px; text-transform: uppercase; }
        .summary-grid { width: 100%; border: none; margin-bottom: 4px; }
        .summary-grid td { border: 1px solid #ddd; padding: 8px 10px; }
        .summary-label { color: #666; font-size: 8px; text-transform: uppercase; display: block; margin-bottom: 2px; }
        .summary-value { font-size: 13px; font-weight: bold; }
        .variance-up { color: #15803d; }
        .variance-down { color: #b91c1c; }
        .link { color: #1d4ed8; text-decoration: underline; }
        .no-data { color: #aaa; font-style: italic; }
        .footer-note { margin-top: 16px; font-size: 8px; color: #888; }
        .anchor { display: inline-block; }
    </style>
</head>
<body>
    <h1>Vehicle Log Report</h1>
    <p class="muted">
        <strong>{{ $data['vehicle']['registration_number'] ?? '-' }}</strong>
        &middot; {{ $data['vehicle']['make'] ?? '' }} {{ $data['vehicle']['model'] ?? '' }}
        &middot; Period: {{ $data['period']['from'] ?? '' }} to {{ $data['period']['to'] ?? '' }}
    </p>

    <h2>Summary</h2>
    <table class="summary-grid">
        <tr>
            <td><span class="summary-label">Total Journeys</span><span class="summary-value">{{ $data['total_journeys'] ?? 0 }}</span></td>
            <td><span class="summary-label">Total Distance</span><span class="summary-value">{{ number_format($data['total_distance'] ?? 0, 2) }} km</span></td>
            <td><span class="summary-label">Total Fuel Purchased</span><span class="summary-value">{{ number_format($data['total_fuel_litres'] ?? 0, 2) }} L</span></td>
        </tr>
        <tr>
            <td><span class="summary-label">Total Fuel Cost</span><span class="summary-value">{{ number_format($data['total_fuel_cost'] ?? 0, 2) }}</span></td>
            <td><span class="summary-label">Actual Fuel Efficiency</span><span class="summary-value">{{ $data['kmpl'] ?? '-' }} km/L</span></td>
            <td colspan="2"><span class="summary-label">Total Maintenance Cost (this period)</span><span class="summary-value">{{ number_format($data['total_maintenance_cost'] ?? 0, 2) }}</span></td>

            {{-- <td><span class="summary-label">Rated Fuel Efficiency</span><span class="summary-value">{{ $data['mileage_rated'] ?? '-' }} km/L</span></td> --}}
        </tr>
        {{-- <tr>
            <td>
                <span class="summary-label">Efficiency vs. Rated</span>
                <span class="summary-value">
                    @if ($data['mileage_variance_percent'] !== null)
                        <span class="{{ $data['mileage_variance_percent'] >= 0 ? 'variance-up' : 'variance-down' }}">
                            {{ $data['mileage_variance_percent'] >= 0 ? '+' : '' }}{{ $data['mileage_variance_percent'] }}%
                        </span>
                    @else
                        -
                    @endif
                </span>
            </td>
            <td colspan="2"><span class="summary-label">Total Maintenance Cost (this period)</span><span class="summary-value">{{ number_format($data['total_maintenance_cost'] ?? 0, 2) }}</span></td>
        </tr> --}}
    </table>

    <h2>Journey Log</h2>
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
                <th>Photos</th>
            </tr>
            <tr>
                <th></th><th>From</th><th>To</th><th></th><th></th><th></th><th></th>
                <th>From</th><th>To</th><th></th><th></th><th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['journeys'] as $journey)
                <tr>
                    <td>
                        <a class="anchor" name="journey-{{ $journey->id }}">&nbsp;</a>
                        {{ optional($journey->start_time)->format('Y-m-d') ?? '-' }}
                    </td>
                    <td>{{ $journey->start_time ? $journey->start_time->format('h:i A') : '-' }}</td>
                    <td>{{ $journey->end_time ? $journey->end_time->format('h:i A') : '-' }}</td>
                    <td>{{ $journey->driver_name }}</td>
                    <td>{{ $journey->detail_display }}</td>
                    <td>{{ $journey->purpose_display }}</td>
                    <td>{{ $journey->officer_display }}</td>
                    <td>{{ $journey->start_km_display }}</td>
                    <td>{{ $journey->end_km_display }}</td>
                    <td>{{ $journey->distance_display }}</td>
                    <td>{{ $journey->signature_display }}</td>
                    <td>
                        @if ($journey->start_photo_url)
                            <a class="link" href="{{ $journey->start_photo_url }}" target="_blank">Start Photo</a><br>
                        @else
                            <span class="no-data">No start photo</span><br>
                        @endif
                        @if ($journey->end_photo_url)
                            <a class="link" href="{{ $journey->end_photo_url }}" target="_blank">End Photo</a>
                        @else
                            <span class="no-data">No end photo</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="12" class="no-data">No journeys recorded in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Fuel Purchases</h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Driver</th>
                <th>Litres</th>
                <th>Rate/Litre</th>
                <th>Total Cost</th>
                <th>Odometer at Purchase</th>
                <th>Linked Trip</th>
                <th>Receipt</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data['fuel_entries'] as $entry)
                <tr>
                    <td>
                        <a class="anchor" name="fuel-{{ $entry->id }}">&nbsp;</a>
                        {{ optional($entry->entry_time)->format('Y-m-d h:i A') ?? '-' }}
                    </td>
                    <td>{{ $entry->driver?->name ?? '-' }}</td>
                    <td>{{ $entry->quantity_litres }}</td>
                    <td>{{ $entry->rate_per_litre }}</td>
                    <td>{{ $entry->total_cost }}</td>
                    <td>{{ $entry->odometer_reading }}</td>
                    <td>
                        @if ($entry->linked_journey_id)
                            <a class="link" href="#journey-{{ $entry->linked_journey_id }}">View Trip ({{ $entry->linked_journey_date }})</a>
                        @else
                            <span class="no-data">Not linked to a trip</span>
                        @endif
                    </td>
                    <td>
                        @if ($entry->receipt_url)
                            <a class="link" href="{{ $entry->receipt_url }}" target="_blank">View Receipt</a>
                        @else
                            <span class="no-data">No receipt</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="no-data">No fuel purchases recorded in this period.</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer-note">
        Clicking "View Trip" jumps to that journey's row in the Journey Log above (supported in most desktop PDF viewers).
    </p>
</body>
</html>