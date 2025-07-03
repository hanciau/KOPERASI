<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialReport;
use App\Models\ProfitDistribution;
use App\Models\SavingTransaction;
use App\Models\Member;
use Illuminate\Support\Facades\DB;

class ProfitController extends Controller
{
    // Menampilkan semua laporan keuntungan + status member
    public function index()
    {
        $member = auth('member')->user();

        $reports = FinancialReport::with(['profitDistributions' => function ($q) use ($member) {
            $q->where('member_id', $member->id);
        }])->orderBy('year', 'desc')->get();

        $data = $reports->map(function ($report) use ($member) {
            $distribution = $report->profitDistributions->first();
            return [
                'id' => $report->id,
                'year' => $report->year,
                'description' => $report->description,
                'total_income' => $report->total_income,
                'file_url' => $report->file_path ? asset($report->file_path) : null,
                'status' => $distribution ? $distribution->status : 'not_assigned',
                'amount' => $distribution ? $distribution->amount : 0,
            ];
        });


        return response()->json($data);
    }

    // Klaim keuntungan: ubah status menjadi "waiting"
    public function claim($reportId)
    {
        $member = auth('member')->user();

        $distribution = ProfitDistribution::where('member_id', $member->id)
            ->where('financial_report_id', $reportId)
            ->where('status', 'pending')
            ->firstOrFail();

        $distribution->status = 'menunggu';
        $distribution->save();

        return response()->json([
            'message' => 'Permintaan pencairan telah diterima. Silakan datang ke koperasi sebelum 6 Januari.'
        ]);
    }

    // Reinvest keuntungan ke tabungan
    public function reinvest($reportId)
    {
        $member = auth('member')->user();

        $distribution = ProfitDistribution::where('member_id', $member->id)
            ->where('financial_report_id', $reportId)
            ->where('status', 'pending')
            ->firstOrFail();

        DB::transaction(function () use ($distribution, $member) {
            // Update saldo
            Member::where('id', $member->id)->increment('current_balance', $distribution->amount);

            // Catat transaksi
            SavingTransaction::create([
                'member_id' => $member->id,
                'type' => 'reinvest',
                'amount' => $distribution->amount,
                'transaction_date' => now()->toDateString(),
            ]);

            // Update status distribusi
            $distribution->status = 'reinvested';
            $distribution->save();
        });

        return response()->json(['message' => 'Keuntungan berhasil direinvestasikan.']);
    }
}
