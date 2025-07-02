<?php
// app/Traits/SendsOtp.php
namespace App\Traits;

use App\Mail\OtpMail;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

trait SendsOtp
{
    /**
     * Generate, store, and send OTP to the given email.
     *
     * @param string $email
     * @param int $expiryMinutes
     * @return string The generated OTP code.
     * @throws \Exception If OTP sending fails.
     */
    protected function sendOtp(string $email, int $expiryMinutes = 5): string
    {
        // Hapus OTP lama yang masih berlaku untuk email ini
        Otp::where('email', $email)->where('expires_at', '>', Carbon::now())->delete();

        // Generate a 6-digit OTP
        $otpCode = Str::random(6); // Atau gunakan mt_rand(100000, 999999); untuk digit
        // For development, you might want predictable OTPs:
        // $otpCode = '123456';

        // Store OTP in database
        Otp::create([
            'email' => $email,
            'code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
        ]);

        // Send OTP via email
        try {
            Mail::to($email)->send(new OtpMail($otpCode));
        } catch (\Exception $e) {
            // Log error, and potentially remove the stored OTP if sending failed
            logger()->error("Failed to send OTP to $email: " . $e->getMessage());
            // Optionally, delete the OTP from DB if email sending is critical
            // Otp::where('email', $email)->where('code', $otpCode)->delete();
            throw new \Exception('Failed to send OTP. Please try again later.');
        }

        return $otpCode;
    }

    /**
     * Verify the given OTP for the given email.
     *
     * @param string $email
     * @param string $otpCode
     * @return bool
     */
    protected function verifyOtp(string $email, string $otpCode): bool
    {
        $otp = Otp::where('email', $email)
                  ->where('code', $otpCode)
                  ->where('expires_at', '>', Carbon::now())
                  ->first();

        if ($otp) {
            // OTP is valid, delete it to prevent reuse
            $otp->delete();
            return true;
        }

        return false;
    }
}