<!DOCTYPE html>
<html>
<head>
    <title>Status Pengajuan</title>
</head>
<body>
    <h2>Assalamu'alaikum,</h2>
    <p>{{ $messageText }}</p>

    <p><strong>Status Saat Ini:</strong> {{ strtoupper($request->status) }}</p>
    @if ($request->reason)
        <p><strong>Alasan Penolakan:</strong> {{ $request->reason }}</p>
    @endif

    <p>Terima kasih,</p>
    <p><em>Koperasi Pesantren</em></p>
</body>
</html>
z