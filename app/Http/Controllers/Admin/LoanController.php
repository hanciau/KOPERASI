<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoanApplication;
use App\Models\LoanInstallment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class LoanController extends Controller
{
    // [1] Lihat semua pinjaman
    public function index()
    {
        $loans = LoanApplication::with('member')->latest()->get();
        return response()->json($loans);
    }

    // [2] Detail pinjaman dan cicilan
    public function show($id)
    {
        $loan = LoanApplication::with(['member', 'installments'])->findOrFail($id);
        return response()->json($loan);
    }

    // [3] Set status interview
    public function markInterview($id)
    {
        $loan = LoanApplication::findOrFail($id);
        $loan->status = 'interview';
        $loan->save();

        return response()->json(['message' => 'Pinjaman ditandai sudah interview.']);
    }

public function approve(Request $request, $id)
{
    $loan = LoanApplication::findOrFail($id);

    $request->validate([
        'status' => 'required|in:approved,rejected',
        'rejection_reason' => 'nullable|string',
        'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
    ]);

    // === REJECT ===
    if ($request->status == 'rejected') {
        $loan->status = 'rejected';
        $loan->rejection_reason = $request->rejection_reason;
        $loan->save();
        return response()->json(['message' => 'Pinjaman ditolak.']);
    }

    // === APPROVE ===
    DB::transaction(function () use ($request, $loan) {
        $loan->status = 'approved';

        // Upload bukti serah terima jika ada
        if ($request->hasFile('proof')) {
            // Hapus file lama jika ada
            if ($loan->disbursement_proof && Storage::exists($loan->disbursement_proof)) {
                Storage::delete($loan->disbursement_proof);
            }

            $path = $request->file('proof')->store('disbursement_proofs');
            $loan->disbursement_proof = $path;
        }

        $loan->save();

        // Generate cicilan
        $startDate = now()->addMonth();
        for ($i = 1; $i <= $loan->tenor; $i++) {
            LoanInstallment::create([
                'loan_application_id' => $loan->id,
                'month_number' => $i,
                'due_date' => $startDate->copy()->addMonths($i - 1)->startOfMonth(),
                'amount' => round($loan->total_amount / $loan->tenor, 2),
            ]);
        }
    });

    return response()->json(['message' => 'Pinjaman disetujui dan bukti serah terima dicatat.']);
}

}
