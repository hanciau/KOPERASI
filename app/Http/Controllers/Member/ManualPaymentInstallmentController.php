<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\LoanInstallment;
use App\Models\ManualPaymentInstallment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ManualPaymentInstallmentController extends Controller
{
public function submit(Request $request)
{
    $member = auth('member')->user();

    $data = $request->validate([
        'installment_ids' => 'required|array|min:1',
        'installment_ids.*' => 'exists:loan_installments,id',
        'payment_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:2048',
    ]);

    $installments = LoanInstallment::whereIn('id', $data['installment_ids'])
        ->where('status', 'unpaid')
        ->whereHas('loanApplication', function ($q) use ($member) {
            $q->where('member_id', $member->id);
        })->get();

    if ($installments->isEmpty()) {
        return response()->json(['error' => 'Cicilan tidak valid atau sudah dibayar.'], 422);
    }

    // Upload bukti ke public storage
    $path = $request->file('payment_proof')->store('manual_payments', 'public');
    $url = asset("https://ta.sunnysideup.my.id/storage/app/public/{$path}");

    DB::transaction(function () use ($installments, $url, $member) {
        $manualPayment = ManualPaymentInstallment::create([
            'member_id' => $member->id,
            'proof_path' => $url,
            'status' => 'waiting',
        ]);

        $manualPayment->installments()->attach($installments->pluck('id'));

        foreach ($installments as $inst) {
            $inst->update(['status' => 'waiting']);
        }
    });

    return response()->json([
        'message' => 'Pembayaran manual dikirim. Menunggu verifikasi admin.',
    ]);
}

}
