<?php
// routes/api.php
use App\Http\Controllers\Auth\AdminAuthController;
use App\Http\Controllers\Auth\MemberAuthController;
use App\Http\Controllers\Admin\MemberRequestController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\AppReportController;
use App\Http\Controllers\Admin\AdminSalaryUpdateController;
use App\Http\Controllers\Admin\LoanController as AdminLoanController;
use App\Http\Controllers\Admin\ManualPaymentController as AdminManualPaymentController;
use App\Http\Controllers\Admin\AdminAccountClosureController;
use App\Http\Controllers\Admin\AdminSummaryController;
use App\Http\Controllers\Member\MemberAccountClosureController;
use App\Http\Controllers\Member\ProfitController;
use App\Http\Controllers\Member\SalaryUpdateController;
use App\Http\Controllers\Member\LoanController;
use App\Http\Controllers\Member\ManualPaymentInstallmentController;
use App\Http\Controllers\Member\ProfileController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request; // Make sure to import Request
use App\Models\Admin; // Import the Admin model
use App\Models\Member; // Import the Member model

Route::prefix('admin')->group(function () {
Route::get('admin/dashboard', [AdminSummaryController::class, 'index']);
Route::post('send-otp', [AdminAuthController::class, 'sendOtp']);
Route::post('login', [AdminAuthController::class, 'login']);
});
Route::prefix('admin')->middleware('auth:admin')->group(function () { // <-- Ganti 'abilities:admin' dengan ini
    Route::post('auth/admin/logout', [AdminAuthController::class, 'logout']);
    Route::get('admin/dashboard', function (Request $request) {
        /** @var Admin $user */
        $user = $request->user();
        return response()->json(['message' => 'Welcome Admin!', 'user' => $user]);
    });
    //registrasi
    Route::get('/member-requests', [MemberRequestController::class, 'index']); // Menampilkan semua pengajuan
    Route::get('/member-requests/{id}', [MemberRequestController::class, 'show']); // Menampilkan detail pengajuan
    Route::post('/member-requests/{id}/send-interview', [MemberRequestController::class, 'sendInterview']); // Kirim undangan interview
    Route::post('/member-requests/{id}/reject', [MemberRequestController::class, 'reject']); // Tolak dengan alasan
    Route::post('/member-requests/{id}/complete-interview', [MemberRequestController::class, 'completeInterview']);

    //update gaji terbaru
    Route::get('salary-requests', [AdminSalaryUpdateController::class, 'index']);
    Route::post('salary-requests/{id}/approve', [AdminSalaryUpdateController::class, 'approve']);
    Route::post('salary-requests/{id}/reject', [AdminSalaryUpdateController::class, 'reject']);

    //DOWNLOAD DAFTAR MEMBER UNTUK MEMOTONG GAJI DAN STOR SIMPANAN
    Route::get('/app-reports', [AppReportController::class, 'index']);
    Route::get('/app-reports/{id}/download', [AppReportController::class, 'download']);

    //pinjaman
    Route::get('/loans', [AdminLoanController::class, 'index']);
    Route::get('/loans/{id}', [AdminLoanController::class, 'show']);
    Route::post('/loans/{id}/mark-interview', [AdminLoanController::class, 'markInterview']);
    Route::post('/loans/{id}/approve', [AdminLoanController::class, 'approve']);

    //confirmation manual payment
    Route::get('manual-payment/', [AdminManualPaymentController::class, 'index']);
    Route::get('manual-payment/{id}', [AdminManualPaymentController::class, 'show']);
    Route::post('manual-payment/{id}/confirm', [AdminManualPaymentController::class, 'confirm']);
    Route::post('manual-payment/{id}/reject', [AdminManualPaymentController::class, 'reject']);

    //close akun
    Route::get('/account-closures', [AdminAccountClosureController::class, 'index']);
    Route::post('/account-closures/{id}/approve', [AdminAccountClosureController::class, 'approve']);
    Route::post('/account-closures/{id}/reject', [AdminAccountClosureController::class, 'reject']);

    //pembagian keuntungan dan la[poran tahunana
    Route::get('/financial-reports', [FinancialReportController::class, 'index']);
    Route::post('/financial-reports', [FinancialReportController::class, 'store']);
    Route::get('/financial-reports/{id}', [FinancialReportController::class, 'show']);
    Route::put('/financial-reports/{id}', [FinancialReportController::class, 'update']);
    Route::post('/financial-reports/{id}/distribute', [FinancialReportController::class, 'distributeProfit']);
    Route::get('/financial-reports/{id}/download', [FinancialReportController::class, 'download']);
});

Route::post('send-otp', [MemberAuthController::class, 'sendOtp']);
Route::post('login', [MemberAuthController::class, 'login']);
Route::post('register/send-otp', [MemberAuthController::class, 'sendRegistrationOtp']);
Route::post('register/verify-email', [MemberAuthController::class, 'verifyRegistrationEmail']);
Route::post('register', [MemberAuthController::class, 'register']);

Route::prefix('member')->middleware('auth:member')->group(function () { // <-- Ganti 'abilities:member' dengan ini
    //profil
    Route::get('profile', [ProfileController::class, 'getProfile']); 
    Route::get('saving-transactions', [ProfileController::class, 'getSavingTransactions']); // Riwayat transaksi simpanan

    //cek keuntungan dan memilih ambil atau simpan lagi
    Route::get('/profits', [ProfitController::class, 'index']); // Menampilkan semua laporan keuntungan
    Route::post('/profits/{reportId}/claim', [ProfitController::class, 'claim']); // Klaim manual
    Route::post('/profits/{reportId}/reinvest', [ProfitController::class, 'reinvest']); // Reinvestasi

    //menambahkan data gaji baru
    Route::post('salary-update', [SalaryUpdateController::class, 'store']);
    Route::get('salary-requests', [SalaryUpdateController::class, 'myRequests']);

    //pinjaman
    Route::get('/loan/limit', [LoanController::class, 'limit']);
    Route::post('/loan/apply', [LoanController::class, 'store']);
    Route::get('/loans', [LoanController::class, 'index']);
    Route::get('/loans/{id}', [LoanController::class, 'show']);

    //manual payment
    Route::post('/submit', [ManualPaymentInstallmentController::class, 'submit']);

    //close account
    Route::post('/account-closure', [MemberAccountClosureController::class, 'submit']);

    Route::post('auth/member/logout', [MemberAuthController::class, 'logout']);
    Route::get('member/dashboard', function (Request $request) {
        /** @var Member $user */
        $user = $request->user();
        return response()->json(['message' => 'Welcome Member!', 'user' => $user]);
    });
});

// Route test for public access
Route::get('/', function () {
    return response()->json(['message' => 'Koprasi Pesantren API is running!']);
});
