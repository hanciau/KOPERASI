<!DOCTYPE html>
<html>
<head>
    <title>Interview Koperasi</title>
</head>
<body>
    <h2>Assalamu'alaikum,</h2>
    <p>Anda telah dijadwalkan untuk interview sebagai calon anggota koperasi.</p>
    <p><strong>Nama:</strong> {{ $request->nama }}</p>
    <p><strong>Email:</strong> {{ $request->email }}</p>
    <p><strong>Tanggal Pengiriman:</strong> {{ $request->interview_sent_at->format('d M Y H:i') }}</p>

    <p>Silakan hadir ke ruang koperasi untuk menyelesaikan proses interview dalam 5 hari.</p>

    <p>Terima kasih,</p>
    <p><em>Koperasi Pesantren</em></p>
</body>
</html>
