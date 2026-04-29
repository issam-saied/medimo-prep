<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 6px 8px; border: 1px solid #d1d5db; }
        td { padding: 6px 8px; border: 1px solid #d1d5db; }
        tr:nth-child(even) { background: #f9fafb; }
        .badge { padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: bold; }
        .active   { background: #d1fae5; color: #065f46; }
        .completed { background: #dbeafe; color: #1e40af; }
        .stopped  { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <h1>Prescriptions Export</h1>
    <div class="meta">Generated on {{ now()->format('Y-m-d H:i') }} &mdash; {{ count($prescriptions) }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Patient</th>
                <th>Medication</th>
                <th>Prescriber</th>
                <th>Dosage</th>
                <th>Frequency</th>
                <th>Status</th>
                <th>Start Date</th>
                <th>End Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($prescriptions as $prescription)
            <tr>
                <td>{{ $prescription->id }}</td>
                <td>{{ $prescription->patient->name }}</td>
                <td>{{ $prescription->medication->name }} {{ $prescription->medication->strength }}{{ $prescription->medication->unit }}</td>
                <td>{{ $prescription->prescriber->name }}</td>
                <td>{{ $prescription->dosage }}</td>
                <td>{{ $prescription->frequency }}x/day</td>
                <td><span class="badge {{ $prescription->status }}">{{ $prescription->status }}</span></td>
                <td>{{ \Carbon\Carbon::parse($prescription->start_date)->format('Y-m-d') }}</td>
                <td>{{ $prescription->end_date ? \Carbon\Carbon::parse($prescription->end_date)->format('Y-m-d') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
