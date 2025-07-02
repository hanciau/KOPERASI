<?php

use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\{
    Member,
    MemberRequest,
    SavingTransaction,
    ProfitDistribution,
    LoanInstallment,
    AppReport,
    AccountClosureRequest,
    LoanApplication
};
use App\Mail\InterviewExpiredNotification;

/*
|--------------------------------------------------------------------------
| Laravel Task Scheduler via routes/console.php
|--------------------------------------------------------------------------
| Semua jadwal berkala yang dijalankan otomatis oleh Laravel
|
*/

/**
 * [1] Interview MemberRequest - Auto Reject setelah 5 hari
 */
Schedule::call(function () {
    $expiredRequests = MemberRequest::where('status', 'interview')
        ->where('interview_sent_at', '<', now()->subDays(5))
        ->get();

    foreach ($expiredRequests as $request) {
        $request->update([
            'status' => 'rejected',
            'reason' => 'Tidak menghadiri interview dalam 5 hari.'
        ]);

        Mail::to($request->email)->send(new InterviewExpiredNotification($request));
    }

    Log::info('⏰ Schedule: Interview kadaluarsa telah diproses.');
})->dailyAt('06:00');

/**
 * [2] Laporan PDF Member Aktif - Tanggal 25 setiap bulan
 */
Schedule::call(function () {
    $bulan = now()->month;
    $members = Member::where('status', 'active')->get();

    $html = view('exports.active_members_pdf', ['members' => $members])->render();
    $pdf = Pdf::loadHTML($html);
    $fileName = "laporan_member_{$bulan}.pdf";
    $filePath = "app_reports/{$fileName}";

    Storage::put($filePath, $pdf->output());

    AppReport::create([
        'file_path' => $filePath,
        'bulan' => $bulan,
        'description' => "Laporan member aktif bulan $bulan",
    ]);

    Log::info("📄 Laporan member aktif bulan $bulan berhasil dibuat.");
})->monthlyOn(25, '00:00');

/**
 * [3] Tambah Simpanan Wajib Otomatis - Tanggal 1 setiap bulan
 */
Schedule::call(function () {
    $today = now()->toDateString();
    $amount = 50000;
    $members = Member::where('status', 'active')->get();

    foreach ($members as $member) {
        $member->increment('current_balance', $amount);

        SavingTransaction::create([
            'member_id' => $member->id,
            'type' => 'monthly',
            'amount' => $amount,
            'transaction_date' => $today,
        ]);
    }

    Log::info("💰 Simpanan bulanan diproses tanggal $today.");
})->monthlyOn(1, '06:00');

/**
 * [4] Potong Gaji Otomatis untuk Cicilan - Tanggal 1
 */
Schedule::call(function () {
    $today = now()->toDateString();
    $members = Member::where('status', 'active')->get();

    foreach ($members as $member) {
        $installment = LoanInstallment::whereHas('loanApplication', function ($q) use ($member) {
            $q->where('member_id', $member->id);
        })
        ->where('status', 'unpaid')
        ->orderBy('due_date', 'asc')
        ->first();

        if ($installment && $member->salary >= $installment->amount) {
            $installment->update([
                'status' => 'potong_gaji',
                'paid_at' => now(),
            ]);

            Log::info("✅ Gaji member ID {$member->id} dipotong untuk cicilan ID {$installment->id}.");
        } elseif ($installment) {
            Log::warning("⚠️ Gaji member ID {$member->id} tidak cukup untuk cicilan ID {$installment->id}.");
        }
    }

    Log::info("📅 Potong gaji otomatis dijalankan tanggal $today.");
})->monthlyOn(1, '06:00');

/**
 * [5] Reinvest Profit Otomatis - Hanya tanggal 7 Januari
 */
Schedule::call(function () {
    $distributions = ProfitDistribution::whereIn('status', ['pending', 'waiting'])->get();

    foreach ($distributions as $distribution) {
        $member = Member::find($distribution->member_id);
        if (!$member) continue;

        $member->increment('current_balance', $distribution->amount);

        SavingTransaction::create([
            'member_id' => $member->id,
            'type' => 'reinvest',
            'amount' => $distribution->amount,
            'transaction_date' => now()->toDateString(),
        ]);

        $distribution->update(['status' => 'reinvest']);
    }

    Log::info("🔄 Distribusi keuntungan otomatis direinvest pada 7 Januari.");
})->yearlyOn(1, 7, '00:01');

/**
 * [6] Laporan Cicilan Terdekat - Tanggal 25
 */
Schedule::call(function () {
    $bulan = now()->month;
    $members = Member::where('status', 'active')->get();
    $data = [];

    foreach ($members as $member) {
        $installment = LoanInstallment::whereHas('loanApplication', function ($q) use ($member) {
            $q->where('member_id', $member->id);
        })
        ->where('status', 'unpaid')
        ->orderBy('due_date', 'asc')
        ->first();

        if ($installment) {
            $data[] = [
                'bulan' => $bulan,
                'member_name' => $member->name,
                'member_email' => $member->email,
                'loan_id' => $installment->loan_application_id,
                'installment_id' => $installment->id,
                'due_date' => $installment->due_date->format('Y-m-d'),
                'amount' => number_format($installment->amount, 0, ',', '.'),
                'status' => $installment->status,
            ];
        }
    }

    $html = view('exports.unpaid_installments_pdf', ['data' => $data])->render();
    $pdf = Pdf::loadHTML($html);
    $fileName = "laporan_cicilan_terdekat_{$bulan}.pdf";
    $filePath = "app_reports/{$fileName}";

    Storage::put($filePath, $pdf->output());

    AppReport::create([
        'file_path' => $filePath,
        'bulan' => $bulan,
        'description' => "Laporan cicilan belum dibayar bulan $bulan",
    ]);

    Log::info("📄 Laporan cicilan terdekat berhasil dibuat.");
})->monthlyOn(25, '00:01');

/**
 * [7] Laporan Tahunan Simpanan Member - Setiap 25 Desember
 */
Schedule::call(function () {
    $tahun = now()->year;
    $members = Member::where('status', 'active')->get();
    $total_simpanan = $members->sum('current_balance');
    $data = [];

    foreach ($members as $member) {
        $persentase = $total_simpanan > 0 ? ($member->current_balance / $total_simpanan) * 100 : 0;

        $data[] = [
            'name' => $member->name,
            'email' => $member->email,
            'current_balance' => number_format($member->current_balance, 0, ',', '.'),
            'persentase' => number_format($persentase, 2),
        ];
    }

    $html = view('exports.member_saving_yearly_pdf', [
        'data' => $data,
        'tahun' => $tahun,
        'total_simpanan' => number_format($total_simpanan, 0, ',', '.')
    ])->render();

    $pdf = Pdf::loadHTML($html);
    $fileName = "laporan_simpanan_member_tahunan_{$tahun}.pdf";
    $filePath = "app_reports/{$fileName}";

    Storage::put($filePath, $pdf->output());

    AppReport::create([
        'file_path' => $filePath,
        'bulan' => 12,
        'description' => "Laporan Tahunan Simpanan Member Tahun $tahun",
    ]);

    Log::info("📄 Laporan tahunan simpanan member tahun $tahun berhasil dibuat.");
})->yearlyOn(12, 25, '00:01');

/**
 * [8] Penutupan Akun - Interview Tidak Hadir > 5 Hari
 */
Schedule::call(function () {
    $expired = AccountClosureRequest::where('status', 'interview')
        ->where('interview_sent_at', '<', now()->subDays(5))
        ->get();

    foreach ($expired as $req) {
        $req->update([
            'status' => 'rejected',
            'admin_reason' => 'Tidak datang interview dalam 5 hari.',
        ]);
    }

    Log::info("🛑 Penutupan akun yang tidak datang interview otomatis ditolak.");
})->dailyAt('06:00');

/**
 * [9] Interview Pinjaman Tidak Hadir > 5 Hari → Reject
 */
Schedule::call(function () {
    $expiredInterviews = LoanApplication::where('status', 'interview')
        ->where('interview_sent_at', '<', now()->subDays(5))
        ->get();

    foreach ($expiredInterviews as $application) {
        $application->update([
            'status' => 'rejected',
            'admin_reason' => 'Tidak hadir interview lebih dari 5 hari.',
        ]);

        Log::info("⛔ Pengajuan pinjaman ID {$application->id} otomatis ditolak karena tidak hadir interview.");
    }

    Log::info('🕒 Scheduler: Pemeriksaan pengajuan pinjaman interview yang kadaluarsa selesai dijalankan.');
})->dailyAt('06:00');
