<?php

namespace App\Traits;

use App\Mail\OtpMail;
use App\Models\Otp;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

trait SendsOtp
{
    /**
     * Generate, store, and send OTP to the given email.
     *
     * @param string $email
     * @param int $expiryMinutes
     * @return string
     * @throws \Exception
     */
    protected function sendOtp(string $email, int $expiryMinutes = 5): string
    {
        Otp::where('email', $email)
            ->where('expires_at', '>', Carbon::now())
            ->delete();

        $otpCode = mt_rand(100000, 999999);

        Otp::create([
            'email' => $email,
            'code' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes($expiryMinutes),
        ]);

        try {
            Mail::to($email)->send(new OtpMail($otpCode));
        } catch (\Exception $e) {
            logger()->error("Failed to send OTP to $email: " . $e->getMessage());
            throw new \Exception('Failed to send OTP. Please try again later.');
        }

        return (string) $otpCode;
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
            $otp->delete();
            return true;
        }

        return false;
    }
}