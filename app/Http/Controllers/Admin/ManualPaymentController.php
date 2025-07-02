<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ManualPaymentInstallment;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ManualPaymentController extends Controller
{
    public function index()
    {
        return ManualPaymentInstallment::with(['member', 'installments'])->latest()->get();
    }

    public function show($id)
    {
        $payment = ManualPaymentInstallment::with(['member', 'installments'])->findOrFail($id);
        return response()->json($payment);
    }

    public function confirm(Request $request, $id)
    {
        $data = $request->validate([
            'admin_note' => 'nullable|string',
            'admin_proof' => 'required|image|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $payment = ManualPaymentInstallment::with('installments')->findOrFail($id);

        if ($payment->status !== 'waiting') {
            return response()->json(['error' => 'Pembayaran ini sudah diproses.'], 422);
        }

        $adminProofPath = $request->file('admin_proof')->store('admin_mutations');

        DB::transaction(function () use ($payment, $data, $adminProofPath) {
            foreach ($payment->installments as $installment) {
                $installment->update([
                    'status' => 'paid',
                    'paid_at' => Carbon::now(),
                    'admin_note' => $data['admin_note'] ?? null,
                    'payment_proof' => $adminProofPath,
                ]);
            }

            $payment->update([
                'status' => 'confirmed',
            ]);
        });

        return response()->json(['message' => 'Pembayaran berhasil dikonfirmasi.']);
    }

    public function reject(Request $request, $id)
    {
        $data = $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $payment = ManualPaymentInstallment::with('installments')->findOrFail($id);

        if ($payment->status !== 'waiting') {
            return response()->json(['error' => 'Status pembayaran tidak valid untuk ditolak.'], 422);
        }

        DB::transaction(function () use ($payment, $data) {
            foreach ($payment->installments as $installment) {
                $installment->update(['status' => 'unpaid']);
            }

            $payment->update([
                'status' => 'rejected',
                'admin_note' => $data['reason'],
            ]);
        });

        return response()->json(['message' => 'Pembayaran manual ditolak.']);
    }
}
