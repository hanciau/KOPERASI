<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use App\Models\Admin;
use App\Models\Member;
use Illuminate\Support\Facades\Log; // <<< Pastikan ini di-import

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        Log::info('--- CheckUserRole Middleware START ---');
        Log::info('Request URL: ' . $request->fullUrl());
        Log::info('Requested roles: ' . implode(', ', $roles));

        // Debug: Dump all currently configured guards
        // Log::info('Configured Guards: ' . json_encode(config('auth.guards')));

        // 1. Pastikan pengguna terautentikasi oleh Sanctum
        if (! $request->user()) {
            Log::warning('CheckUserRole Middleware: User NOT authenticated by Sanctum.');
            Log::info('--- CheckUserRole Middleware END (Auth Failed) ---');
            throw new AuthenticationException('Unauthenticated. Please log in.');
        }

        // 2. Dapatkan objek pengguna yang terautentikasi
        $user = $request->user();
        Log::info('CheckUserRole Middleware: User authenticated.');
        Log::info('User Class: ' . get_class($user)); // <<< Ini sangat penting
        Log::info('User ID: ' . $user->id); // <<< Ini juga penting
        Log::info('User Email: ' . $user->email); // <<< Dan ini

        // 3. Periksa apakah pengguna memiliki salah satu peran yang diizinkan
        $hasRequiredRole = false;
        foreach ($roles as $role) {
            Log::info("CheckUserRole Middleware: Evaluating role '{$role}' against authenticated user class " . get_class($user));
            if ($role === 'admin') {
                if ($user instanceof Admin) {
                    $hasRequiredRole = true;
                    Log::info('CheckUserRole Middleware: User IS an Admin (instanceof check passed). Access granted.');
                    break;
                } else {
                    Log::info('CheckUserRole Middleware: User is NOT an Admin (instanceof check failed).');
                }
            }
            if ($role === 'member') {
                if ($user instanceof Member) {
                    $hasRequiredRole = true;
                    Log::info('CheckUserRole Middleware: User IS a Member (instanceof check passed). Access granted.');
                    break;
                } else {
                    Log::info('CheckUserRole Middleware: User is NOT a Member (instanceof check failed).');
                }
            }
        }

        // 4. Jika pengguna tidak memiliki peran yang dibutuhkan, tolak akses
        if (! $hasRequiredRole) {
            Log::error('CheckUserRole Middleware: Authorization FAILED. User does not have required role.');
            Log::info('--- CheckUserRole Middleware END (Authorization Failed) ---');
            throw new AuthorizationException('You do not have the necessary permissions to access this resource.');
        }

        // Jika semua cek lolos, lanjutkan request
        Log::info('CheckUserRole Middleware: Authorization PASSED. Continuing request.');
        Log::info('--- CheckUserRole Middleware END (Success) ---');
        return $next($request);
    }
}