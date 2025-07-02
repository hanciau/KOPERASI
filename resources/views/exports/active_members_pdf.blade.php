<!DOCTYPE html>
<html>
<head>
    <title>Laporan Member Aktif</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: left; }
    </style>
</head>
<body>
    <h2>Laporan Member Aktif - {{ date('Y') }}</h2>
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Email</th>
                <th>NIP</th>
                <th>Nama</th>
                <th>Jabatan</th>
                <th>Bergabung</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($members as $i => $member)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->nip }}</td>
                    <td>{{ $member->name ?? '-' }}</td>
                    <td>{{ $member->jabatan }}</td>
                    <td>{{ \Carbon\Carbon::parse($member->joined_at)->format('d-m-Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
