<?php
// app/Http/Controllers/Auth/AdminAuthController.php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\OtpLoginRequest;
use App\Http\Requests\Auth\OtpVerificationRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Send OTP for Admin Login.
     * @param OtpLoginRequest $request
     * @return JsonResponse
     */
    public function sendOtp(OtpLoginRequest $request): JsonResponse
    {
        try {
            $this->authService->sendAdminLoginOtp($request->email);
            return response()->json([
                'message' => 'Kode OTP telah dikirim ke email Anda.'
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => $e->getMessage(),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal mengirim OTP. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Verify OTP and Login Admin.
     * @param OtpVerificationRequest $request
     * @return JsonResponse
     */
    public function login(OtpVerificationRequest $request): JsonResponse
    {
        try {
            $token = $this->authService->adminLogin($request->email, $request->otp_code);
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
     * Logout Admin.
     * @param Request $request
     * @return JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        $request->user('admin')->currentAccessToken()->delete(); // Hapus token saat ini
        return response()->json([
            'message' => 'Logout berhasil.'
        ], 200);
    }
}