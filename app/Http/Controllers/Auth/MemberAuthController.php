<?php

// app/Http/Controllers/Auth/MemberAuthController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\MemberRegistrationRequest;
use App\Http\Requests\Auth\OtpLoginRequest;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
class MemberAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Send OTP for Member Login or initiate registration.
     * @param OtpLoginRequest $request
     * @return JsonResponse
     */
public function sendOtp(OtpLoginRequest $request): JsonResponse
{
    try {
        $result = $this->authService->sendMemberLoginOtp($request->email);

        if ($result['action'] === 'otp_sent') {
            return response()->json([
                'message' => $result['message'],
                'action' => 'otp_sent'
            ], 200);
        }

        if ($result['action'] === 'existing_request') {
            return response()->json([
                'message' => $result['message'],
                'action' => 'existing_request',
                'status' => $result['status'],
                'details' => $result['details']
            ], 200);
        }

        // Jika action: registration_needed
        return response()->json([
            'message' => $result['message'],
            'action' => 'registration_needed'
        ], 200);
        
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengirim OTP. Silakan coba lagi.',
            'error' => $e->getMessage() // opsional untuk debugging
        ], 500);
    }
}


    /**
     * Verify OTP and Login Member.
     * @param OtpVerificationRequest $request
     * @return JsonResponse
     */
    public function login(OtpVerificationRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->memberLogin($request->email, $request->otp_code);
            return response()->json([
                'message' => 'Login berhasil.',
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal melakukan login. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Send OTP for email verification during registration.
     * @param OtpLoginRequest $request
     * @return JsonResponse
     */
public function sendRegistrationOtp(OtpLoginRequest $request): JsonResponse
{
    try {
        $result = $this->authService->sendRegistrationOtp($request->email);

        if ($result['action'] === 'existing_request') {
            return response()->json([
                'message' => $result['message'],
                'action' => 'existing_request',
                'status' => $result['status'],
            ], 200);
        }

        // Jika berhasil kirim OTP untuk registrasi baru
        return response()->json([
            'message' => 'Kode OTP verifikasi email telah dikirim. Mohon cek email Anda.',
            'action' => 'otp_sent'
        ], 200);

    } catch (ValidationException $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal mengirim OTP verifikasi. Silakan coba lagi.'
            // 'error' => $e->getMessage() // Aktifkan jika ingin debug
        ], 500);
    }
}


    /**
     * Verify OTP for email verification during registration.
     * @param OtpVerificationRequest $request
     * @return JsonResponse
     */
     public function verifyRegistrationEmail(OtpVerificationRequest $request): JsonResponse
    {
        try {
            if ($this->authService->verifyRegistrationEmail($request->email, $request->otp_code)) {
                return response()->json([
                    'message' => 'Email berhasil diverifikasi. Anda dapat melanjutkan pendaftaran.',
                    'email_verified' => true // Tambahkan ini untuk client
                ], 200);
            } else {
                // Tambahkan return di sini jika OTP tidak valid
                return response()->json([
                    'message' => 'Kode OTP salah atau sudah kadaluarsa.'
                ], 422); // Unprocessable Entity
            }
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Verifikasi email gagal. Silakan coba lagi.',
                'error_detail' => $e->getMessage() // Untuk debug, hapus di production
            ], 500);
        }
    }

    /**
     * Submit member registration form after email verification.
     * @param MemberRegistrationRequest $request
     * @return JsonResponse
     */
public function register(MemberRegistrationRequest $request): JsonResponse
{
    try {
        $data = $request->validated();

        if ($request->hasFile('slip_gaji_file')) {
            $filePath = $request->file('slip_gaji_file')->store('slip_gaji', 'public');
            $filepathreal = asset("https://ta.sunnysideup.my.id/storage/app/public/{$filePath}");
            $data['slip_gaji_path'] = $filepathreal;
        } else {
            return response()->json([
                'message' => 'File slip gaji wajib diunggah.'
            ], 422);
        }

        unset($data['slip_gaji_file']);

        $memberRequest = $this->authService->registerMemberRequest($data);

        return response()->json([
            'message' => 'Pengajuan pendaftaran berhasil dikirim. Silakan tunggu konfirmasi Admin.',
            'member_request' => $memberRequest
        ], 201);
    } catch (ValidationException $e) {
        return response()->json([
            'message' => $e->getMessage(),
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Gagal menyimpan data pendaftaran. Silakan coba lagi.',
            'error_detail' => $e->getMessage()
        ], 500);
    }
}
    /**
     * Logout Member.
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user('member')->currentAccessToken()->delete(); // Hapus token saat ini
        return response()->json([
            'message' => 'Logout berhasil.'
        ], 200);
    }
}