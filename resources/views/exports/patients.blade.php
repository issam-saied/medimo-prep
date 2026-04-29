<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1a1a1a; }
        h1 { font-size: 18px; margin-bottom: 4px; }
        .meta { color: #666; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th { background: #f3f4f6; text-align: left; padding: 8px; border: 1px solid #d1d5db; }
        td { padding: 8px; border: 1px solid #d1d5db; }
        tr:nth-child(even) { background: #f9fafb; }
    </style>
</head>
<body>
    <h1>Patients Export</h1>
    <div class="meta">Generated on {{ now()->format('Y-m-d H:i') }} &mdash; {{ count($patients) }} record(s)</div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Birthdate</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($patients as $patient)
            <tr>
                <td>{{ $patient->id }}</td>
                <td>{{ $patient->name }}</td>
                <td>{{ $patient->birthdate ? \Carbon\Carbon::parse($patient->birthdate)->format('Y-m-d') : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
