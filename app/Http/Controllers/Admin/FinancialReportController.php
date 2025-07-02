<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FinancialReport;
use App\Models\ProfitDistribution;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FinancialReportController extends Controller
{
    public function index()
    {
        return FinancialReport::withCount('profitDistributions')->latest()->get();
    }

public function store(Request $request)
{
    $data = $request->validate([
        'year' => 'required|integer|unique:financial_reports,year',
        'file' => 'required|file|mimes:pdf,xlsx',
        'total_income' => 'required|numeric|min:0',
        'description' => 'nullable|string',
    ]);

    // Simpan ke storage/app/public/financial_reports
    $path = $request->file('file')->store('financial_reports', 'public');

    $report = FinancialReport::create([
        'year' => $data['year'],
        'file_path' => $path, // Simpan path relatif dari folder 'public'
        'total_income' => $data['total_income'],
        'description' => $data['description'] ?? null,
    ]);

    return response()->json(['message' => 'Laporan berhasil ditambahkan.', 'data' => $report]);
}


    public function show($id)
    {
        return FinancialReport::with('profitDistributions')->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $report = FinancialReport::withCount('profitDistributions')->findOrFail($id);

        // Cegah update jika distribusi sudah dilakukan
        if ($report->profit_distributions_count > 0) {
            return response()->json([
                'error' => 'Laporan tidak dapat diedit karena keuntungan sudah dibagikan.'
            ], 403);
        }

        $data = $request->validate([
            'total_income' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'file' => 'nullable|file|mimes:pdf,xlsx',
        ]);

        if ($request->hasFile('file')) {
            if ($report->file_path && Storage::exists($report->file_path)) {
                Storage::delete($report->file_path);
            }

            $path = $request->file('file')->store('financial_reports');
            $report->file_path = $path;
        }

        $report->total_income = $data['total_income'];
        $report->description = $data['description'] ?? $report->description;
        $report->save();

        return response()->json([
            'message' => 'Laporan berhasil diperbarui.',
            'data' => $report
        ]);
    }


    public function distributeProfit($id)
    {
        $report = FinancialReport::findOrFail($id);

        DB::beginTransaction();
        try {
            $total_simpanan = Member::sum('current_balance');
            $members = Member::all();

            foreach ($members as $member) {
                $persentase = $member->current_balance / $total_simpanan;
                $jumlah = $persentase * $report->total_income;

                ProfitDistribution::create([
                    'member_id' => $member->id,
                    'financial_report_id' => $report->id,
                    'amount' => $jumlah,
                    'status' => 'pending',
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Distribusi keuntungan berhasil.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Gagal melakukan distribusi.'], 500);
        }
    }

    // Tambahan: download file laporan keuangan
    public function download($id)
    {
        $report = FinancialReport::findOrFail($id);

        if (!$report->file_path || !Storage::exists($report->file_path)) {
            return response()->json(['error' => 'File tidak ditemukan.'], 404);
        }

        return Storage::download($report->file_path);
    }
}
