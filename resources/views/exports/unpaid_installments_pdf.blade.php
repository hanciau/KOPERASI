<!DOCTYPE html>
<html>
<head>
    <title>Laporan Cicilan Member Bulan <?php{ $data['bulan'] }?></title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 5px;
            text-align: left;
        }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <h2>Laporan Cicilan Belum Dibayar (1 Cicilan per Member) Bulan <?php{ $data['bulan'] }?></h2>
    <table>
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>ID Pinjaman</th>
                <th>ID Cicilan</th>
                <th>Jatuh Tempo</th>
                <th>Jumlah</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $item)
                <tr>
                    <td>{{ $item['member_name'] }}</td>
                    <td>{{ $item['member_email'] }}</td>
                    <td>{{ $item['loan_id'] }}</td>
                    <td>{{ $item['installment_id'] }}</td>
                    <td>{{ $item['due_date'] }}</td>
                    <td>Rp {{ $item['amount'] }}</td>
                    <td>{{ $item['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
