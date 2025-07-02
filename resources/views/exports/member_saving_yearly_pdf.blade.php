<!DOCTYPE html>
<html>
<head>
    <title>Laporan Tahunan Simpanan Member</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        h2 { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h2>Laporan Tahunan Simpanan Member - Tahun {{ $tahun }}</h2>
    <p>Total Simpanan Koperasi: <strong>Rp {{ $total_simpanan }}</strong></p>

    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Simpanan</th>
                <th>Persentase Kepemilikan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $member)
                <tr>
                    <td>{{ $member['name'] }}</td>
                    <td>{{ $member['email'] }}</td>
                    <td>Rp {{ $member['current_balance'] }}</td>
                    <td>{{ $member['persentase'] }} %</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
