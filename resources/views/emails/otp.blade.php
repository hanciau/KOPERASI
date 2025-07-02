{{-- resources/views/emails/otp.blade.php --}}
<x-mail::message>
# Kode OTP Anda

Gunakan kode berikut untuk melanjutkan proses login/verifikasi Anda:

<h1 style="text-align: center; color: #333; font-size: 24px; font-weight: bold;">{{ $otpCode }}</h1>

Kode ini berlaku selama 5 menit.

Jika Anda tidak merasa melakukan permintaan ini, abaikan email ini.

Terima kasih,<br>
{{ config('app.name') }}
</x-mail::message>