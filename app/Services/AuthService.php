<?php
// app/Services/AuthService.php
namespace App\Services;

use App\Models\Admin;
use App\Models\Member;
use App\Models\MemberRequest;
use App\Traits\SendsOtp;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class AuthService
{
    use SendsOtp; // Trait ini diasumsikan memiliki sendOtp() dan verifyOtp()

    /**
     * Send OTP for Admin login.
     *
     * @param string $email
     * @return void
     * @throws ValidationException|\Exception
     */
    public function sendAdminLoginOtp(string $email): void
    {
        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Email tidak terdaftar sebagai Admin.'],
            ]);
        }
        $this->sendOtp($email); // Ini akan mengupdate MemberRequest OTP. Sesuaikan jika Admin OTP terpisah.
    }

    /**
     * Send OTP for Member login or determine if registration is needed.
     * Disesuaikan: Sekarang menampilkan status jika email sudah terdaftar sebagai MemberRequest.
     *
     * @param string $email
     * @return array {'action': string, 'status'?: string, 'message': string}
     * @throws \Exception
     */
    public function sendMemberLoginOtp(string $email): array
    {
        // 1. Cek apakah sudah menjadi member aktif
        $member = Member::where('email', $email)->first();

        if ($member) {
            // Kirim OTP untuk login
            $this->sendOtp($email);

            return [
                'action' => 'otp_sent',
                'message' => 'Kode OTP telah dikirim ke email Anda untuk login member.'
            ];
        }

        // 2. Cek apakah sedang dalam proses pengajuan (belum jadi member)
        $memberRequest = MemberRequest::where('email', $email)->first();

        if ($memberRequest) {
            return [
                'action' => 'existing_request',
                'message' => 'Email ini sudah memiliki pengajuan pendaftaran silahkan tunggu email undangan interview',
                'status' => $memberRequest->status,
                'details' => [
                    'id' => $memberRequest->id,
                    'email' => $memberRequest->email,
                    'nama' => $memberRequest->nama,
                    'nip' => $memberRequest->nip,
                    'nik' => $memberRequest->nik,
                    'jabatan' => $memberRequest->jabatan,
                    'reason' => $memberRequest->reason,
                    'attempts' => $memberRequest->attempts,
                    'interview_sent_at' => $memberRequest->interview_sent_at,
                    'interview_completed' => $memberRequest->interview_completed,
                    'created_at' => $memberRequest->created_at,
                    'updated_at' => $memberRequest->updated_at,
                ]
            ];
        }

        // 3. Tidak ditemukan
        return [
            'action' => 'registration_needed',
            'message' => 'Email tidak terdaftar. Silakan daftar terlebih dahulu.'
        ];
    }


    /**
     * Verify OTP and log in Admin.
     *
     * @param string $email
     * @param string $otpCode
     * @return string API token
     * @throws ValidationException
     */
    public function adminLogin(string $email, string $otpCode): string
    {
        if (!$this->verifyOtp($email, $otpCode)) {
            throw ValidationException::withMessages([
                'otp_code' => ['Kode OTP salah atau sudah kadaluarsa.'],
            ]);
        }

        $admin = Admin::where('email', $email)->first();

        if (!$admin) {
            throw ValidationException::withMessages([
                'email' => ['Admin tidak ditemukan.'],
            ]);
        }

        $admin->tokens()->delete();
        $token = $admin->createToken('admin-token')->plainTextToken;

        return $token;
    }

    /**
     * Verify OTP and log in Member.
     *
     * @param string $email
     * @param string $otpCode
     * @return string API token
     * @throws ValidationException
     */
    public function memberLogin(string $email, string $otpCode): string
    {
        if (!$this->verifyOtp($email, $otpCode)) {
            throw ValidationException::withMessages([
                'otp_code' => ['Kode OTP salah atau sudah kadaluarsa.'],
            ]);
        }

        $member = Member::where('email', $email)->where('status', 'active')->first();

        if (!$member) {
            throw ValidationException::withMessages([
                'email' => ['Akun member tidak ditemukan atau belum aktif.'],
            ]);
        }

        $member->tokens()->delete();
        $token = $member->createToken('member-token')->plainTextToken;

        return $token;
    }

    /**
     * Send OTP for email verification during member registration.
     * Disesuaikan: Menangani re-registrasi untuk status 'rejected'.
     *
     * @param string $email
     * @return array {'action': string, 'status'?: string, 'message': string}
     * @throws ValidationException|\Exception
     */
    public function sendRegistrationOtp(string $email): array
    {
        // 1. Cek apakah sudah menjadi anggota aktif
        if (Member::where('email', $email)->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah terdaftar sebagai anggota aktif. Silakan gunakan fitur login.'],
            ]);
        }

        // 2. Cek apakah email sudah pernah mengajukan pendaftaran
        $memberRequest = MemberRequest::where('email', $email)->first();

        if ($memberRequest) {
            if (in_array($memberRequest->status, ['pending', 'interview'])) {
                return [
                    'action' => 'existing_request',
                    'message' => 'Email ini sudah memiliki pengajuan pendaftaran.',
                    'status' => $memberRequest->status,
                    'details' => [
                        'email' => $memberRequest->email,
                        'nama' => $memberRequest->nama,
                        'created_at' => $memberRequest->created_at,
                    ]
                ];
            }

            if ($memberRequest->status === 'approved') {
                throw ValidationException::withMessages([
                    'email' => ['Email ini terkait dengan pengajuan yang sudah disetujui. Silakan gunakan fitur login.'],
                ]);
            }

            if ($memberRequest->status === 'rejected') {
                // Kirim OTP untuk pendaftaran ulang
                $this->sendOtp($email);
                return [
                    'action' => 're_registration_allowed',
                    'message' => 'Pengajuan Anda sebelumnya ditolak. Kode OTP telah dikirim untuk pendaftaran ulang.',
                    'status' => 'rejected'
                ];
            }
        }

        // 3. Belum pernah daftar → kirim OTP baru
        $this->sendOtp($email);
        return [
            'action' => 'otp_sent',
            'message' => 'Kode OTP verifikasi email telah dikirim. Mohon cek email Anda.'
        ];
    }
    /**
     * Verify OTP for email verification during member registration.
     *
     * @param string $email
     * @param string $otpCode
     * @return bool
     * @throws ValidationException
     */
    public function verifyRegistrationEmail(string $email, string $otpCode): bool
    {
        if (!$this->verifyOtp($email, $otpCode)) {
            throw ValidationException::withMessages([
                'otp_code' => ['Kode OTP salah atau sudah kadaluarsa.'],
            ]);
        }
        return true;
    }

    /**
     * Handle member registration submission after email is verified.
     * Disesuaikan: Menambahkan `attempts` (+1) untuk re-registrasi.
     *
     * @param array $data
     * @return MemberRequest
     * @throws ValidationException
     */
    public function registerMemberRequest(array $data): MemberRequest
    {
        // Pastikan email belum terdaftar sebagai member aktif
        if (Member::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah terdaftar sebagai member aktif.'],
            ]);
        }

        // Cek apakah ada request sebelumnya
        $memberRequest = MemberRequest::where('email', $data['email'])->first();

        if ($memberRequest) {
            // Jika pengajuan sebelumnya rejected, izinkan pendaftaran ulang dan tambahkan attempts
            if ($memberRequest->status === 'rejected') {
                $data['attempts'] = $memberRequest->attempts + 1; // Increment attempts (1+1)
            }
            // Jika masih pending, interview, atau approved, tidak boleh mendaftar ulang
            elseif (in_array($memberRequest->status, ['pending', 'interview', 'approved'])) {
                throw ValidationException::withMessages([
                    'email' => ['Email ini sudah memiliki pengajuan pendaftaran yang sedang diproses atau sudah disetujui.'],
                ]);
            }
        } else {
            // Ini adalah pengajuan pertama
            $data['attempts'] = 1;
        }

        // Batasi jumlah attempts untuk pengajuan pendaftaran
        if (isset($data['attempts']) && $data['attempts'] > 3) { // Batas 3 kali coba untuk pengajuan total
            throw ValidationException::withMessages([
                'email' => ['Email ini sudah mencapai batas maksimum pengajuan pendaftaran (3x). Silakan hubungi Admin.'],
            ]);
        }

        // Simpan atau update data ke tabel member_requests
        // Pastikan kolom OTP dibersihkan jika ada data lama yang tersimpanz
        return MemberRequest::updateOrCreate(
            ['email' => $data['email']],
            array_merge($data, [
                'status' => 'pending', // Set status ke pending untuk pengajuan baru/ulang
            ])
        );
    }

    // HAPUS DUA METODE INI KARENA TIDAK JADI ADA LOGIN TERPISAH UNTUK MEMBER_REQUEST:
    // public function sendStatusCheckOtp(string $email): void { ... }
    // public function verifyStatusCheckOtp(string $email, string $otpCode): string { ... }
}
